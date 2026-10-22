<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) { header('Location: ' . (is_admin() ? 'admin.php' : 'dashboard.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identity = trim($_POST['student-id'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT id,name,username,email,password_hash,role FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->bind_param('ss', $identity, $identity); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $upd = $conn->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $upd->bind_param('i', $user['id']); $upd->execute(); $upd->close();
        audit_log($conn, (int)$user['id'], 'login', 'Successful login');
        if (!empty($_POST['remember'])) {
            $selector = bin2hex(random_bytes(8));
            $validator = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $validator);
            $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
            $del = $conn->prepare('DELETE FROM remember_tokens WHERE user_id = ?'); $del->bind_param('i', $user['id']); $del->execute(); $del->close();
            $ins = $conn->prepare('INSERT INTO remember_tokens (user_id,selector,token_hash,expires_at) VALUES (?,?,?,?)');
            $ins->bind_param('isss', $user['id'], $selector, $tokenHash, $expires); $ins->execute(); $ins->close();
            setcookie('studenthub_remember', $selector . ':' . $validator, ['expires'=>time()+60*60*24*30,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
        }
        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php')); exit;
    }
    $error = 'Login failed. Check your Student ID/email and password.';
    audit_log($conn, null, 'login_failed', 'Failed login attempt');
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Login</title>
    <link rel="stylesheet" href="Login.css">
    <link rel="stylesheet" href="messages.css">
</head>

<body>

<section>

  <h2>StudentHub Portal</h2>

  <p>Log in to manage your classes, view announcements, and stay connected with campus life.</p>

  <?php flash_message(); ?>
  <?php if ($error): ?><div class="message error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <p>
      <label for="student-id">Student ID</label><br>
      <input type="text" id="student-id" name="student-id" placeholder="25CE001" required autocomplete="username">
    </p>

    <p>
      <label for="password">Password</label><br>
      <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
    </p>

    <p>
      <label><input type="checkbox" name="remember" value="1" style="width:auto"> Remember me for 30 days</label>
    </p>

    <p>
      <button type="submit">Log In</button>
      <a href="forgetpass.html">Forgot Password?</a>
    </p>

  </form>

  <p>New to StudentHub? <a href="signup.php">Create an account</a></p>
  <p class="muted"><small>Demo: admin / Admin@123 &middot; student / Student@123</small></p>

</section>

<button class="button-back" onclick="window.location.href='Home.php'">
    &larr; Back to Home
</button>

</body>
</html>
