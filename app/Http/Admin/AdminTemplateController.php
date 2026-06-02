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
        $templates = IpcrfTemplate::with(['positions', 'uploader'])
            ->latest()
            ->get();
        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'name'     => 'required|string|max:255',
            'file'     => 'required|file|mimes:xlsx|max:102400',
            'description' => 'nullable|string',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('ipcrf_templates', 'private');
        $fullPath = Storage::disk('private')->path($path);

        try {
            $parsed = $this->parser->parse($fullPath);
        } catch (\Exception $e) {
            Storage::disk('private')->delete($path);
            return response()->json(['success' => false, 'message' => 'Could not parse XLSX: ' . $e->getMessage()], 422);
        }

        $template = IpcrfTemplate::create([
            'name'              => $request->name,
            'description'       => $request->description,
            'file_path'         => $path,
            'file_name'         => Str::slug($request->name) . '.xlsx',
            'file_original_name' => $file->getClientOriginalName(),
            'sheet_data'        => $parsed['rows'],
            'merged_cells'      => $parsed['merged_cells'],
            'total_rows'        => $parsed['total_rows'],
            'total_cols'        => $parsed['total_cols'],
            'uploaded_by'       => session('user')['id'] ?? null,
        ]);

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

        return response()->json([
            'success'     => true,
            'message'     => 'Template uploaded successfully!',
            'template_id' => $template->id,
            'builder_url' => route('admin.templates.builder', $template->id),
        ]);
    }

    public function builder(int $id)
    {
        ini_set('memory_limit', '512M');
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

        return response()->json(['success' => true, 'message' => 'Positions assigned!']);
    }

    public function destroy(int $id)
    {
        $template = IpcrfTemplate::findOrFail($id);
        Storage::disk('private')->delete($template->file_path);
        $template->delete();
        AuditService::log('template_deleted', null, 'IpcrfTemplate', $id);
        return response()->json(['success' => true]);
    }

    /**
     * Upload picture directly onto a template cell (static image).
     */
    public function uploadPicture(Request $request, int $id)
    {
        $request->validate([
            'file'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'cell_ref' => 'required|string',
        ]);

        $template = IpcrfTemplate::findOrFail($id);
        $file     = $request->file('file');
        
        $publicDir = public_path('storage/ipcrf_images');
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = uniqid('tpl_img_', true) . '.' . $extension;
        $file->move($publicDir, $filename);

        $url = asset('storage/ipcrf_images/' . $filename);
        
        // Get image dimensions
        $size = @getimagesize($publicDir . '/' . $filename);
        $width = $size[0] ?? 120;
        $height = $size[1] ?? 80;

        // Update sheet_data with this drawing
        $sheetData = $template->sheet_data;
        $cellRef = strtoupper($request->cell_ref);
        
        $updated = false;
        foreach ($sheetData as &$row) {
            foreach ($row as &$cell) {
                if (strtoupper($cell['cell_ref']) === $cellRef) {
                    if (!isset($cell['drawings']) || !is_array($cell['drawings'])) {
                        $cell['drawings'] = [];
                    }
                    $cell['drawings'][] = [
                        'url'     => $url,
                        'width'   => $width,
                        'height'  => $height,
                        'offsetX' => 0,
                        'offsetY' => 0,
                        'name'    => $file->getClientOriginalName(),
                    ];
                    $updated = true;
                    break 2;
                }
            }
        }

        if ($updated) {
            $template->update(['sheet_data' => $sheetData]);
        }

        return response()->json([
            'success' => true,
            'url'     => $url,
            'width'   => $width,
            'height'  => $height,
            'name'    => $file->getClientOriginalName(),
        ]);
    }

    public function getAll()
    {
        $templates = IpcrfTemplate::with(['positions', 'uploader', 'fields'])
            ->active()
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'description' => $t->description,
                'positions'   => $t->positions->pluck('name')->toArray(),
                'field_count' => $t->fields->count(),
                'created_at'  => $t->created_at->format('M d, Y'),
                'uploader'    => $t->uploader?->name ?? 'System',
            ]);
        return response()->json(['templates' => $templates]);
    }
}
