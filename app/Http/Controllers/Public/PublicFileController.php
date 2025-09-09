<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicFileController extends Controller
{
    protected $fileService;

    public function __construct(FileManagementService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * List all published files (public access)
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        if ($search) {
            $files = $this->fileService->searchFiles($search, true, 15);
        } else {
            $files = $this->fileService->getPublishedFiles(15);
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
     * Show specific published file details
     */
    public function show($id)
    {
        $file = File::where('id', $id)
            ->where('is_published', true)
            ->with('user:id,name')
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or not published',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'description' => $file->description,
                'created_at' => $file->created_at,
                'user' => $file->user->name ?? 'Unknown',
                'download_url' => route('public.files.download', ['id' => $file->id]),
            ],
        ]);
    }

    /**
     * Download published file (encapsulated URL)
     */
    public function download($id)
    {
        $file = File::where('id', $id)
            ->where('is_published', true)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or not published',
            ], 404);
        }

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
     * Get file statistics for public view
     */
    public function stats()
    {
        $totalPublished = File::where('is_published', true)->count();
        $totalSizePublished = File::where('is_published', true)->sum('size');

        // Get file types distribution
        $fileTypes = File::where('is_published', true)
            ->selectRaw('mime_type, COUNT(*) as count')
            ->groupBy('mime_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $totalPublished,
                'total_size' => $totalSizePublished,
                'total_size_human' => $this->formatBytes($totalSizePublished),
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