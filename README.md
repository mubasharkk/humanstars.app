# HumanStars – Calendar Module

> **Task reference:** [REQUIREMENTS.md](./REQUIREMENTS.md)

---

## Task Summary

Design and implement the backend of a **calendar module** for a Laravel application. Users can create calendars, schedule events (including recurring ones via RRULE), invite individual users or groups, and share calendars with configurable permissions. The solution also covers how email reminders are delivered before a meeting starts.

---

## Planning Strategy

### 1. Data Modelling

Seven domain tables built around the existing `users` table. Calendar and event PKs use **UUIDs** with **soft deletes**; groups use standard auto-increment IDs.

| Table | Purpose |
|---|---|
| `groups` | Named collections of users, owned by a user |
| `group_members` | Pivot: `group_id` + `user_id`, unique constraint |
| `calendars` | User-owned calendars with name, colour, and IANA timezone |
| `calendar_events` | Events with UTC datetimes, RRULE, type (`virtual`/`on-site`), address, meeting URL |
| `calendar_shares` | Polymorphic share records (`User` or `Group`) — `READ` / `READWRITE` |
| `event_invitees` | Polymorphic invite records (`User` or `Group`) — `pending` / `accepted` / `declined` |

Polymorphic morphs (`shareable`, `inviteable`) make both share and invite tables open to any future entity without schema changes.

### 2. API Design

Stateless JWT API (`tymon/jwt-auth`) with two auth layers:

- **Web routes** — session-based (Breeze / Inertia) for the frontend
- **API routes** — JWT bearer token for external / mobile clients

Authorization is enforced via Laravel Policies — only owners or READWRITE share-holders can mutate resources; invitees can update only their own RSVP status.

### 3. Backend Structure

The module follows an **Actions + Services** pattern to keep controllers thin and testable:

```
app/
├── Actions/
│   ├── Calendar/          CreateCalendarAction, UpdateCalendarAction, DeleteCalendarAction
│   ├── CalendarEvent/     CreateEventAction, UpdateEventAction, DeleteEventAction
│   ├── EventInvitee/      CreateInviteeAction, UpdateInviteeAction, DeleteInviteeAction
│   └── Group/             CreateGroupAction, UpdateGroupAction, DeleteGroupAction,
│                          AddGroupMemberAction, RemoveGroupMemberAction
├── Services/
│   └── GoogleMapsService  Geocoding stub (TODO: wire GOOGLE_MAPS_API_KEY)
├── Observers/
│   └── CalendarEventObserver   Dispatches GeocodeEventLocationJob on address change
├── Jobs/
│   ├── GeocodeEventLocationJob  Queued — fills latitude/longitude silently
│   └── SendEventReminderJob     Queued — sends EventReminderMail to all recipients
├── Console/Commands/
│   └── SendEventReminders  Scheduled every minute — finds due reminders and dispatches jobs
├── Mail/
│   └── EventReminderMail   Mailable with HTML template (event details, join link / address)
├── Policies/
│   ├── CalendarPolicy
│   ├── CalendarEventPolicy
│   ├── EventInviteePolicy
│   └── GroupPolicy
└── Http/
    ├── Controllers/Api/
    │   ├── Auth/           AuthController
    │   ├── Calendar/       CalendarController, CalendarEventController, EventInviteeController
    │   └── Group/          GroupController, GroupMemberController
    ├── Requests/Calendar/, Requests/CalendarEvent/, Requests/EventInvitee/, Requests/Group/
    └── Resources/          CalendarResource, CalendarEventResource,
                            EventInviteeResource, GroupResource, GroupMemberResource
```

### 4. Notification Design (Email Reminders)

**Synchronous (at event save time):**
- `reminder_minutes` is validated and stored on the event — no email is sent immediately.

**Asynchronous (background):**
- `SendEventReminders` artisan command runs **every minute** via the Laravel scheduler.
- It queries events where `starts_at − reminder_minutes = now (±30 s)`.
- For each match a queued `SendEventReminderJob` is dispatched — it collects all accepted User invitees, members of accepted Group invitees, and the calendar owner, then sends `EventReminderMail` to each.

```
Scheduler (every minute)
  └── SendEventReminders command
        └── dispatch SendEventReminderJob  (queued, per event)
              └── EventReminderMail → accepted invitees + group members + owner
```

No HTTP request is ever blocked by email delivery. The scheduler is the single source of truth — no reminders are missed even if the queue is temporarily slow.

### 5. Location / Geocoding

Events have an optional `address` text field (user-facing). `latitude` and `longitude` are **internal-only** — excluded from all API responses and `$fillable`, auto-populated by `GeocodeEventLocationJob` whenever an on-site event's address changes. The `GoogleMapsService` is currently a stub; see inline TODO to wire the Geocoding API key.

---

## ERD

```
USERS ──────────────────────< CALENDARS ──────────────────< CALENDAR_EVENTS
  │  \                              │                               │
  │   \──< GROUP_MEMBERS >──< GROUPS                               │
  │                                 │                    ┌─────────┴──────────┐
  │                                 │                    │                    │
  │                         CALENDAR_SHARES        EVENT_INVITEES       EVENT_INVITEES
  │                         (shareable morph)      (inviteable=User)  (inviteable=Group)
  │                              │                       │                    │
  └──────────────────────────────┘                    USERS               GROUPS
```

---

## User Action Flow

```
[User]
   │
   ├── Register / Login ──► receive JWT token
   │
   ├── Create Group
   │      ├── Add members (by user_id)
   │      └── Remove members
   │
   ├── Create Calendar
   │      └── stored with owner = user, default timezone
   │
   ├── Share Calendar
   │      └── assign READ or READWRITE to a User or Group
   │
   ├── Create Event
   │      ├── virtual  → requires meeting_url
   │      ├── on-site  → optional address (geocoded async in background)
   │      ├── optional RRULE (e.g. FREQ=WEEKLY;BYDAY=MO)
   │      └── optional reminder_minutes (triggers scheduler-based email)
   │
   ├── Invite Participants
   │      ├── Invite User  → EventInvitee (inviteable = User,  status = pending)
   │      └── Invite Group → EventInvitee (inviteable = Group, status = pending)
   │
   ├── Respond to Invitation  (invitee only)
   │      └── PATCH status → accepted | declined
   │
   ├── View Events (calendar view)
   │      └── filtered by date range (from / to) across owned + shared calendars
   │
   └── Receive Reminder Email  (background)
          └── Scheduler → SendEventReminderJob → EventReminderMail
                └── recipients: accepted invitees + group members + calendar owner
```

---

## API Reference

### Auth

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/auth/login` | Login, returns JWT token |
| `GET` | `/api/auth/me` | Authenticated user profile |
| `POST` | `/api/auth/logout` | Invalidate token |

### Groups

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/groups` | List owned + member groups (split) |
| `POST` | `/api/groups` | Create group |
| `GET` | `/api/groups/{id}` | Get group (owner or member) |
| `PUT` | `/api/groups/{id}` | Update group (owner only) |
| `DELETE` | `/api/groups/{id}` | Delete group (owner only) |
| `GET` | `/api/groups/{id}/members` | List members |
| `POST` | `/api/groups/{id}/members` | Add member `{ user_id }` |
| `DELETE` | `/api/groups/{id}/members/{user}` | Remove member |

### Calendars

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/calendars` | List owned + shared calendars (split) |
| `POST` | `/api/calendars` | Create calendar |
| `GET` | `/api/calendars/{id}` | Get calendar |
| `PUT` | `/api/calendars/{id}` | Update calendar |
| `DELETE` | `/api/calendars/{id}` | Soft-delete calendar |

### Calendar Events

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/calendars/{id}/events` | List events (`?from=&to=`) |
| `POST` | `/api/calendars/{id}/events` | Create event |
| `GET` | `/api/calendars/{id}/events/{id}` | Get event |
| `PUT` | `/api/calendars/{id}/events/{id}` | Update event |
| `DELETE` | `/api/calendars/{id}/events/{id}` | Soft-delete event |

### Event Invitees

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/calendars/{id}/events/{id}/invitees` | List invitees |
| `POST` | `/api/calendars/{id}/events/{id}/invitees` | Invite user or group |
| `PATCH` | `/api/calendars/{id}/events/{id}/invitees/{id}` | Update RSVP status |
| `DELETE` | `/api/calendars/{id}/events/{id}/invitees/{id}` | Remove invitee |

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
| Scheduler | Laravel Scheduler — `artisan schedule:run` every minute |
| Testing | PHPUnit feature tests — **96 passing** |

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

# Start queue worker (required for geocoding + email reminders)
./vendor/bin/sail artisan queue:work

# Start scheduler (required for email reminders — runs every minute)
./vendor/bin/sail artisan schedule:work
```

---

## API Collection

A Postman collection is available at [`postman/HumanStars.postman_collection.json`](./postman/HumanStars.postman_collection.json).

Import it into Postman, run **Login** first (token is saved automatically), then follow the workflow:
**Create Group** → **Add Member** → **Create Calendar** → **Create Event** → **Invite User / Group** — IDs are captured into collection variables automatically.
