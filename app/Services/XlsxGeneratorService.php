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

        foreach ($fields as $field) {
            $cellRef = $field->cell_ref;
            $value   = '';

            // Handle picture field (draggable overlay)
            if ($field->field_type === 'picture') {
                if (isset($answers[$field->id])) {
                    $data = json_decode($answers[$field->id]->value, true);
                    if ($data && !empty($data['url']) && !empty($data['cell_ref'])) {
                        $urlPath = parse_url($data['url'], PHP_URL_PATH);
                        $filename = basename($urlPath);
                        $localPath = public_path('storage/ipcrf_images/' . $filename);
                        
                        if (file_exists($localPath)) {
                            try {
                                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                $drawing->setName($field->field_label ?: 'User Picture');
                                $drawing->setPath($localPath);
                                $drawing->setCoordinates($data['cell_ref']);
                                $drawing->setOffsetX($data['offsetX'] ?? 0);
                                $drawing->setOffsetY($data['offsetY'] ?? 0);
                                if (!empty($data['width']))  $drawing->setWidth($data['width']);
                                if (!empty($data['height'])) $drawing->setHeight($data['height']);
                                $drawing->setWorksheet($worksheet);
                            } catch (\Exception $e) {
                                \Log::error('Failed to inject user picture: ' . $e->getMessage());
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

            if ($field->field_type === 'signature') {
                // For signature, write the name as text (canvas signatures future enhancement)
                $value = $user->full_name ?? $user->name;
            }

            $worksheet->setCellValue($cellRef, $value);
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
            'autofill_position'   => $user->position?->name ?? '',
            'autofill_department' => $user->department ?? $user->office ?? '',
            'autofill_date'       => Carbon::now()->format('F d, Y'),
            default               => '',
        };
    }
}
