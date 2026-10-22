<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Sign Up';
$errors = [];
$studentCode = $name = $department = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $studentCode = trim($_POST['student_code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $studentCode)) $errors[] = 'Student ID must be 3–30 letters, numbers, underscores or hyphens.';
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name (maximum 100 characters).';
    if ($department === '' || mb_strlen($department) > 100) $errors[] = 'Enter a department (maximum 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address (maximum 190 characters).';
    if (strlen($password) < 6 || strlen($password) > 72) $errors[] = 'Password must be between 6 and 72 characters.';

    if (!$errors) {
        try {
            $check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $check->bind_param('ss', $studentCode, $email); $check->execute();
            $exists = $check->get_result()->num_rows > 0; $check->close();
            if ($exists) {
                $errors[] = 'That Student ID or email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'student';
                $ins = $conn->prepare('INSERT INTO users (name,email,username,password_hash,role,course) VALUES (?,?,?,?,?,?)');
                $ins->bind_param('ssssss', $name, $email, $studentCode, $hash, $role, $department);
                $ins->execute(); $newId = $ins->insert_id; $ins->close();
                audit_log($conn, $newId, 'account_registered', 'Student account created');
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration successful. You can now log in.'];
                header('Location: login.php'); exit;
            }
        } catch (mysqli_sql_exception $e) {
            $errors[] = ((int)$e->getCode() === 1062)
                ? 'That Student ID or email is already registered.'
                : 'Registration could not be saved. Check that MySQL is running and database.sql is imported.';
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="panel narrow">
    <h1>Create Student Account</h1>
    <p class="muted">Enter your details to register. Fields are checked in your browser and again on the server.</p>

    <?php if ($errors): ?>
        <div class="message error" role="alert">
            <strong>Please correct the following:</strong>
            <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="signup.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label for="student_code">Student ID / Username</label>
        <input id="student_code" name="student_code" type="text" value="<?= e($studentCode) ?>" minlength="3" maxlength="30"
               pattern="[A-Za-z0-9_-]{3,30}" title="3–30 letters, numbers, underscores or hyphens" required>

        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?= e($name) ?>" maxlength="100" required>

        <label for="department">Department</label>
        <input id="department" name="department" type="text" value="<?= e($department) ?>" maxlength="100" required>

        <label for="email">Email ID</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" maxlength="190" autocomplete="email" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" minlength="6" maxlength="72" autocomplete="new-password" required>
        <p class="muted">Use at least 6 characters.</p>

        <button type="submit">Register Student</button>
    </form>
    <p class="muted">Already registered? <a href="login.php">Log in</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
