<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Http\Requests\Admin\TemplateStoreRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        // Eager-load audit relations for display
        $templates = DocumentTemplate::with([
                'createdBy:id,name',
                'updatedBy:id,name',
            ])
            ->latest()
            ->paginate(10);

        return view('admin.templates.index', compact('templates'));
    }

    public function store(TemplateStoreRequest $request)
    {
        // Validation is handled by the Form Request.
        // Standard input name is "file". If you still have old forms using "document",
        // you can keep this fallback line for compatibility:
        $file = $request->file('file') ?? $request->file('document');

        // Store on the public disk under templates/
        $path = $file->store('templates', 'public');

        DocumentTemplate::create([
            'name'        => $request->input('name') ?: $file->getClientOriginalName(),
            'file_path'   => $path,
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template uploaded successfully.');
    }

    // Allow authenticated users (students/supervisors/admin) to download templates
    public function download(DocumentTemplate $template)
    {
        $extension = pathinfo($template->file_path, PATHINFO_EXTENSION);
        $downloadAs = Str::slug($template->name ?: 'template') . ($extension ? '.' . $extension : '');

        return Storage::disk('public')->download($template->file_path, $downloadAs);
    }

    // Soft delete (do NOT remove physical file here)
    // Option B: stamp deleter in controller, then soft-delete
    public function destroy(DocumentTemplate $template)
    {
        $template->forceFill(['deleted_by_id' => auth()->id()])->saveQuietly();
        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template moved to Recycle Bin.');
    }

    // Recycle Bin list
    public function trash()
    {
        $templates = DocumentTemplate::onlyTrashed()
            ->with([
                'deletedBy:id,name',
                'createdBy:id,name',
            ])
            ->latest('deleted_at')
            ->paginate(10);

        return view('admin.templates.trash', compact('templates'));
    }

    // Clear deleted_by_id on restore (controller-side)
    public function restore(string $templateId)
    {
        $template = DocumentTemplate::withTrashed()->findOrFail($templateId);

        $template->restore();
        $template->forceFill(['deleted_by_id' => null])->saveQuietly();

        return redirect()->route('admin.templates.trash')
            ->with('success', 'Template restored.');
    }

    // Permanently delete (also remove the physical file)
    public function forceDelete(string $templateId)
    {
        $template = DocumentTemplate::withTrashed()->findOrFail($templateId);

        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->forceDelete();

        return redirect()->route('admin.templates.trash')
            ->with('success', 'Template permanently deleted.');
    }

    public function view(DocumentTemplate $template)
    {
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($template->file_path);

        if (!file_exists($absolutePath)) {
            abort(404);
        }

        if (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) !== 'pdf') {
            abort(404, 'Only PDF templates can be viewed inline.');
        }

        $filename = Str::slug($template->name ?: pathinfo($absolutePath, PATHINFO_FILENAME)) . '.pdf';

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

     public function clearTrash()
    {
        $filesDeleted = 0;
        $records = 0;

        DocumentTemplate::onlyTrashed()
            ->orderBy('id')
            ->chunkById(200, function ($batch) use (&$filesDeleted, &$records) {
                foreach ($batch as $t) {
                    $records++;

                    if ($t->file_path) {
                        try {
                            if (Storage::disk('public')->delete($t->file_path)) {
                                $filesDeleted++;
                            }
                        } catch (\Throwable $e) {
                            // Ignore individual file delete errors, continue with forceDelete
                        }
                    }

                    $t->forceDelete();
                }
            });

        return redirect()->route('admin.templates.trash')
            ->with('success', "Recycle Bin cleared. Records purged: {$records}. Files deleted: {$filesDeleted}.");
    }
}