<?php
$conn = new mysqli("localhost", "root", "", "student_attendance");
if ($conn->connect_error) {
    die("Database connection failed.");
}
$conn->set_charset("utf8mb4");

$check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD role VARCHAR(20) NOT NULL DEFAULT 'student'");
}

$admin_email = "admin@gmail.com";
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $admin_email);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $admin_name = "Admin";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $role = "admin";
    $add = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $add->bind_param("ssss", $admin_name, $admin_email, $admin_password, $role);
    $add->execute();
}
