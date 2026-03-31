export default function Sidebar({ owned, shared, selected, onToggle }) {
    return (
        <aside className="w-60 shrink-0 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col overflow-y-auto">
            <div className="p-4 space-y-6">
                <CalendarGroup
                    title="My Calendars"
                    calendars={owned}
                    selected={selected}
                    onToggle={onToggle}
                />
                {shared.length > 0 && (
                    <CalendarGroup
                        title="Shared with Me"
                        calendars={shared}
                        selected={selected}
                        onToggle={onToggle}
                    />
                )}
            </div>
        </aside>
    );
}

function CalendarGroup({ title, calendars, selected, onToggle }) {
    return (
        <div>
            <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                {title}
            </p>
            <ul className="space-y-1">
                {calendars.map((calendar) => (
                    <li key={calendar.id}>
                        <label className="flex items-center gap-2.5 cursor-pointer group rounded-md px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span
                                className="w-3.5 h-3.5 rounded-sm shrink-0 border-2 flex items-center justify-center transition-colors"
                                style={{
                                    backgroundColor: selected[calendar.id] ? calendar.color : 'transparent',
                                    borderColor: calendar.color,
                                }}
                            >
                                {selected[calendar.id] && (
                                    <svg className="w-2.5 h-2.5 text-white" viewBox="0 0 10 8" fill="none">
                                        <path d="M1 4l3 3 5-6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                )}
                            </span>
                            <input
                                type="checkbox"
                                className="sr-only"
                                checked={!!selected[calendar.id]}
                                onChange={() => onToggle(calendar.id)}
                            />
                            <span className="text-sm text-gray-700 dark:text-gray-300 truncate">
                                {calendar.name}
                            </span>
                        </label>
                    </li>
                ))}
            </ul>
        </div>
    );
}
