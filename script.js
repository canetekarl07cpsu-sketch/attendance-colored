const today = new Date();
let shownMonth = new Date(today.getFullYear(), today.getMonth(), 1);

function showClock() {
    const now = new Date();
    document.getElementById("clock").textContent = now.toLocaleTimeString();
    document.getElementById("todayDate").textContent = now.toLocaleDateString(undefined, {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
    });
}

function drawCalendar() {
    const calendar = document.getElementById("calendar");
    const monthLabel = document.getElementById("monthLabel");
    const year = shownMonth.getFullYear();
    const month = shownMonth.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    monthLabel.textContent = shownMonth.toLocaleDateString(undefined, { month: "long", year: "numeric" });
    calendar.innerHTML = "";

    for (let i = 0; i < firstDay; i++) {
        const blank = document.createElement("div");
        calendar.appendChild(blank);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const cell = document.createElement("div");
        cell.textContent = day;
        cell.className = "rounded-lg py-2 text-sm";
        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
            cell.className += " bg-blue-600 font-bold text-white";
        } else {
            cell.className += " hover:bg-slate-100";
        }
        calendar.appendChild(cell);
    }
}

document.getElementById("prevMonth").addEventListener("click", () => {
    shownMonth.setMonth(shownMonth.getMonth() - 1);
    drawCalendar();
});

document.getElementById("nextMonth").addEventListener("click", () => {
    shownMonth.setMonth(shownMonth.getMonth() + 1);
    drawCalendar();
});

document.getElementById("searchHistory").addEventListener("input", function () {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll(".history-row");
    let visible = 0;

    rows.forEach((row) => {
        const matches = row.cells[0].textContent.toLowerCase().includes(search);
        row.classList.toggle("hidden", !matches);
        if (matches) visible++;
    });

    document.getElementById("noResults").classList.toggle("hidden", visible > 0);
});

showClock();
setInterval(showClock, 1000);
drawCalendar();
