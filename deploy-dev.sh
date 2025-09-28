#!/bin/bash

# ===========================================
# GIFTMAKEBOT DEVELOPMENT DEPLOYMENT SCRIPT
# ===========================================
# This script starts the development environment
# Author: GiftMakeBot Team
# Version: 1.0

set -e

echo "🚀 Starting GiftMakeBot Development Environment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
print_status "Checking Docker..."
if ! docker info >/dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi
print_success "Docker is running"

# Check if docker-compose is available
if ! command -v docker-compose >/dev/null 2>&1; then
    print_warning "docker-compose not found, trying docker compose..."
    COMPOSE_CMD="docker compose"
else
    COMPOSE_CMD="docker-compose"
fi

# Copy development environment file
print_status "Setting up development environment..."
if [ -f ".env.development" ]; then
    cp .env.development .env
    print_success "Development environment configured (.env.development → .env)"
else
    print_error ".env.development file not found!"
    exit 1
fi

# Create logs directory
print_status "Creating logs directory..."
mkdir -p logs/{nginx,redis,rabbitmq,telegram-bot,api-gateway,health,web_app}
print_success "Logs directory created"

# Stop existing containers (if any)
print_status "Stopping existing containers..."
$COMPOSE_CMD -f docker-compose.yml -f docker-compose.dev.yml down >/dev/null 2>&1 || true
print_success "Existing containers stopped"

# Build and start development environment
print_status "Building and starting development services..."
$COMPOSE_CMD -f docker-compose.yml -f docker-compose.dev.yml up -d --build

if [ $? -eq 0 ]; then
    print_success "Development environment started successfully!"
    echo ""
    echo "🌐 Development URLs:"
    echo "   • Web Application: http://localhost:8080"
    echo "   • API Gateway:     http://localhost:8080/api"
    echo "   • Health Monitor:   http://localhost:8080/health"
    echo "   • RabbitMQ UI:     http://localhost:15673"
    echo "   • React Dev:       http://localhost:3001"
    echo ""
    echo "📊 Development Tools:"
    echo "   • Redis:           localhost:6380"
    echo "   • RabbitMQ AMQP:   localhost:5673"
    echo "   • Hot Reload:      Enabled"
    echo "   • Debug Mode:      Enabled"
    echo ""
    echo "📝 Useful Commands:"
    echo "   • View logs:       docker-compose -f docker-compose.yml -f docker-compose.dev.yml logs -f"
    echo "   • Stop services:   docker-compose -f docker-compose.yml -f docker-compose.dev.yml down"
    echo "   • Restart service: docker-compose -f docker-compose.yml -f docker-compose.dev.yml restart <service>"
    echo ""
    print_warning "Remember: This is a DEVELOPMENT environment with debug enabled and default credentials!"
else
    print_error "Failed to start development environment!"
    exit 1
fi