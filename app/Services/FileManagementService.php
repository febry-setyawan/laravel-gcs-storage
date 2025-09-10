<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FileManagementService
{
    protected $gcsService;

    public function __construct(GoogleCloudStorageService $gcsService)
    {
        $this->gcsService = $gcsService;
    }

    /**
     * Upload and store file with database record
     */
    public function uploadFile(UploadedFile $file, User $user, ?string $description = null): array
    {
        DB::beginTransaction();

        try {
            // Upload to GCS
            $uploadResult = $this->gcsService->upload($file, 'uploads/'.$user->id);

            if (! $uploadResult['success']) {
                throw new \Exception('Failed to upload file to GCS: '.$uploadResult['error']);
            }

            // Create database record
            $fileRecord = File::create([
                'user_id' => $user->id,
                'original_name' => $uploadResult['original_name'],
                'filename' => $uploadResult['filename'],
                'path' => 'uploads/'.$user->id.'/'.$uploadResult['filename'],
                'mime_type' => $uploadResult['mime_type'],
                'size' => $uploadResult['size'],
                'gcs_path' => $uploadResult['gcs_path'],
                'is_published' => false,
                'description' => $description,
            ]);

            DB::commit();

            return [
                'success' => true,
                'file' => $fileRecord,
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('File upload failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download file content
     */
    public function downloadFile(File $file): ?array
    {
        $content = $this->gcsService->download($file->gcs_path);

        if ($content === null) {
            return null;
        }

        return [
            'content' => $content,
            'filename' => $file->original_name,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
        ];
    }

    /**
     * Delete file from both GCS and database
     */
    public function deleteFile(File $file): bool
    {
        DB::beginTransaction();

        try {
            // Delete from GCS
            $deleted = $this->gcsService->delete($file->gcs_path);

            if (! $deleted) {
                Log::warning('Failed to delete file from GCS, but continuing with database deletion', [
                    'file_id' => $file->id,
                    'gcs_path' => $file->gcs_path,
                ]);
            }

            // Delete from database
            $file->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('File deletion failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Toggle publication status of a file
     */
    public function togglePublication(File $file): bool
    {
        try {
            $file->is_published = ! $file->is_published;
            $file->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to toggle file publication: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Update file metadata
     */
    public function updateFile(File $file, array $data): bool
    {
        try {
            $file->update(array_intersect_key($data, array_flip([
                'description',
                'is_published',
            ])));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update file: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get published files for public access
     */
    public function getPublishedFiles(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return File::where('is_published', true)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user's files for internal management
     */
    public function getUserFiles(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $user->files()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search files
     */
    public function searchFiles(string $query, bool $publishedOnly = false, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $builder = File::query()
            ->where(function ($q) use ($query) {
                $q->where('original_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('user:id,name');

        if ($publishedOnly) {
            $builder->where('is_published', true);
        }

        return $builder->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
