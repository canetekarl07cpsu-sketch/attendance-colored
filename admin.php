<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $id = (int) $_POST["delete_id"];
    $stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_student"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name !== "" && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hash, $role);
            $stmt->execute();
        }
    }
    header("Location: admin.php");
    exit;
}

$students = $conn->query("SELECT id, name, email, created_at FROM users WHERE role = 'student' ORDER BY name");
$history = $conn->query("SELECT attendance.id, users.name, users.email, attendance.attendance_date, attendance.time_in, attendance.time_out FROM attendance JOIN users ON attendance.user_id = users.id ORDER BY attendance.attendance_date DESC, attendance.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 bg-gradient-to-r from-green-300 to-white">
    <header class="bg-gradient-to-r from-white to-green-300 border-b border-slate-600">
        <div class="max-w-7xl mx-auto px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-xl font-bold">Admin Dashboard</h1><p class="text-sm text-slate-500">Welcome, <?= htmlspecialchars($_SESSION["name"]) ?></p></div>
            <a href="logout.php" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Log out</a>
        </div>
    </header>
    <main class="max-w-7xl mx-auto p-5 space-y-6">
        <section class="rounded-2xl bg-gradient-to-r from-green-300 to-white p-6 shadow-sm border border-slate-600">
            <h2 class="text-xl font-bold">Add Student Account</h2>
            <p class="mt-1 text-sm text-slate-500">Create a login for a student. Password must have at least 6 characters.</p>
            <form method="POST" class="mt-4 grid gap-3 md:grid-cols-4">
                <input name="name" required maxlength="100" placeholder="Student full name" class="rounded-lg border border-slate-300 px-3 py-3 text-sm border border-slate-600">
                <input name="email" required type="email" placeholder="Student email" class="rounded-lg border border-slate-300 px-3 py-3 text-sm border border-slate-600">
                <input name="password" required minlength="6" type="password" placeholder="Temporary password" class="rounded-lg border border-slate-300 px-3 py-3 text-sm border border-slate-600">
                <button name="add_student" value="1" class="rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700">Add student</button>
            </form>
        </section>
        <section class="rounded-2xl  p-6 shadow-sm bg-gradient-to-r from-green-300 to-white border border-slate-600">
            <h2 class="text-xl font-bold bg-gradient-to-r from-green-300 to-white">Student Accounts</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[550px] text-left text-sm border border-slate-600 ">
                    <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Created</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr><td class="px-4 py-3"><?= htmlspecialchars($student["name"]) ?></td><td class="px-4 py-3"><?= htmlspecialchars($student["email"]) ?></td><td class="px-4 py-3"><?= htmlspecialchars($student["created_at"]) ?></td></tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="rounded-2xl bg-white p-6 shadow-sm bg-gradient-to-r from-green-300 to-white border border-slate-600">
            <h2 class="text-xl font-bold">All Attendance Records</h2>
            <p class="mt-1 text-sm text-slate-500">View all students' clock-in and clock-out records. You can remove an incorrect record.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-sm border border-slate-600">
                    <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Clock in</th><th class="px-4 py-3">Clock out</th><th class="px-4 py-3">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3"><?= htmlspecialchars($row["name"]) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row["email"]) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row["attendance_date"]) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row["time_in"] ?? "—") ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row["time_out"] ?? "—") ?></td>
                            <td class="px-4 py-3"><form method="POST" onsubmit="return confirm('Delete this attendance record?')"><input type="hidden" name="delete_id" value="<?= (int) $row["id"] ?>"><button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button></form></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
