# Vibe

A plain **Laravel + Blade + Tailwind CSS + Alpine.js** web application scaffold.

## Stack

- **Laravel 12** (supports the project's PHP 8.3 requirement; Laravel 13 needs PHP >= 8.4) — PHP framework
- **Blade** — server-side templating
- **Tailwind CSS 4** — utility-first CSS, compiled by Vite
- **Alpine.js** — lightweight interactivity, bundled by Vite
- **Vite** — frontend build tool
- **Laravel Pint** — PHP code style fixer (`composer format`)
- **spatie/laravel-permission** — roles & permissions (one role per user, see [Roles & Permissions](#roles--permissions))

> Explicitly **not** installed: Breeze, Jetstream, Inertia, Livewire, Vue, React.

## Requirements

- PHP >= 8.3 (with `pdo_mysql`)
- Composer
- Node.js >= 20
- npm
- MySQL 8+ / MariaDB (database `starter`, user `root`, password `root`)

## Run Locally

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy the environment file and generate an app key
cp .env.example .env
php artisan key:generate

# 3. Install frontend dependencies
npm install

# 4. Run the database migrations and seed the initial admin user
#    (create the `starter` database first, e.g. `CREATE DATABASE starter;`)
php artisan migrate --seed

# 5. Start the dev servers
composer dev        # runs `php artisan serve` + `npm run dev` together
# or, in two separate terminals:
php artisan serve
npm run dev
```

Visit http://localhost:8000. For a production build of assets, run `npm run build`.

> The application is **Arabic-only**: UI text is hardcoded Arabic and the locale is
> fixed to `ar` in `config/app.php`. See [Arabic-only UI & RTL](#arabic-only-ui--rtl).

## Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Account/           # Per-user preferences controller
│   │   ├── Admin/            # Admin-only controllers (roles, users, settings)
│   │   ├── Auth/             # Authentication controllers
│   │   └── Controller.php
│   └── Requests/
│       ├── Account/           # Preferences form request
│       ├── Admin/            # Admin form requests (users, settings)
│       ├── Auth/             # Auth form requests (LoginRequest)
│       └── ...               # All form validation (Form Requests)
├── Models/                   # Eloquent models (User, Setting)
├── Policies/                 # Authorization policies (UserPolicy)
├── Providers/
└── Services/                 # Business logic (UserService, SettingsService)
resources/
├── css/app.css               # Tailwind + theme CSS variables + font-size scale
├── js/                       # Alpine.js entry point + components (sidebar, theme-switcher)
└── views/
    ├── layouts/              # Base layouts: app (sidebar shell), guest
    ├── components/           # Reusable Blade components (sidebar, sidebar-link, theme-switcher, card)
    ├── auth/                 # Auth screens (login)
    ├── account/              # Account preferences screen
    ├── admin/roles/          # Role management screens
    ├── admin/users/          # User management screens
    ├── admin/settings/       # App settings screen
    └── dashboard.blade.php   # Authenticated landing page (placeholder)
config/                       # Laravel config + permissions.php (permission catalogue)
routes/
├── web.php                   # Route definitions only
└── auth.php                  # Login/logout routes
database/
└── seeders/
    ├── DatabaseSeeder.php    # Permissions → roles → admin user
    ├── PermissionSeeder.php  # Syncs config/permissions.php into the DB
    └── RoleSeeder.php        # Example roles (Admin, Viewer)
```

## Arabic-only UI & RTL

- The application is **Arabic-only** — there is no translation layer. All UI strings
  are hardcoded Arabic text written directly in the Blade views, and validation
  messages are hardcoded Arabic in the form requests. There are no `resources/lang`
  files and no `__()` / `trans()` calls.
- The locale is fixed to Arabic in `config/app.php`
  (`'locale' => 'ar'`, `'fallback_locale' => 'ar'`); it is not env-driven.
- Both layouts hardcode `<html lang="ar" dir="rtl">`.
- RTL is handled with **CSS logical properties** (`ms-*`, `me-*`, `ps-*`, `pe-*`,
  `start-*`, `end-*`, `text-start`/`text-end`). Avoid `ml-*`/`mr-*`/`pl-*`/`pr-*` in
  layout and components.

## Theming

Themes are CSS-variable-driven, not `dark:` class-driven, so new themes require no
component changes.

### Where the variables live

- `config/themes.php` — lists available themes, the default, and the cookie name.
- `resources/css/app.css` — semantic tokens map to Tailwind utilities via
  `@theme inline` (e.g. `bg-background`, `text-foreground`, `border-border`), and each
  theme defines the `--app-*` CSS variables in a `[data-theme="..."]` block.
- The active theme is rendered on `<html data-theme="{{ $theme }}">` (set from the
  theme cookie by a global view composer in `AppServiceProvider`).

### How to add a new theme

1. Add its key to `config/themes.php` → `themes` (e.g. `'sepia'`).
2. Add its Arabic label to the `$labels` array in
   `app/Providers/AppServiceProvider.php` (e.g. `'sepia' => 'بني'`).
3. Add a `[data-theme="sepia"] { ... }` block in `resources/css/app.css` overriding
   the `--app-*` variables.

The theme switcher dropdown, Alpine component, and cookie persistence pick it up
automatically — no other changes needed.

### Persistence

Theme choice is stored in a **cookie** (`theme`, 1 year, `SameSite=Lax`) rather than
the session because it is a display preference: it should persist across visits
without a login, be readable by Alpine to apply instantly on the client, and stay out
of server-side session state. The cookie is set by the Alpine component
(`resources/js/components/theme-switcher.js`) and read server-side to render
`data-theme` without a flash. It is excluded from Laravel's encrypted cookies in
`bootstrap/app.php` so Alpine can read/write it directly.

### Interaction with font size

The per-user font size (see [Settings & Preferences](#settings--preferences)) is
implemented orthogonally to themes: a `data-font-size="small|default|large"`
attribute on the `<html>` element scales the root font size via CSS
(`[data-font-size='small'] { font-size: 90% }` / `large` = 115%) in
`resources/css/app.css`, so every `rem`/`em`-based Tailwind utility scales. Themes
only change colors via `--app-*` variables; font size only changes the typographic
scale. The two settings do not override each other — a theme's `--app-*` values stay
color-only, and components use `rem`/`em` (Tailwind's default) rather than fixed
pixel values.

## Authentication

Authentication is a hand-rolled Laravel **session guard** flow — no Breeze/Fortify
— and intentionally diverges from Laravel's defaults:

- **Users table** — `name`, `username` (unique, the login credential), `email`
  (nullable, *not* unique, not used for login; reserved for future notifications),
  `password`, `remember_token`, `font_size` (per-user UI preference, default
  `default`), `is_active` (boolean, default `true`; when `false` the account cannot
  log in — see [Account activation & deactivation](#account-activation--deactivation)).
- **Login is username + password only.** `LoginRequest`
  (`app/Http/Requests/Auth/LoginRequest.php`) validates `username` (required) and
  `password` (required), then authenticates with `Auth::attempt()` against the
  `username` column. It includes Laravel's standard login rate limiting (5 attempts).
- **No self-service flows** — there is intentionally **no registration**, no
  forgot-password / password reset, and no email verification. Users are created by an
  admin via the [User Management](#user-management) screen.
- **Routes** live in `routes/auth.php`:
  - `GET /login` (guest middleware) — `name('login')`
  - `POST /login` (guest middleware)
  - `POST /logout` (auth middleware) — `name('logout')`
- **Redirects** — the `auth` middleware redirects unauthenticated users to
  `route('login')` (the default `Authenticate::redirectTo()`). Successful login
  regenerates the session and redirects to `/`.
- The login page (`resources/views/auth/login.blade.php`) extends
  `layouts.guest`, so it inherits the RTL direction and theme from step 1. The
  logout button in the `layouts.app` top bar only renders for authenticated users.

### Initial admin user

`DatabaseSeeder` creates one admin user so the app is usable immediately and
assigns it the **Admin** role (all permissions):

| field      | value      |
|------------|------------|
| username   | `admin`    |
| password   | `password` |
| role       | `Admin`    |

> **Change this placeholder password after the first login.** An admin can reset any
> user's password (including their own) from the [User Management](#user-management)
> edit screen. Reset the DB anytime with `php artisan migrate:fresh --seed`.

## Roles & Permissions

Powered by **spatie/laravel-permission** with two deliberate simplifications:
there is **one role per user** (see below), and the permission list is driven by a
single config file instead of being managed ad-hoc in the database.

### Single source of truth: `config/permissions.php`

Every permission in the app is declared in `config/permissions.php`, grouped by
feature area:

```php
return [
    'users'    => ['users.view', 'users.create', 'users.edit', 'users.delete'],
    'roles'    => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
    'settings' => ['settings.edit'],
];
```

The **`PermissionSeeder`** reads this file and syncs it into the `permissions`
table (guard `web`):

- permissions listed in the config are created if missing;
- permissions that exist in the DB but were **removed** from the config are only
  **reported** (a warning in the seeder output) and never deleted automatically —
  so renaming a key can't silently destroy a role's assignments.

### Adding a new permission

1. Add its key to the relevant group in `config/permissions.php`.
2. Run the seeders (idempotent — safe to re-run any time):

```bash
php artisan db:seed --class=PermissionSeeder   # creates the new permission
php artisan db:seed --class=RoleSeeder         # re-syncs seeded roles (Admin gets it)
```

or reset everything with `php artisan migrate:fresh --seed`.

### Example roles

`RoleSeeder` creates two roles to demonstrate the pattern:

| role      | permissions                                              |
|-----------|----------------------------------------------------------|
| `Admin`   | **all** permissions in the catalogue                     |
| `Viewer`  | only the `*.view` permissions (`users.view`, `roles.view`) |

Re-running `RoleSeeder` re-applies these rules, so a permission added to the config
is picked up by `Admin` automatically on the next seed.

### One role per user (business rule)

A user holds **exactly one role**; permissions are granted to roles, and roles to
users 1:1. This is enforced at the application level in
`App\Services\UserService::assignRole()`, which uses Spatie's `syncRoles()` so any
previous role is detached before the new one is attached:

```php
app(UserService::class)->assignRole($user, 'Admin');
```

**Always go through `UserService` when assigning a role** (seeders, controllers,
the user management screen) instead of calling Spatie's `assignRole()` /
`syncRoles()` directly — the service is the enforcement point and documents the
rule. (Spatie's own `assignRole()` *adds* roles and would allow a user to end up
with several.)

### Role management screen

An admin-only screen at **`/admin/roles`** (sidebar link visible via
`@can('roles.view')`) lets a user with the `roles.*` permissions:

- list roles (`roles.view`),
- create a role (`roles.create`) — name + permission checkboxes grouped by feature
  area from `config/permissions.php`,
- edit a role's name and permissions (`roles.edit`),
- delete a role (`roles.delete`).

Routes live in `routes/web.php` under the `admin.roles.*` names and are protected
with Spatie permissions via the `can:` middleware (e.g.
`Route::get('roles', ...)->middleware('can:roles.view')`). Views are in
`resources/views/admin/roles/` and use hardcoded Arabic text.

### Checking permissions in views

Spatie registers every permission on Laravel's Gate, so `@can` works directly:

```blade
@can('users.create')
    {{-- only for users whose role grants users.create --}}
@endcan
```

`@cannot`, `$user->can(...)`, and the `can:` route middleware behave the same way.
No extra Blade directive is needed.

## User Management

An admin-only screen at **`/admin/users`** (sidebar link visible via
`@can('users.view')`) lets a user with the `users.*` permissions manage users:

- list users (`users.view`) — a paginated table showing name, username, role, and
  account status (`نشط` / `معطل` badges);
- create a user (`users.create`) — name, username, optional email, optional password,
  and exactly one role (assigned through `UserService`, so a user never has two roles);
- edit a user (`users.edit`) — change name, username, email, and role;
- activate / deactivate an account (`users.edit`) — see
  [Account activation & deactivation](#account-activation--deactivation);
- delete a user (`users.delete`) — an admin can never delete their own account.

Authorization is centralized in **`App\Policies\UserPolicy`** (`viewAny`/`view`/
`create`/`update`/`delete`) and enforced in both the controllers (`$this->authorize(...)`)
and the Blade views (`@can`). Routes live in `routes/web.php` under the `admin.users.*`
names, protected by the `can:` middleware (e.g.
`Route::get('users', ...)->middleware('can:users.view')`). Views are in
`resources/views/admin/users/` and use hardcoded Arabic text.

### Passwords

- **On create, the password is optional.** If it is left blank, the system generates a
  random password (`Str::password(16)`) and shows it **once** in the success flash
  message — copy it before leaving the page. This is an internal admin tool with no
  email flow, so there is no way to recover it later. Alternatively, type a password
  (min 8 characters) to set it yourself.
- **Reset Password** (on the edit screen, requires `users.edit`): the admin types a new
  password and confirms with a small "are you sure" dialog (Alpine `$refs`). The change
  takes effect immediately — no email, no token. This is meant for locked-out users who
  can no longer sign in, since there is no self-service recovery.

### Account activation & deactivation

An account can be **deactivated** (login access revoked) and **reactivated** (access
restored) without touching the record itself — the user row, password, and role are
kept intact. Deactivation is the "soft block" alternative to deletion:

- **Permission** — both actions require `users.edit` (the same permission as editing);
  an admin can never deactivate their own account.
- **UI** — an inline "are you sure?" toggle (Alpine) on both the list and edit screens
  (`resources/views/components/user-status-toggle.blade.php`), plus `نشط` / `معطل`
  badges in the list.
- **Login enforcement** — a deactivated user is rejected at login even with correct
  credentials (`LoginRequest`), with a clear Arabic message
  ("تم تعطيل هذا الحساب. يرجى التواصل مع المسؤول."). A wrong password on a deactivated
  account still shows the generic invalid-credentials error.
- **Open sessions** — deactivation takes effect immediately: the
  `EnsureUserIsActive` middleware (`app/Http/Middleware/EnsureUserIsActive.php`, web
  group) force-logs-out a deactivated user on their very next request, so sessions that
  were open before the deactivation die right away and redirect to the login screen.

## Settings & Preferences

### Admin settings (`/admin/settings`, requires `settings.edit`)

App-wide settings that apply to **all users immediately** after saving:

- **App name** — shown in the sidebar brand, footer, page titles, and the guest
  (login) branding.
- **Logo** — an optional image (max 2 MB). Uploaded to the `public` disk
  (`storage/app/public/logos`, served through `public/storage` via
  `Storage::url()`); uploads require the storage symlink
  (`php artisan storage:link`). When set it replaces the plain app-name text in the
  sidebar/guest branding.

Storage is a key-value `settings` table
(`app/Models/Setting.php`, accessed through `app/Services/SettingsService.php`):
`get()` reads with `Cache::rememberForever('settings.<key>')`, `set()` persists and
forgets the cache entry so the change is visible on the very next request. The shared
view composer in `AppServiceProvider` provides `$appName`/`$logoUrl`/`$userFontSize`
to every view. `DatabaseSeeder` seeds a default `app_name` (from `config('app.name')`)
when the key is absent.

### Account preferences (`/account/preferences`, any authenticated user)

Per-user preferences that affect **only the account owner**:

- **Font size** — `small` / `default` / `large`, stored on the user's `font_size`
  column and rendered as `data-font-size="..."` on the `<html>` element of the
  authenticated layout (scaled by CSS in `resources/css/app.css`). Two users see
  different font sizes on their own pages without affecting each other.

## Application Shell

The authenticated area (`layouts.app`) is a sidebar + top-bar shell:

- **Sidebar** (`resources/views/components/sidebar.blade.php`) — fixed on the
  **right** in RTL (first flex child). Contains the brand (logo + app name), the nav,
  and a collapse toggle.
- **Collapsible nav** — Alpine-driven (`resources/js/components/sidebar.js`),
  collapses to icon-only (`w-64` ⇄ `w-16`) with a width transition, and persists the
  collapsed/expanded choice in `localStorage` (`sidebar-collapsed`), so no page reload
  is needed and the state survives visits.
- **Permission-aware items** — each item is wrapped in `@can('<permission>')` using
  the exact key from `config/permissions.php`. Items the current user cannot see are
  removed from the DOM entirely (not just hidden/disabled):
  - Dashboard (`/`, always shown to authenticated users)
  - Users (`@can('users.view')`)
  - Roles (`@can('roles.view')`)
  - Settings (`@can('settings.edit')`)
  The active item is marked with `aria-current="page"` (matched by route name via
  `request()->routeIs(...)`). The dashboard quick-link tiles use the same `@can` gates.
- **Top bar** — sticky header with the page title on one side and, on the other, the
  current user's avatar + username (links to their preferences), the theme switcher
  (Step 1), and the logout button.
- **RTL & font size** — the sidebar inherits the root `data-font-size` scale like every
  other `rem`-based element, and all layout edges use logical properties
  (`border-e`, `ms-*`/`me-*`, `justify-start/end`) so it aligns correctly in Arabic.

## Conventions

- **Validation** — All validation lives in dedicated **Form Request** classes under
  `app/Http/Requests/`. No inline `$request->validate()` calls inside controllers.
- **Authorization** — All authorization checks go through **Policies** in
  `app/Policies/`, used with `$this->authorize()` / `Gate::` / `@can`. Never inline
  ad-hoc checks in controllers or views.
- **Views** — No logic in views. Views are for presentation only (loops, conditionals,
  output). Business logic belongs in models, services, or actions.
- **Routes** — `routes/web.php` contains route definitions only. No business logic
  in route files; use controllers. Admin routes go under the `admin.*` name prefix.
- **Controllers** — Admin-only controllers live under `app/Http/Controllers/Admin/`
  and only handle admin-facing screens.
- **Code style** — Run `composer format` (Laravel Pint) before committing.

## Formatting

```bash
composer format        # Laravel Pint with default Laravel style rules
composer format --test # dry run (list files that would change)
```

## Tests

```bash
composer test
```

Feature tests run against MySQL database `starter_test` (see `phpunit.xml`); create
it once with `CREATE DATABASE starter_test CHARACTER SET utf8mb4 COLLATE
utf8mb4_unicode_ci;`. `RefreshDatabase` migrates and isolates each test.
