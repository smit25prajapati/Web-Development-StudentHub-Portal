<?php
require_once __DIR__ . '/includes/init.php'; require_admin();
$pageTitle = 'Saved Messages';
$file = __DIR__ . '/data/contacts.csv'; $rows = [];
if (is_file($file) && ($fp = fopen($file, 'rb')) !== false) { while (($row = fgetcsv($fp)) !== false) $rows[] = $row; fclose($fp); }
include __DIR__ . '/includes/header.php';
?>
<div class="panel">
<h1>Saved Contact Messages</h1>
<?php if (count($rows) <= 1): ?><p>No messages have been saved yet.</p>
<?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><?php foreach ($rows[0] as $heading): ?><th><?= e($heading) ?></th><?php endforeach; ?></tr></thead>
<tbody><?php foreach (array_reverse(array_slice($rows, 1)) as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<div class="links"><a href="contact.php">Contact form</a><a href="admin.php">Admin dashboard</a></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
