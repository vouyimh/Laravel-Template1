# Calendar — Project Details

Reference document for the **Calendar** feature in this Laravel app. Use this to onboard, debug, or add new features without re-reading every file.

---

## 1. Purpose

A per-user event calendar that lives at `/calendar`. Each logged-in user keeps their own events. Mirrors the Sneat template's calendar demo: Month / Week / Day / List views, color-coded categories, mini-calendar in the sidebar, category filters, and "Add Event" modal.

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| UI library | **FullCalendar v6** (CDN, no build step) |
| Backend | Laravel 10/11, Eloquent |
| Storage | MySQL via the existing default DB connection |
| Auth scope | `auth` middleware — every authenticated user has their own calendar |
| Frontend templating | Blade extending `layouts/contentNavbarLayout` |
| Sidebar | Bootstrap-style category filter using `<input type="checkbox">` |

> No node build is required. FullCalendar is loaded from `https://cdn.jsdelivr.net/npm/fullcalendar@6.x/`.

---

## 3. File Map

```
app/
├── Http/Controllers/CalendarController.php   ← all CRUD + JSON feed
└── Models/Event.php                          ← Eloquent model

database/migrations/
└── 2026_05_22_000000_create_events_table.php

resources/
├── views/calendar/
│   └── index.blade.php                       ← the whole calendar page
└── menu/verticalMenu.json                    ← sidebar entry (slug "app-calendar")

routes/
└── web.php                                   ← /calendar* routes under auth

lang/
├── en.json                                   ← (passthrough)
└── fr.json                                   ← French translations for calendar UI
```

---

## 4. Routes

All routes are wrapped in `Route::middleware(['auth', '2fa'])` so only verified, logged-in users reach them.

| Method | URL | Name | Purpose |
|---|---|---|---|
| GET    | `/calendar`               | `calendar.index`   | Render the calendar page |
| GET    | `/calendar/events`        | `calendar.events`  | JSON feed FullCalendar polls — returns events in `start..end` range |
| POST   | `/calendar/events`        | `calendar.store`   | Create new event (from "Add Event" modal) |
| PUT    | `/calendar/events/{event}`| `calendar.update`  | Update event (drag-resize, edit modal) |
| DELETE | `/calendar/events/{event}`| `calendar.destroy` | Delete event |

**Authorization rule**: a user can only see/edit/delete events where `events.user_id === auth()->id()`. Anything else returns 403.

---

## 5. Data Model

### Table: `events`

| Column | Type | Notes |
|---|---|---|
| `id`          | bigint, PK | Auto-increment |
| `user_id`     | bigint, FK → `users.id`, on delete cascade | Owner. Filter scope. |
| `title`       | string(255) | Required, shown on the event chip |
| `description` | text, nullable | Free-form, shown in event detail modal |
| `start`       | datetime | Required, inclusive lower bound |
| `end`         | datetime, nullable | Exclusive upper bound (FullCalendar convention) |
| `all_day`     | boolean, default false | When true, FullCalendar renders as a full-row chip |
| `category`    | enum string: `personal`, `business`, `family`, `holiday`, `etc` | Drives color |
| `url`         | string, nullable | Optional link — click chip → open in new tab |
| `created_at`  | timestamp | |
| `updated_at`  | timestamp | |

Indexes: `(user_id, start)` composite index — every query filters on user + date range, so this index keeps the JSON feed fast.

### Category → Color map

These are baked into both the model accessor and the UI sidebar. Keep them in sync.

| Category | Hex | Tailwind-ish name |
|---|---|---|
| `personal` | `#FF3E1D` | Red |
| `business` | `#03C3EC` | Cyan |
| `family`   | `#FFAB00` | Orange |
| `holiday`  | `#71DD37` | Green |
| `etc`      | `#696CFF` | Primary purple |

---

## 6. Eloquent Model — `App\Models\Event`

```php
class Event extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description',
        'start', 'end', 'all_day', 'category', 'url',
    ];

    protected $casts = [
        'start'   => 'datetime',
        'end'     => 'datetime',
        'all_day' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function getColorAttribute(): string
    {
        return [
            'personal' => '#FF3E1D',
            'business' => '#03C3EC',
            'family'   => '#FFAB00',
            'holiday'  => '#71DD37',
            'etc'      => '#696CFF',
        ][$this->category] ?? '#696CFF';
    }

    public function toFullCalendar(): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'start'          => $this->start->toIso8601String(),
            'end'            => optional($this->end)->toIso8601String(),
            'allDay'         => (bool) $this->all_day,
            'url'            => $this->url,
            'backgroundColor'=> $this->color,
            'borderColor'    => $this->color,
            'extendedProps'  => [
                'description' => $this->description,
                'category'    => $this->category,
            ],
        ];
    }
}
```

---

## 7. Controller — `App\Http\Controllers\CalendarController`

| Method | What it does |
|---|---|
| `index()`                   | Returns the calendar page view |
| `events(Request)`           | Reads `start`, `end` query params; returns user's events in that window as JSON array of `toFullCalendar()` shapes |
| `store(Request)`            | Validates + creates an event owned by `auth()->id()`. Returns the created event JSON. |
| `update(Request, $event)`   | Authorizes ownership; validates partial input; saves; returns event JSON. Used by both the edit modal and drag-resize. |
| `destroy($event)`           | Authorizes ownership; deletes. Returns 204. |

Validation contract for store/update:

```php
[
    'title'       => 'required|string|max:255',
    'description' => 'nullable|string',
    'start'       => 'required|date',
    'end'         => 'nullable|date|after_or_equal:start',
    'all_day'     => 'boolean',
    'category'    => ['required', Rule::in(['personal','business','family','holiday','etc'])],
    'url'         => 'nullable|url',
]
```

---

## 8. UI — `resources/views/calendar/index.blade.php`

Two columns inside the regular admin layout:

```
┌────────────────────────────────────────────────────────────────────┐
│  [+ Add Event]                                                     │
│                                                                    │
│   ◀  May 2026  ▶            < >  May 2026   [Month|Week|Day|List]  │
│  ┌────────────┐  ┌──────────────────────────────────────────────┐  │
│  │ mini cal   │  │                                              │  │
│  │ S M T W T  │  │                                              │  │
│  │           │  │            FullCalendar main grid             │  │
│  │           │  │                                              │  │
│  └────────────┘  │                                              │  │
│                  │                                              │  │
│  Event Filters   │                                              │  │
│  ☑ View All      │                                              │  │
│  ☑ Personal      │                                              │  │
│  ☑ Business      │                                              │  │
│  ☑ Family        │                                              │  │
│  ☑ Holiday       │                                              │  │
│  ☑ Etc           └──────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────┘
```

### Component IDs / data-* hooks

| Element | DOM id / data attribute | Notes |
|---|---|---|
| Main calendar container | `#calendar` | FullCalendar mounts here |
| Mini-calendar | `#mini-calendar` | A second FullCalendar instance with `initialView: 'dayGridMonth'`, no events, dateClick → `mainCalendar.gotoDate(date)` |
| Add Event button | `#btn-add-event` | Opens `#event-modal` |
| Event modal | `#event-modal` | Bootstrap modal — fields: title, description, start, end, all-day toggle, category, url |
| Event filter checkboxes | `[data-filter-cat="personal"]` etc. | Each toggles visibility of events with that category |
| View All checkbox | `#filter-view-all` | Toggles all category boxes |
| View switcher | `#view-month`, `#view-week`, `#view-day`, `#view-list` | Buttons that call `mainCalendar.changeView(...)` |
| Header date label | `#calendar-title` | Manually synced on `datesSet` callback |
| Prev/Next | `#cal-prev`, `#cal-next` | Calls `mainCalendar.prev()` / `.next()` |

### FullCalendar init shape

```js
const mainCalendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    headerToolbar: false,                  // we build our own header
    height: 'auto',
    editable: true,                        // drag/resize
    selectable: true,                      // drag to create
    dayMaxEvents: 3,
    events: '/calendar/events',            // FullCalendar appends ?start=&end=
    select:        handleDateSelect,       // opens modal pre-filled
    eventClick:    handleEventClick,       // opens modal for edit/delete
    eventDrop:     handleEventChange,      // PUT to /calendar/events/{id}
    eventResize:   handleEventChange,
    datesSet:      syncHeaderTitle,        // updates #calendar-title
});
mainCalendar.render();
```

### Filter mechanism

Each filter checkbox toggles a CSS class on `#calendar`:

```css
.cal-hide-personal .fc-event[data-cat="personal"] { display: none; }
.cal-hide-business .fc-event[data-cat="business"] { display: none; }
/* ...etc */
```

In the FullCalendar `eventDidMount` callback we set `info.el.dataset.cat = info.event.extendedProps.category`. So filtering is pure CSS — no re-fetch, no flicker.

---

## 9. Authorization Cheat-Sheet

| Action | Rule |
|---|---|
| List events | `where('user_id', auth()->id())` only |
| Create | Force `user_id = auth()->id()` server-side; never trust the request |
| Update / Delete | `abort_unless($event->user_id === auth()->id(), 403)` |
| JSON feed | Same scope; never leak other users' events |

If you ever add team/shared calendars, add a `team_id` column and a pivot — do NOT loosen the `user_id` check above.

---

## 10. Common Bugs & Fixes

| Symptom | Likely cause | Where to look |
|---|---|---|
| Calendar grid is blank, white area | FullCalendar JS failed to load (CDN blocked) | DevTools Network tab — confirm `fullcalendar/index.global.min.js` returns 200. Fallback: `npm i fullcalendar` and vite bundle. |
| Events don't appear | `/calendar/events` returns `[]` or 401 | Hit the URL directly in browser. If redirected to `/login`, the session expired. If `[]`, check `where('user_id', ...)`. |
| Events show, but wrong color | `category` in DB doesn't match any key in `getColorAttribute()` | Inspect `events.category` value; add it to the enum or fix data. |
| Drag-resize doesn't persist | `editable: true` missing, OR `eventDrop` not POSTing | Check the `handleEventChange` body — it must PUT to `route('calendar.update', event.id)`. |
| Modal opens with stale data | The same modal element is reused for create + edit; old values not cleared | In `openModalForEdit()`, set every form field BEFORE `show()`. In `openModalForCreate()`, call `form.reset()` first. |
| 419 CSRF on create/update | Missing `<meta name="csrf-token">` or missing `X-CSRF-TOKEN` header in fetch | Layout `contentNavbarLayout.blade.php` already injects the meta; check that AJAX requests forward it. |
| 403 on update of own event | `user_id` column in DB doesn't match `auth()->id()` (perhaps user_id was forgotten on create) | Inspect the row; backfill `user_id`. |
| Mini-calendar clicks don't move main calendar | `dateClick` handler missing on mini-calendar | Add `dateClick: info => mainCalendar.gotoDate(info.date)`. |
| Filter checkbox doesn't hide events | The `data-cat` attribute isn't on `.fc-event` | Confirm `eventDidMount` sets `info.el.dataset.cat`. |
| Timezone off by hours | Server timezone (`config('app.timezone')`) differs from browser | Use `start->toIso8601String()` server-side so dates carry an offset; FullCalendar reads the offset and renders correctly. |

---

## 11. Adding New Features

### 11.1 New event category

1. Add the key + color to `App\Models\Event::getColorAttribute()`.
2. Add the same key to the `Rule::in(...)` list in `CalendarController` validation.
3. Add a checkbox + CSS rule in [calendar/index.blade.php](resources/views/calendar/index.blade.php) sidebar.
4. Add a `<option>` to the category dropdown in `#event-modal`.
5. Add the translated label to `lang/fr.json`.

### 11.2 Recurring events

FullCalendar supports `rrule`. Steps:

1. Add CDN `<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.x/index.global.min.js">` BEFORE the main FullCalendar init.
2. Add `recur` JSON column to `events`.
3. Update `toFullCalendar()` to emit `rrule` when `recur` is set.
4. Update validation to accept an `rrule` shape: `freq, interval, byweekday, until`.
5. Add a "Repeat" section to the event modal.

### 11.3 Calendar export (.ics)

1. Add `Route::get('/calendar/export', [CalendarController::class, 'export'])->name('calendar.export');`.
2. `export()` streams `text/calendar` with VCALENDAR/VEVENT blocks per row.
3. Add a "Download .ics" menu item in the page header.

### 11.4 Share with another user

1. Add `event_shares` pivot: `event_id`, `shared_with_user_id`, `permission` (view/edit).
2. Loosen the `events()` query: union events where user is owner OR shared_with.
3. Add UI to assign shares in the modal.

### 11.5 Reminders / notifications

1. Add `reminder_minutes` column to `events`.
2. Schedule a job (`php artisan schedule:run`) that scans for `start - reminder_minutes ≤ now < start` and dispatches a notification.

---

## 12. Test Checklist

- [ ] `/calendar` redirects to `/login` when logged out
- [ ] Logged in as Admin → page renders, mini-calendar visible
- [ ] Click empty day on main grid → modal opens with that date pre-filled
- [ ] Save event → appears in the right color
- [ ] Drag event to another day → still owned by same user; new date persists
- [ ] Resize event → end date persists
- [ ] Click event → modal opens with values; edit + save reflects
- [ ] Click event → modal Delete button removes event
- [ ] Uncheck "Personal" filter → personal events disappear instantly
- [ ] Click mini-calendar day → main calendar navigates to that month
- [ ] Switch to Week / Day / List views — counts/dates make sense
- [ ] Hit `/calendar/events?start=2026-05-01&end=2026-06-01` while authed as another user → returns only that user's events

---

## 13. Sidebar Menu Pointer

Replace the vendor demo URL with the local route:

```diff
- "url": "https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo-1/app/calendar",
- "target": "_blank",
+ "url": "/calendar",
  "name": "Calendar",
  "icon": "menu-icon icon-base bx bx-calendar",
  "slug": "app-calendar",
  "roles": ["admin", "staff"]
```

`target: "_blank"` is removed so the link opens in the same tab.

---

## 14. Translations

Add the following to `lang/fr.json` (each key matches `__('Key')` usage):

```json
{
  "Calendar": "Calendrier",
  "Add Event": "Ajouter un événement",
  "Event Filters": "Filtres d'événements",
  "View All": "Tout afficher",
  "Personal": "Personnel",
  "Business": "Professionnel",
  "Family": "Famille",
  "Holiday": "Vacances",
  "Etc": "Autre",
  "Month": "Mois",
  "Week": "Semaine",
  "Day": "Jour",
  "List": "Liste",
  "Title": "Titre",
  "Start": "Début",
  "End": "Fin",
  "All day": "Toute la journée",
  "Category": "Catégorie",
  "URL": "URL",
  "Save": "Enregistrer",
  "Delete event?": "Supprimer l'événement ?"
}
```

---

## 15. Performance Notes

- The events feed uses a date-range filter from FullCalendar (`?start=&end=`) so the DB returns only what the viewport needs. No need to paginate.
- The `(user_id, start)` composite index keeps the feed query as `Index Range Scan`, not a full table scan.
- Category filtering is **CSS-only** — no second DB hit when toggling checkboxes.
- The mini-calendar uses a second FullCalendar instance with `events: []`; it's just a date picker. Don't load events into it.

---

## 16. Out of Scope (for now)

- Recurring events (see §11.2)
- Multi-user shared calendars (see §11.4)
- iCal/Google Calendar sync (see §11.3 for one-way export)
- Reminders / push notifications (see §11.5)
- Event attendees / RSVPs

When any of these become a real ticket, copy the relevant subsection from §11 into the implementation PR description and follow the steps.

---

*Single page. No build step (CDN). Per-user data. Open `/calendar` and go.*
