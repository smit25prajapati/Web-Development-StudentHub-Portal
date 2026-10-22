<?php $pageTitle = $pageTitle ?? 'StudentHub'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - StudentHub</title>
    <link rel="stylesheet" href="backend.css">
</head>
<body>
<header class="site-header">
    <h2>StudentHub Portal</h2>
    <nav>
        <a href="Home.php">Home</a>
        <a href="courses.html">Courses</a>
        <a href="result.html">Results</a>
        <a href="events.php">Events</a>
        <a href="contact.php">Contact Us</a>
        <?php if (is_logged_in()): ?>
            <a href="dashboard.php">Dashboard</a>
            <?php if (is_admin()): ?>
                <a href="students.php">Students</a>
                <a href="admin_events.php">Manage Events</a>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php flash_message(); ?>
