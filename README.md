# HumanStars – Calendar Module

> **Task reference:** [REQUIREMENTS.md](./REQUIREMENTS.md)

---

## Task Summary

Design and implement the backend of a **calendar module** for a Laravel application. Users can create calendars, schedule events (including recurring ones via RRULE), invite individual users or groups, and share calendars with configurable permissions. The solution should also cover how email reminders are delivered before a meeting starts.

---

## Planning Strategy

### 1. Data Modelling

Four domain models built around the existing `users` table, using **UUID primary keys** and **soft deletes** throughout:

| Table | Purpose |
|---|---|
| `calendars` | User-owned calendars with name, colour, and IANA timezone |
| `calendar_events` | Events belonging to a calendar; stores UTC datetimes, RRULE string, type (`virtual` / `on-site`), address, and meeting URL |
| `calendar_shares` | Polymorphic share records (`User` or `Group`) with `READ` / `READWRITE` permission |
| `event_invitees` | Polymorphic invite records (`User` or `Group`) with `pending` / `accepted` / `declined` status |

Polymorphic morphs (`shareable`, `inviteable`) keep both tables extensible — adding a new invitable entity requires no schema change.

### 2. API Design

Stateless JWT API (`tymon/jwt-auth`) with two layers of auth:

- **Web routes** — session-based (Breeze / Inertia) for the frontend
- **API routes** — JWT bearer token for external / mobile clients

Authorization is enforced via Laravel Policies (`CalendarPolicy`, `CalendarEventPolicy`) — only owners or READWRITE share-holders can mutate resources.

### 3. Backend Structure

The module follows a **Actions + Services** pattern to keep controllers thin:

```
app/
├── Actions/
│   ├── Calendar/        CreateCalendarAction, UpdateCalendarAction, DeleteCalendarAction
│   └── CalendarEvent/   CreateEventAction, UpdateEventAction, DeleteEventAction
├── Services/
│   └── GoogleMapsService   Geocoding stub (async, non-blocking)
├── Observers/
│   └── CalendarEventObserver   Dispatches GeocodeEventLocationJob on address change
├── Jobs/
│   └── GeocodeEventLocationJob   Queued — fills latitude/longitude silently
├── Policies/
│   ├── CalendarPolicy
│   └── CalendarEventPolicy
├── Http/
│   ├── Controllers/Api/Calendar/
│   ├── Requests/Calendar/
│   ├── Requests/CalendarEvent/
│   └── Resources/          CalendarResource, CalendarEventResource
```

### 4. Notification Design (Email Reminders)

**Synchronous (at event save time):**
- Validate `reminder_minutes` on the request.
- No email is sent immediately — only scheduling metadata is stored.

**Asynchronous (background):**
- A **scheduled command** (`artisan schedule:run`, every minute) queries for events where `starts_at - reminder_minutes` falls within the current minute window.
- For each match, a queued **`SendEventReminderJob`** is dispatched — it sends a Laravel `Mailable` to every accepted invitee.
- The queue worker handles delivery without blocking any request cycle.

```
Scheduler (every minute)
  └── FindUpcomingEventsCommand
        └── dispatch SendEventReminderJob  (queued)
              └── EventReminderMail → SMTP / SES
```

This approach guarantees no missed reminders (scheduler is the source of truth) and no HTTP latency impact (fully async via queues).

### 5. Location / Geocoding

Events have an optional `address` field (user-facing). `latitude` and `longitude` are **internal fields** — never exposed in API responses, auto-populated via `GeocodeEventLocationJob` whenever an on-site event's address changes.

---

## ERD

> _Diagram to be added_

```
[ ERD placeholder — paste or embed entity-relationship diagram here ]
```

---

## User Action Flow

> _Diagram to be added_

```
[ User action diagram placeholder — paste or embed flow diagram here ]
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Auth (API) | `tymon/jwt-auth` — JWT bearer tokens |
| Auth (Web) | Laravel Breeze — session-based |
| Frontend | Inertia.js + React 18 + Tailwind CSS v3 |
| Calendar UI | `react-big-calendar` + `moment` |
| Database | MySQL (via Laravel Sail / Docker) |
| Queue | Laravel Queue (database driver, swap to Redis in production) |
| Testing | PHPUnit feature tests — 62 passing |

---

## Local Setup

```bash
# Start containers
./vendor/bin/sail up -d

# Install dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Run migrations
./vendor/bin/sail artisan migrate

# Generate JWT secret
./vendor/bin/sail artisan jwt:secret

# Build frontend
./vendor/bin/sail npm run build

# Run tests
./vendor/bin/sail artisan test
```

---

## API Collection

A Postman collection is available at [`postman/HumanStars.postman_collection.json`](./postman/HumanStars.postman_collection.json).

Import it into Postman, run **Login** first (token is saved automatically), then use **Create Calendar** and **Create Event** — IDs are captured into collection variables automatically.
