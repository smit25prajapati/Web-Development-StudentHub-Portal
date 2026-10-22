<?php
require_once __DIR__ . '/includes/init.php'; require_admin();
$pageTitle = 'Manage events';
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
function save_poster(array $file, string $uploadDir): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2*1024*1024) throw new RuntimeException('Poster must be under 2 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, and WEBP posters are allowed.');
    $name = bin2hex(random_bytes(12)).'.'.$allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'],$uploadDir.'/'.$name)) throw new RuntimeException('Could not save poster.');
    return 'uploads/'.$name;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $action=$_POST['action']??'';
    try {
        if ($action==='save') {
            $id=(int)($_POST['id']??0); $title=trim($_POST['title']??''); $description=trim($_POST['description']??''); $date=trim($_POST['event_date']??''); $location=trim($_POST['location']??''); $category=trim($_POST['category']??''); $status=($_POST['status']??'open')==='closed'?'closed':'open';
            if (strlen($title)<3 || $description==='' || $date==='' || $location==='') throw new RuntimeException('Complete all required event fields.');
            $poster=save_poster($_FILES['poster']??[], $uploadDir);
            if ($id>0) {
                if ($poster) { $stmt=$conn->prepare('UPDATE events SET title=?,description=?,event_date=?,location=?,category=?,status=?,poster=? WHERE id=?'); $stmt->bind_param('sssssssi',$title,$description,$date,$location,$category,$status,$poster,$id); }
                else { $stmt=$conn->prepare('UPDATE events SET title=?,description=?,event_date=?,location=?,category=?,status=? WHERE id=?'); $stmt->bind_param('ssssssi',$title,$description,$date,$location,$category,$status,$id); }
                $stmt->execute(); $stmt->close(); audit_log($conn,(int)$_SESSION['user_id'],'event_updated',$title);
                $_SESSION['flash']=['type'=>'success','message'=>'Event updated.'];
            } else {
                $stmt=$conn->prepare('INSERT INTO events (title,description,event_date,location,category,status,poster,created_by) VALUES (?,?,?,?,?,?,?,?)'); $uid=(int)$_SESSION['user_id']; $stmt->bind_param('sssssssi',$title,$description,$date,$location,$category,$status,$poster,$uid); $stmt->execute(); $stmt->close(); audit_log($conn,$uid,'event_added',$title);
                $_SESSION['flash']=['type'=>'success','message'=>'Event added.'];
            }
        } elseif ($action==='delete') {
            $id=(int)($_POST['id']??0); $stmt=$conn->prepare('SELECT title,poster FROM events WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $old=$stmt->get_result()->fetch_assoc(); $stmt->close();
            $stmt=$conn->prepare('DELETE FROM events WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
            if ($old && $old['poster'] && is_file(__DIR__.'/'.$old['poster'])) unlink(__DIR__.'/'.$old['poster']);
            audit_log($conn,(int)$_SESSION['user_id'],'event_deleted',$old['title']??('Event '.$id)); $_SESSION['flash']=['type'=>'success','message'=>'Event deleted.'];
        }
    } catch (Throwable $e) { $_SESSION['flash']=['type'=>'error','message'=>$e instanceof RuntimeException ? $e->getMessage() : 'Could not save this event. Check required fields and database settings.']; }
    header('Location: admin_events.php'); exit;
}
$edit=null;
if(isset($_GET['edit'])){$id=(int)$_GET['edit'];$st=$conn->prepare('SELECT * FROM events WHERE id=?');$st->bind_param('i',$id);$st->execute();$edit=$st->get_result()->fetch_assoc();$st->close();}
$events=$conn->query('SELECT e.id,e.title,e.event_date,e.location,e.status,e.category,e.poster,(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) AS regs FROM events e ORDER BY e.event_date DESC');
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><p class="eyebrow">ADMIN TOOLS · PRACTICAL 12</p><h1>Manage events</h1><p class="muted">Add, edit, delete events and upload a validated poster image.</p></div>
<div class="form-card" style="max-width:none;margin-bottom:24px"><h3><?= $edit?'Edit event':'Add an event' ?></h3>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div class="form-grid"><div class="field"><label>Event title</label><input name="title" required minlength="3" value="<?= e($edit['title']??'') ?>"></div><div class="field"><label>Date and time</label><input type="datetime-local" name="event_date" required value="<?= !empty($edit['event_date'])?e(date('Y-m-d\TH:i',strtotime($edit['event_date']))):'' ?>"></div><div class="field"><label>Location</label><input name="location" required value="<?= e($edit['location']??'') ?>"></div><div class="field"><label>Category</label><input name="category" value="<?= e($edit['category']??'') ?>" placeholder="Workshop, Sports, Club"></div><div class="field"><label>Status</label><select name="status"><option value="open" <?= ($edit['status']??'open')==='open'?'selected':'' ?>>Open / published</option><option value="closed" <?= ($edit['status']??'')==='closed'?'selected':'' ?>>Closed / hidden</option></select></div><div class="field"><label>Poster (JPG, PNG, WEBP; max 2 MB)</label><input type="file" name="poster" accept="image/jpeg,image/png,image/webp"></div></div>
<div class="field"><label>Description</label><textarea name="description" required><?= e($edit['description']??'') ?></textarea></div><button class="btn" type="submit"><?= $edit?'Save changes':'Add event' ?></button> <?php if($edit): ?><a class="btn btn-light" href="admin_events.php">Cancel edit</a><?php endif; ?></form></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Event</th><th>Date</th><th>Location</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead><tbody><?php while($ev=$events->fetch_assoc()): ?><tr><td><?= e($ev['title']) ?> <small class="muted"><?= e($ev['category']) ?></small></td><td><?= e(date('d M Y',strtotime($ev['event_date']))) ?></td><td><?= e($ev['location']) ?></td><td><span class="pill"><?= e($ev['status']) ?></span></td><td><?= (int)$ev['regs'] ?></td><td><a class="btn btn-light btn-small" href="admin_events.php?edit=<?= (int)$ev['id'] ?>">Edit</a> <form class="inline-form" method="post" onsubmit="return confirm('Delete this event?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$ev['id'] ?>"><button class="btn btn-danger btn-small">Delete</button></form></td></tr><?php endwhile; ?></tbody></table></div>
<?php include __DIR__ . '/includes/footer.php'; ?>