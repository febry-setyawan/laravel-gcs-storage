# Setup Guide

## Laravel GCS Storage Service Setup

This guide will help you set up and configure the Laravel GCS Storage Service.

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Google Cloud Platform account with Storage API enabled
- Web server (Apache/Nginx) or PHP development server

### Step 1: Environment Configuration

1. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file with your configuration:

   **Database Configuration:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_gcs_storage
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

   **Google Cloud Storage Configuration:**
   ```env
   GOOGLE_CLOUD_PROJECT_ID=your-gcp-project-id
   GOOGLE_CLOUD_KEY_FILE=/path/to/service-account-key.json
   GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
   GOOGLE_CLOUD_STORAGE_PATH_PREFIX=uploads
   ```

3. Generate application key:
   ```bash
   php artisan key:generate
   ```

### Step 2: Google Cloud Storage Setup

1. Create a Google Cloud Platform project
2. Enable the Cloud Storage API
3. Create a service account with Storage Admin permissions
4. Download the service account key JSON file
5. Create a storage bucket
6. Update your `.env` file with the correct paths and credentials

### Step 3: Database Setup

1. Create the database:
   ```sql
   CREATE DATABASE laravel_gcs_storage;
   ```

2. Run migrations:
   ```bash
   php artisan migrate
   ```

3. (Optional) Seed sample data:
   ```bash
   php artisan db:seed
   ```

### Step 4: Dependencies Installation

1. Install PHP dependencies:
   ```bash
   composer install
   ```

2. For production, use optimized autoloader:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

### Step 5: Permissions

Set proper file permissions:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Step 6: Web Server Configuration

#### PHP Development Server (for testing)
```bash
php artisan serve
```
The application will be available at `http://localhost:8000`

#### Apache Configuration
Create a virtual host pointing to the `public` directory:
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/laravel-gcs-storage/public
    ServerName your-domain.com
    
    <Directory /path/to/laravel-gcs-storage/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/laravel-gcs-storage/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 7: Testing

1. Test the API health endpoint:
   ```bash
   curl http://localhost:8000/api/health
   ```

2. Run the comprehensive test script:
   ```bash
   ./test-api.sh
   ```

3. Run PHPUnit tests:
   ```bash
   ./vendor/bin/phpunit
   ```

### Step 8: Production Deployment

Use the deployment script for production:
```bash
./deploy.sh
```

This will:
- Install optimized dependencies
- Cache configuration and routes
- Set proper permissions
- Generate application key

## Security Considerations

### File Upload Security
- Maximum file size is set to 100MB
- File type validation is implemented
- Files are stored with unique names to prevent conflicts
- Real storage URLs are hidden from public users

### Authentication Security
- Laravel Sanctum provides secure API authentication
- Tokens can be revoked and have configurable expiration
- Users can only access their own files
- Published status controls public access

### Google Cloud Storage Security
- Service account has minimal required permissions
- Files are stored in private bucket
- Access is controlled through the application
- No direct public access to storage bucket

## Environment Variables Reference

### Required Variables
```env
APP_NAME="Laravel GCS Storage"
APP_KEY=base64:generated_key_here
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_gcs_storage
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=/path/to/key.json
GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket
```

### Optional Variables
```env
GOOGLE_CLOUD_STORAGE_PATH_PREFIX=uploads
GOOGLE_CLOUD_STORAGE_API_URI=https://storage.googleapis.com

LOG_CHANNEL=stack
LOG_LEVEL=debug

CACHE_STORE=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
```

## Next Steps

1. Configure your Google Cloud Storage credentials
2. Test file upload/download functionality
3. Customize the file validation rules if needed
4. Set up monitoring and logging
5. Configure backup strategy for your database
6. Set up SSL/TLS certificate for production

## Support

For issues and questions:
1. Check the troubleshooting section below
2. Review the API documentation
3. Check Laravel and Google Cloud Storage documentation
4. Create an issue in the repository

---

# Troubleshooting

## Common Issues and Solutions

### 1. "Class 'Google\Cloud\Storage\StorageClient' not found"

**Cause:** Google Cloud Storage package not installed

**Solution:**
```bash
composer require google/cloud-storage
```

### 2. "No application encryption key has been specified"

**Cause:** Application key not generated

**Solution:**
```bash
php artisan key:generate
```

### 3. "SQLSTATE[HY000] [1049] Unknown database"

**Cause:** Database doesn't exist

**Solution:**
```sql
CREATE DATABASE laravel_gcs_storage;
```

### 4. "Permission denied" errors

**Cause:** Incorrect file permissions

**Solution:**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. "Google Cloud Storage authentication failed"

**Cause:** Invalid credentials or service account key

**Solution:**
1. Verify the service account key file exists and path is correct
2. Ensure the service account has Storage Admin permissions
3. Check that the project ID is correct
4. Verify the bucket exists and is accessible

### 6. "Maximum execution time exceeded" during file upload

**Cause:** Large file upload or slow connection

**Solution:**
Increase PHP limits in `php.ini`:
```ini
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
```

### 7. "Route [internal.files.show] not defined"

**Cause:** Routes not cached or config cached incorrectly

**Solution:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 8. CORS errors in browser

**Cause:** CORS middleware not configured

**Solution:**
Add the CORS middleware to your API routes or configure CORS properly for your domain.

### 9. "Token Mismatch" errors

**Cause:** CSRF token issues (shouldn't happen with API)

**Solution:**
Ensure you're using the API routes (`/api/*`) and not web routes, and that you're sending the `Authorization: Bearer token` header.

### 10. File uploads fail silently

**Cause:** Various issues with GCS configuration

**Solution:**
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify GCS bucket permissions
3. Test GCS connection manually
4. Check file size limits

## Debug Mode

For debugging, enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**Remember to disable debug mode in production!**

## Performance Tips

1. **Use Redis for caching:**
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

2. **Optimize Composer autoloader:**
   ```bash
   composer dump-autoload --optimize
   ```

3. **Cache configuration for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Use database queue for file processing:**
   ```bash
   php artisan queue:work
   ```

## Monitoring

1. **Monitor storage usage:**
   - Check Google Cloud Storage usage in GCP Console
   - Monitor database size growth
   - Track API usage and response times

2. **Set up logging:**
   - Configure log rotation
   - Monitor error logs
   - Set up alerts for critical errors

3. **Health checks:**
   - Use the `/api/health` endpoint for monitoring
   - Monitor database connectivity
   - Check GCS connectivity