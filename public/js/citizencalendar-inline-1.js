        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const eventDialog = document.querySelector('.event-details-dialog');
            const eventTitle = document.getElementById('event-details-title');
            const eventDate = document.getElementById('event-details-date');
            const eventTime = document.getElementById('event-details-time');
            const eventDescription = document.getElementById('event-details-description');
            const closeEventDialog = eventDialog.querySelector('.event-details-close');
            const compactToolbar = window.matchMedia(
                '(max-width: 680px), (max-width: 1024px) and (max-height: 480px)'
            );
            const getToolbar = (compact) => compact
                ? { left: 'prev,next', center: 'title', right: 'today' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                expandRows: true,
                events: '/publicevents',
                selectable: false,
                editable: false,
                headerToolbar: getToolbar(compactToolbar.matches),
                eventClick({ event }) {
                    eventTitle.textContent = event.title;
                    eventDate.textContent = calendar.formatDate(event.start, {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    eventTime.textContent = event.extendedProps.time
                        || (event.allDay
                            ? 'All day'
                            : calendar.formatDate(event.start, { hour: 'numeric', minute: '2-digit' }));
                    eventDescription.textContent = event.extendedProps.description || 'No description available.';
                    eventDialog.showModal();
                }
            });

            closeEventDialog.addEventListener('click', () => eventDialog.close());
            eventDialog.addEventListener('click', (clickEvent) => {
                if (clickEvent.target === eventDialog) {
                    eventDialog.close();
                }
            });
            document.addEventListener('keydown', (keyEvent) => {
                if (keyEvent.key === 'Escape' && eventDialog.open) {
                    eventDialog.close();
                }
            });

            calendar.render();

            compactToolbar.addEventListener('change', (event) => {
                calendar.setOption('headerToolbar', getToolbar(event.matches));
            });
        });
