# StudentHub

## Setup (XAMPP, Windows)
1. Put this folder at `C:\xampp\htdocs\StudentHub` (so `Home.php` is directly inside it).
2. Start **Apache** and **MySQL** in XAMPP.
3. phpMyAdmin (`http://localhost/phpmyadmin`) -> Import -> `database.sql`. This creates the `studenthub_portal` database.
4. Open `http://localhost/StudentHub/`

DB settings are in `includes/db.php` (root, empty password, db `studenthub_portal`).

## Demo logins
- Admin: `admin` / `Admin@123`
- Student: `student` / `Student@123` (also `student03` ... `student22`)

## Pages
- Public: `Home.php`, `login.php`, `signup.php`, `contact.php`, `events.php`, `courses.html`, `result.html`, `Profile.html`, `forgetpass.html`
- Student: `dashboard.php` (profile + my event registrations); register/cancel on `events.php`
- Admin: `admin.php` (stats, audit log), `students.php` (add/search/filter/delete), `admin_events.php` (add/edit/delete + poster upload), `view_contacts.php`, `api_demo.php` (fetch API) -> `api/students.php`

## Notes
- Login Student ID = the username chosen at signup. Sessions expire after 15 minutes of inactivity.
- Contact messages are saved to `data/contacts.csv` (blocked from direct web access, viewable by admin).
- Old `.html` links for Home/login/signup/contact redirect to the `.php` pages.
- Replace `student.png` with your own picture if you like.
