<?php
// API Gateway - Simple PHP API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string if present
$path = parse_url($requestUri, PHP_URL_PATH);

// Simple routing
switch ($path) {
    case '/api/v1/users':
        if ($requestMethod === 'GET') {
            handleGetUsers();
        } else {
            sendMethodNotAllowed();
        }
        break;
        
    case '/api/v1/health':
        if ($requestMethod === 'GET') {
            handleApiHealth();
        } else {
            sendMethodNotAllowed();
        }
        break;
        
    default:
        sendNotFound();
        break;
}

function handleGetUsers() {
    // Fake user data
    $users = [
        [
            'id' => 1,
            'name' => 'Jabborov Abduroziq',
            'email' => 'abduroziq@example.com',
            'role' => 'Admin',
            'avatar' => 'https://ui-avatars.com/api/?name=Jabborov+Abduroziq&background=0D8ABC&color=fff',
            'created_at' => '2025-01-15T10:30:00Z',
            'is_active' => true
        ],
        [
            'id' => 2,
            'name' => 'Alisher Karimov',
            'email' => 'alisher@example.com',
            'role' => 'Developer',
            'avatar' => 'https://ui-avatars.com/api/?name=Alisher+Karimov&background=6366F1&color=fff',
            'created_at' => '2025-02-01T14:20:00Z',
            'is_active' => true
        ],
        [
            'id' => 3,
            'name' => 'Fotima Nazarova',
            'email' => 'fotima@example.com',
            'role' => 'Designer',
            'avatar' => 'https://ui-avatars.com/api/?name=Fotima+Nazarova&background=EC4899&color=fff',
            'created_at' => '2025-02-10T09:15:00Z',
            'is_active' => true
        ],
        [
            'id' => 4,
            'name' => 'Sardor Toshmatov',
            'email' => 'sardor@example.com',
            'role' => 'Manager',
            'avatar' => 'https://ui-avatars.com/api/?name=Sardor+Toshmatov&background=10B981&color=fff',
            'created_at' => '2025-02-20T16:45:00Z',
            'is_active' => false
        ],
        [
            'id' => 5,
            'name' => 'Malika Yusupova',
            'email' => 'malika@example.com',
            'role' => 'QA Engineer',
            'avatar' => 'https://ui-avatars.com/api/?name=Malika+Yusupova&background=F59E0B&color=fff',
            'created_at' => '2025-03-01T11:30:00Z',
            'is_active' => true
        ]
    ];

    // Add pagination support
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    
    $totalUsers = count($users);
    $paginatedUsers = array_slice($users, $offset, $limit);
    
    $response = [
        'success' => true,
        'data' => $paginatedUsers,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalUsers,
            'total_pages' => ceil($totalUsers / $limit),
            'has_more' => ($offset + $limit) < $totalUsers
        ],
        'meta' => [
            'timestamp' => date('c'),
            'version' => 'v1.0.0',
            'server' => 'API Gateway'
        ]
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

function handleApiHealth() {
    $response = [
        'success' => true,
        'service' => 'API Gateway',
        'version' => '1.0.0',
        'status' => 'healthy',
        'timestamp' => date('c'),
        'uptime' => getUptime(),
        'endpoints' => [
            'GET /api/v1/users' => 'Get users list with pagination',
            'GET /api/v1/health' => 'API health check'
        ]
    ];
    
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

function getUptime() {
    if (file_exists('/proc/uptime')) {
        $uptime = file_get_contents('/proc/uptime');
        $uptime = floatval(explode(' ', $uptime)[0]);
        
        // Convert to int first to avoid deprecation warning with modulo operator in PHP 8.1+
        $uptimeInt = intval($uptime);
        
        $days = intval(floor($uptime / 86400));
        $hours = intval(floor(($uptimeInt % 86400) / 3600));
        $minutes = intval(floor(($uptimeInt % 3600) / 60));
        
        return sprintf('%dd %02dh %02dm', $days, $hours, $minutes);
    }
    return 'N/A';
}

function sendMethodNotAllowed() {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed',
        'message' => 'The requested HTTP method is not supported for this endpoint.',
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}

function sendNotFound() {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Not Found',
        'message' => 'The requested API endpoint was not found.',
        'available_endpoints' => [
            'GET /api/v1/users',
            'GET /api/v1/health'
        ],
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>