#!/bin/bash

# ===========================================
# GIFTMAKEBOT PRODUCTION DEPLOYMENT SCRIPT
# ===========================================
# This script starts the production environment
# Author: GiftMakeBot Team
# Version: 1.0

set -e

echo "🚀 Starting GiftMakeBot Production Deployment..."

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

# Check if running as root (recommended for production)
if [[ $EUID -ne 0 ]]; then
   print_warning "This script is not running as root. Consider running with sudo for production deployment."
fi

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

# Copy production environment file
print_status "Setting up production environment..."
if [ -f ".env.production" ]; then
    cp .env.production .env
    print_success "Production environment configured (.env.production → .env)"
else
    print_error ".env.production file not found!"
    exit 1
fi

# Security check for production credentials
print_status "Checking production security..."
if grep -q "CHANGE_THIS" .env; then
    print_error "⚠️  SECURITY ALERT: Default credentials found in .env file!"
    print_error "Please update all CHANGE_THIS values in .env before production deployment."
    echo ""
    echo "Required changes:"
    grep "CHANGE_THIS" .env | sed 's/^/  • /'
    echo ""
    exit 1
fi
print_success "Production credentials configured"

# Create production directories
print_status "Setting up production directories..."
mkdir -p /opt/giftmakebot/{data,logs,ssl}
mkdir -p /opt/giftmakebot/data/{redis,rabbitmq}
mkdir -p /opt/giftmakebot/logs/{nginx,redis,rabbitmq,telegram-bot,api-gateway,health,web_app}

# Set proper permissions
chown -R 1000:1000 /opt/giftmakebot/data
chown -R 1000:1000 /opt/giftmakebot/logs
print_success "Production directories created"

# Check SSL certificates
print_status "Checking SSL certificates..."
if [ ! -f "./ssl/cert.pem" ] || [ ! -f "./ssl/private.key" ]; then
    print_warning "SSL certificates not found. HTTPS will not be available."
    print_warning "Please add your SSL certificates to ./ssl/ directory:"
    print_warning "  • ./ssl/cert.pem (public certificate)"
    print_warning "  • ./ssl/private.key (private key)"
else
    print_success "SSL certificates found"
fi

# Create backup of current deployment (if exists)
if docker ps -a --format "table {{.Names}}" | grep -q "giftmakebot_prod"; then
    print_status "Creating backup of current deployment..."
    $COMPOSE_CMD -f docker-compose.yml -f docker-compose.prod.yml exec -T postgres pg_dump -U postgres giftmakebot > "backup_$(date +%Y%m%d_%H%M%S).sql" 2>/dev/null || true
    print_success "Backup created (if database exists)"
fi

# Stop existing containers
print_status "Stopping existing containers..."
$COMPOSE_CMD -f docker-compose.yml -f docker-compose.prod.yml down >/dev/null 2>&1 || true
print_success "Existing containers stopped"

# Pull latest images
print_status "Pulling latest Docker images..."
$COMPOSE_CMD -f docker-compose.yml -f docker-compose.prod.yml pull

# Build and start production environment
print_status "Building and starting production services..."
$COMPOSE_CMD -f docker-compose.yml -f docker-compose.prod.yml up -d --build

if [ $? -eq 0 ]; then
    # Wait for services to be healthy
    print_status "Waiting for services to be healthy..."
    sleep 30
    
    # Health check
    if curl -s http://localhost/health >/dev/null 2>&1; then
        print_success "Production environment deployed successfully!"
        echo ""
        echo "🌐 Production URLs:"
        echo "   • Web Application: https://your-domain.com"
        echo "   • API Gateway:     https://your-domain.com/api"
        echo "   • Health Monitor:   https://your-domain.com/health"
        echo "   • RabbitMQ UI:     http://localhost:15672 (internal only)"
        echo ""
        echo "🔒 Security Features:"
        echo "   • SSL/TLS enabled"
        echo "   • Rate limiting active"
        echo "   • Debug mode disabled"
        echo "   • Resource limits applied"
        echo ""
        echo "📊 Monitoring:"
        echo "   • Health endpoint: /health"
        echo "   • Container logs: docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs"
        echo "   • System metrics: enabled"
        echo ""
        print_success "🎉 Production deployment completed!"
        print_warning "⚠️  Remember to configure your reverse proxy/load balancer to point to port 80/443"
        print_warning "⚠️  Set up regular backups and monitoring"
        print_warning "⚠️  Review firewall rules and security settings"
    else
        print_error "Health check failed! Please check the logs:"
        print_error "$COMPOSE_CMD -f docker-compose.yml -f docker-compose.prod.yml logs"
        exit 1
    fi
else
    print_error "Failed to start production environment!"
    exit 1
fi