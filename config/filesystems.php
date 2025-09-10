<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            // Normalize key file path: if env provides an absolute path keep it,
            // otherwise prefix with project base path so clients always receive
            // an absolute path. This avoids "Given keyfile path ... does not exist"
            // errors caused by relative paths or different working directories.
            'key_file' => (function () {
                $envPath = env('GOOGLE_CLOUD_KEY_FILE');

                if (! $envPath) {
                    return null;
                }

                // If absolute (Unix or Windows) return as-is, otherwise prefix project base path
                if (str_starts_with($envPath, '/') || preg_match('/^[A-Za-z]:\\\\?/', $envPath)) {
                    return $envPath;
                }

                return base_path($envPath);
            })(),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', null),
            'visibility' => 'private',
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
