<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal</title>
    <link rel="Stylesheet" href="Home.css">
    <link rel="Stylesheet" href="messages.css">
</head>

<body>

    <header>
        <a href="Profile.html">
            <img src="student.png" height="100" width="100" alt="Student">
        </a>
        <h2> Welcome to StudentHub portal </h2>
        <nav>
            <ul>
                <?php if (is_logged_in()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="courses.html">Courses</a></li>
                <?php if (!is_logged_in()): ?>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
                <li><a href="result.html">Results</a></li>
                <li><a href="events.php">Events</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="students.php">Students</a></li>
                    <li><a href="admin_events.php">Manage Events</a></li>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if (is_logged_in()): ?>
            <p>Signed in as <b><?= e($_SESSION['name']) ?></b> (<?= e($_SESSION['role']) ?>)</p>
        <?php endif; ?>
    </header>

    <?php if (!empty($_SESSION['flash'])): ?>
        <section style="width:700px;max-width:100%"><?php flash_message(); ?></section>
    <?php endif; ?>

    <section>
        <h3>About StudentHub</h3>
        <p>StudentHub is a comprehensive platform designed to support students in their academic journey. It provides
            resources, tools, and a community for students to connect, learn, and grow.</p>
        <p>Our mission is to empower students by offering access to educational materials, career guidance, and a space
            for collaboration. Whether you're looking for study tips, networking opportunities, or the latest academic
            news, StudentHub is here to help you succeed.</p>
        <p>Join us today and become part of a vibrant community dedicated to student success. Explore our features,
            participate in discussions, and take advantage of the resources available to enhance your learning
            experience.</p>
    </section>

</body>

<footer>
    <p> CHARUSAT </p>
</footer>

</html>
