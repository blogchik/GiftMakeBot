# ===========================================
# GIFTMAKEBOT PRODUCTION DEPLOYMENT SCRIPT (PowerShell)
# ===========================================
# This script starts the production environment on Windows
# Author: GiftMakeBot Team
# Version: 1.0

param(
    [switch]$Help,
    [switch]$Logs,
    [switch]$Stop,
    [switch]$SkipSecurityCheck
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
    Write-Host "GiftMakeBot Production Deployment Script" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage:"
    Write-Host "  .\deploy-prod.ps1                    Start production environment"
    Write-Host "  .\deploy-prod.ps1 -Logs              Show container logs"
    Write-Host "  .\deploy-prod.ps1 -Stop              Stop production environment"
    Write-Host "  .\deploy-prod.ps1 -SkipSecurityCheck Skip security credential check"
    Write-Host "  .\deploy-prod.ps1 -Help              Show this help"
    Write-Host ""
    exit 0
}

Write-Host "🚀 GiftMakeBot Production Deployment" -ForegroundColor Cyan
Write-Host ""

# Stop environment if requested
if ($Stop) {
    Write-Status "Stopping production environment..."
    docker compose -f docker-compose.yml -f docker-compose.prod.yml down
    Write-Success "Production environment stopped"
    exit 0
}

# Show logs if requested
if ($Logs) {
    Write-Status "Showing container logs..."
    docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f
    exit 0
}

# Check if running as Administrator (recommended for production)
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Warning "This script is not running as Administrator. Consider running as Administrator for production deployment."
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

# Copy production environment file
Write-Status "Setting up production environment..."
if (Test-Path ".env.production") {
    Copy-Item ".env.production" ".env" -Force
    Write-Success "Production environment configured (.env.production → .env)"
} else {
    Write-Error ".env.production file not found!"
    exit 1
}

# Security check for production credentials
if (-not $SkipSecurityCheck) {
    Write-Status "Checking production security..."
    $envContent = Get-Content ".env" -Raw
    if ($envContent -match "CHANGE_THIS") {
        Write-Error "⚠️  SECURITY ALERT: Default credentials found in .env file!"
        Write-Error "Please update all CHANGE_THIS values in .env before production deployment."
        Write-Host ""
        Write-Host "Required changes:" -ForegroundColor Yellow
        Get-Content ".env" | Where-Object { $_ -match "CHANGE_THIS" } | ForEach-Object { Write-Host "  • $_" -ForegroundColor Red }
        Write-Host ""
        Write-Host "To skip this check (NOT RECOMMENDED), use: .\deploy-prod.ps1 -SkipSecurityCheck" -ForegroundColor Yellow
        exit 1
    }
    Write-Success "Production credentials configured"
}

# Create production directories (Windows paths)
Write-Status "Setting up production directories..."
$prodDirs = @(
    "C:\ProgramData\GiftMakeBot",
    "C:\ProgramData\GiftMakeBot\data",
    "C:\ProgramData\GiftMakeBot\data\redis",
    "C:\ProgramData\GiftMakeBot\data\rabbitmq",
    "C:\ProgramData\GiftMakeBot\logs",
    "C:\ProgramData\GiftMakeBot\logs\nginx",
    "C:\ProgramData\GiftMakeBot\logs\redis",
    "C:\ProgramData\GiftMakeBot\logs\rabbitmq",
    "C:\ProgramData\GiftMakeBot\logs\telegram-bot",
    "C:\ProgramData\GiftMakeBot\logs\api-gateway",
    "C:\ProgramData\GiftMakeBot\logs\health",
    "C:\ProgramData\GiftMakeBot\logs\web_app",
    "C:\ProgramData\GiftMakeBot\ssl"
)

foreach ($dir in $prodDirs) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}
Write-Success "Production directories created"

# Check SSL certificates
Write-Status "Checking SSL certificates..."
if (!(Test-Path "ssl\cert.pem") -or !(Test-Path "ssl\private.key")) {
    Write-Warning "SSL certificates not found. HTTPS will not be available."
    Write-Warning "Please add your SSL certificates to .\ssl\ directory:"
    Write-Warning "  • .\ssl\cert.pem (public certificate)"
    Write-Warning "  • .\ssl\private.key (private key)"
} else {
    Write-Success "SSL certificates found"
}

# Stop existing containers
Write-Status "Stopping existing containers..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml down 2>$null
Write-Success "Existing containers stopped"

# Pull latest images
Write-Status "Pulling latest Docker images..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml pull

# Build and start production environment
Write-Status "Building and starting production services..."
try {
    docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
    
    # Wait for services to be healthy
    Write-Status "Waiting for services to be healthy..."
    Start-Sleep 30
    
    # Health check
    try {
        Invoke-WebRequest -Uri "http://localhost/health" -UseBasicParsing | Out-Null
        
        Write-Success "Production environment deployed successfully!"
        Write-Host ""
        Write-Host "🌐 Production URLs:" -ForegroundColor Cyan
        Write-Host "   • Web Application: https://your-domain.com" -ForegroundColor White
        Write-Host "   • API Gateway:     https://your-domain.com/api" -ForegroundColor White
        Write-Host "   • Health Monitor:   https://your-domain.com/health" -ForegroundColor White
        Write-Host "   • RabbitMQ UI:     http://localhost:15672 (internal only)" -ForegroundColor White
        Write-Host ""
        Write-Host "🔒 Security Features:" -ForegroundColor Cyan
        Write-Host "   • SSL/TLS enabled" -ForegroundColor White
        Write-Host "   • Rate limiting active" -ForegroundColor White
        Write-Host "   • Debug mode disabled" -ForegroundColor White
        Write-Host "   • Resource limits applied" -ForegroundColor White
        Write-Host ""
        Write-Host "📊 Monitoring:" -ForegroundColor Cyan
        Write-Host "   • Health endpoint: /health" -ForegroundColor White
        Write-Host "   • Container logs: .\deploy-prod.ps1 -Logs" -ForegroundColor White
        Write-Host "   • System metrics: enabled" -ForegroundColor White
        Write-Host ""
        Write-Success "🎉 Production deployment completed!"
        Write-Warning "⚠️  Remember to configure your reverse proxy/load balancer to point to port 80/443"
        Write-Warning "⚠️  Set up regular backups and monitoring"
        Write-Warning "⚠️  Review firewall rules and security settings"
        
    } catch {
        Write-Error "Health check failed! Please check the logs with: .\deploy-prod.ps1 -Logs"
        exit 1
    }
    
} catch {
    Write-Error "Failed to start production environment!"
    Write-Error $_.Exception.Message
    exit 1
}