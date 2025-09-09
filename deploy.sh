#!/bin/bash

# Laravel GCS Storage Deployment Script

echo "Starting deployment..."

# Copy environment file
if [ ! -f .env ]; then
    echo "Copying environment file..."
    cp .env.example .env
    echo "Please edit .env file with your configuration before continuing."
    echo "Required variables:"
    echo "- Database connection details"
    echo "- Google Cloud Storage credentials"
    echo "- Application key"
    exit 1
fi

# Install dependencies
echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Generate application key if not set
echo "Generating application key..."
php artisan key:generate

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure your web server to point to the 'public' directory"
echo "2. Set up Google Cloud Storage credentials"
echo "3. Test the API endpoints"
echo ""
echo "API Health Check: curl http://your-domain/api/health"