<?php

namespace App\Services;

use App\Models\IpcrfSubmission;
use App\Models\IpcrfTemplate;
use App\Models\TemplateField;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class XlsxGeneratorService
{
    /**
     * Generate a filled XLSX for a submission, save to storage, and return the path.
     */
    public function generate(IpcrfSubmission $submission): string
    {
        ini_set('memory_limit', '512M');
        $template = $submission->template;
        $user     = $submission->user;

        // Load the original template file
        $templatePath = Storage::disk('private')->path($template->file_path);
        $spreadsheet  = IOFactory::load($templatePath);
        $worksheet    = $spreadsheet->getActiveSheet();

        // Load all fields and answers
        $fields  = $template->fields()->get()->keyBy('id');
        $answers = $submission->answers()->with('field')->get()->keyBy('template_field_id');
        $sheetData = $template->sheet_data ?? [];

        // 1. Sync merged cells from DB
        $dbMerges = $template->merged_cells ?? [];
        if (!empty($dbMerges)) {
            $currentMerges = $worksheet->getMergeCells();
            foreach ($currentMerges as $mergeRange) {
                $worksheet->unmergeCells($mergeRange);
            }
            foreach ($dbMerges as $mergeRange => $val) {
                try {
                    $worksheet->mergeCells($mergeRange);
                } catch (\Exception $e) {}
            }
        }

        // 2. Sync sheet data text, alignment, and wrap text
        foreach ($sheetData as $row) {
            foreach ($row as $cell) {
                if (!isset($cell['cell_ref'])) continue;
                $cellRef = $cell['cell_ref'];

                // Apply text modifications made in the Builder
                if (isset($cell['value']) && $cell['value'] !== '') {
                    $worksheet->setCellValue($cellRef, $cell['value']);
                }

                // Apply alignment
                if (isset($cell['style']['h_align'])) {
                    $alignValue = match ($cell['style']['h_align']) {
                        'left' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'right' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        'center' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        default => null,
                    };
                    if ($alignValue) {
                        $worksheet->getStyle($cellRef)->getAlignment()->setHorizontal($alignValue);
                    }
                }

                // Enable wrap text for any cell that has text to prevent cropping
                if (!empty($cell['value']) || !empty($cell['raw_value'])) {
                    $worksheet->getStyle($cellRef)->getAlignment()->setWrapText(true);
                }
            }
        }

        foreach ($fields as $field) {
            $cellRef = $field->cell_ref;
            $value   = '';

            // Handle picture and signature fields (draggable image overlays)
            if (in_array($field->field_type, ['picture', 'signature', 'autofill_division_chief_signature', 'autofill_approving_authority_signature'], true)) {
                if (isset($answers[$field->id])) {
                    $data = json_decode($answers[$field->id]->value, true);
                    if ($data && !empty($data['url']) && !empty($data['cell_ref'])) {
                        $urlPath = parse_url($data['url'], PHP_URL_PATH);
                        $filename = basename($urlPath);
                        $localPath = public_path('storage/ipcrf_images/' . $filename);
                        
                        if (file_exists($localPath)) {
                            try {
                                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                $drawing->setName($field->field_label ?: 'User Image');
                                $drawing->setPath($localPath);
                                $drawing->setCoordinates($data['cell_ref']);
                                $drawing->setOffsetX($data['offsetX'] ?? 0);
                                $drawing->setOffsetY($data['offsetY'] ?? 0);
                                if (!empty($data['width']))  $drawing->setWidth($data['width']);
                                if (!empty($data['height'])) {
                                    $drawing->setHeight($data['height']);
                                    // Make sure row is tall enough for the image
                                    $rowNum = Coordinate::coordinateFromString($data['cell_ref'])[1];
                                    $currentHeight = $worksheet->getRowDimension($rowNum)->getRowHeight();
                                    $neededHeight = $data['height'] * 0.75 + 5;
                                    if ($currentHeight == -1 || $currentHeight < $neededHeight) {
                                        $worksheet->getRowDimension($rowNum)->setRowHeight($neededHeight);
                                    }
                                }
                                $drawing->setWorksheet($worksheet);
                            } catch (\Exception $e) {
                                \Log::error('Failed to inject user image: ' . $e->getMessage());
                            }
                        }
                    }
                }
                continue; // Skip standard cell value setting
            }

            if ($field->isAutofill()) {
                $value = $this->resolveAutofill($field->field_type, $user);
            } elseif (isset($answers[$field->id])) {
                $value = $answers[$field->id]->value ?? '';
            }

            $worksheet->setCellValue($cellRef, $value);

            // Enable wrap text and auto row height (stretch downward) for text fields
            if (in_array($field->field_type, [
                'text', 'textarea', 'autofill_name', 'autofill_position', 'autofill_department',
                'autofill_division_chief', 'autofill_approving_authority',
                'autofill_division_chief_position', 'autofill_approving_authority_position'
            ], true)) {
                $worksheet->getStyle($cellRef)->getAlignment()->setWrapText(true);
                $rowNum = Coordinate::coordinateFromString($cellRef)[1];
                $worksheet->getRowDimension($rowNum)->setRowHeight(-1);
            }
        }

        // Write to a temp file
        $outputDir  = 'generated_submissions';
        $outputName = 'submission_' . $submission->id . '_' . time() . '.xlsx';
        $outputPath = $outputDir . '/' . $outputName;

        if (!Storage::disk('private')->exists($outputDir)) {
            Storage::disk('private')->makeDirectory($outputDir);
        }

        $fullPath = Storage::disk('private')->path($outputPath);
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($fullPath);

        return $outputPath;
    }

    /**
     * Stream a generated XLSX as a download response.
     */
    public function download(IpcrfSubmission $submission): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$submission->generated_file_path || !Storage::disk('private')->exists($submission->generated_file_path)) {
            $path = $this->generate($submission);
            $submission->update(['generated_file_path' => $path]);
        }

        $filename = 'IPCRF_' . str_replace(' ', '_', $submission->user->name ?? 'user') . '_' . $submission->id . '.xlsx';

        return Storage::disk('private')->download($submission->generated_file_path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function resolveAutofill(string $type, User $user): string
    {
        return match ($type) {
            'autofill_name'       => $user->full_name ?? $user->name ?? '',
            'autofill_position'   => $user->jobPosition?->name ?? '',
            'autofill_department' => $user->department ?? $user->office ?? '',
            'autofill_date'       => Carbon::now()->format('F d, Y'),
            default               => '',
        };
    }
}
