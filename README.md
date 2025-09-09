# Laravel GCS Storage Service

A Laravel application that encapsulates Google Cloud Storage bucket access with both public and internal services.

## Features

- **Public Service**: No authentication required - browse published files with encapsulated URLs
- **Internal Service**: Authentication required - full file management with database storage
- **File Publishing**: Users can control which files are publicly accessible
- **Encapsulated URLs**: Real GCS URLs are hidden from public users
- **Database Integration**: File metadata stored in MySQL database
- **RESTful API**: Clean JSON API for both public and internal access

## Architecture

### Public Service (No Authentication)
- `GET /api/public/files` - List published files
- `GET /api/public/files/{id}` - Get published file details
- `GET /api/public/files/{id}/download` - Download published file
- `GET /api/public/stats` - Get public file statistics

### Internal Service (Authentication Required)
- `GET /api/internal/files` - List user's files
- `POST /api/internal/files` - Upload new file
- `GET /api/internal/files/{id}` - Get file details
- `PUT /api/internal/files/{id}` - Update file metadata
- `DELETE /api/internal/files/{id}` - Delete file
- `POST /api/internal/files/{id}/toggle-publication` - Toggle publication status
- `GET /api/internal/files/{id}/download` - Download file

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/user` - Get authenticated user details

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your environment variables
3. Set up Google Cloud Storage credentials in `.env`:
   ```
   GOOGLE_CLOUD_PROJECT_ID=your-project-id
   GOOGLE_CLOUD_KEY_FILE=path/to/service-account-key.json
   GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
   ```
4. Configure your MySQL database connection
5. Install dependencies: `composer install`
6. Generate application key: `php artisan key:generate`
7. Run migrations: `php artisan migrate`
8. Start the server: `php artisan serve`

## Environment Variables

Required Google Cloud Storage variables:
- `GOOGLE_CLOUD_PROJECT_ID` - Your GCP project ID
- `GOOGLE_CLOUD_KEY_FILE` - Path to service account JSON key file
- `GOOGLE_CLOUD_STORAGE_BUCKET` - GCS bucket name
- `GOOGLE_CLOUD_STORAGE_PATH_PREFIX` - Optional path prefix for files
- `GOOGLE_CLOUD_STORAGE_API_URI` - Optional custom API URI

## Database Schema

### Users Table
- Standard Laravel users table with authentication

### Files Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `original_name` - Original uploaded filename
- `filename` - Generated unique filename
- `path` - File path in the system
- `mime_type` - File MIME type
- `size` - File size in bytes
- `gcs_path` - Path in Google Cloud Storage
- `is_published` - Publication status (boolean)
- `description` - Optional file description
- `created_at` / `updated_at` - Timestamps

## Usage Examples

### Upload a file (Internal API)
```bash
curl -X POST http://localhost:8000/api/internal/files \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@example.pdf" \
  -F "description=Sample PDF file"
```

### List published files (Public API)
```bash
curl http://localhost:8000/api/public/files
```

### Toggle file publication (Internal API)
```bash
curl -X POST http://localhost:8000/api/internal/files/1/toggle-publication \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Security Features

- All internal operations require authentication via Laravel Sanctum
- Real GCS URLs are never exposed to public users
- File access is controlled through the application layer
- Published status controls public visibility
- Users can only manage their own files

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
