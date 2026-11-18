document.addEventListener("DOMContentLoaded", function () {
    const calendarElement = document.getElementById("calendar");

    if (!calendarElement) {
        return;
    }

    const escapeHtml = function (value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            }[character];
        });
    };

    const events = JSON.parse(calendarElement.dataset.events || "[]");

    new FullCalendar.Calendar(calendarElement, {
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        initialView: "dayGridMonth",
        editable: false,
        selectable: false,
        events: events,
        eventClick: function (info) {
            const { event } = info;
            const description =
                event.extendedProps.description || "No description provided.";
            const type = event.extendedProps.type || "Event";
            const startDate = event.extendedProps.startDate || "-";
            const endDate = event.extendedProps.endDate || startDate;

            Swal.fire({
                title: event.title,
                icon: "info",
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Type:</strong> ${escapeHtml(type)}</p>
                        <p class="mb-2"><strong>Start:</strong> ${escapeHtml(startDate)}</p>
                        <p class="mb-2"><strong>End:</strong> ${escapeHtml(endDate)}</p>
                        <p class="mb-0"><strong>Description:</strong><br>${escapeHtml(description)}</p>
                    </div>
                `,
                confirmButtonText: "Close",
            });
        },
    }).render();
});
