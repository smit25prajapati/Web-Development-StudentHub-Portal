<?php
require_once __DIR__ . '/includes/init.php'; require_login();
$pageTitle = 'Dashboard';
$stmt = $conn->prepare('SELECT name,email,username,course,last_login FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute(); $profile = $stmt->get_result()->fetch_assoc(); $stmt->close();
$regs = $conn->prepare('SELECT e.title,e.event_date,e.location FROM registrations r JOIN events e ON e.id=r.event_id WHERE r.student_id=? ORDER BY e.event_date');
$regs->bind_param('i', $_SESSION['user_id']); $regs->execute(); $myEvents = $regs->get_result();
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><p class="eyebrow">YOUR SPACE</p><h1>Hello, <?= e($_SESSION['name']) ?> 👋</h1><p class="muted">Welcome to your StudentHub dashboard.</p></div>
<div class="grid-3">
  <div class="card"><p class="eyebrow">PROFILE</p><h3><?= e($profile['name']) ?></h3><p class="muted"><?= e($profile['email']) ?></p><p><b>Username:</b> <?= e($profile['username']) ?></p><p><b>Course:</b> <?= e($profile['course'] ?: 'Not set') ?></p></div>
  <div class="card"><p class="eyebrow">CAMPUS</p><h3>Find an event</h3><p class="muted">Browse the current events and campus activities.</p><a class="btn" href="events.php">Browse events</a></div>
  <div class="card"><p class="eyebrow">ACCOUNT</p><h3>Signed in as</h3><p><span class="pill"><?= e(ucfirst($_SESSION['role'])) ?></span></p><p class="muted">Sessions expire after 15 minutes of inactivity.</p><a class="btn btn-light" href="logout.php">Logout</a></div>
</div>
<?php if (!is_admin()): ?><div class="panel" style="margin-top:20px"><h3>My event registrations</h3><?php if ($myEvents->num_rows === 0): ?><p class="muted">You have not registered for any events yet. <a href="events.php">Browse events</a></p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Event</th><th>Date</th><th>Location</th></tr></thead><tbody><?php while ($ev = $myEvents->fetch_assoc()): ?><tr><td><?= e($ev['title']) ?></td><td><?= e(date('d M Y, h:i A', strtotime($ev['event_date']))) ?></td><td><?= e($ev['location']) ?></td></tr><?php endwhile; ?></tbody></table></div><?php endif; ?></div><?php endif; ?>
<?php if (is_admin()): ?><div class="panel" style="margin-top:20px"><h3>Admin shortcuts</h3><a class="btn" href="students.php">Manage students</a> <a class="btn" href="admin_events.php">Manage events</a> <a class="btn" href="admin.php">Admin dashboard</a></div><?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>