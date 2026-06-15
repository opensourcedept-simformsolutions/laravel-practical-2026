# Society Gatekeeper Management System

A web-based society management application built with Laravel 12. Enables society admins to manage residents, residents to create visitor passes, and gatekeepers to log visitor entry/exit and deliveries.

---

## Project Objective

- Society Admin can manage flats, residents, and gatekeepers
- Residents can create and track visitor passes and raise complaints
- Gatekeepers can verify visitors, record entry/exit, and log deliveries
- Admin can view live dashboard stats and generate reports

---

## User Roles

| Role | Responsibilities |
|---|---|
| Admin | Manage residents & gatekeepers, view all reports |
| Resident | Create visitor passes, view visitor history, raise complaints |
| Gatekeeper | Verify & log visitor entry/exit, record deliveries |

---

## Modules

### Module 1 — Authentication & RBAC

- Login, logout, change password, forgot password
- Profile management
- Role-based access control (Admin, Resident, Gatekeeper)
- Laravel Breeze authentication scaffolding
- Route middleware guards per role

### Module 2 — Flat & Resident Management

- Add, edit, delete flats (flat number, wing, floor)
- Add, edit, delete residents (name, phone, email, owner/tenant)
- Family member management per flat
- Link resident to flat and login account
- Resident listing with search

### Module 3 — Visitor Pass Management *(main module)*

- Resident creates a visitor pass:
  - Visitor name, mobile number, purpose, visit date, vehicle number (optional)
- Pass status flow: `Pending → Entered → Exited → Cancelled`
- Resident can view visitor history and cancel a pending pass
- Gatekeeper sees all pending passes for their gate

### Module 4 — Gate Entry & Exit

- Gatekeeper searches visitor by name, mobile, or pass ID
- Mark Entry — records entry time and gatekeeper name
- Mark Exit — records exit time
- Visitor log listing for gatekeeper

### Module 5 — Delivery Management

- Gatekeeper records incoming delivery:
  - Flat number, resident, vendor (Amazon / Flipkart / Swiggy / Zomato / Courier), package details
- Status: `Received → Delivered`
- Delivery listing with flat and date filters

### Module 6 — Complaint Management

- Resident raises a complaint with category and description
- Categories: Security, Cleaning, Water, Electricity, Parking
- Status workflow: `Open → In Progress → Resolved`
- Admin can add notes to any complaint

### Module 7 — Dashboard & Reports

Admin dashboard with live stat cards:

- Visitors Today
- Visitors Currently Inside
- Deliveries Today
- Open Complaints
- Total Residents

Report pages with date range, flat, and status filters:

- Visitor Report
- Delivery Report
- Complaint Report

---

## Nice-to-Have Features *(only if time allows)*

- QR code on visitor pass — gatekeeper scans to auto-fill entry
- Webcam photo capture at gate entry
- Email notification to resident when visitor is marked as entered
- CSV export on all report pages

---

## Project Scope

This project is a trainee assignment at **Simform Solutions**. The scope is intentionally focused — 7 modules, 8 tables — to produce a complete, working application that demonstrates core Laravel concepts: Authentication, RBAC Middleware, CRUD, Eloquent Relationships, Validation, and Reporting.
