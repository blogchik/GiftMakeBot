# GiftMakeBot Setup Instructions

## Quick Setup

1. **Copy environment file:**
   ```powershell
   Copy-Item .env.example .env
   ```

2. **Edit the .env file with your actual values:**
   - Set your `TELEGRAM_BOT_TOKEN` (get from @BotFather)
   - Set a strong `REDIS_PASSWORD`
   - Update other settings as needed

3. **Start the services:**
   ```powershell
   docker compose up -d
   ```

4. **Check status:**
   ```powershell
   docker compose ps
   ```

## Environment Variables

### Required Variables:
- `TELEGRAM_BOT_TOKEN` - Get this from @BotFather on Telegram
- `REDIS_PASSWORD` - Set a strong password for Redis

### Optional Variables:
- `APP_ENV` - Application environment (default: production)
- `DEBUG` - Enable debug mode (default: false)
- Container names and ports (have defaults)

## Security Notes

⚠️ **IMPORTANT**: 
- Never commit the `.env` file to version control
- Use strong passwords in production
- Change default passwords before deploying

## Services Access

After starting with `docker compose up -d`:

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