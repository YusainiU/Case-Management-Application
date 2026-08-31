import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export default function plannerCalendar() {
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

                events: async (info, successCallback, failureCallback) => {
                    try {
                        const response = await fetch(
                            `/planner/events?start=${encodeURIComponent(
                                info.startStr
                            )}&end=${encodeURIComponent(
                                info.endStr
                            )}`
                        );

                        const events = await response.json();

                        successCallback(events);
                    } catch (error) {
                        failureCallback(error);
                    }
                },

                eventClick(info) {
                    if (info.event.url) {
                        window.location.href =
                            info.event.url;

                        info.jsEvent.preventDefault();
                    }
                },

                eventDidMount(info) {
                    info.el.title =
                        info.event.title;
                },
            });

            this.calendar.render();
        },

        setView(view) {
            this.calendar.changeView(view);
        },
    };
}