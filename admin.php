<?php
require_once __DIR__ . '/includes/init.php'; require_admin();
$pageTitle = 'Admin dashboard';
$students=(int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch_assoc()['c'];
$events=(int)$conn->query("SELECT COUNT(*) c FROM events")->fetch_assoc()['c'];
$regs=(int)$conn->query("SELECT COUNT(*) c FROM registrations")->fetch_assoc()['c'];
$users=(int)$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$latest=$conn->query("SELECT name,email,created_at FROM users ORDER BY created_at DESC LIMIT 5");
$logs=$conn->query("SELECT a.action,a.details,a.created_at,u.username FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.id DESC LIMIT 10");
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><p class="eyebrow">ADMIN AREA · PRACTICAL 14</p><h1>Admin dashboard</h1><p class="muted">Quick overview of portal activity. This page is restricted to admin users.</p></div>
<div class="stat-grid"><div class="stat-card"><small>Total accounts</small><strong><?= $users ?></strong></div><div class="stat-card"><small>Student records</small><strong><?= $students ?></strong></div><div class="stat-card"><small>Events</small><strong><?= $events ?></strong></div><div class="stat-card"><small>Event registrations</small><strong><?= $regs ?></strong></div></div>
<div class="grid-3" style="grid-template-columns:1fr 1fr">
<div class="panel"><h3>Latest registrations</h3><div class="table-wrap"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Date</th></tr></thead><tbody><?php while($r=$latest->fetch_assoc()): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e(date('d M',strtotime($r['created_at']))) ?></td></tr><?php endwhile; ?></tbody></table></div></div>
<div class="panel"><h3>Recent audit log</h3><div class="table-wrap"><table class="data-table"><thead><tr><th>Action</th><th>User</th><th>Time</th></tr></thead><tbody><?php while($l=$logs->fetch_assoc()): ?><tr><td><?= e($l['action']) ?><br><small class="muted"><?= e($l['details']) ?></small></td><td><?= e($l['username']??'System') ?></td><td><?= e(date('d M H:i',strtotime($l['created_at']))) ?></td></tr><?php endwhile; ?></tbody></table></div></div>
</div>
<div class="panel" style="margin-top:20px"><h3>Quick actions</h3><a class="btn" href="students.php">Manage students</a> <a class="btn" href="admin_events.php">Manage events</a> <a class="btn btn-light" href="api_demo.php">AJAX demo</a> <a class="btn btn-light" href="view_contacts.php">Contact messages</a></div>
<?php include __DIR__ . '/includes/footer.php'; ?>