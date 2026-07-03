**Project Presentation: Laravel Practical 2026**

This document lists required packages, a minimal project configuration checklist, modules overview, upcoming feature examples, and sample bug fixes you can replace with current items.

**Required Packages (composer)**
- **php**: ^8.2 (project platform uses 8.3.30)
- **laravel/framework**: ^12.0
- **laravel/breeze**: ^2.4 (dev)
- **simplesoftwareio/simple-qrcode**: ^4.2
- **livewire/livewire**: ^4.3
- **yajra/laravel-datatables**: ^12.0

**NPM / Frontend**
- Uses Laravel Vite + Tailwind (see package.json and tailwind.config.js)

**Quick Install / Simple Config**
1. Clone and install PHP dependencies:

   composer install

2. Copy environment and set core values:

   - Copy `.env.example` to `.env` and set `APP_URL`, DB credentials, mail and queue driver.
   - Run `php artisan key:generate`.

3. Database & storage:

   - Run migrations: `php artisan migrate --force`.
   - (Optional) Seed: `php artisan db:seed`.
   - Link storage: `php artisan storage:link`.

4. Authentication (Breeze):

   - Breeze is included as a dev dependency. To scaffold and build auth UI run:

     composer require laravel/breeze --dev
     php artisan breeze:install blade
     npm install && npm run build

5. QR Code usage (simplesoftwareio/simple-qrcode):

   - Already required in composer. Example Blade usage:

     {!! QrCode::size(250)->generate($urlOrText) !!}

6. Mail & Notifications:

   - Configure mail settings in `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`).
   - Ensure queue worker is running for notification sending: `php artisan queue:work` or `php artisan queue:listen`.

7. Running locally:

   - Dev server + Vite: `npm run dev` and `php artisan serve` (or use the provided `dev` script).

**Modules (high-level)**
- **Users & Roles**: registration, authentication, roles, permissions
- **Residents**: resident profiles, welcome notifications
- **Flats**: flat management and assignment
- **Visitors & VisitorLog**: visitor registration, entry/exit logs, email notifications
- **Deliveries**: delivery records, delivery status
- **Complaints**: complaint categories, status tracking
- **Activity Logs**: audit trail for actions
- **Society**: society-level settings and metadata

**Upcoming Features (placeholder examples)**
- Feature: Real-time visitor dashboard — live updates when visitors enter/exit (uses WebSockets/Livewire).
- Feature: Resident self-service portal — residents can update profile and request services online.

**Sample Bug Fixes (placeholder examples)**
- Bug: Visitor exit time not recorded when scanning QR code — Fix: ensure `VisitorLog` exit handler writes `exited_at` timestamp and dispatches `VisitorExited` event.
- Bug: Mail queue failing silently — Fix: add logging around mail send jobs and ensure queue worker runs with correct connection.
- Bug: DataTables pagination breaks on filtered results — Fix: apply server-side filter pipeline before counting total records.

**Notes & Next Steps**
- Replace the placeholder upcoming features and sample bug fixes with real items before presenting.
- You can point the presentation to this file: [PRESENTATION.md](PRESENTATION.md)
