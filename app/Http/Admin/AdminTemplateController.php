<?php

namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpcrfTemplate;
use App\Models\TemplateField;
use App\Models\Position;
use App\Services\TemplateParserService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTemplateController extends Controller
{
    public function __construct(private TemplateParserService $parser) {}

    public function index()
    {
        $templates = IpcrfTemplate::select('id', 'name', 'description', 'file_path', 'file_name', 'file_original_name', 'total_rows', 'total_cols', 'is_active', 'uploaded_by', 'created_at', 'updated_at')
            ->with(['positions', 'uploader'])
            ->latest()
            ->get();
        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '1G');
        set_time_limit(300); // Allow up to 5 minutes for large files
        $request->validate([
            'name'               => 'required|string|max:255',
            'file'               => 'required|file|mimes:xlsx|max:102400',
            'description'        => 'nullable|string',
            'semester'           => 'required|string|in:1st,2nd',
            'form_specification' => 'required|string|in:Target,Rating',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('ipcrf_templates', 'private');
        $fullPath = Storage::disk('private')->path($path);

        try {
            $parsed = $this->parser->parse($fullPath);
            gc_collect_cycles();
        } catch (\Exception $e) {
            Storage::disk('private')->delete($path);
            return response()->json(['success' => false, 'message' => 'Could not parse XLSX: ' . $e->getMessage()], 422);
        }

        // Create the record first (without sheet_data) so we have an ID,
        // then persist the parsed rows to a compressed file on disk.
        $template = IpcrfTemplate::create([
            'name'               => $request->name,
            'description'        => $request->description,
            'file_path'          => $path,
            'file_name'          => Str::slug($request->name) . '.xlsx',
            'file_original_name' => $file->getClientOriginalName(),
            'merged_cells'       => $parsed['merged_cells'],
            'total_rows'         => $parsed['total_rows'],
            'total_cols'         => $parsed['total_cols'],
            'uploaded_by'        => session('user')['id'] ?? null,
            'semester'           => $request->semester,
            'form_specification' => $request->form_specification,
        ]);

        // Save sheet_data to a gzip-compressed file (avoids MySQL packet limits)
        $template->saveSheetData($parsed['rows']);
        unset($parsed); // free memory
        gc_collect_cycles();

        $adminId = session('user')['id'] ?? null;
        AuditService::log('template_uploaded', $adminId, 'IpcrfTemplate', $template->id, [
            'name' => $template->name,
        ]);

        // Publish a system notice announcement for users
        \App\Models\Notice::create([
            'subject'   => 'New IPCRF Template: ' . $template->name,
            'content'   => 'A new IPCRF template has been published for assigned positions. You can now fill and submit this form from your Available IPCRF Templates panel.',
            'priority'  => 'High',
            'posted_by' => $adminId ?? 1,
            'posted_at' => now(),
            'is_active' => true
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Template uploaded successfully!',
                'template_id' => $template->id,
                'builder_url' => route('admin.templates.builder', $template->id),
            ]);
        }

        return redirect()->back()->with('success', 'Template uploaded successfully!');
    }

    public function builder(int $id)
    {
        ini_set('memory_limit', '1G');
        set_time_limit(300);
        $template  = IpcrfTemplate::with(['fields', 'positions'])->findOrFail($id);
        $positions = Position::active()->orderBy('name')->get();
        $parsed    = [
            'rows'         => $template->sheet_data ?? [],
            'merged_cells' => $template->merged_cells ?? [],
            'col_widths'   => [],
            'row_heights'  => [],
            'total_rows'   => $template->total_rows,
            'total_cols'   => $template->total_cols,
        ];

        // Re-parse to get col/row dimensions
        $fullPath = Storage::disk('private')->path($template->file_path);
        if (file_exists($fullPath)) {
            try {
                $parsed = $this->parser->parse($fullPath);
                if ($template->sheet_data) {
                    $parsed['rows'] = $template->sheet_data;
                }
            } catch (\Exception $e) {}
        }

        $mappedFields = $template->fields->map(fn($f) => [
            'id'           => $f->id,
            'cell_ref'     => $f->cell_ref,
            'field_type'   => $f->field_type,
            'field_label'  => $f->field_label,
            'field_options' => $f->field_options,
            'is_required'  => $f->is_required,
        ])->toArray();

        $htmlTable = $this->parser->toHtmlTable($parsed, $mappedFields, true);

        return view('admin.templates.builder', compact('template', 'positions', 'htmlTable', 'parsed', 'mappedFields'));
    }

    public function saveFields(Request $request, int $id)
    {
        $request->validate([
            'fields'               => 'required|array',
            'fields.*.cell_ref'    => 'required|string',
            'fields.*.field_type'  => 'required|string',
            'fields.*.field_label' => 'nullable|string',
        ]);

        $template = IpcrfTemplate::findOrFail($id);

        // Replace all fields for this template
        TemplateField::where('template_id', $id)->delete();

        foreach ($request->fields as $i => $fieldData) {
            // Parse cell ref to row/col
            preg_match('/^([A-Z]+)(\d+)$/', strtoupper($fieldData['cell_ref']), $m);
            $colLetter = $m[1] ?? 'A';
            $rowIndex  = (int)($m[2] ?? 1);
            $colIndex  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLetter);

            TemplateField::create([
                'template_id'   => $id,
                'cell_ref'      => strtoupper($fieldData['cell_ref']),
                'sheet_index'   => 0,
                'row_index'     => $rowIndex,
                'col_index'     => $colIndex,
                'field_type'    => $fieldData['field_type'],
                'field_label'   => $fieldData['field_label'] ?? null,
                'field_options' => $fieldData['field_options'] ?? null,
                'is_required'   => (bool)($fieldData['is_required'] ?? false),
                'sort_order'    => $i,
            ]);
        }

        AuditService::log('template_fields_saved', null, 'IpcrfTemplate', $id, [
            'field_count' => count($request->fields),
        ]);

        return response()->json(['success' => true, 'message' => 'Field mappings saved!']);
    }

    public function assignPositions(Request $request, int $id)
    {
        $request->validate(['position_ids' => 'nullable|array', 'position_ids.*' => 'exists:positions,id']);
        $template = IpcrfTemplate::findOrFail($id);
        $template->positions()->sync($request->position_ids ?? []);

        AuditService::log('template_positions_updated', null, 'IpcrfTemplate', $id, [
            'positions' => $request->position_ids,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Positions assigned!']);
        }
        return redirect()->back()->with('success', 'Positions assigned successfully');
    }

    public function updateCellText(Request $request, int $id)
    {
        $request->validate([
            'cell_ref' => 'required|string|max:20',
            'value'    => 'nullable|string|max:2000',
        ]);

        $template  = IpcrfTemplate::findOrFail($id);
        $sheetData = $template->sheet_data ?? [];
        $target    = strtoupper(trim($request->cell_ref));
        $found     = false;

        foreach ($sheetData as &$row) {
            foreach ($row as &$cell) {
                if (isset($cell['cell_ref']) && $cell['cell_ref'] === $target) {
                    $cell['value']     = $request->value ?? '';
                    $cell['raw_value'] = $request->value ?? '';
                    $found = true;
                    break 2;
                }
            }
        }
        unset($row, $cell);

        if ($found) {
            $template->update(['sheet_data' => $sheetData]);
        }

        return response()->json(['success' => true, 'found' => $found]);
    }

    public function updateCellAlign(Request $request, int $id)
    {
        $request->validate([
            'cell_ref' => 'required|string|max:20',
            'align'    => 'required|string|in:left,center,right',
        ]);

        $template  = IpcrfTemplate::findOrFail($id);
        $sheetData = $template->sheet_data ?? [];
        $target    = strtoupper(trim($request->cell_ref));
        $found     = false;

        foreach ($sheetData as &$row) {
            foreach ($row as &$cell) {
                if (isset($cell['cell_ref']) && $cell['cell_ref'] === $target) {
                    if (!isset($cell['style']) || !is_array($cell['style'])) {
                        $cell['style'] = [];
                    }
                    $cell['style']['h_align'] = $request->align;
                    $found = true;
                    break 2;
                }
            }
        }
        unset($row, $cell);

        if ($found) {
            $template->update(['sheet_data' => $sheetData]);
        }

        return response()->json(['success' => true, 'found' => $found]);
    }

    public function uploadCellImage(Request $request, int $id)
    {
        $request->validate([
            'image'    => 'required|image|max:5120',
            'cell_ref' => 'required|string|max:20',
        ]);

        $publicDir = public_path('storage/ipcrf_images');
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $file      = $request->file('image');
        $filename  = uniqid('img_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($publicDir, $filename);
        $url = asset('storage/ipcrf_images/' . $filename);

        // Persist drawing info into sheet_data
        $template  = IpcrfTemplate::findOrFail($id);
        $sheetData = $template->sheet_data ?? [];
        $target    = strtoupper(trim($request->cell_ref));

        foreach ($sheetData as &$row) {
            foreach ($row as &$cell) {
                if (isset($cell['cell_ref']) && $cell['cell_ref'] === $target) {
                    $cell['drawings'] = [[
                        'url'     => $url,
                        'width'   => 120,
                        'height'  => 60,
                        'offsetX' => 0,
                        'offsetY' => 0,
                        'name'    => 'uploaded',
                    ]];
                    break 2;
                }
            }
        }
        unset($row, $cell);

        $template->update(['sheet_data' => $sheetData]);

        return response()->json(['success' => true, 'url' => $url]);
    }

    public function saveMergedCells(Request $request, int $id)
    {
        $request->validate([
            'action'      => 'required|in:merge,unmerge',
            'primary_ref' => 'required|string|max:20',
            'range'       => 'nullable|string|max:30',
            'rowspan'     => 'nullable|integer|min:1|max:100',
            'colspan'     => 'nullable|integer|min:1|max:100',
        ]);

        $template    = IpcrfTemplate::findOrFail($id);
        $sheetData   = $template->sheet_data   ?? [];
        $mergedCells = $template->merged_cells ?? [];
        $primaryRef  = strtoupper(trim($request->primary_ref));

        if ($request->action === 'merge' && $request->range) {
            $range   = strtoupper(trim($request->range));
            $rowspan = max(1, (int)($request->rowspan ?? 1));
            $colspan = max(1, (int)($request->colspan ?? 1));
            $mergedCells[$range] = null;

            [$start, $end] = array_pad(explode(':', $range), 2, $range);
            [$sc, $sr] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($start);
            [$ec, $er] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($end);
            $scIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sc);
            $ecIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ec);

            foreach ($sheetData as &$row) {
                foreach ($row as &$cell) {
                    $ref = $cell['cell_ref'] ?? '';
                    if ($ref === $primaryRef) {
                        $cell['rowspan'] = $rowspan;
                        $cell['colspan'] = $colspan;
                    } else {
                        preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
                        if ($m) {
                            $r = (int)$m[2];
                            $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[1]);
                            if ($r >= (int)$sr && $r <= (int)$er && $c >= $scIdx && $c <= $ecIdx) {
                                $cell['hidden'] = true;
                            }
                        }
                    }
                }
            }
            unset($row, $cell);

        } elseif ($request->action === 'unmerge') {
            foreach ($mergedCells as $range => $_) {
                [$start] = explode(':', $range);
                if (strtoupper(trim($start)) !== $primaryRef) continue;

                unset($mergedCells[$range]);
                [$start2, $end2] = array_pad(explode(':', $range), 2, $range);
                [$sc, $sr] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($start2);
                [$ec, $er] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($end2);
                $scIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sc);
                $ecIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ec);

                foreach ($sheetData as &$row) {
                    foreach ($row as &$cell) {
                        $ref = $cell['cell_ref'] ?? '';
                        if ($ref === $primaryRef) {
                            $cell['rowspan'] = 1;
                            $cell['colspan'] = 1;
                        } else {
                            preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
                            if ($m) {
                                $r = (int)$m[2];
                                $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[1]);
                                if ($r >= (int)$sr && $r <= (int)$er && $c >= $scIdx && $c <= $ecIdx) {
                                    $cell['hidden'] = false;
                                }
                            }
                        }
                    }
                }
                unset($row, $cell);
                break;
            }
        }

        $template->update(['sheet_data' => $sheetData, 'merged_cells' => $mergedCells]);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'semester'           => 'required|string|in:1st,2nd',
            'form_specification' => 'required|string|in:Target,Rating',
            'description'        => 'nullable|string',
        ]);

        $template = IpcrfTemplate::findOrFail($id);
        $template->update([
            'name'               => $request->name,
            'semester'           => $request->semester,
            'form_specification' => $request->form_specification,
            'description'        => $request->description,
        ]);

        AuditService::log('template_updated', session('user')['id'] ?? null, 'IpcrfTemplate', $id, [
            'name' => $template->name,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template updated successfully!']);
        }

        return redirect()->back()->with('success', 'Template updated successfully!');
    }

    public function destroy(int $id)
    {
        $template = IpcrfTemplate::findOrFail($id);
        Storage::disk('private')->delete($template->file_path);
        $template->delete();
        AuditService::log('template_deleted', null, 'IpcrfTemplate', $id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Template deleted successfully');
    }

    public function saveLayout(Request $request, int $id)
    {
        $request->validate([
            'rows' => 'required|array',
        ]);

        $template = IpcrfTemplate::findOrFail($id);
        $totalRows = count($request->rows);
        $totalCols = $totalRows > 0 ? count($request->rows[0]) : 0;

        $template->update([
            'sheet_data' => $request->rows,
            'total_rows' => $totalRows,
            'total_cols' => $totalCols,
        ]);

        AuditService::log('template_layout_saved', null, 'IpcrfTemplate', $id, [
            'total_rows' => $totalRows,
            'total_cols' => $totalCols,
        ]);

        return response()->json(['success' => true, 'message' => 'Layout saved successfully!']);
    }

    public function getAll()
    {
        $templates = IpcrfTemplate::select('id', 'name', 'description', 'file_path', 'file_name', 'file_original_name', 'total_rows', 'total_cols', 'is_active', 'uploaded_by', 'semester', 'form_specification', 'created_at', 'updated_at')
            ->with(['positions', 'uploader', 'fields'])
            ->active()
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'                 => $t->id,
                'name'               => $t->name,
                'description'        => $t->description,
                'semester'           => $t->semester,
                'form_specification' => $t->form_specification,
                'positions'          => $t->positions->pluck('name')->toArray(),
                'field_count'        => $t->fields->count(),
                'created_at'         => $t->created_at->format('M d, Y'),
                'uploader'           => $t->uploader?->name ?? 'System',
            ]);
        return response()->json(['templates' => $templates]);
    }
}

