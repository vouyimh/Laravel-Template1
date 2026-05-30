# StaffTask — Project Details

A complete reference for the **StaffTask** role-based task-management web app. Single-page HTML application with optional Node sync server and a Laravel port.

---

## 1. Purpose

StaffTask helps a small team coordinate field/office work using a Kanban board with two roles:

- **Admin** — creates, assigns, edits, and tracks tasks across columns.
- **Staff** — executes assigned tasks, captures GPS location at start, uploads photo/video proof, and marks the task complete.

The whole UI runs in a single browser tab; data persists in `localStorage` and (optionally) syncs across devices via a lightweight Node server.

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| UI | Vanilla HTML5 + CSS3 (dark theme, glassmorphism) |
| Logic | Vanilla JavaScript (no framework, no build step) |
| Storage (client) | `localStorage` (tasks, users, session, sync code) |
| Geocoding | OpenStreetMap **Nominatim** reverse-geocoding API |
| Sync server (optional) | Node.js + Express + CORS ([package.json](package.json)) |
| Backend port | Laravel 10+ (PHP) — see [LARAVEL_CONVERSION_GUIDE.md](LARAVEL_CONVERSION_GUIDE.md) |

---

## 3. File Layout

```
c:\Test_Web_App\
├── index.html                       ← Main app (UI + JS, ~3,534 lines)
├── public-css-app.css               ← Extracted CSS (Laravel asset, 1,551 lines)
├── public-js-stafftask.js           ← Extracted JS (Laravel asset, 640 lines)
├── package.json                     ← Node sync-server deps (express, cors)
│
├── Laravel-Controller-TaskController.php
├── Laravel-Migration-CreateTasksTable.php
├── Laravel-Migration-CreateTaskActivitiesTable.php
├── Laravel-Migration-CreateTaskFilesTable.php
├── Laravel-Models-Task.php
├── Laravel-Models-TaskActivity.php
├── Laravel-Models-TaskFile.php
├── Laravel-Routes-Addition.php
├── Laravel-View-stafftask-index.blade.php
│
└── docs/
    ├── README.md                    ← Top-level intro
    ├── PROJECT_DETAILS.md           ← This file
    ├── CHANGES.md
    ├── IMPLEMENTATION_SUMMARY.md
    ├── DRAG_DROP_UPDATE.md
    ├── FILES_SUMMARY.md
    ├── INTEGRATION_STEPS.md
    ├── COMPLETE_INTEGRATION_GUIDE.md
    ├── LARAVEL_CONVERSION_GUIDE.md
    ├── README_LARAVEL_CONVERSION.md
    └── QUICK_REFERENCE.txt
```

> The single-file build is [index.html](index.html). The `public-*` and `Laravel-*` files are siblings for a Laravel deployment and mirror the same behavior.

---

## 4. Roles & Permissions

| Action | Admin | Staff |
|---|:---:|:---:|
| View all tasks | ✓ | ✓ (assigned only, in production) |
| Create task | ✓ | — |
| Edit task fields | ✓ | — |
| Delete task | ✓ | — |
| Drag between columns | ✓ | — |
| Start task (capture GPS) | — | ✓ |
| Upload image/video proof | — | ✓ |
| Complete task | — | ✓ (proof required) |
| View timeline / activity | ✓ | ✓ |

Role is stored in `localStorage` under `stafftask_role`. Login (when enabled) writes a session to `stafftask_session`.

---

## 5. Kanban Columns / Task Statuses

```
┌──────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐
│  To Do   │→│ In Progress  │→│   Review     │→│ Complete │
└──────────┘  └──────────────┘  └──────────────┘  └──────────┘
   todo         inprogress         review           complete
```

Admins drag cards between columns. Staff transitions happen via action buttons (Start → Upload → Complete).

---

## 6. Staff To-Do Workflow (Detailed)

This is the core flow the project is built around.

### 6.1 Step-by-step

1. **Login as Staff** (or toggle role).
2. **Open Kanban board** — staff sees tasks assigned to them in the *To Do* column.
3. **Click 🚀 Start Task**
   - Browser prompts for **Geolocation permission**.
   - On allow → `navigator.geolocation.getCurrentPosition()` returns `(lat, lng)`.
   - Coordinates are sent to **Nominatim** (`https://nominatim.openstreetmap.org/reverse`) to resolve a human-readable address.
   - Task gains `startedAt`, `startLocation { lat, lng, address }`, status flips to `inprogress`.
4. **Upload proof** — click 📷 Upload Image or 🎥 Upload Video
   - Validates MIME + extension (jpg/png/gif/webp/mp4/webm/mov/mkv/avi/…).
   - Max **200 MB** per file (single-file workflow); legacy modal limit was 10 MB.
   - File is read via `FileReader` to **base64** and pushed onto `task.uploadedFiles[]`.
   - Live upload progress bar shown.
5. **Click ✅ Complete Task**
   - Validates at least one proof file is attached.
   - Sets `completedAt`, status → `complete`, logs activity entry.
   - Triggers confetti animation.
6. **Auto-save** — every state change writes back to `localStorage` and (if running) POSTs to the sync server.

### 6.2 State changes per step

| Step | Task fields written |
|---|---|
| Create (admin) | `id, title, description, assignee, priority, dueDate, status='todo', createdAt, activity[]` |
| Start (staff) | `status='inprogress', startedAt, startLocation` |
| Upload (staff) | `uploadedFiles[].push({ id, type, name, size, data, uploadedAt })` |
| Complete (staff) | `status='complete', completedAt, activity[].push('Task completed')` |

---

## 7. Task Data Shape

```js
{
  id: "abc123",
  title: "Fix the pump",
  description: "Water pump needs repair",
  assignee: "John Doe",          // display name
  assignedTo: "userId_42",       // user id (when auth enabled)
  priority: "high",              // high | medium | low
  dueDate: "2026-04-30",
  status: "todo",                // todo | inprogress | review | complete
  location: { lat, lng, address } | null,
  startLocation: { lat, lng, address } | null,
  startedAt: 1750000000000 | null,
  completedAt: 1750000000000 | null,
  createdAt: 1740000000000,
  updatedAt: 1740000000000,
  uploadedFiles: [
    {
      id: "file_1",
      type: "image",             // image | video
      name: "before.jpg",
      size: 1024000,             // bytes
      data: "data:image/jpeg;base64,...",
      uploadedAt: 1750000000000
    }
  ],
  activity: [
    { action: "Created", timestamp: 1740000000000 },
    { action: "Task started", timestamp: 1750000000000 },
    { action: "Uploaded proof.jpg", timestamp: 1750000000001 },
    { action: "Task completed", timestamp: 1750000000002 }
  ]
}
```

---

## 8. Storage Keys (localStorage)

| Key | Purpose |
|---|---|
| `stafftask_tasks` | JSON array of every task |
| `stafftask_users` | Seeded user list (admin + staff demo accounts) |
| `stafftask_role` | Current role: `admin` or `staff` |
| `stafftask_session` | Logged-in user snapshot |
| `stafftask_sync_code` | Pairing code for the optional sync server |

> Total `localStorage` budget is typically **5–10 MB**. Base64-encoded videos eat into this fast — move to cloud storage for production.

---

## 9. Key JavaScript Functions

All in [index.html](index.html) (CSS+HTML+JS in one file). Approximate line ranges:

### Authentication (~1894–1960)
- `initializeUsers()` — seeds demo users on first load.
- `handleLogin(event)` — validates and writes session.
- `logout()`, `checkSession()`.

### Role / UI (~2020–2070)
- `loadRole()`, `setRole(role)`, `updateUIForRole()` — toggles admin vs staff UI.
- `showStatusMessage(message, type)` — toast notifications.

### Staff actions from card (~2076–2295)
- `staffStartTaskFromCard(taskId)` — start + capture GPS inline.
- `handleStaffFileUploadCard(taskId)` — upload + progress.
- `removeStaffFileFromCard(taskId, fileId)`.
- `staffCompleteTaskFromCard(taskId)` — finalize.

### Staff modal view (~2298–2640)
- `openStaffTaskModal(taskId)` / `displayStaffTaskModal(taskId)`.
- `updateStaffModalSections(task)` — shows/hides Start, Upload, Complete blocks.
- `showStaffTaskTimeline(task)` — renders activity log.
- `staffStartTask()`, `handleStaffFileUpload(type)`, `staffCompleteTask()`.

### Utilities (~2645+)
- `generateId()`, `formatDate(dateString)`, `getDueDateStatus(dueDate)`.
- `getColorFromString(str)`, `getInitials(name)` — avatar styling.

### Admin / board
- `openNewTaskModal()`, `openTaskModal(taskId)`, `saveTask(event)`, `deleteCurrentTask()`.
- `renderBoard()`, `renderTaskCard(task)`, `getFilteredTasks()`.
- `setupDragAndDrop()` — admin-only column-drop handlers.

---

## 10. Geolocation Pipeline

```
Click "Start Task"
   │
   ▼
navigator.geolocation.getCurrentPosition()
   │  on success: { coords.latitude, coords.longitude }
   ▼
fetch("https://nominatim.openstreetmap.org/reverse?lat=…&lon=…&format=json")
   │
   ▼
address = data.address.city || .town || .village || .county || .state
   │
   ▼
task.startLocation = { lat, lng, address }   →  saved to localStorage
```

Failure modes: permission denied, timeout, offline → captured location is `null` and the task still starts with an activity note.

---

## 11. File Upload Pipeline

```
<input type="file"> change
   │
   ▼
Validate MIME + extension + size (≤ 200 MB)
   │
   ▼
FileReader.readAsDataURL(file)    ──progress──▶ progress bar UI
   │
   ▼
{ id, type, name, size, data: <base64>, uploadedAt } pushed to task.uploadedFiles
   │
   ▼
saveTasks()  →  localStorage  →  (optional) POST to sync server
```

Supported types: `jpg, jpeg, png, gif, webp` (images) and `mp4, webm, ogg, mov, avi, mkv, flv, wmv, m4v` (videos).

---

## 12. Optional Sync Server

Lightweight Express server defined in [package.json](package.json) (`npm start`).

- Port: **3000**
- Endpoints (typical): `GET /tasks`, `POST /tasks`, `GET /users`, etc.
- Pairing: each client gets a `stafftask_sync_code`; matching codes share the same task set.
- Poll interval (client → server): `SYNC_INTERVAL = 1000ms`.

Run locally:

```bash
npm install
npm start
# then open index.html — it auto-detects http://localhost:3000
```

---

## 13. Laravel Port

The repo includes a complete Laravel translation:

| File | Purpose |
|---|---|
| [Laravel-Migration-CreateTasksTable.php](Laravel-Migration-CreateTasksTable.php) | `tasks` table schema |
| [Laravel-Migration-CreateTaskActivitiesTable.php](Laravel-Migration-CreateTaskActivitiesTable.php) | Activity log |
| [Laravel-Migration-CreateTaskFilesTable.php](Laravel-Migration-CreateTaskFilesTable.php) | Uploaded proof metadata |
| [Laravel-Models-Task.php](Laravel-Models-Task.php), [Laravel-Models-TaskActivity.php](Laravel-Models-TaskActivity.php), [Laravel-Models-TaskFile.php](Laravel-Models-TaskFile.php) | Eloquent models |
| [Laravel-Controller-TaskController.php](Laravel-Controller-TaskController.php) | CRUD + start/upload/complete actions |
| [Laravel-Routes-Addition.php](Laravel-Routes-Addition.php) | Route definitions |
| [Laravel-View-stafftask-index.blade.php](Laravel-View-stafftask-index.blade.php) | Blade view |
| [public-css-app.css](public-css-app.css), [public-js-stafftask.js](public-js-stafftask.js) | Assets to drop into `public/` |

Step-by-step integration: [LARAVEL_CONVERSION_GUIDE.md](LARAVEL_CONVERSION_GUIDE.md) and [INTEGRATION_STEPS.md](INTEGRATION_STEPS.md).

---

## 14. Running the App

### Static (no server)

```
Double-click index.html → opens in browser → done.
```

### With sync server

```bash
npm install
npm start
# open http://localhost:3000  (or index.html)
```

### In Laravel

1. Copy migrations → `php artisan migrate`
2. Copy models → `app/Models/`
3. Copy controller → `app/Http/Controllers/`
4. Copy assets → `public/css/app.css`, `public/js/stafftask.js`
5. Copy view → `resources/views/stafftask/index.blade.php`
6. Merge routes from `Laravel-Routes-Addition.php`

---

## 15. Test Checklist

- [ ] Open `index.html`
- [ ] Login (or toggle to **Admin**)
- [ ] Create a task with title, priority, due date, assignee
- [ ] Drag the card from **To Do** → **In Progress**
- [ ] Switch to **Staff**
- [ ] Click **🚀 Start Task** — allow location → see address rendered
- [ ] Upload one image and one video — see progress bar and previews
- [ ] Click **✅ Complete Task** — confetti
- [ ] Refresh the browser — data persists
- [ ] Re-open the task in **Admin** — proof files and timeline visible

---

## 16. Known Limitations

- Auth is client-side only; switching roles is not authoritative.
- Proof files are base64 in `localStorage` → not suitable beyond a handful of small videos.
- No multi-user concurrency without the sync server.
- Nominatim is rate-limited; production should use a paid geocoder.
- No automated tests in the repo yet.

---

## 17. Production Hardening Checklist

- [ ] Real auth (JWT or session) + server-side role enforcement
- [ ] Move file uploads to S3 / Cloudinary; store URLs only
- [ ] Replace localStorage with API + DB (the Laravel files are ready for this)
- [ ] Add HTTPS, CORS allow-list, rate limiting
- [ ] Server-side validation of every mutating endpoint
- [ ] Backup / export of tasks
- [ ] CSP headers, file-type/AV scanning on uploads

---

## 18. Changelog Pointers

- [CHANGES.md](CHANGES.md) — technical change history
- [DRAG_DROP_UPDATE.md](DRAG_DROP_UPDATE.md) — drag-and-drop revisions
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) — what was built and where
- Recent fixes (from `git log`): hidden uploaded-files section on new task, scrollable admin modal, grid overflow fix, admin always loads latest data, persistent admin form buttons.

---

*Single-file vanilla app. No build step. No framework. Open `index.html` and go.*
