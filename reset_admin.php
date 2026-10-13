<?php
require "db.php";

$email = "admin@gmail.com";
$name = "Admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE email = ?");
    $stmt->bind_param("ssss", $name, $password, $role, $email);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
}

echo "Admin account reset. Email: admin@gmail.com Password: admin123. Delete reset_admin.php now.";
