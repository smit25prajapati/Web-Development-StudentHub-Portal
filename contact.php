<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Contact Us';
$errors = [];
$success = isset($_GET['sent']);
$name = $email = $topic = $message = '';
$allowedTopics = ['General Inquiry', 'Support', 'Courses', 'Partnerships'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $topic = trim($_POST['topic'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter your name (up to 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (!in_array($topic, $allowedTopics, true)) $errors[] = 'Choose a valid topic.';
    if ($message === '' || mb_strlen($message) > 3000) $errors[] = 'Enter a message (up to 3000 characters).';
    if (!$errors) {
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true)) $errors[] = 'Could not prepare storage. Please try again.';
        $file = $dataDir . '/contacts.csv';
        if (!$errors) {
            $fp = fopen($file, 'ab');
            if ($fp === false) {
                $errors[] = 'Could not save your message. Check folder permissions.';
            } else {
                if (flock($fp, LOCK_EX)) {
                    if (filesize($file) === 0) fputcsv($fp, ['submitted_at', 'name', 'email', 'topic', 'message']);
                    $ok = fputcsv($fp, [date('c'), $name, $email, $topic, $message]);
                    fflush($fp); flock($fp, LOCK_UN);
                    if ($ok === false) $errors[] = 'Could not save your message. Please try again.';
                } else {
                    $errors[] = 'Storage is busy. Please try again.';
                }
                fclose($fp);
            }
        }
        if (!$errors) { header('Location: contact.php?sent=1'); exit; }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="panel narrow">
<h1>Contact Us</h1>
<p class="muted">Send a message to the StudentHub team. Fields are checked on the server before the message is saved.</p>
<?php if ($success): ?><div class="message success" role="status">Your message was submitted and saved successfully.</div><?php endif; ?>
<?php if ($errors): ?><div class="message error" role="alert"><strong>Please correct the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="contact.php" novalidate>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label for="name">Full Name</label><input id="name" name="name" maxlength="100" value="<?= e($name) ?>" required>
<label for="email">Email Address</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($email) ?>" required>
<label for="topic">Topic</label>
<select id="topic" name="topic"><?php foreach ($allowedTopics as $t): ?><option <?= $topic === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
<label for="message">Message</label><textarea id="message" name="message" rows="5" maxlength="3000" required><?= e($message) ?></textarea>
<button type="submit">Send Message</button>
</form>
<?php if (is_admin()): ?><div class="links"><a href="view_contacts.php">View saved messages</a></div><?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
