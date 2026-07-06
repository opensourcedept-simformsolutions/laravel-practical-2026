# Society Management System - Detailed Workflow & Module Documentation

This guide describes the complete system architecture, modules, roles, database interactions, and real-time workflows of the project. It serves as a master blueprint for developers and AI coding agents.

---

## 1. Core Technology Stack & Architecture

* **Backend Framework:** Laravel 11/12 (PHP 8.2+)
* **Database:** MySQL / MariaDB (utilizing soft deletes and custom notification mappings)
* **Real-time Push Server:** Pusher Channels (Cloud-based pub/sub WebSocket service)
* **Real-time Client:** Laravel Echo & Pusher JS
* **Data Visualization:** Yajra DataTables (serverside processing)
* **Dynamic Modals:** SweetAlert2 for inline approvals & dismissals

---

## 2. Roles & Permissions Scoping

The system utilizes role-based middleware (`RoleMiddleware`) mapped to four user roles:

1. **Super Admin:** Global system controller. Manages Societies and Society Admins, views global metrics, and runs diagnostic impersonation.
2. **Society Admin:** Confined to a single society. Manages flats, residents, gatekeepers, and resolves complaints.
3. **Resident:** Belongs to a specific flat within a society. Pre-creates visitor passes, approves/rejects walk-in visitors, receives deliveries, and files complaints.
4. **Gatekeeper:** Guards the society gates. Logs walk-in visitors, records deliveries, scans visitor QRs, and updates entry/exit logs.

---

## 3. Core Modules & Detailed Workflows

### A. Authentication & Security
* **Guest Flow:**
  * Login on `/login`. Resolves user role and redirects to the appropriate dashboard.
  * Forgot Password workflow on `/forgot-password`. Sends a password reset email link; clicking the link opens the `/reset-password/{token}` page to save a new password.
* **Welcome Verification Email:**
  * When a Society Admin creates a Resident or Gatekeeper, the system sends a welcome email containing their credentials and a secure onboarding link.
* **Admin Impersonation:**
  * Super Admins can impersonate any society admin (`impersonate/{user}`) to debug society configurations. Clicking "Leave Impersonation" on the header returns the Super Admin to their original session.

### B. Super Admin Module
* **Society Management (`societies`):**
  * Super Admin configures societies, specifying name, address, and flat capacity.
  * Can soft delete societies and restore them.
  * Manages the Society Admins linked to each society.

### C. Society Admin Management Module
* **Flat Management (`flats`):**
  * CRUD operations for Flats (defining wing, floor, flat number).
  * Flats can be soft-deleted and restored.
  * Supports exporting the complete flats inventory of the society to Excel.
* **Resident Management (`residents`):**
  * Society Admin registers residents under a specific Flat.
  * Enforces flat capacity constraints.
  * Dynamic filters on the Resident List allow filtering residents by Flat and by deletion status (Active, Deleted, All) with inline **Restore** options.
* **Gatekeeper Management:**
  * Creation and management of Gatekeeper accounts for the society.

### D. Delivery Module
* **Log Incoming Delivery:**
  * Gatekeeper creates a delivery log (`deliveries.create`) when a courier arrives, recording the company name (Amazon, Flipkart, etc.), package description, and target Flat.
* **Resident Notification:**
  * Creating the delivery log automatically dispatches a database notification (`CustomDatabaseChannel`) to all residents of that Flat.
* **Mark Handover:**
  * When the package is delivered, the Gatekeeper or the Resident marks it as `delivered` on the deliveries page.
  * Soft delete and Restore capabilities are supported.
* **Delivery Reports:**
  * Date-range filtered listing and Excel exports of delivery logs.

### E. Complaint Module
* **Raise Complaint:**
  * Residents file complaints (`complaints.create`) specifying Category (Security, Cleaning, Water, Parking) and Description.
* **Admin Processing:**
  * Admins view complaints and update the status (`open`, `in_progress`, `resolved`) while saving custom admin notes.
  * Complaints support soft deletion and restoration.
* **Complaint Reports:**
  * Exportable Excel complaints sheet with status filters.

### F. Visitor Pass Module (Real-time Approval System)

#### Workflow A: Pre-Created Visitor Pass (Resident)
1. A Resident creates a pass in advance. The status is set to `pending`.
2. When the visitor arrives at the gate, the Gatekeeper scans their QR code (encrypted pass ID) or searches by phone.
3. The Gatekeeper opens the entry modal, captures a photo (optional), and clicks **Mark Entry**. The status updates to `entered`, and the entry timestamp is recorded.
4. When the visitor exits, the Gatekeeper clicks **Mark Exit**. The status updates to `exited`, and the exit timestamp is recorded.

#### Workflow B: Walk-in Visitor Pass (Gatekeeper + Real-time Resident Approval)
1. **Creation:** A visitor arrives without a pre-created pass. The Gatekeeper logs their details and flat destination. The pass status is saved as `pending_approval`.
2. **Real-time Request Broadcast:** Saving the pass dispatches a `VisitorApprovalRequested` event which broadcasts over the WebSocket private channel `flat.{flatId}`.
3. **Modal Display:** All active resident devices subscribed to `flat.{flatId}` immediately show a SweetAlert2 approval popup modal. An in-app database notification is also written.
4. **Approve / Reject Action:**
   * **Reject:** If a resident rejects, the status updates to `rejected`. The gatekeeper receives a red toast notification.
   * **Approve:** If a resident approves, the status transitions directly to `entered` (Auto-Entry), capturing the current timestamp as `entry_time` and the user ID as `approved_by`. Bypasses any gatekeeper marking steps.
   * **Gatekeeper Notification:** The gatekeeper receives a green success toast: *"Visitor Name for flat X-YYY has been ENTERED by Resident Name!"* and their active table refreshes.
5. **Flat Assignment Correction (Gatekeeper Recall):**
   * If the Gatekeeper realizes they logged the wrong flat number (e.g. inputted flat 101 instead of 202) while the request is pending or approved, they edit the pass.
   * The system deletes the notifications written to the database for flat 101 members and resets the status back to `pending_approval` (clearing any approvals).
   * The system broadcasts a `VisitorApprovalRecalled` event on private channel `flat.101`. The active SweetAlert2 popup on all flat 101 residents' screens **instantly closes** with a warning toast.
   * The system broadcasts a new `VisitorApprovalRequested` event on private channel `flat.202`, causing the approval popup to appear on flat 202 residents' screens.
   * The recall action is logged in `ActivityLog`.

---

## 4. WebSockets & Broadcasting Specifications

We use Pusher Channels for event broadcasting and real-time client communication.

### Events Map:
* **`App\Events\VisitorApprovalRequested`**
  * **Channel:** Private `flat.{flatId}`
  * **Listener Client Side:** `.visitor.approval.requested`
  * **Behavior:** Renders a SweetAlert2 action popup modal.
* **`App\Events\VisitorApprovalStatusUpdated`**
  * **Channel:** Private `society.{societyId}`
  * **Listener Client Side:** `.visitor.approval.status.updated`
  * **Behavior:** Renders green/red Toast notification for Gatekeepers & Admins and reloads DataTables.
* **`App\Events\VisitorApprovalRecalled`**
  * **Channel:** Private `flat.{oldFlatId}`
  * **Listener Client Side:** `.visitor.approval.recalled`
  * **Behavior:** Runs `Swal.close()` to dismiss incorrect popups and shows an information toast.

---

## 5. System Loggers & Middleware

### A. Database Activity Logger (`App\Services\ActivityLogger`)
Logs CRUD actions to the `activity_logs` table:
* **Captured Fields:** `user_id`, `society_id`, `action` (create, update, delete, restore, cancel, approve, reject), `subject_type`, `subject_id`, `description`, `ip_address`, `user_agent`.

### B. Global Request Logger Middleware (`App\Http\Middleware\RequestLoggerMiddleware`)
Registered globally in `bootstrap/app.php` to track performance and input:
* **Logs to file:** Incoming request HTTP method, full URL, client IP, payload (excluding `password`, `_token`), response status code, and execution duration in milliseconds (`duration_ms`).

---

## 6. Backed Enums (`App\Enums`)

We define centralized states to maintain backend type-safety:

* **`ComplaintCategory` (`ComplaintCategory.php`):**
  * `SECURITY = 'security'`
  * `CLEANING = 'cleaning'`
  * `WATER = 'water'`
  * `PARKING = 'parking'`
* **`ComplaintStatus` (`ComplaintStatus.php`):**
  * `OPEN = 'open'`
  * `INPROGRESS = 'in_progress'`
  * `RESOLVED = 'resolved'`
* **`DeliveryStatus` (`DeliveryStatus.php`):**
  * `PENDING = 'pending'`
  * `DELIVERED = 'delivered'`
* **`ResidentType` (`ResidentType.php`):**
  * `OWNER = 'owner'`
  * `TENANT = 'tenant'`
* **`VisitorStatus` (`VisitorStatus.php`):**
  * `ACCEPTED = 'accepted'`
  * `PENDING = 'pending'`
  * `PENDING_APPROVAL = 'pending_approval'`
  * `APPROVED = 'approved'`
  * `REJECTED = 'rejected'`
  * `ENTERED = 'entered'`
  * `EXITED = 'exited'`
  * `CANCELLED = 'cancelled'`

---

## 7. Local Onboarding & Testing Checklist

1. **Verify WebSocket Connection:**
   * Configure your Pusher credentials in your local `.env` file.
   * Make sure your system is connected to the internet to reach Pusher servers.
2. **Build Javascript Assets:**
   * Run `npm run build` to compile the Echo listener changes in `public/build`.
3. **Verify Middleware Logs:**
   * Perform any HTTP request and tail the storage logs: `tail -f storage/logs/laravel.log`. Look for `"HTTP Request processed"` entries.
4. **Test Flat Correction Recall:**
   * Log in as Gatekeeper and Resident of Flat A.
   * Gatekeeper creates walk-in pass for Flat A.
   * Resident A gets popup.
   * Gatekeeper edits pass, changing flat to Flat B.
   * Resident A's popup must close immediately. Resident B gets the popup.
