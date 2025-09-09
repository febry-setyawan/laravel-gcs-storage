#!/bin/bash

# Laravel GCS Storage - Docker Setup Script
echo "🐳 Setting up Laravel GCS Storage with Docker..."

# Check if Docker and Docker Compose are installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker template..."
    cp .env.docker .env
else
    echo "⚠️  .env file already exists. You may want to update it with Docker-specific settings."
fi

# Build and start the containers
echo "🏗️  Building Docker containers..."
docker compose build

echo "🚀 Starting Docker containers..."
docker compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Run Laravel setup commands
echo "🔧 Setting up Laravel application..."
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan storage:link
docker compose exec app composer install

# Set proper permissions
echo "🔐 Setting proper permissions..."
docker compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo ""
echo "🎉 Setup complete! Your Laravel GCS Storage application is now running."
echo ""
echo "📊 Services Available:"
echo "   • Application: http://localhost:8000"
echo "   • phpMyAdmin: http://localhost:8080"
echo "   • MySQL: localhost:3306"
echo "   • Redis: localhost:6379"
echo ""
echo "📁 Database Credentials:"
echo "   • Database: laravel_gcs_storage"
echo "   • Username: laravel"
echo "   • Password: secret"
echo ""
echo "🔧 Useful Docker Commands:"
echo "   • View logs: docker compose logs -f"
echo "   • Stop services: docker compose down"
echo "   • Restart services: docker compose restart"
echo "   • Run artisan commands: docker compose exec app php artisan [command]"
echo "   • Access container shell: docker compose exec app bash"
echo ""
echo "⚠️  Don't forget to:"
echo "   1. Set up your Google Cloud Storage credentials in .env"
echo "   2. Place your GCS service account JSON file in storage/app/"
echo "   3. Update GOOGLE_CLOUD_* variables in .env"
echo ""