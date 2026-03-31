import { useState, useEffect, useRef, useCallback } from 'react';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Sidebar from '@/Components/Calendar/Sidebar';
import BigCalendarView from '@/Components/Calendar/BigCalendarView';

export default function CalendarIndex({ owned, shared }) {
    const allCalendars = [...owned, ...shared];

    // All calendars selected by default
    const [selected, setSelected] = useState(() =>
        Object.fromEntries(allCalendars.map((c) => [c.id, true]))
    );

    // Current visible date range driven by react-big-calendar navigation
    const [dateRange, setDateRange] = useState(() => {
        const now = new Date();
        return {
            from: new Date(now.getFullYear(), now.getMonth(), 1),
            to: new Date(now.getFullYear(), now.getMonth() + 1, 0),
        };
    });

    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false);

    // Stable ref for allCalendars so the fetch callback doesn't recreate every render
    const calendarsRef = useRef(allCalendars);
    calendarsRef.current = allCalendars;

    const fetchEvents = useCallback(async (from, to, currentSelected) => {
        const activeIds = Object.entries(currentSelected)
            .filter(([, active]) => active)
            .map(([id]) => id);

        if (activeIds.length === 0) {
            setEvents([]);
            return;
        }

        setLoading(true);
        try {
            const results = await Promise.all(
                activeIds.map((id) =>
                    axios
                        .get(`/calendar/${id}/events`, {
                            params: {
                                from: formatDate(from),
                                to: formatDate(to),
                            },
                        })
                        .then((res) => {
                            const cal = calendarsRef.current.find((c) => c.id === id);
                            return res.data.map((ev) => ({
                                id: ev.id,
                                title: ev.title,
                                start: new Date(ev.starts_at),
                                end: new Date(ev.ends_at),
                                type: ev.type,
                                address: ev.address,
                                meeting_url: ev.meeting_url,
                                color: cal?.color ?? '#3B82F6',
                                calendarId: id,
                            }));
                        })
                )
            );
            setEvents(results.flat());
        } finally {
            setLoading(false);
        }
    }, []);

    // Re-fetch whenever selected calendars or date range changes
    useEffect(() => {
        fetchEvents(dateRange.from, dateRange.to, selected);
    }, [selected, dateRange, fetchEvents]);

    const handleToggle = (id) => {
        setSelected((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    const handleRangeChange = (range) => {
        if (Array.isArray(range)) {
            // Week / day view returns an array of dates
            setDateRange({ from: range[0], to: range[range.length - 1] });
        } else if (range.start && range.end) {
            // Month / agenda view returns { start, end }
            setDateRange({ from: range.start, to: range.end });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Calendar
                </h2>
            }
        >
            <Head title="Calendar" />

            <div className="flex" style={{ height: 'calc(100vh - 64px)' }}>
                <Sidebar
                    owned={owned}
                    shared={shared}
                    selected={selected}
                    onToggle={handleToggle}
                />

                <div className="flex-1 p-4 overflow-hidden">
                    <BigCalendarView
                        events={events}
                        onRangeChange={handleRangeChange}
                        loading={loading}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}
