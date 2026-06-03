<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Settings;

class TemplateParserService
{
    /**
     * Configure PhpSpreadsheet to use disk-based cell cache to reduce RAM usage.
     */
    private function configureCellCache(): void
    {
        try {
            $cacheDir = storage_path('framework/cache/spreadsheet');
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
                'phpspreadsheet',
                3600,
                $cacheDir
            );
            Settings::setCache($cache);
        } catch (\Throwable $e) {
            // Cache not available, fall back to in-memory
        }
    }

    /**
     * Parse an uploaded XLSX file and return a structured JSON representation.
     */
    public function parse(string $filePath, bool $dataOnly = false): array
    {
        ini_set('memory_limit', '2G');
        $this->configureCellCache();

        $reader = IOFactory::createReaderForFile($filePath);
        if ($dataOnly) {
            $reader->setReadDataOnly(true);
        }
        $spreadsheet = $reader->load($filePath);
        $worksheet   = $spreadsheet->getActiveSheet();

        $highestRow    = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();
        $highestColIdx = Coordinate::columnIndexFromString($highestColumn);

        // Extract drawings
        $drawingsMap = [];
        try {
            $publicDir = public_path('storage/ipcrf_images');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            foreach ($worksheet->getDrawingCollection() as $drawing) {
                $coordinates = $drawing->getCoordinates();
                $contents = null;
                $filename = '';

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                    $path = $drawing->getPath();
                    if (file_exists($path)) {
                        $contents = @file_get_contents($path);
                        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
                        $filename = uniqid('img_', true) . '.' . $extension;
                    }
                } elseif ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $contents = ob_get_contents();
                    ob_end_clean();

                    $mimeType = $drawing->getMimeType();
                    $extension = match ($mimeType) {
                        \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG => 'jpg',
                        \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF => 'gif',
                        default => 'png',
                    };
                    $filename = uniqid('img_', true) . '.' . $extension;
                }

                if ($contents && $filename) {
                    @file_put_contents($publicDir . '/' . $filename, $contents);
                    $drawingsMap[$coordinates][] = [
                        'url' => asset('storage/ipcrf_images/' . $filename),
                        'width' => $drawing->getWidth(),
                        'height' => $drawing->getHeight(),
                        'offsetX' => $drawing->getOffsetX(),
                        'offsetY' => $drawing->getOffsetY(),
                        'name' => $drawing->getName(),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Drawing extraction failed: ' . $e->getMessage());
        }

        // Build merged cell map (cell_ref => [rowspan, colspan])
        $mergedCellRanges = $worksheet->getMergeCells();
        $mergedMap        = $this->buildMergedCellMap($mergedCellRanges);

        $rows = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $highestColIdx; $col++) {
                $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
                $cell    = $worksheet->getCell($cellRef);
                $style   = $worksheet->getStyle($cellRef);

                $mergeInfo = $mergedMap[$cellRef] ?? null;
                $isHidden  = isset($mergedMap[$cellRef . '_hidden']);

                $rowData[] = [
                    'cell_ref'   => $cellRef,
                    'row'        => $row,
                    'col'        => $col,
                    'col_letter' => Coordinate::stringFromColumnIndex($col),
                    'value'      => $cell->getFormattedValue(),
                    'raw_value'  => $cell->getValue(),
                    'rowspan'    => $mergeInfo ? $mergeInfo['rowspan'] : 1,
                    'colspan'    => $mergeInfo ? $mergeInfo['colspan'] : 1,
                    'hidden'     => $isHidden,
                    'style'      => $this->extractStyle($style, $worksheet, $row, $col),
                    'drawings'   => $drawingsMap[$cellRef] ?? null,
                ];
            }
            $rows[] = $rowData;
        }

        // Extract column widths
        $colWidths = [];
        for ($col = 1; $col <= $highestColIdx; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $colDim    = $worksheet->getColumnDimension($colLetter);
            $width     = $colDim->getWidth();
            $colWidths[$col] = $width > 0 ? round($width * 7) : 80; // px approximation
        }

        // Extract row heights
        $rowHeights = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowDim         = $worksheet->getRowDimension($row);
            $height         = $rowDim->getRowHeight();
            $rowHeights[$row] = $height > 0 ? round($height * 1.33) : 20; // px approximation
        }

        $result = [
            'rows'         => $rows,
            'merged_cells' => $mergedCellRanges,
            'col_widths'   => $colWidths,
            'row_heights'  => $rowHeights,
            'total_rows'   => $highestRow,
            'total_cols'   => $highestColIdx,
            'sheet_name'   => $worksheet->getTitle(),
        ];

        // Free memory immediately
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $worksheet);

        return $result;
    }

    /**
     * Build a map of merged cells: primary cell => {rowspan, colspan}, hidden cells => _hidden flag.
     */
    private function buildMergedCellMap(array $mergedRanges): array
    {
        $map = [];
        foreach ($mergedRanges as $range => $_) {
            [$start, $end] = explode(':', $range);
            [$startCol, $startRow] = Coordinate::coordinateFromString($start);
            [$endCol, $endRow]     = Coordinate::coordinateFromString($end);

            $startColIdx = Coordinate::columnIndexFromString($startCol);
            $endColIdx   = Coordinate::columnIndexFromString($endCol);
            $rowspan = (int)$endRow - (int)$startRow + 1;
            $colspan = $endColIdx - $startColIdx + 1;

            // Primary cell gets rowspan/colspan
            $map[$start] = ['rowspan' => $rowspan, 'colspan' => $colspan];

            // All other cells in the range are hidden
            for ($r = (int)$startRow; $r <= (int)$endRow; $r++) {
                for ($c = $startColIdx; $c <= $endColIdx; $c++) {
                    $ref = Coordinate::stringFromColumnIndex($c) . $r;
                    if ($ref !== $start) {
                        $map[$ref . '_hidden'] = true;
                    }
                }
            }
        }
        return $map;
    }

    /**
     * Extract relevant style info from a cell for HTML rendering.
     */
    private function extractStyle($style, Worksheet $ws, int $row, int $col): array
    {
        $font       = $style->getFont();
        $fill       = $style->getFill();
        $alignment  = $style->getAlignment();
        $borders    = $style->getBorders();

        $bgColor = '';
        if ($fill->getFillType() !== Fill::FILL_NONE) {
            $color   = $fill->getStartColor()->getRGB();
            if ($color && $color !== '000000' && $color !== 'FFFFFF' && strlen($color) === 6) {
                $bgColor = '#' . $color;
            }
        }

        return [
            'bold'       => $font->getBold(),
            'italic'     => $font->getItalic(),
            'underline'  => $font->getUnderline() !== 'none',
            'font_size'  => $font->getSize(),
            'font_color' => $font->getColor()->getRGB() ? '#' . $font->getColor()->getRGB() : '#000000',
            'bg_color'   => $bgColor,
            'h_align'    => $alignment->getHorizontal() ?: 'left',
            'v_align'    => $alignment->getVertical() ?: 'top',
            'wrap_text'  => $alignment->getWrapText(),
            'border_top'    => $this->borderStyle($borders->getTop()),
            'border_bottom' => $this->borderStyle($borders->getBottom()),
            'border_left'   => $this->borderStyle($borders->getLeft()),
            'border_right'  => $this->borderStyle($borders->getRight()),
        ];
    }

    private function borderStyle($border): string
    {
        $style = $border->getBorderStyle();
        if (!$style || $style === Border::BORDER_NONE) {
            return 'none';
        }
        $color = $border->getColor()->getRGB();
        $color = $color ? '#' . $color : '#000000';
        $thickness = in_array($style, [Border::BORDER_MEDIUM, Border::BORDER_THICK]) ? '2px' : '1px';
        return "{$thickness} solid {$color}";
    }

    /**
     * Generate an HTML table string for preview (used in builder and user form views).
     */
    public function toHtmlTable(array $parsedData, array $mappedFields = [], bool $editable = false, bool $adminMode = false): string
    {
        $rows       = $parsedData['rows'];
        $colWidths  = $parsedData['col_widths'];
        $rowHeights = $parsedData['row_heights'];

        // Build field map: cell_ref => field
        $fieldMap = [];
        foreach ($mappedFields as $field) {
            $fieldMap[$field['cell_ref']] = $field;
        }

        $html = '<table class="ipcrf-preview-table" cellspacing="0" cellpadding="0"><colgroup>';

        // Add row header col (1, 2, 3... row numbers col)
        $html .= '<col style="width:40px">';

        // Column widths
        foreach ($colWidths as $width) {
            $html .= '<col style="width:' . $width . 'px">';
        }
        $html .= '</colgroup><tbody>';

        // Render top column header row (A, B, C...)
        $html .= '<tr style="height:24px">';
        $html .= '<td class="ipcrf-hdr-corner"></td>'; // Top-left corner cell
        $totalCols = count($colWidths);
        for ($c = 1; $c <= $totalCols; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $html .= '<td class="ipcrf-hdr-col" data-col-idx="' . $c . '">' 
                  . $colLetter 
                  . '<div class="col-resizer"></div>'
                  . '</td>';
        }
        $html .= '</tr>';

        foreach ($rows as $rowIdx => $rowCells) {
            $rowNum    = $rowIdx + 1;
            $rowHeight = $rowHeights[$rowNum] ?? 20;
            $html .= '<tr style="height:' . $rowHeight . 'px">';

            // Prepend row header (1, 2, 3...)
            $html .= '<td class="ipcrf-hdr-row" data-row-idx="' . $rowNum . '">' 
                  . $rowNum 
                  . '<div class="row-resizer"></div>'
                  . '</td>';

            foreach ($rowCells as $cell) {
                if ($cell['hidden']) continue;

                $cellRef = $cell['cell_ref'];
                $style   = $cell['style'];
                $css     = $this->buildCss($style);

                $cellText = htmlspecialchars((string)($cell['value'] ?? ''));
                $attrs = 'data-cell="' . $cellRef . '" data-row="' . $cell['row'] . '" data-col="' . $cell['col'] . '" data-text="' . $cellText . '"';
                if ($cell['rowspan'] > 1) $attrs .= ' rowspan="' . $cell['rowspan'] . '"';
                if ($cell['colspan'] > 1) $attrs .= ' colspan="' . $cell['colspan'] . '"';

                $drawingsHtml = '';
                if (!empty($cell['drawings'])) {
                    $css .= 'position:relative;';
                    foreach ($cell['drawings'] as $img) {
                        $imgUrl = htmlspecialchars($img['url']);
                        $w = $img['width'] ? $img['width'] . 'px' : 'auto';
                        $h = $img['height'] ? $img['height'] . 'px' : 'auto';
                        $ox = $img['offsetX'] ? $img['offsetX'] . 'px' : '0px';
                        $oy = $img['offsetY'] ? $img['offsetY'] . 'px' : '0px';
                        
                        $drawingsHtml .= '<img src="' . $imgUrl . '" style="position:absolute; left:' . $ox . '; top:' . $oy . '; width:' . $w . '; height:' . $h . '; z-index:50; pointer-events:none; max-width:none;" alt="logo" />';
                    }
                }

                $content = '';
                if (isset($fieldMap[$cellRef])) {
                    $field   = $fieldMap[$cellRef];
                    if (in_array($field['field_type'], ['text', 'textarea', 'autofill_name', 'autofill_position', 'autofill_department', 'autofill_division_chief', 'autofill_approving_authority', 'autofill_division_chief_position', 'autofill_approving_authority_position'], true)) {
                        $css .= 'white-space:pre-wrap !important;word-wrap:break-word;';
                    }
                    if ($editable) {
                        $content = $this->renderFieldBadge($field, true);
                    } else {
                        // Read-only rendering of user values
                        $val = $field['current_value'] ?? '';
                        
                        $isAdminEditable = $adminMode && in_array($field['field_type'], [
                            'autofill_division_chief',
                            'autofill_approving_authority',
                            'autofill_division_chief_position',
                            'autofill_approving_authority_position',
                            'autofill_division_chief_signature',
                            'autofill_approving_authority_signature'
                        ]);
                        
                        if ($isAdminEditable) {
                            if ($field['field_type'] === 'autofill_division_chief' || $field['field_type'] === 'autofill_approving_authority') {
                                $content = '<input type="text" class="admin-form-input w-full h-full bg-blue-50/50 hover:bg-blue-50 focus:bg-white px-1 border-none text-center" data-field-id="' . $field['id'] . '" value="' . htmlspecialchars((string)$val) . '" placeholder="Enter Name">';
                            } elseif ($field['field_type'] === 'autofill_division_chief_position' || $field['field_type'] === 'autofill_approving_authority_position') {
                                // Position fields
                                $positionsList = \Illuminate\Support\Facades\DB::table('positions')->where('is_active', true)->orderBy('name')->pluck('name')->toArray();
                                $content = '<select class="admin-form-input w-full h-full bg-blue-50/50 hover:bg-blue-50 focus:bg-white px-1 border-none text-center" data-field-id="' . $field['id'] . '">';
                                $content .= '<option value="">-- Select Position --</option>';
                                foreach ($positionsList as $pos) {
                                    $selected = ($pos === $val) ? 'selected' : '';
                                    $content .= '<option value="' . htmlspecialchars($pos) . '" ' . $selected . '>' . htmlspecialchars($pos) . '</option>';
                                }
                                $content .= '</select>';
                            } else {
                                // Admin signature upload
                                $imgUrl = '';
                                if (!empty($val)) {
                                    $decoded = json_decode($val, true);
                                    if (is_array($decoded) && isset($decoded['url'])) {
                                        $imgUrl = $decoded['url'];
                                    } elseif (str_starts_with($val, 'http') || str_starts_with($val, '/')) {
                                        $imgUrl = $val;
                                    }
                                }

                                $content = '<div class="admin-sig-wrapper w-full h-full flex items-center justify-center relative cursor-pointer min-h-[40px] p-1" data-field-id="' . $field['id'] . '" data-cell-ref="' . $field['cell_ref'] . '">';
                                if (!empty($imgUrl)) {
                                    $content .= '<img src="' . htmlspecialchars($imgUrl) . '" style="max-height:40px; max-width:100%; display:block; margin:0 auto;" />';
                                    $content .= '<span class="absolute top-0 right-0 bg-gray-800/80 text-white text-[8px] px-1 rounded hover:bg-black pointer-events-none">Replace</span>';
                                } else {
                                    $content .= '<span class="text-indigo-600 text-[10px] font-semibold hover:text-indigo-800 pointer-events-none"><i class="fas fa-signature mr-1"></i>Upload Sig</span>';
                                }
                                $content .= '<input type="file" class="admin-sig-file-input hidden" accept=".png" />';
                                $content .= '</div>';
                            }
                        } else {
                            $isImageField = in_array($field['field_type'], [
                                'picture', 'signature', 'autofill_division_chief_signature', 'autofill_approving_authority_signature'
                            ], true);

                            if ($isImageField) {
                                $imgUrl = '';
                                if (!empty($val)) {
                                    $decoded = json_decode($val, true);
                                    if (is_array($decoded) && isset($decoded['url'])) {
                                        $imgUrl = $decoded['url'];
                                    } elseif (str_starts_with($val, 'http') || str_starts_with($val, '/')) {
                                        $imgUrl = $val;
                                    }
                                }
                                if (!empty($imgUrl)) {
                                    $imgStyle = 'max-height:40px; max-width:100%; display:block;';
                                    $hAlign = $style['h_align'] ?? 'center';
                                    if ($hAlign === 'right') {
                                        $imgStyle .= 'margin-left:auto; margin-right:0;';
                                    } elseif ($hAlign === 'left') {
                                        $imgStyle .= 'margin-left:0; margin-right:auto;';
                                    } else {
                                        $imgStyle .= 'margin:0 auto;';
                                    }
                                    $content = '<img src="' . htmlspecialchars($imgUrl) . '" class="user-uploaded-picture" style="' . $imgStyle . '" />';
                                } else {
                                    $content = '<span class="text-gray-400 text-xs italic">[No Image]</span>';
                                }
                            } else {
                                $content = htmlspecialchars((string)$val);
                            }
                        }
                    }
                    $css    .= 'position:relative;';
                    $class   = 'ipcrf-cell ipcrf-cell--mapped ipcrf-field-' . $field['field_type'];
                } else {
                    $content = htmlspecialchars((string)($cell['value'] ?? ''));
                    $class   = 'ipcrf-cell';
                    $css    .= 'position:relative;';
                }

                if (!empty($drawingsHtml)) {
                    $content = $drawingsHtml . $content;
                }

                $html .= "<td class=\"{$class}\" {$attrs} style=\"{$css}\">{$content}</td>";
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function buildCss(array $style): string
    {
        $css = '';
        if (!empty($style['bg_color']))  $css .= 'background-color:' . $style['bg_color'] . ';';
        if (!empty($style['font_color']) && $style['font_color'] !== '#000000') $css .= 'color:' . $style['font_color'] . ';';
        if (!empty($style['font_size'])) $css .= 'font-size:' . $style['font_size'] . 'pt;';
        if ($style['bold'])              $css .= 'font-weight:bold;';
        if ($style['italic'])            $css .= 'font-style:italic;';
        if ($style['underline'])         $css .= 'text-decoration:underline;';
        if (!empty($style['h_align']))   $css .= 'text-align:' . $style['h_align'] . ';';
        if ($style['border_top'] !== 'none')    $css .= 'border-top:' . $style['border_top'] . ';';
        if ($style['border_bottom'] !== 'none') $css .= 'border-bottom:' . $style['border_bottom'] . ';';
        if ($style['border_left'] !== 'none')   $css .= 'border-left:' . $style['border_left'] . ';';
        if ($style['border_right'] !== 'none')  $css .= 'border-right:' . $style['border_right'] . ';';
        if ($style['wrap_text'])         $css .= 'white-space:pre-wrap;';
        else                             $css .= 'white-space:nowrap;overflow:hidden;';
        $css .= 'padding:2px 4px;vertical-align:middle;';
        return $css;
    }

    private function renderFieldBadge(array $field, bool $editable): string
    {
        $label = htmlspecialchars($field['field_label'] ?: $field['field_type']);
        $type  = $field['field_type'];
        return '<span class="field-badge field-badge--' . $type . '" title="' . $label . '">'
            . '<i class="fas ' . $this->fieldTypeIcon($type) . '"></i> '
            . $label
            . '</span>';
    }

    private function fieldTypeIcon(string $type): string
    {
        return match ($type) {
            'autofill_name'                         => 'fa-user',
            'autofill_position'                     => 'fa-briefcase',
            'autofill_department'                   => 'fa-building',
            'autofill_date'                         => 'fa-calendar-day',
            'date'                                  => 'fa-calendar-alt',
            'autofill_division_chief'               => 'fa-user-tie',
            'autofill_approving_authority'          => 'fa-stamp',
            'autofill_division_chief_position'      => 'fa-briefcase',
            'autofill_approving_authority_position' => 'fa-briefcase',
            'calculated_mean'                       => 'fa-calculator',
            'text'                                  => 'fa-font',
            'number'                                => 'fa-hashtag',
            'textarea'                              => 'fa-align-left',
            'rating'                                => 'fa-star',
            'dropdown'                              => 'fa-chevron-down',
            'signature'                             => 'fa-signature',
            'readonly'                              => 'fa-lock',
            'picture'                               => 'fa-image',
            default                                 => 'fa-square',
        };
    }
}
