<?php
// api.php
header('Content-Type: application/json');
require_once 'config.php';

session_start();

function loadUsers() {
    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, json_encode([]));
    }
    return json_decode(file_get_contents(DATA_FILE), true);
}

function saveUsers($users) {
    file_put_contents(DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function generateToken($email) {
    $payload = [
        'email' => $email,
        'iat'   => time(),
        'exp'   => time() + 86400 * 7   // 7 days
    ];
    $header = base64_encode(json_encode(['alg'=>'HS256','typ'=>'JWT']));
    $payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$payload", SECRET_KEY, true);
    $signature = str_replace(['+','/','='], ['-','_',''], base64_encode($signature));
    return "$header.$payload.$signature";
}

function validateToken($token) {
    if (!$token) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    $payload = json_decode(base64_decode($parts[1]), true);
    if (!$payload || !isset($payload['email']) || $payload['exp'] < time()) {
        return null;
    }
    
    $expected = hash_hmac('sha256', $parts[0].'.'.$parts[1], SECRET_KEY, true);
    $expected = str_replace(['+','/','='], ['-','_',''], base64_encode($expected));
    
    if ($expected !== $parts[2]) return null;
    
    return $payload['email'];
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$users = loadUsers();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    if ($action === 'register') {
        $email = trim($input['email'] ?? '');
        $pass  = trim($input['password'] ?? '');

        if (!$email || !$pass) {
            echo json_encode(['error' => 'Email aur password zaroori hain']);
            exit;
        }

        if (isset($users[$email])) {
            echo json_encode(['error' => 'Email already registered']);
            exit;
        }

        $users[$email] = [
            'email'    => $email,
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'credits'  => 100,
            'last_earn'=> time(),
            'is_admin' => ($email === ADMIN_EMAIL)
        ];

        saveUsers($users);
        $token = generateToken($email);
        echo json_encode(['token' => $token]);
        exit;
    }

    if ($action === 'login') {
        $email = trim($input['email'] ?? '');
        $pass  = trim($input['password'] ?? '');

        if (!$email || !$pass || !isset($users[$email])) {
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        if (!password_verify($pass, $users[$email]['password'])) {
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $token = generateToken($email);
        echo json_encode(['token' => $token]);
        exit;
    }

    if ($action === 'earn-credit') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $email = validateToken(str_replace('Bearer ', '', $token));

        if (!$email || !isset($users[$email])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $now = time();
        $diff = $now - ($users[$email]['last_earn'] ?? 0);

        if ($diff >= 60) {
            $earned = floor($diff / 60) * CREDIT_PER_MINUTE;
            $users[$email]['credits'] += $earned;
            $users[$email]['last_earn'] = $now;
            saveUsers($users);
            echo json_encode([
                'credits' => $users[$email]['credits'],
                'message' => "+$earned credits earned"
            ]);
        } else {
            echo json_encode([
                'credits' => $users[$email]['credits'],
                'message' => 'Wait for next minute'
            ]);
        }
        exit;
    }

    if ($action === 'admin-give-credits') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $email = validateToken(str_replace('Bearer ', '', $token));

        if (!$email || !isset($users[$email]) || !$users[$email]['is_admin']) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin only']);
            exit;
        }

        $target = trim($input['user'] ?? '');
        $amount = (int)($input['amount'] ?? 0);

        if (!$target || $amount <= 0 || !isset($users[$target])) {
            echo json_encode(['error' => 'Invalid user or amount']);
            exit;
        }

        $users[$target]['credits'] += $amount;
        saveUsers($users);
        echo json_encode(['message' => "Added $amount credits to $target"]);
        exit;
    }
}

if ($action === 'me') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $email = validateToken(str_replace('Bearer ', '', $token));

    if (!$email || !isset($users[$email])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    echo json_encode([
        'email'    => $email,
        'credits'  => $users[$email]['credits'],
        'is_admin' => $users[$email]['is_admin'] ?? false
    ]);
    exit;
}

if ($action === 'users') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $email = validateToken(str_replace('Bearer ', '', $token));

    if (!$email || !isset($users[$email]) || !$users[$email]['is_admin']) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin only']);
        exit;
    }

    $list = [];
    foreach ($users as $u) {
        $list[] = [
            'email'   => $u['email'],
            'credits' => $u['credits'],
            'is_admin'=> $u['is_admin'] ?? false
        ];
    }
    echo json_encode($list);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
