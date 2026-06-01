document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("calendar");
  var eventsEl = document.getElementById("schedule-events");

  if (!calendarEl || !eventsEl || typeof FullCalendar === "undefined") {
    return;
  }

  var events = [];
  try {
    events = JSON.parse(eventsEl.textContent || "[]");
  } catch (error) {
    console.error("Khong parse duoc du lieu lich hoc:", error);
  }

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    locale: "vi",
    height: "auto",
    events: events,
  });

  calendar.render();
});
