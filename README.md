# 🎁 GiftMakeBot

[![Docker](https://img.shields.io/badge/Docker-20.10+-blue.svg)](https://www.docker.com/)
[![Docker Compose](https://img.shields.io/badge/Docker%20Compose-v2.0+-blue.svg)](https://docs.docker.com/compose/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://www.php.net/)
[![React](https://img.shields.io/badge/React-18.0+-61DAFB.svg)](https://reactjs.org/)
[![License](https://img.shields.io/badge/License-Apache%202.0-green.svg)](LICENSE)

Professional multi-environment Telegram bot platform built with Docker microservices architecture.

## ⚡ Quick Start

```bash
# Development
git clone https://github.com/blogchik/GiftMakeBot.git
cd GiftMakeBot
./deploy-dev.ps1    # Windows
./deploy-dev.sh     # Linux/Mac
```

```bash  
# Production
./deploy-prod.ps1   # Windows (configure .env.production first)
./deploy-prod.sh    # Linux/Mac (configure .env.production first)
```

## 🏗️ Architecture

- **🌐 Nginx** - Reverse proxy & load balancer
- **🤖 Telegram Bot** - Core bot functionality  
- **🔗 API Gateway** - RESTful API services
- **📱 Web App** - React frontend
- **⚡ Redis** - Caching & sessions
- **🐰 RabbitMQ** - Message queuing
- **💊 Health Monitor** - System monitoring

## 📚 Complete Documentation

**All detailed guides, configuration, and documentation are available in the Wiki:**

### **[📖 Visit Complete Wiki →](https://github.com/blogchik/GiftMakeBot/wiki)**

| Guide | Description |
|-------|-------------|
| [🚀 Deployment Guide](https://github.com/blogchik/GiftMakeBot/wiki/Deployment-Guide) | Multi-environment setup, security, monitoring |
| [🔧 Service Development](https://github.com/blogchik/GiftMakeBot/wiki/Service-Development-Guide) | Adding services, Docker integration, testing |
| [⚙️ Environment Variables](https://github.com/blogchik/GiftMakeBot/wiki/Environment-Variables) | Complete configuration reference |

## 🔗 Quick Links

- 🏠 **[Home Wiki](https://github.com/blogchik/GiftMakeBot/wiki)** - Start here
- 🐛 **[Issues](https://github.com/blogchik/GiftMakeBot/issues)** - Report bugs
- 💬 **[Discussions](https://github.com/blogchik/GiftMakeBot/discussions)** - Get help
- 📧 **[Contact](mailto:abduroziq@nuqtauz.com)** - Direct support

## 📄 License

Licensed under the Apache 2.0 License - see [LICENSE](LICENSE) file.

---

<div align="center">

**[📚 Read Full Documentation](https://github.com/blogchik/GiftMakeBot/wiki)** • **[🚀 Deploy Now](https://github.com/blogchik/GiftMakeBot/wiki/Deployment-Guide)** • **[💬 Get Help](https://github.com/blogchik/GiftMakeBot/discussions)**

Made with ❤️ by **Jabborov Abduroziq**

</div>