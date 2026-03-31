# Backend Design Task – Calendar Module

## Overview

The goal of this task is to understand how you approach backend design, data modelling, and real-world problems.

---

## Rules

- You may use the internet, documentation, IDE, and AI tools.
- Please answer in the way you think is most practical.
- We are mainly interested in your reasoning, structure, and backend design decisions.
- The task is expected to take approximately **60–90 minutes**.
- Please keep your solution concise and focused.
- Please send your solution back by email by the end of the same day after receiving the task.

---

## Situation

- Laravel is used as the backend framework.
- The database already contains `users` and `groups` tables.
- A group is a collection of users that can be invited to events.
- Frontend is not relevant for this task.

---

## Your Task

Develop the backend design of a **calendar module**.

---

## Requirements

- Users can create multiple calendars.
- Users can create calendar events, such as meetings.
- Users can invite individual users or groups to calendar events.
- Calendar events can repeat using RRULE (e.g., `FREQ=DAILY;COUNT=5`).
- Users can share their calendars with permission levels: `READ` or `READWRITE`.

---

## 1. Database Design

Design the database tables including:

- Necessary columns
- Relationships
- Indexes
- Constraints

---

## 2. Notification Design

How would you implement an **email notification X minutes before a meeting starts**?

Please explain:

- General approach
- What should happen **synchronously vs asynchronously**
- What Laravel components you would use (e.g., Jobs, Scheduling, Queues, Events)

---

## 3. Backend Structure (Short Answer)

Briefly describe how you would structure this in Laravel so that the module remains **maintainable as it grows**.

---

## Notes

- If anything is unclear, feel free to make reasonable assumptions and state them.
- Focus on clarity, structure, and practical backend decisions.
