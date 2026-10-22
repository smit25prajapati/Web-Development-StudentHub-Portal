CREATE DATABASE IF NOT EXISTS studenthub_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_portal;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 username VARCHAR(60) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','student') NOT NULL DEFAULT 'student',
 course VARCHAR(100) DEFAULT '',
 last_login DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(150) NOT NULL,
 description TEXT NOT NULL,
 event_date DATETIME NOT NULL,
 location VARCHAR(150) NOT NULL,
 category VARCHAR(80) DEFAULT '',
 status ENUM('open','closed') NOT NULL DEFAULT 'open',
 poster VARCHAR(255) DEFAULT NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_events_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id INT UNSIGNED NOT NULL,
 event_id INT UNSIGNED NOT NULL,
 registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_registration (student_id,event_id),
 CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS remember_tokens (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 selector CHAR(16) NOT NULL UNIQUE,
 token_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 details VARCHAR(255) DEFAULT '',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Demo password hashes are generated for these known demo passwords:
-- admin / Admin@123 ; student / Student@123
INSERT IGNORE INTO users (id,name,email,username,password_hash,role,course) VALUES
(1,'StudentHub Admin','admin@studenthub.local','admin','$2y$12$YSOjtOFJ5TroRdgG/bmi5OovOXEe3UujOrcKCB/nU0YqTGlIYlC9a','admin','Administration'),
(2,'Demo Student','student@studenthub.local','student','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA');

INSERT IGNORE INTO users (id,name,email,username,password_hash,role,course) VALUES
(3,'Aarav Patel','student03@studenthub.local','student03','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(4,'Diya Shah','student04@studenthub.local','student04','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(5,'Vivaan Mehta','student05@studenthub.local','student05','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BSc IT'),
(6,'Anaya Desai','student06@studenthub.local','student06','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCom'),
(7,'Reyansh Joshi','student07@studenthub.local','student07','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(8,'Kiara Trivedi','student08@studenthub.local','student08','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BSc IT'),
(9,'Aditya Parmar','student09@studenthub.local','student09','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(10,'Myra Shah','student10@studenthub.local','student10','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BBA'),
(11,'Arjun Patel','student11@studenthub.local','student11','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(12,'Saanvi Mehta','student12@studenthub.local','student12','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BSc IT'),
(13,'Ishaan Dave','student13@studenthub.local','student13','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCom'),
(14,'Aadhya Modi','student14@studenthub.local','student14','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(15,'Krish Vyas','student15@studenthub.local','student15','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BBA'),
(16,'Pari Thakkar','student16@studenthub.local','student16','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(17,'Rudra Pandya','student17@studenthub.local','student17','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BSc IT'),
(18,'Ira Shah','student18@studenthub.local','student18','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(19,'Dev Patel','student19@studenthub.local','student19','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCom'),
(20,'Navya Joshi','student20@studenthub.local','student20','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BCA'),
(21,'Kabir Desai','student21@studenthub.local','student21','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BBA'),
(22,'Riya Parmar','student22@studenthub.local','student22','$2y$12$ph7L.30tU7OxsmxS3/EzV.3b382TTcrVUOb07vLXb.RZII5MLNsDK','student','BSc IT');
INSERT IGNORE INTO events (id,title,description,event_date,location,category,status,created_by) VALUES
(1,'Web Development Workshop','A beginner-friendly introduction to building websites.','2027-01-18 10:00:00','Computer Lab 2','Workshop','open',1),
(2,'Annual Sports Day','Join campus teams for friendly competitions and games.','2027-02-05 09:00:00','College Ground','Sports','open',1),
(3,'Project Showcase','Present student projects and learn from classmates.','2027-02-20 11:00:00','Seminar Hall','Academic','open',1);
