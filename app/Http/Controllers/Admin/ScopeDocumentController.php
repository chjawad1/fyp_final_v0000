<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ScopeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ScopeDocumentController extends Controller
{
    /**
     * Display a listing of the scope document versions for a specific project.
     */
    public function index(Project $project)
    {
        // Eager load the uploader information to prevent N+1 query issues
        $versions = $project->scopeDocuments()->with('uploader')->get();

        return view('admin.scope_documents.index', [
            'project' => $project,
            'versions' => $versions,
        ]);
    }

    /**
     * Show the form for creating a new scope document version.
     */
    public function create(Project $project)
    {
        return view('admin.scope_documents.create', ['project' => $project]);
    }

    /**
     * Store a newly created scope document version in storage.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'version' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB Max
            'changelog' => 'nullable|string',
        ]);

        // Store the file and get its path
        $filePath = $request->file('document')->store('scope_documents');

        // Create the database record
        $project->scopeDocuments()->create([
            'user_id' => Auth::id(),
            'version' => $request->input('version'),
            'changelog' => $request->input('changelog'),
            'file_path' => $filePath,
        ]);

        return redirect()->route('admin.projects.scope-documents.index', $project)
                         ->with('success', 'New document version uploaded successfully.');
    }

    /**
     * Download the specified scope document.
     */
    public function download(ScopeDocument $scope_document)
    {
        // Ensure the file exists before attempting to download
        if (!Storage::exists($scope_document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download($scope_document->file_path);
    }
}