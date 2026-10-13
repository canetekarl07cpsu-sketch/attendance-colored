<?php
session_start();
require "db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = "Enter a name, valid email, and password with at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $error = "That email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Account could not be created. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-200 flex items-center justify-center p-5 bg-gradient-to-r from-green-600 to-white">
    <main class="w-full max-w-md rounded-2xl bg-gradient-to-r from-white to-green-300 p-8 shadow-sm border border-slate-800">
        <h1 class="text-2xl font-bold">Create account</h1>
        <p class="mt-2 text-sm text-slate-500">Make a student account to save attendance records.</p>
        <?php if ($error): ?><p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST" class="mt-6 space-y-4">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Full name</label>
                <input id="name" name="name" type="text" required maxlength="100" class="w-full rounded-lg border border-slate-500 px-3 py-3 outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required class="w-full rounded-lg border border-slate-500 px-3 py-3 outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" minlength="6" required class="w-full rounded-lg border border-slate-500 px-3 py-3 outline-none focus:ring-2 focus:ring-blue-500"><p class="mt-1 text-xs text-slate-700">Use at least 6 characters.</p></div>
            <button class="w-full rounded-lg bg-blue-600 py-3 font-medium text-white hover:bg-blue-700">Create student account</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-900">Already registered? <a href="login.php" class="font-semibold text-blue-600 hover:underline">Log in</a></p>
    </main>
</body>
</html>
