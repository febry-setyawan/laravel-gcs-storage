<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InternalFileController extends Controller
{
    protected $fileService;

    public function __construct(FileManagementService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * List user's files (requires authentication)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        
        if ($search) {
            $files = $this->fileService->searchFiles($search, false, 15);
            // Filter to only show user's files
            $files->getCollection()->transform(function ($file) use ($user) {
                return $file->user_id === $user->id ? $file : null;
            })->filter();
        } else {
            $files = $this->fileService->getUserFiles($user, 15);
        }

        return response()->json([
            'success' => true,
            'data' => $files->items(),
            'pagination' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    /**
     * Upload new file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $result = $this->fileService->uploadFile(
            $request->file('file'),
            $user,
            $request->get('description')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $result['error'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => $result['file'],
        ], 201);
    }

    /**
     * Show specific file details (user's files only)
     */
    public function show($id)
    {
        $user = Auth::user();
        $file = $user->files()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'filename' => $file->filename,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'description' => $file->description,
                'is_published' => $file->is_published,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at,
                'download_url' => route('internal.files.download', ['id' => $file->id]),
                'public_url' => $file->is_published ? route('public.files.show', ['id' => $file->id]) : null,
            ],
        ]);
    }

    /**
     * Update file metadata
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
            'is_published' => 'boolean',
        ]);

        $user = Auth::user();
        $file = $user->files()->findOrFail($id);

        $success = $this->fileService->updateFile($file, $request->only([
            'description',
            'is_published',
        ]));

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update file',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'data' => $file->fresh(),
        ]);
    }

    /**
     * Delete file
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $file = $user->files()->findOrFail($id);

        $success = $this->fileService->deleteFile($file);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Download file (user's files only)
     */
    public function download($id)
    {
        $user = Auth::user();
        $file = $user->files()->findOrFail($id);

        $downloadData = $this->fileService->downloadFile($file);

        if (!$downloadData) {
            return response()->json([
                'success' => false,
                'message' => 'File content not available',
            ], 404);
        }

        return response($downloadData['content'])
            ->header('Content-Type', $downloadData['mime_type'])
            ->header('Content-Disposition', 'attachment; filename="' . $downloadData['filename'] . '"')
            ->header('Content-Length', $downloadData['size']);
    }

    /**
     * Toggle publication status
     */
    public function togglePublication($id)
    {
        $user = Auth::user();
        $file = $user->files()->findOrFail($id);

        $success = $this->fileService->togglePublication($file);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle publication status',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Publication status updated successfully',
            'data' => [
                'is_published' => $file->fresh()->is_published,
                'public_url' => $file->fresh()->is_published ? route('public.files.show', ['id' => $file->id]) : null,
            ],
        ]);
    }

    /**
     * Get user's file statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        $totalFiles = $user->files()->count();
        $publishedFiles = $user->files()->where('is_published', true)->count();
        $totalSize = $user->files()->sum('size');

        // Get file types distribution
        $fileTypes = $user->files()
            ->selectRaw('mime_type, COUNT(*) as count')
            ->groupBy('mime_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $totalFiles,
                'published_files' => $publishedFiles,
                'unpublished_files' => $totalFiles - $publishedFiles,
                'total_size' => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
                'file_types' => $fileTypes,
            ],
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}