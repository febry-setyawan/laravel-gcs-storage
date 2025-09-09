# Quick Start Guide - Docker Development

This guide gets you up and running with the Laravel GCS Storage application in under 5 minutes using Docker.

## Prerequisites

- Docker Desktop installed and running
- Git installed

## Quick Setup

1. **Clone and enter the project**:
   ```bash
   git clone https://github.com/febry-setyawan/laravel-gcs-storage.git
   cd laravel-gcs-storage
   ```

2. **Run the setup script**:
   ```bash
   ./docker-setup.sh
   ```
   
   This will:
   - Build Docker containers
   - Start all services (app, MySQL, Redis, phpMyAdmin)
   - Install dependencies
   - Run database migrations
   - Set up proper permissions

3. **Access the application**:
   - **Main App**: http://localhost:8000
   - **Database Admin**: http://localhost:8080 (login: laravel/secret)

## Test the API

Register a user and test the API:

```bash
# Register a new user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Login and get token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# List files (replace TOKEN with actual token from login)
curl -X GET http://localhost:8000/api/internal/files \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Configure Google Cloud Storage

To enable file uploads to GCS:

1. **Get GCS credentials**:
   - Create a Google Cloud project
   - Enable Cloud Storage API
   - Create a service account with Storage Admin role
   - Download the JSON key file

2. **Configure the app**:
   ```bash
   # Copy your service account file
   cp /path/to/your/service-account.json storage/app/gcs-service-account.json
   
   # Edit .env file
   nano .env
   ```
   
   Update these values in `.env`:
   ```env
   GOOGLE_CLOUD_PROJECT_ID=your-project-id
   GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
   ```

3. **Restart the application**:
   ```bash
   docker compose restart app
   ```

## Common Commands

```bash
# View application logs
docker compose logs -f app

# Access application shell
docker compose exec app bash

# Run Laravel commands
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker

# Stop everything
docker compose down

# Start again
docker compose up -d
```

## Troubleshooting

**Port conflicts?** Edit `.env` and change:
```env
APP_PORT=8001
FORWARD_DB_PORT=3307
FORWARD_PHPMYADMIN_PORT=8081
```

**Permission issues?** Run:
```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
```

**Need help?** Check the full documentation in [DOCKER_SETUP.md](DOCKER_SETUP.md)

---

**You're ready to develop!** 🎉

The application is now running locally with all services containerized and ready for development.