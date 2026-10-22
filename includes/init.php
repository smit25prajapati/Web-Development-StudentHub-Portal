<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
define('SESSION_TIMEOUT', 900); // 15 minutes of inactivity

if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your session expired. Please sign in again.'];
}
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Restore a remembered login only when no normal session is active.
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['studenthub_remember'])) {
    $parts = explode(':', $_COOKIE['studenthub_remember'], 2);
    if (count($parts) === 2 && ctype_xdigit($parts[0]) && ctype_xdigit($parts[1])) {
        [$selector, $validator] = $parts;
        $stmt = $conn->prepare('SELECT rt.user_id, rt.token_hash, rt.expires_at, u.name, u.username, u.role FROM remember_tokens rt JOIN users u ON u.id=rt.user_id WHERE rt.selector=? LIMIT 1');
        $stmt->bind_param('s', $selector); $stmt->execute();
        $token = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($token && strtotime($token['expires_at']) > time() && hash_equals($token['token_hash'], hash('sha256', $validator))) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$token['user_id'];
            $_SESSION['name'] = $token['name'];
            $_SESSION['username'] = $token['username'];
            $_SESSION['role'] = $token['role'];
            $_SESSION['last_activity'] = time();
        } else {
            if (isset($selector)) { $del = $conn->prepare('DELETE FROM remember_tokens WHERE selector=?'); $del->bind_param('s', $selector); $del->execute(); $del->close(); }
            setcookie('studenthub_remember', '', ['expires'=>time()-3600,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
        }
    }
}
