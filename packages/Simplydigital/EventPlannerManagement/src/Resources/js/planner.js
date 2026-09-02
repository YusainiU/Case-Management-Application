import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export default function plannerCalendar(events = []) {
    return {
        calendar: null,

        init() {
            this.calendar = new Calendar(this.$refs.calendar, {
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin,
                ],

                initialView: 'dayGridMonth',
                headerToolbar: false,

                events: this.events,

                eventClick(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },

                eventDidMount(info) {
                    info.el.title = info.event.title;
                },
            });

            this.calendar.render();
        },

        setView(view) {
            this.calendar.changeView(view);
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('plannerCalendar', plannerCalendar);
});