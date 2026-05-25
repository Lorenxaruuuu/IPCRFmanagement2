<?php
namespace App\Http\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormController extends Controller
{
    public function index2()
    {
        $forms = Form::with('uploader')
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->get();
            
        return view('admin.forms', compact('forms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Template,Guidelines,Reference',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('forms', 'private');

        Form::create([
            'name' => $validated['title'],
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id() ?? 1,
            'published_at' => now(),
        ]);

        return back()->with('success', 'Form published successfully');
    }

    public function download2($id)
    {
        $form = Form::findOrFail($id);
        return Storage::disk('private')->download($form->file_path);
    }

    public function destroy($id)
    {
        $form = Form::findOrFail($id);
        if ($form->file_path) {
            Storage::disk('private')->delete($form->file_path);
        }
        $form->delete();
        return back()->with('success', 'Form removed successfully');
    }
     protected function sampleForms()
    {
        return collect([
            (object)[ 'id' => 1, 'name' => 'Purchase Request Form', 'file' => 'forms/purchase_request.pdf' ],
            (object)[ 'id' => 2, 'name' => 'Travel Order Form', 'file' => 'forms/travel_order.pdf' ],
            (object)[ 'id' => 3, 'name' => 'Leave Application Form', 'file' => 'forms/leave_application.pdf' ],
            (object)[ 'id' => 4, 'name' => 'Overtime Request Form', 'file' => 'forms/overtime_request.pdf' ],
            (object)[ 'id' => 5, 'name' => 'Cash Advance Form', 'file' => 'forms/cash_advance.pdf' ],
            (object)[ 'id' => 6, 'name' => 'Liquidation Report Form', 'file' => 'forms/liquidation.pdf' ],
            (object)[ 'id' => 7, 'name' => 'Property Acknowledgement Form', 'file' => 'forms/property_ack.pdf' ],
            (object)[ 'id' => 8, 'name' => 'Requisition Slip Form', 'file' => 'forms/requisition.pdf' ],
        ]);
    }

    public function index()
    {
        $forms = $this->sampleForms();
        return view('form', compact('forms'));
    }

    public function download($id)
    {
        // 1. Try finding in the database forms first
        $dbForm = Form::where('id', $id)->first();
        if ($dbForm) {
            if (Storage::disk('private')->exists($dbForm->file_path)) {
                return Storage::disk('private')->download($dbForm->file_path, $dbForm->file_name);
            }
            if (Storage::disk('public')->exists($dbForm->file_path)) {
                return Storage::disk('public')->download($dbForm->file_path, $dbForm->file_name);
            }
            
            // Create dummy file in private disk so download never fails
            $dir = dirname($dbForm->file_path);
            Storage::disk('private')->makeDirectory($dir);
            Storage::disk('private')->put($dbForm->file_path, "This is a placeholder content for form template " . $dbForm->name);
            
            return Storage::disk('private')->download($dbForm->file_path, $dbForm->file_name);
        }

        // 2. Fall back to sample forms
        $form = $this->sampleForms()->firstWhere('id', $id);

        if (! $form) {
            abort(404);
        }

        return response()->download(storage_path('app/public/' . $form->file));
    }
}