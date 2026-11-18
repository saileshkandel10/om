<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadedFileController extends Controller
{
    public function index(Request $request): View
    {
        $query = UploadedFile::with('user');

        if ($request->filled('module')) {
            $query->where('module_name', $request->module);
        }

        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('original_name', 'like', "%{$request->search}%");
        }

        $files = $query->latest()->get();

        return view('admin.uploaded-files.index', compact('files'));
    }

    public function show(UploadedFile $uploadedFile): View
    {
        $uploadedFile->load('user');

        return view('admin.uploaded-files.show', compact('uploadedFile'));
    }

    public function destroy(UploadedFile $uploadedFile): RedirectResponse
    {
        if ($uploadedFile->file_path && Storage::exists($uploadedFile->file_path)) {
            Storage::delete($uploadedFile->file_path);
        }

        $uploadedFile->delete();

        return to_route('admin.uploaded-files.index')->with('success', __('File deleted successfully.'));
    }
}
