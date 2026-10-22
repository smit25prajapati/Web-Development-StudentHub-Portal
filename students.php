<?php
require_once __DIR__ . '/includes/init.php'; require_admin();
$pageTitle = 'Student management';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id && $id !== (int)$_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
            $stmt->bind_param('i', $id); $stmt->execute(); $deleted = $stmt->affected_rows; $stmt->close();
            audit_log($conn, (int)$_SESSION['user_id'], 'student_deleted', 'User id ' . $id);
            $_SESSION['flash'] = ['type'=>$deleted ? 'success':'error','message'=>$deleted ? 'Student deleted.':'Student could not be deleted.'];
        } else $_SESSION['flash'] = ['type'=>'error','message'=>'Invalid student selection.'];
        header('Location: students.php'); exit;
    }
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $username = trim($_POST['username'] ?? ''); $course = trim($_POST['course'] ?? '');
        if (strlen($name) < 2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($username) < 3) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Enter a valid name, email, and username.'];
        } else {
            $tempPass = password_hash('Student@123', PASSWORD_DEFAULT);
            $role='student';
            $stmt=$conn->prepare('INSERT INTO users (name,email,username,password_hash,role,course) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ssssss',$name,$email,$username,$tempPass,$role,$course);
            try {$stmt->execute(); audit_log($conn,(int)$_SESSION['user_id'],'student_added','Added '.$username); $_SESSION['flash']=['type'=>'success','message'=>'Student added. Default password is Student@123.'];}
            catch (Throwable $e) { $_SESSION['flash']=['type'=>'error','message'=>'Could not add student. Check for duplicate email or username.']; }
            $stmt->close();
        }
        header('Location: students.php'); exit;
    }
}
$q = trim($_GET['q'] ?? ''); $courseFilter = trim($_GET['course'] ?? '');
$sql = "SELECT id,name,email,username,course,created_at FROM users WHERE role='student'";
$params=[]; $types='';
if ($q !== '') { $sql .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ?)"; $like='%'.$q.'%'; array_push($params,$like,$like,$like); $types.='sss'; }
if ($courseFilter !== '') { $sql .= " AND course=?"; $params[]=$courseFilter; $types.='s'; }
$sql .= ' ORDER BY id DESC';
$stmt=$conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute(); $students=$stmt->get_result();
$courses=$conn->query("SELECT DISTINCT course FROM users WHERE role='student' AND course<>'' ORDER BY course");
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><p class="eyebrow">ADMIN TOOLS · PRACTICAL 11</p><h1>Student management</h1><p class="muted">Add, search, filter, and delete student records.</p></div>
<div class="panel" style="margin-bottom:20px"><h3>Add a student</h3><form method="post" class="toolbar"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input name="name" placeholder="Full name" required><input type="email" name="email" placeholder="Email" required><input name="username" placeholder="Username" required><input name="course" placeholder="Course"><button class="btn" type="submit">Add student</button></form><p class="help-text">New student accounts get the starter password Student@123. They should change it before real-world use.</p></div>
<form class="toolbar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search name, email, course"><select name="course"><option value="">All courses</option><?php while($c=$courses->fetch_assoc()): ?><option value="<?= e($c['course']) ?>" <?= $courseFilter===$c['course']?'selected':'' ?>><?= e($c['course']) ?></option><?php endwhile; ?></select><button class="btn" type="submit">Search / filter</button><a class="btn btn-light" href="students.php">Reset</a></form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Course</th><th>Action</th></tr></thead><tbody>
<?php while($s=$students->fetch_assoc()): ?><tr><td><?= (int)$s['id'] ?></td><td><?= e($s['name']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['username']) ?></td><td><?= e($s['course']) ?></td><td><form class="inline-form" method="post" onsubmit="return confirm('Delete this student record?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></td></tr><?php endwhile; ?>
</tbody></table></div>
<?php include __DIR__ . '/includes/footer.php'; ?>