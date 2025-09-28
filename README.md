# 🎁 GiftMakeBot - Multi-Environment Microservices Platform

A powerful Telegram bot platform built with Docker microservices architecture, supporting both development and production environments.

## 🚀 Quick Start

### Development Environment
```bash
# Linux/Mac
./deploy-dev.sh

# Windows PowerShell  
.\deploy-dev.ps1
```

### Production Environment
```bash
# ⚠️ Configure credentials in .env.production first!

# Linux/Mac
./deploy-prod.sh

# Windows PowerShell
.\deploy-prod.ps1
```

## 🏗️ Architecture

### Services
- **🌐 Nginx** - Reverse proxy and load balancer
- **🤖 Telegram Bot** - PHP-FPM bot backend
- **🔗 API Gateway** - REST API service
- **⚡ Redis** - High-performance cache
- **🐰 RabbitMQ** - Message queue with management UI
- **💊 Health Monitor** - System health monitoring
- **📱 Web App** - React frontend application

### Environments
- **Development** - Hot reload, debugging, verbose logs
- **Production** - Optimized, secured, monitoring enabled

## 📋 Prerequisites

- Docker Desktop/Engine
- Docker Compose v2.0+
- PowerShell (Windows) or Bash (Linux/Mac)

## 🎯 Environment Setup

### 🔧 Development Configuration
```bash
# Automatic setup
cp .env.development .env

# Manual configuration
APP_ENV=development
DEBUG=true
HTTP_PORT=8080
REDIS_PORT=6380
# ... development settings
```

### 🔒 Production Configuration  
```bash
# ⚠️ SECURITY CRITICAL: Update all CHANGE_THIS values!
cp .env.production .env

# Required changes:
REDIS_PASSWORD=YourSecurePassword2025
RABBITMQ_USER=your_admin_user  
RABBITMQ_PASSWORD=YourSecureRabbitMQPassword2025
TELEGRAM_BOT_TOKEN=your_production_bot_token
TELEGRAM_SUPER_ADMIN_ID=your_admin_id
```

## 🌐 Service Access

### Development URLs
| Service | URL | Description |
|---------|-----|-------------|
| Web App | http://localhost:8080 | Main application |
| API Gateway | http://localhost:8080/api | REST API |
| Health Monitor | http://localhost:8080/health | System status |
| RabbitMQ UI | http://localhost:15673 | Message queue management |
| React Dev | http://localhost:3001 | Hot reload development |

### Production URLs
| Service | URL | Description |
|---------|-----|-------------|
| Web App | https://your-domain.com | Main application |
| API Gateway | https://your-domain.com/api | REST API |
| Health Monitor | https://your-domain.com/health | System status |
| RabbitMQ UI | http://localhost:15672 | Internal management |

## 🛠️ Development Commands

```bash
# Start development environment
./deploy-dev.sh                 # Linux/Mac
.\deploy-dev.ps1                # Windows

# View logs
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f

# Stop services  
.\deploy-dev.ps1 -Stop          # Windows
docker compose -f docker-compose.yml -f docker-compose.dev.yml down

# Restart specific service
docker compose -f docker-compose.yml -f docker-compose.dev.yml restart nginx
```

## 🚀 Production Commands

```bash
# Deploy to production (with security checks)
./deploy-prod.sh                # Linux/Mac  
.\deploy-prod.ps1               # Windows

# View production logs
.\deploy-prod.ps1 -Logs         # Windows
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# Stop production
.\deploy-prod.ps1 -Stop         # Windows
docker compose -f docker-compose.yml -f docker-compose.prod.yml down
```

## 📊 Monitoring & Health Checks

### Health Endpoint
```bash
# Development
curl http://localhost:8080/health

# Production  
curl https://your-domain.com/health
```

### Service Status
```json
{
  "overall_status": "healthy",
  "services": {
    "nginx": {"status": "healthy"},
    "redis": {"status": "healthy"},
    "rabbitmq": {"status": "healthy"},
    "telegram-bot": {"status": "healthy"},
    "api-gateway": {"status": "healthy"},
    "web_app": {"status": "healthy"},
    "system": {"status": "healthy"}
  }
}
```

## 🔒 Security Features

### Development
- ✅ Safe default credentials
- ✅ Hot reload enabled
- ✅ Verbose debug logging
- ✅ CORS enabled for local development

### Production  
- ✅ SSL/TLS encryption
- ✅ Rate limiting
- ✅ Resource limits
- ✅ Security headers
- ✅ Production credential validation
- ✅ Monitoring and alerting

## 📚 Documentation

- **[📖 Deployment Guide](DEPLOYMENT.md)** - Complete multi-environment setup
- **[🔧 Service Guide](NEW_SERVICES_GUIDE.md)** - Adding new services
- **[🏥 Health API](services/health/index.php)** - Health monitoring details

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Test in development environment
4. Update documentation
5. Submit pull request

## 📝 Environment Files

| File | Purpose |
|------|---------|
| `.env.development` | Development configuration |
| `.env.production` | Production configuration |  
| `.env.example` | Template with all variables |

## 🚨 Security Checklist

Before production deployment:

- [ ] Change all default passwords
- [ ] Configure SSL certificates
- [ ] Set up firewall rules
- [ ] Configure monitoring
- [ ] Test backup procedures
- [ ] Verify health endpoints

## 📞 Support

- 🐛 **Issues**: Create GitHub issue
- 📖 **Documentation**: Check DEPLOYMENT.md
- 💡 **Features**: Submit feature request
- 🔒 **Security**: Report security issues privately

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

- **Nginx**: http://localhost (ports from .env)
- **Redis**: localhost:6379 (requires password)
- **Telegram Bot**: Runs in background

## Useful Commands

```powershell
# View logs
docker compose logs -f telegram-bot

# Restart specific service
docker compose restart telegram-bot

# Stop all services
docker compose down

# Rebuild and start
docker compose up -d --build
```