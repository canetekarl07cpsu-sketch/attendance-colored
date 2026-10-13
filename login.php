<?php
session_start();
require "db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "admin") {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Email or password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-r from-green-500 to-white flex items-center justify-center p-5">
    <main class="w-full max-w-md rounded-2xl bg-gradient-to-r from-white to-green-300 p-8 shadow-sm border border-slate-500">
        <h1 class="text-2xl font-bold">CCIT-01 Attendance</h1>

        <p class="mt-2 text-sm text-slate-500">Log in to your attendance account.</p>

        <?php if ($error): ?><p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <form method="POST" class="mt-6 space-y-4">
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required class="w-full rounded-lg border border-slate-500 px-3 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-lg border border-slate-500 px-3 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button class="w-full rounded-lg bg-blue-600 py-3 font-medium text-white hover:bg-blue-700">Log in</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-900">Need a student account? <a href="register.php" class="font-semibold text-blue-600 hover:underline">Register here</a></p>
    </main>
</body>

</html>