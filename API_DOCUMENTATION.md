# API Documentation

## Laravel GCS Storage Service API

This API provides encapsulated access to Google Cloud Storage with both public and internal services.

### Base URL
```
http://localhost:8000/api
```

### Authentication
Most endpoints require Bearer token authentication. Obtain tokens through the authentication endpoints.

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Public Endpoints (No Authentication Required)

### List Published Files
**GET** `/public/files`

Returns a paginated list of all published files.

**Query Parameters:**
- `search` (optional): Search in file names and descriptions

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "original_name": "document.pdf",
      "mime_type": "application/pdf",
      "size": 1024576,
      "description": "Important document",
      "created_at": "2024-01-01T00:00:00Z",
      "user": "John Doe"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67
  }
}
```

### Get Published File Details
**GET** `/public/files/{id}`

Returns details of a specific published file.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "mime_type": "application/pdf",
    "size": 1024576,
    "description": "Important document",
    "created_at": "2024-01-01T00:00:00Z",
    "user": "John Doe",
    "download_url": "/api/public/files/1/download"
  }
}
```

### Download Published File
**GET** `/public/files/{id}/download`

Downloads the file content. Returns the file as binary data with appropriate headers.

### Get Public Statistics
**GET** `/public/stats`

Returns statistics about published files.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_files": 150,
    "total_size": 52428800,
    "total_size_human": "50.0 MB",
    "file_types": [
      {
        "mime_type": "application/pdf",
        "count": 75
      },
      {
        "mime_type": "image/jpeg",
        "count": 45
      }
    ]
  }
}
```

---

## Authentication Endpoints

### Register User
**POST** `/auth/register`

Register a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Login User
**POST** `/auth/login`

Authenticate user and get access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Logout User
**POST** `/auth/logout`

**Requires Authentication**

Logout user and invalidate current token.

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get Current User
**GET** `/auth/user`

**Requires Authentication**

Get current authenticated user details.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

---

## Internal Endpoints (Authentication Required)

### List User Files
**GET** `/internal/files`

Returns paginated list of current user's files.

**Query Parameters:**
- `search` (optional): Search in file names and descriptions

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "original_name": "document.pdf",
      "filename": "document_abc123.pdf",
      "mime_type": "application/pdf",
      "size": 1024576,
      "description": "Important document",
      "is_published": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

### Upload File
**POST** `/internal/files`

Upload a new file to the system.

**Request:** `multipart/form-data`
- `file`: File to upload (required)
- `description`: File description (optional)

**Response:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "filename": "document_abc123.pdf",
    "mime_type": "application/pdf",
    "size": 1024576,
    "description": "Important document",
    "is_published": false,
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

### Get File Details
**GET** `/internal/files/{id}`

Get details of a specific file owned by the user.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "filename": "document_abc123.pdf",
    "mime_type": "application/pdf",
    "size": 1024576,
    "description": "Important document",
    "is_published": true,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z",
    "download_url": "/api/internal/files/1/download",
    "public_url": "/api/public/files/1"
  }
}
```

### Update File
**PUT** `/internal/files/{id}`

Update file metadata.

**Request Body:**
```json
{
  "description": "Updated description",
  "is_published": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "File updated successfully",
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "description": "Updated description",
    "is_published": true,
    "updated_at": "2024-01-01T12:00:00Z"
  }
}
```

### Delete File
**DELETE** `/internal/files/{id}`

Delete a file from both the database and Google Cloud Storage.

**Response:**
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

### Download File
**GET** `/internal/files/{id}/download`

Download file content. Returns binary data with appropriate headers.

### Toggle Publication Status
**POST** `/internal/files/{id}/toggle-publication`

Toggle the publication status of a file.

**Response:**
```json
{
  "success": true,
  "message": "Publication status updated successfully",
  "data": {
    "is_published": true,
    "public_url": "/api/public/files/1"
  }
}
```

### Get User File Statistics
**GET** `/internal/files/stats`

Get statistics about the current user's files.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_files": 25,
    "published_files": 15,
    "unpublished_files": 10,
    "total_size": 52428800,
    "total_size_human": "50.0 MB",
    "file_types": [
      {
        "mime_type": "application/pdf",
        "count": 15
      },
      {
        "mime_type": "image/jpeg",
        "count": 10
      }
    ]
  }
}
```

---

## Error Responses

All endpoints return consistent error responses:

**Validation Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

**Authentication Error (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Authorization Error (403):**
```json
{
  "message": "This action is unauthorized."
}
```

**Not Found Error (404):**
```json
{
  "success": false,
  "message": "Resource not found"
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## File Size Limits

- Maximum file size: 100MB
- Supported file types: All types accepted
- Files are stored in Google Cloud Storage with metadata in MySQL database

## Security Features

- All internal operations require authentication
- Real GCS URLs are never exposed to public users
- File access is controlled through the application layer
- Users can only manage their own files
- Publication status controls public visibility