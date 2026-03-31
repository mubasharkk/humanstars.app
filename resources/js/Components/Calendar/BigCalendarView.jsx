import { Calendar, momentLocalizer } from 'react-big-calendar';
import moment from 'moment';
import 'react-big-calendar/lib/css/react-big-calendar.css';

const localizer = momentLocalizer(moment);

export default function BigCalendarView({ events, onRangeChange, loading }) {
    return (
        <div className="relative h-full">
            {loading && (
                <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/60 dark:bg-gray-900/60 rounded-lg">
                    <div className="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
                </div>
            )}
            <Calendar
                localizer={localizer}
                events={events}
                startAccessor="start"
                endAccessor="end"
                titleAccessor="title"
                style={{ height: '100%' }}
                onRangeChange={onRangeChange}
                eventPropGetter={(event) => ({
                    style: {
                        backgroundColor: event.color,
                        borderColor: event.color,
                        color: '#fff',
                        borderRadius: '4px',
                        border: 'none',
                        padding: '1px 4px',
                        fontSize: '0.8rem',
                    },
                })}
                tooltipAccessor={(event) =>
                    [
                        event.title,
                        event.type === 'virtual' ? `🔗 ${event.meeting_url}` : event.address,
                    ]
                        .filter(Boolean)
                        .join('\n')
                }
                popup
                views={['month', 'week', 'day', 'agenda']}
                defaultView="month"
            />
        </div>
    );
}
