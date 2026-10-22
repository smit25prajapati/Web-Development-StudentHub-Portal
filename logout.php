<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) audit_log($conn, (int)$_SESSION['user_id'], 'logout', 'User signed out');
if (!empty($_COOKIE['studenthub_remember'])) {
    $parts = explode(':', $_COOKIE['studenthub_remember'], 2);
    if (count($parts) === 2) {
        $stmt = $conn->prepare('DELETE FROM remember_tokens WHERE selector = ?');
        $stmt->bind_param('s', $parts[0]); $stmt->execute(); $stmt->close();
    }
    setcookie('studenthub_remember', '', ['expires'=>time()-3600,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
session_start();
$_SESSION['flash'] = ['type'=>'success','message'=>'You have been logged out safely.'];
header('Location: login.php'); exit;
