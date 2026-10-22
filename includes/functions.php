<?php
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please sign in to view that page.'];
        header('Location: login.php');
        exit;
    }
}
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        $pageTitle = 'Access denied';
        include __DIR__ . '/header.php';
        echo '<div class="panel narrow"><div class="message error">You do not have permission to access this admin page.</div><a class="btn" href="dashboard.php">Return to dashboard</a></div>';
        include __DIR__ . '/footer.php';
        exit;
    }
}
function flash_message(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="message ' . e($f['type']) . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400);
            exit('Invalid form token. Go back and try again.');
        }
    }
}
function audit_log(mysqli $conn, ?int $userId, string $action, string $details = ''): void {
    $stmt = $conn->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $userId, $action, $details);
    $stmt->execute();
    $stmt->close();
}
