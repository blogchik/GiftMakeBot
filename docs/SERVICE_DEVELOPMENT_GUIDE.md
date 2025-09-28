# 🔧 Complete Service Development Guide

## 📋 Table of Contents
1. [Adding New Services](#-adding-new-services)
2. [Service Architecture](#-service-architecture) 
3. [Development Workflow](#-development-workflow)
4. [Integration Patterns](#-integration-patterns)
5. [Testing & Validation](#-testing--validation)
6. [Production Deployment](#-production-deployment)
7. [Monitoring & Maintenance](#-monitoring--maintenance)

## 🚀 Adding New Services

### 📁 Service Directory Structure

When adding a new service to the GiftMakeBot platform, follow this standardized structure:

```
services/
├── your-new-service/
│   ├── Dockerfile                 # Container definition
│   ├── index.php                  # Main application file
│   ├── config/                    # Configuration files
│   │   ├── nginx.conf             # If web server needed
│   │   └── php.ini                # PHP configuration
│   ├── src/                       # Source code
│   │   ├── Controllers/           # Request handlers
│   │   ├── Models/               # Data models
│   │   ├── Services/             # Business logic
│   │   └── Utils/                # Helper functions
│   ├── tests/                     # Unit and integration tests
│   │   ├── Unit/                 # Unit tests
│   │   └── Integration/          # Integration tests
│   ├── docs/                      # Service documentation
│   │   ├── API.md                # API documentation
│   │   └── README.md             # Service overview
│   └── composer.json              # PHP dependencies (if needed)
```

### 🐳 Dockerfile Template

Create a standardized Dockerfile for your service:

```dockerfile
# services/your-new-service/Dockerfile
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install PHP dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Set permissions
RUN chown -R www-data:www-data /app
RUN chmod -R 755 /app

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:9000/health || exit 1

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm", "-F"]
```

### 🔧 Service Integration Steps

#### Step 1: Create Service Structure
```bash
# Create service directory
mkdir -p services/your-new-service/{src,config,tests,docs}

# Create main application file
touch services/your-new-service/index.php

# Create Dockerfile
touch services/your-new-service/Dockerfile
```

#### Step 2: Implement Core Functionality
```php
<?php
// services/your-new-service/index.php

// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

switch ($path) {
    case '/health':
        handleHealthCheck();
        break;
    case '/api/v1/your-endpoint':
        handleYourEndpoint();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function handleHealthCheck() {
    $health = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s') . ' UTC',
        'service' => 'your-new-service',
        'version' => '1.0.0',
        'environment' => getenv('APP_ENV') ?: 'development',
        'details' => [
            'php_version' => phpversion(),
            'memory_usage' => [
                'current' => formatBytes(memory_get_usage()),
                'peak' => formatBytes(memory_get_peak_usage()),
                'limit' => ini_get('memory_limit')
            ],
            'uptime' => getUptime()
        ]
    ];
    
    echo json_encode($health, JSON_PRETTY_PRINT);
}

function handleYourEndpoint() {
    // Your service logic here
    $response = [
        'success' => true,
        'data' => 'Hello from your new service!',
        'timestamp' => date('c')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

function getUptime() {
    $uptime = file_get_contents('/proc/uptime');
    $uptime = explode(' ', $uptime)[0];
    $days = floor($uptime / 86400);
    $hours = floor(($uptime % 86400) / 3600);
    $minutes = floor(($uptime % 3600) / 60);
    
    return sprintf('%dd %02dh %02dm', $days, $hours, $minutes);
}
?>
```

#### Step 3: Add to Docker Compose

**Base Configuration (docker-compose.yml):**
```yaml
services:
  # ... existing services ...
  
  your-new-service:
    build: ./services/your-new-service
    container_name: ${YOUR_SERVICE_CONTAINER_NAME:-giftmakebot_your_service}
    depends_on:
      - redis  # Add dependencies as needed
    networks:
      - giftmakebot_network
    restart: ${RESTART_POLICY:-unless-stopped}
    environment:
      - APP_ENV=${APP_ENV:-development}
      - DEBUG=${DEBUG:-false}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - REDIS_PASSWORD=${REDIS_PASSWORD}
```

**Development Override (docker-compose.dev.yml):**
```yaml
services:
  your-new-service:
    # Development-specific configurations
    volumes:
      # Mount source code for hot reload
      - ./services/your-new-service:/app:cached
      - ./logs/your-new-service:/var/log/php
    environment:
      - APP_ENV=development
      - DEBUG=true
      - LOG_LEVEL=debug
    # Expose port for external access (development only)
    ports:
      - "9001:9000"  # Choose unused port
```

**Production Override (docker-compose.prod.yml):**
```yaml
services:
  your-new-service:
    # Production-specific configurations
    environment:
      - APP_ENV=production
      - DEBUG=false
      - LOG_LEVEL=error
      - PHP_MEMORY_LIMIT=256M
      - PHP_MAX_EXECUTION_TIME=30
    deploy:
      resources:
        limits:
          memory: 256M
          cpus: '0.5'
        reservations:
          memory: 128M
          cpus: '0.25'
    # No ports exposed in production (internal access only)
```

#### Step 4: Update Nginx Routing

Add routing for your new service in `services/nginx/conf.d/default.conf`:

```nginx
# Your New Service API routing
location /api/v1/your-service/ {
    fastcgi_pass your-new-service:9000;
    fastcgi_param SCRIPT_FILENAME /app/index.php;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_read_timeout 30;
    fastcgi_connect_timeout 5;
    include fastcgi_params;
}

# Health check for your service
location /your-service/health {
    fastcgi_pass your-new-service:9000;
    fastcgi_param SCRIPT_FILENAME /app/index.php;
    fastcgi_param REQUEST_URI /health;
    include fastcgi_params;
}
```

#### Step 5: Update Health Monitor

Add your service to the health monitoring system in `services/health/index.php`:

```php
// Add to the checkAllServices() function
private function checkAllServices() {
    $services = [];
    
    // ... existing service checks ...
    
    // Your new service
    $services['your-new-service'] = $this->checkYourNewService();
    
    return $services;
}

private function checkYourNewService() {
    try {
        $host = 'your-new-service';
        $port = 9000;
        
        // Test TCP connection
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if (!$connection) {
            return [
                'status' => 'unhealthy',
                'message' => "Cannot connect to service: $errstr",
                'details' => [
                    'host' => $host,
                    'port' => $port,
                    'error_code' => $errno,
                    'error_message' => $errstr
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
        fclose($connection);
        
        // Test HTTP endpoint
        $healthUrl = "http://$host:$port/health";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET'
            ]
        ]);
        
        $response = @file_get_contents($healthUrl, false, $context);
        
        if ($response === false) {
            return [
                'status' => 'unhealthy',
                'message' => 'Service not responding to health check',
                'details' => [
                    'host' => $host,
                    'port' => $port,
                    'endpoint' => '/health'
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
        
        $healthData = json_decode($response, true);
        
        return [
            'status' => 'healthy',
            'message' => 'Service is running and responding',
            'details' => [
                'host' => $host,
                'port' => $port,
                'version' => $healthData['version'] ?? 'unknown',
                'php_version' => $healthData['details']['php_version'] ?? 'unknown',
                'environment' => $healthData['environment'] ?? 'unknown'
            ],
            'last_checked' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error checking service: ' . $e->getMessage(),
            'details' => [
                'host' => $host,
                'port' => $port,
                'exception' => $e->getMessage()
            ],
            'last_checked' => date('Y-m-d H:i:s')
        ];
    }
}
```

#### Step 6: Update Environment Files

Add service-specific environment variables:

**`.env.development`:**
```bash
# Your New Service Configuration
YOUR_SERVICE_CONTAINER_NAME=giftmakebot_dev_your_service
YOUR_SERVICE_DEBUG=true
YOUR_SERVICE_LOG_LEVEL=debug
```

**`.env.production`:**
```bash
# Your New Service Configuration
YOUR_SERVICE_CONTAINER_NAME=giftmakebot_prod_your_service
YOUR_SERVICE_DEBUG=false
YOUR_SERVICE_LOG_LEVEL=error
```

## 🏗️ Service Architecture

### 🔌 Integration Patterns

#### Database Integration (Redis Example):
```php
<?php
// services/your-new-service/src/Services/RedisService.php

class RedisService {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect(
            getenv('REDIS_HOST') ?: 'redis',
            getenv('REDIS_PORT') ?: 6379
        );
        
        if ($password = getenv('REDIS_PASSWORD')) {
            $this->redis->auth($password);
        }
    }
    
    public function get($key) {
        return $this->redis->get($key);
    }
    
    public function set($key, $value, $ttl = null) {
        if ($ttl) {
            return $this->redis->setex($key, $ttl, $value);
        }
        return $this->redis->set($key, $value);
    }
    
    public function delete($key) {
        return $this->redis->del($key);
    }
}
?>
```

#### Message Queue Integration (RabbitMQ Example):
```php
<?php
// services/your-new-service/src/Services/QueueService.php

require_once 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueService {
    private $connection;
    private $channel;
    
    public function __construct() {
        $this->connection = new AMQPStreamConnection(
            getenv('RABBITMQ_HOST') ?: 'rabbitmq',
            getenv('RABBITMQ_PORT') ?: 5672,
            getenv('RABBITMQ_USER') ?: 'guest',
            getenv('RABBITMQ_PASSWORD') ?: 'guest',
            getenv('RABBITMQ_VHOST') ?: '/'
        );
        
        $this->channel = $this->connection->channel();
    }
    
    public function publishMessage($queue, $message, $persistent = true) {
        $this->channel->queue_declare($queue, false, $persistent, false, false);
        
        $msg = new AMQPMessage(
            json_encode($message),
            ['delivery_mode' => $persistent ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT]
        );
        
        $this->channel->basic_publish($msg, '', $queue);
    }
    
    public function consumeMessages($queue, $callback) {
        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue, '', false, false, false, false, $callback);
        
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
    
    public function __destruct() {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>
```

#### API Integration:
```php
<?php
// services/your-new-service/src/Services/ApiClient.php

class ApiClient {
    private $baseUrl;
    private $timeout;
    
    public function __construct($baseUrl = null) {
        $this->baseUrl = $baseUrl ?: getenv('API_GATEWAY_URL') ?: 'http://api-gateway:9000';
        $this->timeout = (int)(getenv('API_TIMEOUT') ?: 30);
    }
    
    public function get($endpoint, $headers = []) {
        return $this->makeRequest('GET', $endpoint, null, $headers);
    }
    
    public function post($endpoint, $data, $headers = []) {
        return $this->makeRequest('POST', $endpoint, $data, $headers);
    }
    
    public function put($endpoint, $data, $headers = []) {
        return $this->makeRequest('PUT', $endpoint, $data, $headers);
    }
    
    public function delete($endpoint, $headers = []) {
        return $this->makeRequest('DELETE', $endpoint, null, $headers);
    }
    
    private function makeRequest($method, $endpoint, $data = null, $headers = []) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        $context = [
            'http' => [
                'method' => $method,
                'header' => array_merge([
                    'Content-Type: application/json',
                    'User-Agent: GiftMakeBot-Service/1.0'
                ], $headers),
                'timeout' => $this->timeout
            ]
        ];
        
        if ($data !== null) {
            $context['http']['content'] = json_encode($data);
        }
        
        $response = file_get_contents($url, false, stream_context_create($context));
        
        if ($response === false) {
            throw new Exception("API request failed: $method $url");
        }
        
        return json_decode($response, true);
    }
}
?>
```

## 🧪 Testing & Validation

### Unit Testing Template
```php
<?php
// services/your-new-service/tests/Unit/YourServiceTest.php

use PHPUnit\Framework\TestCase;

class YourServiceTest extends TestCase {
    private $service;
    
    protected function setUp(): void {
        $this->service = new YourService();
    }
    
    public function testHealthCheck() {
        $result = $this->service->getHealth();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('healthy', $result['status']);
    }
    
    public function testApiEndpoint() {
        $result = $this->service->handleRequest('/api/v1/test');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
    
    public function testDatabaseConnection() {
        $redis = new RedisService();
        $result = $redis->set('test_key', 'test_value');
        
        $this->assertTrue($result);
        
        $value = $redis->get('test_key');
        $this->assertEquals('test_value', $value);
        
        $redis->delete('test_key');
    }
}
```

### Integration Testing
```bash
#!/bin/bash
# services/your-new-service/tests/integration-test.sh

echo "Starting integration tests for your-new-service..."

# Start test environment
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d your-new-service

# Wait for service to be ready
sleep 10

# Test health endpoint
echo "Testing health endpoint..."
HEALTH_RESPONSE=$(curl -s http://localhost:9001/health)
if [[ $HEALTH_RESPONSE == *"healthy"* ]]; then
    echo "✅ Health check passed"
else
    echo "❌ Health check failed"
    exit 1
fi

# Test API endpoints
echo "Testing API endpoints..."
API_RESPONSE=$(curl -s http://localhost:9001/api/v1/your-endpoint)
if [[ $API_RESPONSE == *"success"* ]]; then
    echo "✅ API test passed"
else
    echo "❌ API test failed"
    exit 1
fi

# Test database connectivity
echo "Testing database connectivity..."
DB_RESPONSE=$(curl -s http://localhost:9001/test/database)
if [[ $DB_RESPONSE == *"connected"* ]]; then
    echo "✅ Database test passed"
else
    echo "❌ Database test failed"
    exit 1
fi

echo "🎉 All integration tests passed!"
```

## 🚀 Deployment & Production

### Production Checklist
- [ ] **Environment Variables**: All production configs set
- [ ] **Security**: No debug info exposed  
- [ ] **Performance**: Resource limits configured
- [ ] **Monitoring**: Health checks implemented
- [ ] **Logging**: Appropriate log levels set
- [ ] **Error Handling**: Graceful failure modes
- [ ] **Documentation**: API docs updated
- [ ] **Testing**: All tests passing

### Deployment Commands
```bash
# Development deployment
.\deploy-dev.ps1  # Includes your new service

# Production deployment  
.\deploy-prod.ps1  # With security validation

# Manual service-specific deployment
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build your-new-service
```

### Service Documentation Template
Create `services/your-new-service/docs/API.md`:

```markdown
# Your New Service API Documentation

## Overview
Brief description of your service functionality.

## Endpoints

### Health Check
- **URL**: `/health`
- **Method**: `GET`
- **Success Response**: 
  - **Code**: 200
  - **Content**: `{"status": "healthy", "timestamp": "...", ...}`

### Main API Endpoint
- **URL**: `/api/v1/your-endpoint`
- **Method**: `GET|POST|PUT|DELETE`
- **Data Params**: 
  ```json
  {
    "param1": "value1",
    "param2": "value2"
  }
  ```
- **Success Response**:
  - **Code**: 200
  - **Content**: `{"success": true, "data": "..."}`

## Error Codes
- `400`: Bad Request
- `401`: Unauthorized  
- `404`: Not Found
- `500`: Internal Server Error

## Examples
```bash
# Health check
curl http://localhost/your-service/health

# API call
curl -X POST http://localhost/api/v1/your-service/endpoint \
  -H "Content-Type: application/json" \
  -d '{"param": "value"}'
```
```

## 🔍 Monitoring & Maintenance

### Service Monitoring
Your service will automatically be included in:
- System health checks (`/health`)
- Container resource monitoring (`docker stats`)
- Log aggregation (`docker compose logs`)
- Performance metrics collection

### Maintenance Tasks
1. **Regular Updates**: Keep dependencies updated
2. **Log Monitoring**: Watch for errors and warnings
3. **Performance Tuning**: Monitor resource usage
4. **Security Audits**: Regular vulnerability scans
5. **Backup Verification**: Test data recovery procedures

### Service-Specific Monitoring
```bash
# Monitor your service specifically
docker compose logs -f your-new-service

# Check resource usage
docker stats giftmakebot_your_service

# Test health endpoint
curl http://localhost/your-service/health
```

---

## 🎉 Service Development Complete!

Following this guide ensures your new service:
- ✅ **Integrates** seamlessly with existing infrastructure
- ✅ **Scales** appropriately in both dev and production
- ✅ **Monitors** health and performance automatically  
- ✅ **Maintains** consistency with platform standards
- ✅ **Documents** API and functionality properly

**Next Steps:**
1. Test your service in development environment
2. Add comprehensive unit and integration tests
3. Update platform documentation
4. Deploy to production with monitoring
5. Gather feedback and iterate

---

<div align="center">

### 🚀 Ready to Build?

**[📚 Back to Main Docs](README.md)** • **[🔧 Deployment Guide](DEPLOYMENT.md)** • **[💬 Get Help](https://github.com/blogchik/GiftMakeBot/discussions)**

*Happy coding! 🎯*

</div>