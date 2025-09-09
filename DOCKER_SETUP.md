# Docker Local Development Guide

This guide will help you set up and run the Laravel GCS Storage application locally using Docker.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 2.0 or higher)
- Google Cloud Storage account and service account credentials

## Quick Start

1. **Clone the repository** (if you haven't already):
   ```bash
   git clone https://github.com/febry-setyawan/laravel-gcs-storage.git
   cd laravel-gcs-storage
   ```

2. **Run the setup script**:
   ```bash
   ./docker-setup.sh
   ```

3. **Configure Google Cloud Storage**:
   - Place your GCS service account JSON file in `storage/app/gcs-service-account.json`
   - Update the `.env` file with your GCS credentials:
     ```env
     GOOGLE_CLOUD_PROJECT_ID=your-project-id
     GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
     ```

4. **Access the application**:
   - Application: http://localhost:8000
   - phpMyAdmin: http://localhost:8080
   - API Documentation: http://localhost:8000/api/documentation

## Manual Setup

If you prefer to set up manually:

1. **Copy environment file**:
   ```bash
   cp .env.docker .env
   ```

2. **Build and start containers**:
   ```bash
   docker-compose build
   docker-compose up -d
   ```

3. **Install dependencies and setup Laravel**:
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate:fresh --seed
   docker-compose exec app php artisan storage:link
   ```

## Services

### Application Container (app)
- **Port**: 8000
- **PHP**: 8.3-fpm-alpine
- **Web Server**: Nginx
- **Process Manager**: Supervisor

### Database Container (mysql)
- **Port**: 3306
- **Image**: MySQL 8.0
- **Database**: `laravel_gcs_storage`
- **Username**: `laravel`
- **Password**: `secret`

### Cache Container (redis)
- **Port**: 6379
- **Image**: Redis Alpine
- **Used for**: Sessions, caching, queues

### Database Management (phpmyadmin)
- **Port**: 8080
- **Access**: http://localhost:8080
- **Username**: `laravel`
- **Password**: `secret`

## Useful Commands

### Docker Compose Commands
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f app

# Restart a service
docker-compose restart app

# Rebuild containers
docker-compose build --no-cache

# Remove all containers and volumes
docker-compose down -v --remove-orphans
```

### Laravel Commands (inside container)
```bash
# Access the application container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app php artisan queue:work

# Run tests
docker-compose exec app php artisan test

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Database Commands
```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u laravel -p laravel_gcs_storage

# Import database
docker-compose exec mysql mysql -u laravel -p laravel_gcs_storage < backup.sql

# Create database backup
docker-compose exec mysql mysqldump -u laravel -p laravel_gcs_storage > backup.sql
```

## Google Cloud Storage Setup

1. **Create a Google Cloud Project** (if you don't have one)

2. **Enable Cloud Storage API**:
   - Go to Google Cloud Console
   - Navigate to APIs & Services > Library
   - Search for "Cloud Storage API" and enable it

3. **Create a Service Account**:
   - Go to IAM & Admin > Service Accounts
   - Click "Create Service Account"
   - Give it a name and description
   - Grant "Storage Admin" role
   - Create and download the JSON key file

4. **Create a Storage Bucket**:
   - Go to Cloud Storage > Buckets
   - Click "Create Bucket"
   - Choose a unique name
   - Select appropriate location and storage class

5. **Configure the Application**:
   - Place the service account JSON file in `storage/app/gcs-service-account.json`
   - Update `.env` with your project details:
     ```env
     GOOGLE_CLOUD_PROJECT_ID=your-project-id
     GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
     ```

## Testing the API

Once the application is running, you can test the API endpoints:

### Authentication
```bash
# Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### File Operations (replace TOKEN with actual token)
```bash
# Upload a file
curl -X POST http://localhost:8000/api/internal/files \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@/path/to/your/file.pdf" \
  -F "description=Test file"

# List files
curl -X GET http://localhost:8000/api/internal/files \
  -H "Authorization: Bearer TOKEN"

# Make file public
curl -X POST http://localhost:8000/api/internal/files/1/toggle-publication \
  -H "Authorization: Bearer TOKEN"

# Access public files (no authentication needed)
curl -X GET http://localhost:8000/api/public/files
```

## Troubleshooting

### Port Conflicts
If you have port conflicts, you can change the ports in `.env`:
```env
APP_PORT=8001
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
FORWARD_PHPMYADMIN_PORT=8081
```

### Permission Issues
If you encounter permission issues:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### MySQL Connection Issues
If Laravel can't connect to MySQL:
1. Wait a bit longer for MySQL to fully initialize
2. Check MySQL logs: `docker-compose logs mysql`
3. Restart the containers: `docker-compose restart`

### Google Cloud Storage Issues
1. Verify your service account JSON file is correctly placed
2. Check that your service account has proper permissions
3. Ensure your bucket exists and is accessible
4. Verify your project ID and bucket name in `.env`

### Application Not Loading
1. Check application logs: `docker-compose logs app`
2. Verify all containers are running: `docker-compose ps`
3. Check if the key is generated: `docker-compose exec app php artisan key:generate`
4. Clear Laravel caches: `docker-compose exec app php artisan config:clear`

## Development Workflow

For development, you can:

1. **Watch for file changes**: The application directory is mounted as a volume, so changes to PHP files are reflected immediately.

2. **Use Xdebug**: Xdebug is configured and can be enabled by setting `SAIL_XDEBUG_MODE=develop,debug` in your `.env` file.

3. **Run tests continuously**: 
   ```bash
   docker-compose exec app php artisan test --watch
   ```

4. **Monitor queues**: 
   ```bash
   docker-compose exec app php artisan queue:work --verbose
   ```

## Stopping the Environment

To stop the development environment:
```bash
# Stop containers but keep data
docker-compose down

# Stop containers and remove volumes (deletes database data)
docker-compose down -v

# Remove all containers, networks, and images
docker-compose down -v --rmi all --remove-orphans
```