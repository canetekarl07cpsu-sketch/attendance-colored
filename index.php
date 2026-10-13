<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
if (($_SESSION["role"] ?? "") === "admin") {
    header("Location: admin.php");
    exit;
}
require "db.php";

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $action = $_POST["action"] ?? "";

    $stmt = $conn->prepare("SELECT id, time_in, time_out FROM attendance WHERE user_id = ? AND attendance_date = ?");
    $stmt->bind_param("is", $user_id, $date);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();

    if ($action === "in" && !$record) {
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, attendance_date, time_in) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $date, $time);
        $stmt->execute();
    } elseif ($action === "out" && $record && !$record["time_out"]) {
        $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE id = ?");
        $stmt->bind_param("si", $time, $record["id"]);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT attendance_date, time_in, time_out FROM attendance WHERE user_id = ? ORDER BY attendance_date DESC, id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

$today = date("Y-m-d");
$stmt = $conn->prepare("SELECT time_in, time_out FROM attendance WHERE user_id = ? AND attendance_date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$today_record = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-r from-green-500 to-white">
    <header class="bg-gradient-to-r from-white to-green-300 border-b">
        <div class="max-w-6xl mx-auto px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">CCIT 01 Student Attendance</h1>
                <p class="text-sm text-slate-500">Welcome, <?= htmlspecialchars($name) ?></p>
            </div><a href="logout.php" class="rounded-lg bg-slate-800 px-4 py-2 text-white text-sm hover:bg-slate-700">Log out</a>
        </div>
    </header>
    <main class="max-w-6xl mx-auto p-5 space-y-6 ">
        <section class="grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl p-6 shadow-sm border border-slate-600 bg-gradient-to-r from-white to-green-300">
                <p class="text-sm font-medium text-slate-500">Today</p>
                <h2 id="todayDate" class="mt-1 text-2xl font-bold"></h2>
                <p class="mt-4 text-sm text-slate-500">Current time</p>
                <p id="clock" class="text-3xl font-semibold"></p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <form method="POST"><button name="action" value="in" class="rounded-lg bg-emerald-600 px-5 py-3 text-white hover:bg-emerald-700 disabled:opacity-40 border border-slate-600" <?= $today_record && $today_record["time_in"] ? "disabled" : "" ?>>Clock in</button></form>
                    <form method="POST"><button name="action" value="out" class="rounded-lg bg-red-600 px-5 py-3 text-white hover:bg-red-700 disabled:opacity-40 border border-slate-600" <?= !$today_record || !$today_record["time_in"] || $today_record["time_out"] ? "disabled" : "" ?>>Clock out</button></form>
                </div>
                <div class="mt-5 text-sm text-slate-600">
                    <p>Clock in: <span class="font-semibold"><?= htmlspecialchars($today_record["time_in"] ?? "Not recorded") ?></span></p>
                    <p class="mt-1">Clock out: <span class="font-semibold"><?= htmlspecialchars($today_record["time_out"] ?? "Not recorded") ?></span></p>
                </div>
            </div>
            <div class="rounded-2xl p-6 shadow-sm border border-slate-600 bg-gradient-to-r from-white to-green-300">
                <div class="flex items-center justify-between gap-3"><button id="prevMonth" class="rounded-lg border px-3 py-2 hover:bg-slate-50" aria-label="Previous month">&larr;</button>
                    <h2 id="monthLabel" class="text-lg font-bold"></h2><button id="nextMonth" class="rounded-lg border px-3 py-2 hover:bg-slate-50" aria-label="Next month">&rarr;</button>
                </div>
                <div class="mt-5 grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-500">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div id="calendar" class="mt-2 grid grid-cols-7 gap-2 text-center"></div>
                <p class="mt-4 text-xs text-slate-500">Today's date is highlighted in the calendar.</p>
            </div>
        </section>
        <section class="rounded-2xl p-6 shadow-sm border border-slate-600 bg-gradient-to-r from-white to-green-300">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-600 bg-gradient-to-r from-white to-green-300">
                <div>
                    <h2 class="text-xl font-bold">Attendance History</h2>
                    <p class="mt-1 text-sm text-slate-500">Your saved clock-in and clock-out records.</p>
                </div><input id="searchHistory" type="search" placeholder="Search date..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-5 overflow-x-auto border border-slate-600 rounded-lg">
                <table class="w-full min-w-[500px] text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Clock in</th>
                            <th class="px-4 py-3">Clock out</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="historyRows" class="divide-y divide-slate-100">
                        <?php while ($row = $history->fetch_assoc()): ?><tr class="history-row">
                                <td class="px-4 py-3"><?= htmlspecialchars($row["attendance_date"]) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row["time_in"] ?? "—") ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row["time_out"] ?? "—") ?></td>
                                <td class="px-4 py-3"><span class="rounded-full px-3 py-1 text-xs <?= $row["time_out"] ? "bg-emerald-100 text-emerald-700" : "bg-amber-100 text-amber-700" ?>"><?= $row["time_out"] ? "Completed" : "In progress" ?></span></td>
                            </tr><?php endwhile; ?>
                    </tbody>
                </table>
                <p id="noResults" class="hidden py-6 text-center text-sm text-slate-500">No matching records.</p>
            </div>
        </section>
    </main>
    <script src="script.js"></script>
</body>

</html>