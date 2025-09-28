# ===========================================
# GIFTMAKEBOT DEVELOPMENT DEPLOYMENT SCRIPT (PowerShell)
# ===========================================
# This script starts the development environment on Windows
# Author: GiftMakeBot Team
# Version: 1.0

param(
    [switch]$Help,
    [switch]$Logs,
    [switch]$Stop
)

# Colors for output
function Write-Status {
    param($Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param($Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param($Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param($Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Help function
if ($Help) {
    Write-Host "GiftMakeBot Development Deployment Script" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage:"
    Write-Host "  .\deploy-dev.ps1              Start development environment"
    Write-Host "  .\deploy-dev.ps1 -Logs        Show container logs"
    Write-Host "  .\deploy-dev.ps1 -Stop        Stop development environment"
    Write-Host "  .\deploy-dev.ps1 -Help        Show this help"
    Write-Host ""
    exit 0
}

Write-Host "🚀 GiftMakeBot Development Environment" -ForegroundColor Cyan
Write-Host ""

# Stop environment if requested
if ($Stop) {
    Write-Status "Stopping development environment..."
    docker compose -f docker-compose.yml -f docker-compose.dev.yml down
    Write-Success "Development environment stopped"
    exit 0
}

# Show logs if requested
if ($Logs) {
    Write-Status "Showing container logs..."
    docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f
    exit 0
}

# Check if Docker is running
Write-Status "Checking Docker..."
try {
    docker info | Out-Null
    Write-Success "Docker is running"
} catch {
    Write-Error "Docker is not running. Please start Docker Desktop and try again."
    exit 1
}

# Copy development environment file
Write-Status "Setting up development environment..."
if (Test-Path ".env.development") {
    Copy-Item ".env.development" ".env" -Force
    Write-Success "Development environment configured (.env.development → .env)"
} else {
    Write-Error ".env.development file not found!"
    exit 1
}

# Create logs directory
Write-Status "Creating logs directory..."
$logDirs = @(
    "logs",
    "logs\nginx",
    "logs\redis", 
    "logs\rabbitmq",
    "logs\telegram-bot",
    "logs\api-gateway",
    "logs\health",
    "logs\web_app"
)

foreach ($dir in $logDirs) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}
Write-Success "Logs directory created"

# Stop existing containers
Write-Status "Stopping existing containers..."
docker compose -f docker-compose.yml -f docker-compose.dev.yml down 2>$null
Write-Success "Existing containers stopped"

# Build and start development environment
Write-Status "Building and starting development services..."
try {
    docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
    
    Write-Success "Development environment started successfully!"
    Write-Host ""
    Write-Host "🌐 Development URLs:" -ForegroundColor Cyan
    Write-Host "   • Web Application: http://localhost:8080" -ForegroundColor White
    Write-Host "   • API Gateway:     http://localhost:8080/api" -ForegroundColor White
    Write-Host "   • Health Monitor:   http://localhost:8080/health" -ForegroundColor White
    Write-Host "   • RabbitMQ UI:     http://localhost:15673" -ForegroundColor White
    Write-Host "   • React Dev:       http://localhost:3001" -ForegroundColor White
    Write-Host ""
    Write-Host "📊 Development Tools:" -ForegroundColor Cyan
    Write-Host "   • Redis:           localhost:6380" -ForegroundColor White
    Write-Host "   • RabbitMQ AMQP:   localhost:5673" -ForegroundColor White
    Write-Host "   • Hot Reload:      Enabled" -ForegroundColor White
    Write-Host "   • Debug Mode:      Enabled" -ForegroundColor White
    Write-Host ""
    Write-Host "📝 Useful Commands:" -ForegroundColor Cyan
    Write-Host "   • View logs:       .\deploy-dev.ps1 -Logs" -ForegroundColor White
    Write-Host "   • Stop services:   .\deploy-dev.ps1 -Stop" -ForegroundColor White
    Write-Host "   • Restart:         docker compose -f docker-compose.yml -f docker-compose.dev.yml restart" -ForegroundColor White
    Write-Host ""
    Write-Warning "Remember: This is a DEVELOPMENT environment with debug enabled and default credentials!"
    
} catch {
    Write-Error "Failed to start development environment!"
    Write-Error $_.Exception.Message
    exit 1
}