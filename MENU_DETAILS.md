# Menu UI — Project Details

Reference for the sidebar (vertical menu) and its items: what each menu links to, who can see it, where the code lives, and how to add or change items safely.

---

## 1. How the menu is built

```
┌──────────────────────────────┐
│  resources/menu/             │   JSON describes the menu items
│  └── verticalMenu.json       │   (text, icon, URL, roles, submenu)
└──────────────────────────────┘
              │
              ▼
┌──────────────────────────────┐
│  ServiceProvider             │   Loads the JSON into a shared
│  (auto via Sneat template)   │   `$menuData` variable
└──────────────────────────────┘
              │
              ▼
┌──────────────────────────────┐
│  resources/views/layouts/    │   Loops `$menuData`, renders <ul>,
│   sections/menu/             │   filters by user role,
│    verticalMenu.blade.php    │   marks the active item.
│    submenu.blade.php         │   Recursively renders submenus.
└──────────────────────────────┘
```

Key idea: **menu is data, not code.** Add/remove items by editing the JSON. Only edit Blade if you want to change *how* items render.

---

## 2. Files

| File | Purpose |
|---|---|
| [resources/menu/verticalMenu.json](resources/menu/verticalMenu.json) | The menu definition — edit this 99% of the time |
| [resources/views/layouts/sections/menu/verticalMenu.blade.php](resources/views/layouts/sections/menu/verticalMenu.blade.php) | Renders the top-level menu, applies role filtering & active state |
| [resources/views/layouts/sections/menu/submenu.blade.php](resources/views/layouts/sections/menu/submenu.blade.php) | Renders nested submenu items |
| [resources/views/layouts/sections/navbar/navbar-partial.blade.php](resources/views/layouts/sections/navbar/navbar-partial.blade.php) | The top navbar (user dropdown, language switcher, logout) |
| [routes/web.php](routes/web.php) | Where each menu URL maps to a controller |

---

## 3. JSON schema (per item)

```json
{
  "name":   "Display label (translatable via __())",
  "url":    "/path-or-route",                       // omit for parent-only entries
  "icon":   "menu-icon icon-base bx bx-<name>",     // Boxicons class
  "slug":   "matches the route name for active highlight",
  "roles":  ["admin", "staff", "client"],           // who can see this item
  "badge":  ["danger", "1"],                        // optional pill (color, text)
  "target": "_blank",                               // optional: open in new tab
  "submenu": [ { ...same shape, nested... } ]       // optional: children
}
```

- **`roles`**: when set, only users whose `users.role` is in the list see the item. When omitted, everyone sees it.
- **`slug`**: should equal the **named route** (e.g. `calendar.index`) so the menu can detect "you are here" and add `.active`/`open` classes.
- **`url`**: relative path. The renderer pipes it through `url(...)`. Use `/` to mean the site root.

---

## 4. Current menu items

Below: every top-level entry as of today, in display order.

### 4.1 Dashboards `[admin]`

| | |
|---|---|
| **Label** | Dashboards |
| **Icon** | `bx bx-home-smile` |
| **Slug** | `dashboard` |
| **URL** | `/` |
| **Roles** | admin |
| **Badge** | danger pill "1" |
| **Submenu** | `Total booking` → `/dashboard/total-booking` |
| **Controller** | `App\Http\Controllers\dashboard\Analytics@index` |
| **Route name** | `dashboard-analytics` |
| **Notes** | The admin landing page after login (`/` redirects here). The badge is hard-coded — to make it dynamic, replace `"badge"` in JSON with a Blade-rendered count. |

### 4.2 Authentications `[admin]`

| | |
|---|---|
| **Label** | Authentications |
| **Icon** | `bx bx-lock-open-alt` |
| **Slug** | `auth` |
| **Roles** | admin |
| **Submenu** | `Login` → `auth/login-basic` (opens in new tab) |
| **Controller** | `App\Http\Controllers\authentications\LoginBasic` |
| **Notes** | Demo of the auth pages. The real login is at `/login`. This entry is mostly for previewing themed auth screens. Safe to remove if you don't use it. |

### 4.3 Calendar `[admin, staff, client]`

| | |
|---|---|
| **Label** | Calendar |
| **Icon** | `bx bx-calendar` |
| **Slug** | `calendar.index` |
| **URL** | `/calendar` |
| **Roles** | admin, staff, client |
| **Controller** | `App\Http\Controllers\CalendarController` |
| **View** | [resources/views/calendar/index.blade.php](resources/views/calendar/index.blade.php) |
| **Reference doc** | [CALENDAR_DETAILS.md](CALENDAR_DETAILS.md) |
| **Data model** | `events` table (one row per event, per user) |
| **What it does** | Per-user event calendar. Month/Week/Day/List views, drag-resize, category filters, mini-calendar. |

### 4.4 Client `[admin]`

| | |
|---|---|
| **Label** | Client |
| **Icon** | `bxl-php` (yes, a PHP logo — purely cosmetic, change to `bx bx-buildings` for better fit) |
| **Slug** | `laravel-example-user-management` (legacy slug; only used for active-state matching) |
| **Roles** | admin |
| **Submenu** | `List Client` → `/admin/client` &nbsp;·&nbsp; `Add Client` → `/admin/client/add-client` |
| **Controller** | `App\Http\Controllers\ClientController` |
| **Route names** | `admin.client.index`, `admin.client.add-client` |
| **What it does** | Customer accounts: list, view, create. Houses are nested under each client. |

### 4.5 Staff `[admin]`

| | |
|---|---|
| **Label** | Staff |
| **Icon** | `bx bx-group` |
| **Slug** | `auth` (legacy — change to a unique value to avoid clashing with Authentications) |
| **Roles** | admin |
| **Submenu** | `List Staff` → `/admin/staff/pages-staff-list` &nbsp;·&nbsp; `Add Staff` → `/admin/staff/pages-staff-add` |
| **Controller** | `App\Http\Controllers\StaffController` |
| **Models** | `App\Models\Staff` (note: uppercase `Staff` table, `StaffID` primary key) |
| **Photo storage** | `storage/app/public/staff/...` — streamed via `admin.staff.photo` route |
| **What it does** | Manage staff accounts. Profile photo uploads, role+work-type combos (Temporary/Permanent/Company × Part Time/Full Time/Contractor/Calculate Time), address autocomplete via Nominatim. |

### 4.6 Support Chat `[admin, client, staff]`

| | |
|---|---|
| **Label** | Support Chat |
| **Icon** | `bx bx-chat` |
| **Slug** | `app-chat` |
| **URL** | `/rooms` |
| **Roles** | admin, client, staff |
| **What it does** | Real-time chatrooms (uses `chatrooms`, `messages`, `reactions` tables). |

### 4.7 Manage Staff Work `[admin, client, staff]`

| | |
|---|---|
| **Label** | Manage Staff Work |
| **Icon** | `bx bx-grid` |
| **Slug** | `tasks.index` |
| **URL** | `/tasks` |
| **Roles** | admin, client, staff |
| **Controller** | `App\Http\Controllers\TaskController` |
| **What it does** | The **original** task system — table view for admin, card view for staff. Has its own create/edit/show/destroy/start/upload/complete/files.destroy routes. |
| **Relationship to StaffTask Board** | This is the older, table-based interface. The newer Kanban version lives at **StaffTask Board** (below) and uses **the same `tasks` table** — they're two views of one dataset. |

> The Blade has a special `if ($menu->name === 'Manage Staff Work')` block that forces `$menuUrl = '/tasks'` for every role. It exists in case different roles need different landing pages later — currently all three roles land at the same place. Safe to simplify by removing that block.

### 4.8 StaffTask Board `[admin, staff]`

| | |
|---|---|
| **Label** | StaffTask Board |
| **Icon** | `bx bx-task` |
| **Slug** | `stafftask.board` |
| **URL** | `/stafftask` |
| **Roles** | admin, staff |
| **Controller** | `App\Http\Controllers\StaffTaskController` |
| **View** | [resources/views/stafftask/board.blade.php](resources/views/stafftask/board.blade.php) |
| **Reference doc** | [PROJECT_DETAILS.md](PROJECT_DETAILS.md) (StaffTask section) |
| **Data model** | Same `tasks` table + `task_files` + `task_activities` + `assigned_users` pivot |
| **What it does** | Kanban board with To Do / In Progress / Complete columns. Drag-drop between columns (admin only), staff start/upload/complete workflow, photo & video proof uploads, in-page preview + download. |
| **Per-role visibility** | Staff see **only tasks assigned to them**; admin sees all. Enforced server-side in `StaffTaskController::index`. |

### 4.9 Account `[admin, client, staff]`

| | |
|---|---|
| **Label** | Account |
| **Icon** | `bx bx-dock-top` |
| **Slug** | `pages-account-settings-account` |
| **URL** | `pages/account-settings-account` |
| **Roles** | admin, client, staff |
| **Controller** | `App\Http\Controllers\pages\AccountSettingsAccount` |
| **What it does** | The current user's own profile: name, email, organization, phone, address, password change, avatar upload. |

---

## 5. Role visibility matrix

| Item | admin | staff | client |
|---|:---:|:---:|:---:|
| Dashboards | ✓ | — | — |
| Authentications (demo) | ✓ | — | — |
| Calendar | ✓ | ✓ | ✓ |
| Client | ✓ | — | — |
| Staff | ✓ | — | — |
| Support Chat | ✓ | ✓ | ✓ |
| Manage Staff Work | ✓ | ✓ | ✓ |
| StaffTask Board | ✓ | ✓ | — |
| Account | ✓ | ✓ | ✓ |

---

## 6. Top navbar (user dropdown)

Separate from the sidebar. Lives in [navbar-partial.blade.php](resources/views/layouts/sections/navbar/navbar-partial.blade.php).

Contains:

- **Avatar** → click for user dropdown
- **Profile summary** (name + role)
- **My Profile** → `/pages/account-settings-account`
- **Language** → submenu with 7 flags (EN, FR, KH, ZH, ES, DE, IT) — see below
- **Logout** → POST `/logout`

### Language switcher

| Code | Flag (ISO) | Label | Switches via |
|---|---|---|---|
| `en` | 🇺🇸 us | English | `/lang/en` |
| `fr` | 🇫🇷 fr | Français | `/lang/fr` |
| `km` | 🇰🇭 kh | ខ្មែរ | `/lang/km` |
| `zh` | 🇨🇳 cn | 中文 | `/lang/zh` |
| `es` | 🇪🇸 es | Español | `/lang/es` |
| `de` | 🇩🇪 de | Deutsch | `/lang/de` |
| `it` | 🇮🇹 it | Italiano | `/lang/it` |

Flag rendering uses the **flag-icons** CDN (`https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/`). Locale persists in the session via the `SetLocale` middleware.

---

## 7. Adding a new menu item

1. **Edit** [resources/menu/verticalMenu.json](resources/menu/verticalMenu.json) — add an object inside `"menu": [ ... ]`.

   ```json
   {
     "url":   "/reports",
     "name":  "Reports",
     "icon":  "menu-icon icon-base bx bx-line-chart",
     "slug":  "reports.index",
     "roles": ["admin"]
   }
   ```

2. **Create the route** in [routes/web.php](routes/web.php):

   ```php
   Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
   ```

   The route name **must** equal the `slug` field for active-state highlighting to work.

3. **Add the controller + view**. No changes needed in Blade — the JSON drives the menu.

4. **Translate the label**. Add `"Reports": "..."` to **all** seven `lang/*.json` files (or at least `fr.json` — missing keys fall back to English text).

---

## 8. Adding a submenu

```json
{
  "name":  "Reports",
  "icon":  "menu-icon icon-base bx bx-line-chart",
  "slug":  "reports",
  "roles": ["admin"],
  "submenu": [
    { "url": "/reports/daily",   "name": "Daily",   "slug": "reports.daily" },
    { "url": "/reports/weekly",  "name": "Weekly",  "slug": "reports.weekly" }
  ]
}
```

- The parent doesn't have a `url` — clicking it expands/collapses the children.
- Each child can have its own `roles` to restrict further (e.g. only admin sees `Daily`).
- For active-open behaviour to work, the parent `slug` should be a **prefix** of the child route names (here: `reports`).

---

## 9. Icons

The template uses **[Boxicons](https://boxicons.com/)**. The full icon class format is:

```
menu-icon icon-base bx bx-<icon-name>
```

Some commonly used icons in this project:

| Icon | Class | Used by |
|---|---|---|
| 🏠 | `bx bx-home-smile` | Dashboards |
| 📅 | `bx bx-calendar` | Calendar |
| 👥 | `bx bx-group` | Staff |
| 💬 | `bx bx-chat` | Support Chat |
| 🔲 | `bx bx-grid` | Manage Staff Work |
| ✅ | `bx bx-task` | StaffTask Board |
| 📋 | `bx bx-dock-top` | Account |
| 🔒 | `bx bx-lock-open-alt` | Authentications |
| 🌐 | `bx bx-world` | Language (in navbar) |

Browse [boxicons.com](https://boxicons.com/) for the full set; copy the class name (`bx bx-xyz`).

---

## 10. Active-state highlighting

How the menu knows which item is "you are here":

```blade
$currentRouteName = Route::currentRouteName();

if ($currentRouteName === ($menu->slug ?? null)) {
    $activeClass = 'active';
} elseif (isset($menu->submenu)
       && str_starts_with($currentRouteName, $menu->slug)) {
    $activeClass = 'active open';
}
```

So: **the `slug` field must match the route name** for top-level items, or be a **prefix** of child route names for parents with submenus.

If an item never highlights when active, check:

1. The named route exists with that exact name (`php artisan route:list | grep <slug>`).
2. The `slug` in the JSON matches (no typos, no leading slash).
3. The page you're on is the route with that name.

---

## 11. Hiding an item temporarily

Three options:

1. **Remove from JSON** — cleanest, but you'll re-type it later.
2. **Set `"roles": ["__nobody__"]`** — keeps the entry but no real role matches, so it's hidden from everyone.
3. **Comment-out via JSON-incompatible trick** — JSON doesn't support comments. Use option 2.

To restore: drop the `"roles"` line (open to all) or restore the original role list.

---

## 12. Common pitfalls

| Symptom | Cause | Fix |
|---|---|---|
| Menu item doesn't show | Current user's role isn't in `roles` | Add the role or remove the `roles` line |
| Item shows but click does nothing | `url` is missing or wrong | Check the path resolves to a registered route |
| 404 when clicking | Route isn't defined in `web.php` | Add the route, ensure middleware allows the user's role |
| Item never appears "active" | `slug` ≠ route name | Make `slug` match exactly |
| Submenu doesn't expand | Parent has both `url` AND `submenu` | Drop the parent's `url` (parent should only be a toggle) |
| Label is "Reports" in all languages | Missing translation keys | Add `"Reports": "..."` to each `lang/*.json` |
| Two items have the same `slug` | Both will highlight at once | Give each a unique slug |
| Icon doesn't render | Wrong Boxicons class | Verify on boxicons.com; remember the `bx bx-` prefix |
| Badge always says "1" | Hardcoded in JSON | Replace JSON `badge` with a Blade-rendered count in `verticalMenu.blade.php` |

---

## 13. Quick reference of all routes the menu points to

```
GET  /                                       dashboard-analytics
GET  /dashboard/total-booking                dashboard-total-booking
GET  /auth/login-basic                       auth-login-basic
GET  /calendar                               calendar.index
GET  /admin/client                           admin.client.index
GET  /admin/client/add-client                admin.client.add-client
GET  /admin/staff/pages-staff-list           admin.staff.pages-staff-list
GET  /admin/staff/pages-staff-add            admin.staff.pages-staff-add
GET  /rooms                                  (chat root)
GET  /tasks                                  tasks.index
GET  /stafftask                              stafftask.board
GET  /pages/account-settings-account         pages-account-settings-account
```

For the full route list (including all CRUD endpoints triggered from these pages):

```bash
php artisan route:list
```

---

## 14. Cleanup / refactor opportunities

These won't break anything if left alone, but are worth doing during the next pass:

- **Staff entry has `"slug": "auth"`** — same as Authentications. Change to `"staff"` to avoid both items highlighting at once when on either.
- **Client entry has `"icon": "bxl-php"`** — a PHP logo. Replace with `bx bx-buildings` or `bx bx-id-card` for a customer-y icon.
- **Manage Staff Work and StaffTask Board share the same `tasks` table.** If you settle on one UI, retire the other to reduce confusion.
- **`Manage Staff Work` URL logic** in [verticalMenu.blade.php:81-93](resources/views/layouts/sections/menu/verticalMenu.blade.php#L81-L93) currently branches by role but returns the same URL for every role. The whole block can be removed — the default `else` branch handles it.

---

## 15. Out of scope (for now)

- **Per-user customization** of menu items (favorites, drag-to-reorder). Would need a `user_menu_prefs` table.
- **Permission-based visibility** beyond simple role checks. Currently it's role-only — no fine-grained policies on individual items.
- **Mobile-only sub-navigation** (bottom tab bar). The current vertical menu collapses into a hamburger on mobile.

---

*Edit the JSON. Reload the page. Done.*
