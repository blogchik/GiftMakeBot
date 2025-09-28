<?php
// Control error reporting for cleaner output
if (getenv('DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// API routes
if (strpos($path, '/api/v1/health') === 0) {
    handleHealthAPI();
    exit;
}

function handleHealthAPI() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $healthMonitor = new HealthMonitor();
    $healthData = $healthMonitor->getHealthData();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d\TH:i:s\Z'),
        'status' => $healthData['overall_status'],
        'data' => [
            'services' => $healthData['services'],
            'summary' => [
                'total_services' => count($healthData['services']),
                'healthy_count' => count(array_filter($healthData['services'], function($s) { return $s['status'] === 'healthy'; })),
                'unhealthy_count' => count(array_filter($healthData['services'], function($s) { return $s['status'] === 'unhealthy'; })),
                'warning_count' => count(array_filter($healthData['services'], function($s) { return $s['status'] === 'warning'; }))
            ]
        ]
    ]);
}

class HealthMonitor {
    private $services = [];
    private $overallStatus = 'healthy';
    
    public function __construct() {
        $this->checkAllServices();
    }
    
    private function checkAllServices() {
        $this->services = [
            'nginx' => $this->checkNginx(),
            'redis' => $this->checkRedis(),
            'rabbitmq' => $this->checkRabbitMQ(),
            'telegram-bot' => $this->checkTelegramBot(),
            'api-gateway' => $this->checkApiGateway(),
            'web_app' => $this->checkWebApp(),
            'system' => $this->getSystemInfo()
        ];
        
        // Determine overall status
        foreach ($this->services as $service) {
            if ($service['status'] === 'unhealthy' || $service['status'] === 'error') {
                $this->overallStatus = 'unhealthy';
                break;
            } elseif ($service['status'] === 'warning') {
                $this->overallStatus = 'warning';
            }
        }
    }
    
    private function checkRedis() {
        try {
            $redis_host = getenv('REDIS_HOST') ?: 'redis';
            $redis_port = getenv('REDIS_PORT') ?: 6379;
            $redis_password = getenv('REDIS_PASSWORD');
            
            $connection = @fsockopen($redis_host, $redis_port, $errno, $errstr, 2);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to Redis: $errstr",
                    'details' => [
                        'host' => $redis_host,
                        'port' => $redis_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            // Test Redis AUTH if password is set
            if ($redis_password) {
                fwrite($connection, "AUTH $redis_password\r\n");
                $response = fread($connection, 1024);
                
                if (strpos($response, '+OK') === false) {
                    fclose($connection);
                    return [
                        'status' => 'unhealthy',
                        'message' => 'Redis authentication failed',
                        'details' => [
                            'host' => $redis_host,
                            'port' => $redis_port,
                            'auth_status' => 'failed'
                        ],
                        'last_checked' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            // Test Redis PING
            fwrite($connection, "PING\r\n");
            $response = fread($connection, 1024);
            fclose($connection);
            
            if (strpos($response, '+PONG') !== false) {
                return [
                    'status' => 'healthy',
                    'message' => 'Redis is running and responding',
                    'details' => [
                        'host' => $redis_host,
                        'port' => $redis_port,
                        'ping_response' => 'PONG',
                        'auth_enabled' => !empty($redis_password)
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'Redis connected but ping failed',
                    'details' => [
                        'host' => $redis_host,
                        'port' => $redis_port,
                        'response' => trim($response)
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis check failed: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function checkNginx() {
        try {
            // Skip nginx check if we're being called from nginx to avoid recursion
            if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['REQUEST_URI'], '/health') !== false) {
                return [
                    'status' => 'healthy',
                    'message' => 'Nginx is serving this request (assumed healthy)',
                    'details' => [
                        'host' => 'nginx',
                        'port' => 80,
                        'note' => 'Checked by inference - nginx must be working to serve this request'
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            $nginx_host = 'nginx';
            $nginx_port = 80;
            
            $connection = @fsockopen($nginx_host, $nginx_port, $errno, $errstr, 2);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to Nginx: $errstr",
                    'details' => [
                        'host' => $nginx_host,
                        'port' => $nginx_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($connection);
            
            return [
                'status' => 'healthy',
                'message' => 'Nginx port is accessible',
                'details' => [
                    'host' => $nginx_host,
                    'port' => $nginx_port,
                    'connection' => 'successful'
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Nginx check failed: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function checkTelegramBot() {
        try {
            $bot_host = 'telegram-bot';
            $bot_port = 9000;
            
            // Check if PHP-FPM is running
            $connection = @fsockopen($bot_host, $bot_port, $errno, $errstr, 2);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to Telegram Bot PHP-FPM: $errstr",
                    'details' => [
                        'host' => $bot_host,
                        'port' => $bot_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($connection);
            
            // Check if bot token is configured
            $bot_token = getenv('TELEGRAM_BOT_TOKEN');
            $token_status = !empty($bot_token) ? 'configured' : 'missing';
            
            return [
                'status' => !empty($bot_token) ? 'healthy' : 'warning',
                'message' => !empty($bot_token) ? 'Telegram Bot PHP-FPM is running' : 'PHP-FPM running but bot token not configured',
                'details' => [
                    'host' => $bot_host,
                    'port' => $bot_port,
                    'php_fpm' => 'running',
                    'bot_token' => $token_status,
                    'environment' => getenv('APP_ENV') ?: 'production'
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Telegram Bot check failed: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function getSystemInfo() {
        try {
            $load = sys_getloadavg();
            $memory = memory_get_usage(true);
            $memory_peak = memory_get_peak_usage(true);
            
            return [
                'status' => 'healthy',
                'message' => 'System information collected',
                'details' => [
                    'php_version' => PHP_VERSION,
                    'server_time' => date('Y-m-d H:i:s T'),
                    'uptime' => $this->getUptime(),
                    'memory_usage' => [
                        'current' => $this->formatBytes($memory),
                        'peak' => $this->formatBytes($memory_peak),
                        'limit' => ini_get('memory_limit')
                    ],
                    'load_average' => $load ? $load[0] : 'N/A',
                    'environment' => [
                        'APP_ENV' => getenv('APP_ENV') ?: 'production',
                        'DEBUG' => getenv('DEBUG') ?: 'false'
                    ]
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'System info partially available: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function getUptime() {
        try {
            if (file_exists('/proc/uptime')) {
                $uptime = file_get_contents('/proc/uptime');
                $uptime = floatval(explode(' ', $uptime)[0]);
                return $this->formatUptime($uptime);
            }
            return 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }
    
    private function formatUptime($seconds) {
        $days = intval(floor($seconds / 86400));
        $hours = intval(floor(($seconds % 86400) / 3600));
        $minutes = intval(floor(($seconds % 3600) / 60));
        
        return sprintf('%dd %02dh %02dm', $days, $hours, $minutes);
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes = floatval($bytes) / 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    public function getReport() {
        return [
            'overall_status' => $this->overallStatus,
            'timestamp' => date('Y-m-d H:i:s T'),
            'services' => $this->services,
            'summary' => $this->getSummary()
        ];
    }
    
    public function getHealthData() {
        return [
            'overall_status' => $this->overallStatus,
            'services' => $this->services,
            'timestamp' => date('Y-m-d\TH:i:s\Z')
        ];
    }
    
    private function checkApiGateway() {
        try {
            $api_gateway_host = 'api-gateway';
            $api_gateway_port = 9000;
            
            // Check if API Gateway container is running (port check)
            $connection = @fsockopen($api_gateway_host, $api_gateway_port, $errno, $errstr, 3);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to API Gateway PHP-FPM: $errstr",
                    'details' => [
                        'host' => $api_gateway_host,
                        'port' => $api_gateway_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($connection);
            
            // Try to make HTTP request to API Gateway through nginx
            $api_url = 'http://nginx/api/v1/users';
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => "Accept: application/json\r\n"
                ]
            ]);
            
            $response = @file_get_contents($api_url, false, $context);
            
            if ($response === false) {
                return [
                    'status' => 'warning',
                    'message' => 'API Gateway PHP-FPM is running but API endpoint not responding',
                    'details' => [
                        'host' => $api_gateway_host,
                        'port' => $api_gateway_port,
                        'php_fpm' => 'running',
                        'api_endpoint' => 'unreachable',
                        'url_tested' => $api_url
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['success'])) {
                return [
                    'status' => 'healthy',
                    'message' => 'API Gateway is running and serving API requests',
                    'details' => [
                        'host' => $api_gateway_host,
                        'port' => $api_gateway_port,
                        'php_fpm' => 'running',
                        'api_endpoint' => 'working',
                        'response_format' => 'valid_json',
                        'users_count' => count($data['data'] ?? [])
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            return [
                'status' => 'warning',
                'message' => 'API Gateway responding but with invalid format',
                'details' => [
                    'host' => $api_gateway_host,
                    'port' => $api_gateway_port,
                    'php_fpm' => 'running',
                    'response_status' => 'invalid_json'
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking API Gateway: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function checkWebApp() {
        try {
            $web_app_host = 'web_app';
            $web_app_port = 80;
            
            // Check if Web App container is running (port check)
            $connection = @fsockopen($web_app_host, $web_app_port, $errno, $errstr, 3);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to Web App: $errstr",
                    'details' => [
                        'host' => $web_app_host,
                        'port' => $web_app_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($connection);
            
            // Try to make HTTP request to Web App through nginx
            $web_url = 'http://nginx/';
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 5
                ]
            ]);
            
            $headers = @get_headers($web_url, 1, $context);
            
            if ($headers === false) {
                return [
                    'status' => 'warning',
                    'message' => 'Web App container is running but not accessible through nginx',
                    'details' => [
                        'host' => $web_app_host,
                        'port' => $web_app_port,
                        'nginx_proxy' => 'unreachable',
                        'url_tested' => $web_url
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            $status_line = $headers[0] ?? '';
            $is_200 = strpos($status_line, '200') !== false;
            
            if ($is_200) {
                return [
                    'status' => 'healthy',
                    'message' => 'Web App is running and serving React application',
                    'details' => [
                        'host' => $web_app_host,
                        'port' => $web_app_port,
                        'nginx_proxy' => 'working',
                        'http_status' => '200 OK',
                        'content_type' => $headers['Content-Type'] ?? 'text/html',
                        'server' => $headers['Server'] ?? 'nginx'
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            return [
                'status' => 'warning',
                'message' => 'Web App responding but with non-200 status',
                'details' => [
                    'host' => $web_app_host,
                    'port' => $web_app_port,
                    'http_status' => $status_line
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking Web App: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function checkRabbitMQ() {
        try {
            $rabbitmq_host = 'rabbitmq';
            $rabbitmq_port = 5672;
            $rabbitmq_mgmt_port = 15672;
            
            // Check if RabbitMQ AMQP port is accessible
            $connection = @fsockopen($rabbitmq_host, $rabbitmq_port, $errno, $errstr, 3);
            
            if (!$connection) {
                return [
                    'status' => 'unhealthy',
                    'message' => "Cannot connect to RabbitMQ AMQP port: $errstr",
                    'details' => [
                        'host' => $rabbitmq_host,
                        'amqp_port' => $rabbitmq_port,
                        'management_port' => $rabbitmq_mgmt_port,
                        'error_code' => $errno,
                        'error_message' => $errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($connection);
            
            // Check RabbitMQ Management API
            $mgmt_connection = @fsockopen($rabbitmq_host, $rabbitmq_mgmt_port, $mgmt_errno, $mgmt_errstr, 3);
            
            if (!$mgmt_connection) {
                return [
                    'status' => 'warning',
                    'message' => 'RabbitMQ AMQP is running but Management UI is not accessible',
                    'details' => [
                        'host' => $rabbitmq_host,
                        'amqp_port' => $rabbitmq_port,
                        'amqp_status' => 'accessible',
                        'management_port' => $rabbitmq_mgmt_port,
                        'management_status' => 'unreachable',
                        'error' => $mgmt_errstr
                    ],
                    'last_checked' => date('Y-m-d H:i:s')
                ];
            }
            
            fclose($mgmt_connection);
            
            // Try to get basic info from Management API
            $api_url = "http://$rabbitmq_host:$rabbitmq_mgmt_port/api/overview";
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => "Authorization: Basic " . base64_encode('giftmakebot_admin:GiftMakeRabbitMQ@2025') . "\r\n"
                ]
            ]);
            
            $response = @file_get_contents($api_url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($data['rabbitmq_version'])) {
                    return [
                        'status' => 'healthy',
                        'message' => 'RabbitMQ is running with Management API accessible',
                        'details' => [
                            'host' => $rabbitmq_host,
                            'amqp_port' => $rabbitmq_port,
                            'management_port' => $rabbitmq_mgmt_port,
                            'amqp_status' => 'accessible',
                            'management_status' => 'accessible',
                            'rabbitmq_version' => $data['rabbitmq_version'] ?? 'unknown',
                            'erlang_version' => $data['erlang_version'] ?? 'unknown',
                            'node_name' => $data['node'] ?? 'unknown'
                        ],
                        'last_checked' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            return [
                'status' => 'healthy',
                'message' => 'RabbitMQ ports are accessible',
                'details' => [
                    'host' => $rabbitmq_host,
                    'amqp_port' => $rabbitmq_port,
                    'management_port' => $rabbitmq_mgmt_port,
                    'amqp_status' => 'accessible',
                    'management_status' => 'accessible',
                    'api_response' => 'no_data'
                ],
                'last_checked' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking RabbitMQ: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
                'last_checked' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getSummary() {
        $healthy = 0;
        $warning = 0;
        $unhealthy = 0;
        $error = 0;
        
        foreach ($this->services as $service) {
            switch ($service['status']) {
                case 'healthy':
                    $healthy++;
                    break;
                case 'warning':
                    $warning++;
                    break;
                case 'unhealthy':
                    $unhealthy++;
                    break;
                case 'error':
                    $error++;
                    break;
            }
        }
        
        return [
            'total_services' => count($this->services),
            'healthy' => $healthy,
            'warning' => $warning,
            'unhealthy' => $unhealthy,
            'error' => $error
        ];
    }
}

// Create health monitor instance and output JSON
$monitor = new HealthMonitor();
$report = $monitor->getReport();

// Always return JSON
echo json_encode($report, JSON_PRETTY_PRINT);
?>