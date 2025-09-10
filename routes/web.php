<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel GCS Storage Service',
        'description' => 'Encapsulated Google Cloud Storage access with public and internal services',
        'version' => '1.0.0',
        'endpoints' => [
            'public_files' => '/api/public/files',
            'authentication' => '/api/auth',
            'internal_files' => '/api/internal/files',
            'health_check' => '/api/health',
        ],
    ]);
});
