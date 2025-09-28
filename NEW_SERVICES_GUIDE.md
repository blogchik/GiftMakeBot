# 🎉 GiftMakeBot - API Gateway & React Web App

## ✅ **Muvaffaqiyatli qo'shildi:**

### 🌐 **Yangi Servislar:**
1. **API Gateway** - PHP da yozilgan sodda API server
2. **Web App** - React + Vite bilan yozilgan dashboard

---

## 🔧 **API Gateway Servisi**

### 📂 **Struktura:**
```
services/api-gateway/
├── 📄 index.php      # Asosiy API logic
└── 🐳 Dockerfile     # PHP-FPM container
```

### 🛠 **Endpoints:**
- **GET /api/v1/users** - Fake user ma'lumotlari
- **GET /api/v1/health** - API health check

### 📊 **Sample Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Jabborov Abduroziq",
      "email": "abduroziq@example.com",
      "role": "Admin",
      "avatar": "https://ui-avatars.com/api/...",
      "created_at": "2025-01-15T10:30:00Z",
      "is_active": true
    }
    // ... 4 ta ko'proq user
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 5,
    "total_pages": 1,
    "has_more": false
  }
}
```

---

## ⚛️ **React Web App Servisi**

### 📂 **Struktura:**
```
services/web_app/
├── 📄 package.json       # Dependencies
├── 📄 vite.config.js     # Vite configuration  
├── 📄 index.html         # HTML template
├── 🐳 Dockerfile         # Multi-stage build
├── 📄 nginx.conf         # Production nginx config
└── 📁 src/
    ├── 📄 main.jsx       # React entry point
    ├── 📄 App.jsx        # Main component
    └── 📄 index.css      # Styling
```

### 🎨 **Features:**
- **Modern UI** - Gradient background, cards, responsive design
- **API Integration** - /api/v1/users dan ma'lumot oladi
- **Error Handling** - API bilan bog'lanish xatolari
- **Loading States** - Ma'lumot yuklanish holatlari
- **User Cards** - Avatar, role, status bilan
- **Pagination Info** - Sahifalash ma'lumotlari

---

## 🌐 **Nginx Routing**

### 📍 **URL Routing:**
```nginx
# API Gateway - Barcha /api/* so'rovlar
/api/*           → api-gateway:9000

# Health Monitoring
/health          → health:9000  

# Telegram Bot
/bot/*           → telegram-bot:9000

# React Web App - Boshqa barcha so'rovlar
/*               → web_app:80
```

---

## 🚀 **Qanday ishlaydi:**

### 1. **API Gateway:**
```bash
curl http://localhost/api/v1/users
# → PHP API fake users qaytaradi
```

### 2. **React Web App:**
```bash
# Browser: http://localhost
# → React dashboard users ni ko'rsatadi
```

### 3. **Health Monitoring:**
```bash
curl http://localhost/health
# → Barcha servislar holati
```

---

## 📊 **Servislar Holati:**

| Service | Status | Port | URL |
|---------|--------|------|-----|
| **nginx** | ✅ Running | 80, 443 | http://localhost |
| **api-gateway** | ✅ Running | 9000 | /api/* |
| **web_app** | ✅ Running | 3000→80 | / |
| **health** | ✅ Running | 9000 | /health |
| **telegram-bot** | ✅ Running | 9000 | /bot/* |
| **redis** | ✅ Running | 6379 | - |

---

## 🔧 **Environment Variables:**

### 📝 **.env fayliga qo'shildi:**
```env
# Container names
API_GATEWAY_CONTAINER_NAME=giftmakebot_api_gateway
WEB_APP_CONTAINER_NAME=giftmakebot_webapp

# Ports
WEB_APP_PORT=3000
```

---

## 🎯 **Test Commands:**

```bash
# API Gateway test
curl http://localhost/api/v1/users

# Health check
curl http://localhost/health

# React app test
curl -I http://localhost

# Direct web app access
curl -I http://localhost:3000
```

---

## 🏗️ **Arxitektura:**

```
┌─────────────┐
│   Browser   │
└─────┬───────┘
      │ HTTP requests
┌─────▼───────┐
│    Nginx    │ ← Main reverse proxy
│   (Port 80) │
└─────┬───────┘
      │
      ├── /api/* ────────┐
      │                 │
      ├── /health ──────┼─────┐
      │                 │     │
      └── /* ───────────┼──┐  │
                        │  │  │
              ┌─────────▼─┐│  │
              │API Gateway││  │
              │(PHP-FPM)  ││  │
              └───────────┘│  │
                          │  │
                    ┌─────▼─┐│
                    │Health ││
                    │Monitor││
                    └───────┘│
                           │
                    ┌──────▼┐
                    │React │
                    │Web   │
                    │App   │
                    └──────┘
```

---

## 🎉 **Xulosa:**

✅ **API Gateway** - `/api/v1/users` endpoint yaratildi
✅ **React Web App** - Users ni chiroyli ko'rsatadi  
✅ **Nginx Routing** - Barcha routing to'g'ri ishlaydi
✅ **Docker Integration** - Barcha servislar ishlamoqda
✅ **Environment Config** - .env orqali sozlanadi

**Loyiha tayyor!** 🚀 Endi http://localhost da React dashboard va http://localhost/api/v1/users da API ishlaydi!