# 🌍 Multi-Environment Deployment Guide

GiftMakeBot supports two distinct deployment environments:
- **Development** - For local development with hot reload and debugging
- **Production** - For live deployment with security and performance optimizations

## 📁 Environment Files

| File | Purpose | Usage |
|------|---------|-------|
| `.env.development` | Development configuration | Local development, debugging, hot reload |
| `.env.production` | Production configuration | Live deployment, security hardened |
| `docker-compose.yml` | Base configuration | Shared settings for all environments |
| `docker-compose.dev.yml` | Development overrides | Development-specific settings |
| `docker-compose.prod.yml` | Production overrides | Production optimizations |

## 🚀 Quick Start

### Development Environment

**Linux/Mac:**
```bash
./deploy-dev.sh
```

**Windows PowerShell:**
```powershell
.\deploy-dev.ps1
```

**Manual:**
```bash
# Copy development environment
cp .env.development .env

# Start development services
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

### Production Environment

**Linux/Mac:**
```bash
# ⚠️ Update credentials in .env.production first!
./deploy-prod.sh
```

**Windows PowerShell:**
```powershell
# ⚠️ Update credentials in .env.production first!
.\deploy-prod.ps1
```

**Manual:**
```bash
# Copy production environment
cp .env.production .env

# Start production services  
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

## 🔧 Environment Configurations

### Development Environment
- **Debug Mode**: Enabled
- **Hot Reload**: Active
- **Ports**: Non-standard (8080, 6380, 5673, etc.)
- **Logging**: Verbose debug logs
- **Security**: Development credentials (safe for local use)
- **CORS**: Enabled for local development
- **SSL**: Optional
- **Resources**: No limits

### Production Environment
- **Debug Mode**: Disabled
- **Hot Reload**: Disabled
- **Ports**: Standard (80, 443, 6379, 5672)
- **Logging**: Error-level only
- **Security**: Must change all default credentials
- **CORS**: Disabled
- **SSL**: Required (configure certificates)
- **Resources**: CPU and memory limits applied

## 🌐 Service URLs

### Development
| Service | URL | Description |
|---------|-----|-------------|
| Web App | http://localhost:8080 | Main application |
| API Gateway | http://localhost:8080/api | REST API |
| Health Monitor | http://localhost:8080/health | System status |
| RabbitMQ UI | http://localhost:15673 | Message queue management |
| React Dev Server | http://localhost:3001 | Hot reload React app |
| Redis | localhost:6380 | Cache database |

### Production
| Service | URL | Description |
|---------|-----|-------------|
| Web App | https://your-domain.com | Main application |
| API Gateway | https://your-domain.com/api | REST API |
| Health Monitor | https://your-domain.com/health | System status |
| RabbitMQ UI | http://localhost:15672 | Internal management only |
| Redis | Internal only | Cache database |

## 📋 Deployment Scripts

### Development Scripts
- `deploy-dev.sh` - Linux/Mac deployment script  
- `deploy-dev.ps1` - Windows PowerShell script

**Features:**
- Automatic environment setup
- Log directory creation
- Container management
- Health checks
- Useful command shortcuts

**Usage:**
```bash
./deploy-dev.sh           # Start development environment
./deploy-dev.ps1 -Logs    # View container logs
./deploy-dev.ps1 -Stop    # Stop all services
./deploy-dev.ps1 -Help    # Show help
```

### Production Scripts
- `deploy-prod.sh` - Linux/Mac production deployment
- `deploy-prod.ps1` - Windows PowerShell script

**Features:**
- Security credential validation
- SSL certificate checks
- Production directory setup
- Automatic backups
- Health verification
- Performance monitoring

**Usage:**
```bash
./deploy-prod.sh                    # Deploy to production
./deploy-prod.ps1 -Logs             # View logs
./deploy-prod.ps1 -Stop             # Stop production
./deploy-prod.ps1 -SkipSecurityCheck # Skip credential check (NOT recommended)
```

## 🔒 Security Checklist

### Before Production Deployment

#### Required Changes in `.env.production`:
- [ ] `REDIS_PASSWORD` - Change from default
- [ ] `RABBITMQ_USER` - Change from default  
- [ ] `RABBITMQ_PASSWORD` - Change from default
- [ ] `RABBITMQ_ERLANG_COOKIE` - Generate unique cookie
- [ ] `TELEGRAM_BOT_TOKEN` - Your production bot token
- [ ] `TELEGRAM_SUPER_ADMIN_ID` - Your admin user ID

#### SSL Certificate Setup:
- [ ] Add `ssl/cert.pem` (public certificate)
- [ ] Add `ssl/private.key` (private key)
- [ ] Update `SSL_CERT_PATH` and `SSL_KEY_PATH` if needed

#### System Security:
- [ ] Configure firewall rules
- [ ] Set up reverse proxy/load balancer
- [ ] Enable automatic security updates
- [ ] Configure log rotation
- [ ] Set up monitoring and alerts

## 📊 Monitoring & Maintenance

### Health Monitoring
All environments include a comprehensive health monitoring system:

```bash
# Check system health
curl http://localhost/health

# Or for production
curl https://your-domain.com/health
```

### Log Management
**Development:**
```bash
# View all logs
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f

# View specific service
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f nginx
```

**Production:**
```bash
# View all logs
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# View specific service
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f nginx
```

### Backup & Recovery
**Development:**
```bash
# Development uses local volumes - backup not critical
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```

**Production:**
```bash
# Production uses persistent volumes
# Backup is automatically attempted during deployment
# Manual backup:
docker exec giftmakebot_prod_redis redis-cli --rdb /backup/redis-backup.rdb
```

## 🛠️ Troubleshooting

### Common Issues

#### Port Conflicts
**Problem:** Port already in use
**Solution:** Change ports in environment files or stop conflicting services

#### Docker Not Running
**Problem:** Docker daemon not accessible
**Solution:** Start Docker Desktop/Service

#### Permission Denied
**Problem:** Cannot create directories
**Solution:** Run with administrator/sudo privileges

#### Health Check Failed
**Problem:** Services not responding
**Solution:** Check logs and verify all containers are running

### Environment Switching
```bash
# Switch from development to production
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
cp .env.production .env
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Switch from production to development  
docker compose -f docker-compose.yml -f docker-compose.prod.yml down
cp .env.development .env
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

## 🎯 Best Practices

### Development
1. Always use development environment for coding
2. Keep hot reload enabled for faster development
3. Use verbose logging for debugging
4. Regularly check health endpoint
5. Don't commit `.env` files

### Production
1. Never use development credentials in production
2. Always use HTTPS with valid certificates
3. Implement proper monitoring and alerting
4. Regular security updates and patches
5. Backup strategy for data persistence
6. Load testing before deployment
7. Proper firewall and network security

## 📝 Configuration Reference

### Environment Variables

#### Application Settings
- `APP_ENV` - Environment type (development/production)
- `DEBUG` - Enable/disable debug mode

#### Container Names
- `*_CONTAINER_NAME` - Custom container names per environment

#### Network Settings  
- `NETWORK_NAME` - Docker network name
- `NETWORK_DRIVER` - Network driver type

#### Port Mappings
- `HTTP_PORT` - Web server port
- `HTTPS_PORT` - SSL port
- `REDIS_PORT` - Redis port
- `RABBITMQ_PORT` - RabbitMQ AMQP port
- `RABBITMQ_MANAGEMENT_PORT` - RabbitMQ management UI port

#### Service Configuration
- `REDIS_PASSWORD` - Redis authentication
- `RABBITMQ_USER` - RabbitMQ username
- `RABBITMQ_PASSWORD` - RabbitMQ password
- `TELEGRAM_BOT_TOKEN` - Bot API token
- `TELEGRAM_SUPER_ADMIN_ID` - Bot admin user ID

## 🤝 Contributing

When adding new features:
1. Update both environment configurations
2. Add appropriate overrides in dev/prod compose files
3. Update deployment scripts if needed
4. Document configuration changes
5. Test in both environments

## 📞 Support

For issues and questions:
1. Check troubleshooting section
2. Review container logs
3. Verify environment configuration
4. Test health endpoints
5. Create GitHub issue if needed