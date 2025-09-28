# 🔧 Docker Compose va .env File Konfiguratsiyasi

## ✅ Barcha Konfiguratsiya .env orqali boshqariladi

### 📋 **Environment Variables ro'yxati:**

| **Kategoriya** | **O'zgaruvchi nomi** | **Default qiymati** | **Tavsif** |
|----------------|---------------------|---------------------|------------|
| **Application** | `APP_ENV` | `production` | Ilova muhiti |
| | `DEBUG` | `false` | Debug rejimi |
| **Container Names** | `NGINX_CONTAINER_NAME` | `giftmakebot_nginx` | Nginx konteyner nomi |
| | `REDIS_CONTAINER_NAME` | `giftmakebot_redis` | Redis konteyner nomi |
| | `TELEGRAM_BOT_CONTAINER_NAME` | `giftmakebot_telegram` | Bot konteyner nomi |
| | `HEALTH_CONTAINER_NAME` | `giftmakebot_health` | Health konteyner nomi |
| **Ports** | `HTTP_PORT` | `80` | HTTP port |
| | `HTTPS_PORT` | `443` | HTTPS port |
| | `REDIS_PORT` | `6379` | Redis port |
| **Docker Images** | `NGINX_IMAGE` | `nginx:alpine` | Nginx image |
| | `REDIS_IMAGE` | `redis:7-alpine` | Redis image |
| **Restart Policy** | `RESTART_POLICY` | `unless-stopped` | Qayta ishga tushirish siyosati |
| **Redis Config** | `REDIS_PASSWORD` | - | Redis paroli |
| | `REDIS_COMMAND_OPTIONS` | `--appendonly yes` | Redis buyruq parametrlari |
| **Telegram Bot** | `TELEGRAM_BOT_TOKEN` | - | Bot tokeni |
| | `TELEGRAM_SUPER_ADMIN_ID` | - | Admin user ID |

### 🔄 **Docker Compose da qo'llangan o'zgarishlar:**

#### **Health Service:**
- ✅ Container name: `${HEALTH_CONTAINER_NAME}`
- ✅ Restart policy: `${RESTART_POLICY}`
- ✅ Redis port: `${REDIS_PORT}`
- ✅ Debug mode: `${DEBUG}`

#### **Nginx Service:**
- ✅ Docker image: `${NGINX_IMAGE}`
- ✅ Container name: `${NGINX_CONTAINER_NAME}`
- ✅ HTTP/HTTPS ports: `${HTTP_PORT}/${HTTPS_PORT}`
- ✅ Restart policy: `${RESTART_POLICY}`

#### **Redis Service:**
- ✅ Docker image: `${REDIS_IMAGE}`
- ✅ Container name: `${REDIS_CONTAINER_NAME}`
- ✅ Port: `${REDIS_PORT}`
- ✅ Command options: `${REDIS_COMMAND_OPTIONS}`
- ✅ Restart policy: `${RESTART_POLICY}`

#### **Telegram Bot Service:**
- ✅ Container name: `${TELEGRAM_BOT_CONTAINER_NAME}`
- ✅ Bot token: `${TELEGRAM_BOT_TOKEN}`
- ✅ Admin ID: `${TELEGRAM_SUPER_ADMIN_ID}`
- ✅ Redis port: `${REDIS_PORT}`
- ✅ App environment: `${APP_ENV}`
- ✅ Debug mode: `${DEBUG}`
- ✅ Restart policy: `${RESTART_POLICY}`

### 📝 **Foydalanish:**

1. **`.env` faylini tahrirlang:**
   ```bash
   # Port o'zgartirish
   HTTP_PORT=8080
   HTTPS_PORT=8443
   
   # Debug rejimini yoqish
   DEBUG=true
   APP_ENV=development
   
   # Container nomlarini o'zgartirish
   NGINX_CONTAINER_NAME=my_nginx
   ```

2. **Servislarni qayta ishga tushiring:**
   ```bash
   docker compose down && docker compose up -d
   ```

### 🎯 **Afzalliklar:**

- ✅ **Barcha konfiguratsiya bir joyda** - `.env` faylida
- ✅ **Oson boshqarish** - Docker Compose o'zgartirishsiz
- ✅ **Xavfsizlik** - `.env` fayli git'ga yuborilmaydi
- ✅ **Moslashuvchanlik** - Har xil muhitlar uchun
- ✅ **Qayta ishlatish** - Template fayl `.env.example`

Endi siz `.env` faylini tahrirlash orqali docker-compose.yml faylini o'zgartirmasdan barcha konfiguratsiyalarni boshqarishingiz mumkin! 🚀