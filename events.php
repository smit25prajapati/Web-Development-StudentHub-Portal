<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Events';
$uid = is_logged_in() ? (int)$_SESSION['user_id'] : 0;
$isStudent = is_logged_in() && !is_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!$isStudent) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Log in with a student account to register for events.'];
    } else {
        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? 'register';
        if (!$eventId) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Select a valid event.'];
        } elseif ($action === 'cancel') {
            $stmt = $conn->prepare('DELETE FROM registrations WHERE student_id=? AND event_id=?');
            $stmt->bind_param('ii', $uid, $eventId); $stmt->execute(); $stmt->close();
            audit_log($conn, $uid, 'event_unregistered', 'Event id ' . $eventId);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration cancelled.'];
        } else {
            $stmt = $conn->prepare("INSERT IGNORE INTO registrations (student_id,event_id) SELECT ?, id FROM events WHERE id=? AND status='open'");
            $stmt->bind_param('ii', $uid, $eventId); $stmt->execute(); $added = $stmt->affected_rows; $stmt->close();
            if ($added > 0) audit_log($conn, $uid, 'event_registered', 'Event id ' . $eventId);
            $_SESSION['flash'] = $added > 0
                ? ['type' => 'success', 'message' => 'You are registered for this event.']
                : ['type' => 'error', 'message' => 'You are already registered, or the event is not available.'];
        }
    }
    header('Location: events.php'); exit;
}

$mine = [];
if ($isStudent) {
    $r = $conn->prepare('SELECT event_id FROM registrations WHERE student_id=?');
    $r->bind_param('i', $uid); $r->execute();
    foreach ($r->get_result() as $row) $mine[(int)$row['event_id']] = true;
    $r->close();
}
$result = $conn->query("SELECT id,title,description,event_date,location,category,poster FROM events WHERE status='open' ORDER BY event_date ASC");
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><h1>Campus Events</h1><p class="muted">Browse activities published by StudentHub admins<?= $isStudent ? ' and register for the ones you want to attend' : '' ?>.</p></div>
<?php if (!is_logged_in()): ?><div class="message info"><a href="login.php">Log in</a> or <a href="signup.php">sign up</a> to register for events.</div><?php endif; ?>
<div class="event-grid">
<?php $count = 0; while ($event = $result->fetch_assoc()): $count++; $eid = (int)$event['id']; ?>
<article class="event-card">
  <?php if ($event['poster'] && is_file(__DIR__ . '/' . $event['poster'])): ?><img src="<?= e($event['poster']) ?>" alt="Poster for <?= e($event['title']) ?>"><?php else: ?><div class="poster-placeholder">STUDENTHUB EVENT</div><?php endif; ?>
  <div class="event-body">
    <span class="pill"><?= e($event['category'] ?: 'Campus') ?></span>
    <h3><?= e($event['title']) ?></h3>
    <p class="muted"><?= e($event['description']) ?></p>
    <p><b><?= e(date('d M Y, h:i A', strtotime($event['event_date']))) ?></b><br><span class="muted"><?= e($event['location']) ?></span></p>
    <?php if ($isStudent): ?>
      <form method="post" action="events.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="event_id" value="<?= $eid ?>">
        <?php if (isset($mine[$eid])): ?>
          <span class="pill">Registered</span>
          <input type="hidden" name="action" value="cancel"><button class="btn btn-light btn-small" type="submit">Cancel</button>
        <?php else: ?>
          <input type="hidden" name="action" value="register"><button class="btn btn-small" type="submit">Register</button>
        <?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</article>
<?php endwhile; ?>
</div>
<?php if ($count === 0): ?><p>No events are open right now.</p><?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
