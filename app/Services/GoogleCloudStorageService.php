<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCloudStorageService
{
    protected $storage;

    protected $bucket;

    public function __construct()
    {
        $this->storage = new StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);

        $this->bucket = $this->storage->bucket(config('filesystems.disks.gcs.bucket'));
    }

    /**
     * Upload file to Google Cloud Storage
     */
    public function upload(UploadedFile $file, ?string $path = null): array
    {
        try {
            $filename = $this->generateUniqueFilename($file);
            $gcsPath = $path ? $path.'/'.$filename : $filename;

            // Upload file to GCS
            $object = $this->bucket->upload(
                fopen($file->getPathname(), 'r'),
                [
                    'name' => $gcsPath,
                    'metadata' => [
                        'contentType' => $file->getMimeType(),
                        'originalName' => $file->getClientOriginalName(),
                    ],
                ]
            );

            return [
                'success' => true,
                'filename' => $filename,
                'gcs_path' => $gcsPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        } catch (\Exception $e) {
            Log::error('GCS upload failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download file from Google Cloud Storage
     */
    public function download(string $gcsPath): ?string
    {
        try {
            $object = $this->bucket->object($gcsPath);

            if (! $object->exists()) {
                return null;
            }

            return $object->downloadAsString();
        } catch (\Exception $e) {
            Log::error('GCS download failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Delete file from Google Cloud Storage
     */
    public function delete(string $gcsPath): bool
    {
        try {
            $object = $this->bucket->object($gcsPath);

            if ($object->exists()) {
                $object->delete();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('GCS delete failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if file exists in Google Cloud Storage
     */
    public function exists(string $gcsPath): bool
    {
        try {
            $object = $this->bucket->object($gcsPath);

            return $object->exists();
        } catch (\Exception $e) {
            Log::error('GCS exists check failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get file info from Google Cloud Storage
     */
    public function getFileInfo(string $gcsPath): ?array
    {
        try {
            $object = $this->bucket->object($gcsPath);

            if (! $object->exists()) {
                return null;
            }

            $info = $object->info();

            return [
                'name' => $info['name'],
                'size' => $info['size'],
                'content_type' => $info['contentType'] ?? 'application/octet-stream',
                'updated' => $info['updated'],
                'etag' => $info['etag'],
            ];
        } catch (\Exception $e) {
            Log::error('GCS file info failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Generate a unique filename for the uploaded file
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);

        return $basename.'_'.uniqid().'.'.$extension;
    }
}
