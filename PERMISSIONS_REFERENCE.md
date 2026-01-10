# Permissions Reference Guide

This document lists all modules and their associated permissions for view, manage, and edit operations.

## Total Permissions: 52

---

## 1. ROOMS MODULE

### Rooms
- `rooms.view` - View room list and details
- `rooms.manage` - Create, edit, and delete rooms
- `rooms.edit` - Edit existing rooms

### Room Types
- `room_types.view` - View room types list and details
- `room_types.manage` - Create, edit, and delete room types
- `room_types.edit` - Edit existing room types

---

## 2. BOOKINGS MODULE

- `bookings.view` - View booking list and details
- `bookings.create` - Create new bookings
- `bookings.edit` - Edit existing bookings
- `bookings.delete` - Delete bookings
- `bookings.manage` - Full booking management (create, edit, delete)

---

## 3. POS SALES MODULE

- `pos.view` - View POS sales history
- `pos.sell` - Process POS sales
- `pos.edit` - Edit existing POS sales (permission available, feature pending)
- `pos.delete` - Delete POS sales (permission available, feature pending)
- `pos.manage` - Full POS sales management (create, edit, delete)

---

## 4. STOCK/EXTRAS MODULE

### Stock
- `stock.view` - View stock levels and movements
- `stock.manage` - Add, adjust, and manage stock
- `stock.edit` - Edit stock levels and products

### Products/Extras
- `extras.view` - View products/extras list and details
- `extras.manage` - Create, edit, and delete products/extras
- `extras.edit` - Edit existing products/extras

### Product Categories
- `extra_categories.view` - View product categories list
- `extra_categories.manage` - Create, edit, and delete product categories
- `extra_categories.edit` - Edit existing product categories

---

## 5. PAYMENTS MODULE

- `payments.view` - View payment history
- `payments.create` - Record payments for bookings and POS
- `payments.edit` - Edit existing payments (permission available, feature pending)
- `payments.delete` - Delete payments
- `payments.manage` - Full payment management (create, edit, delete)

---

## 6. HOUSEKEEPING MODULE

### General Housekeeping
- `housekeeping.view` - View housekeeping/maintenance tasks and records
- `housekeeping.manage` - Create, edit, and manage housekeeping/maintenance tasks
- `housekeeping.edit` - Edit existing housekeeping records

### Housekeeping Records
- `housekeeping_records.view` - View housekeeping records
- `housekeeping_records.manage` - Create, edit, and manage housekeeping records
- `housekeeping_records.edit` - Edit existing housekeeping records

### Housekeeping Reports
- `housekeeping_reports.view` - View housekeeping reports and analytics

### Hotel Areas
- `hotel_areas.view` - View hotel areas list
- `hotel_areas.manage` - Create, edit, and delete hotel areas
- `hotel_areas.edit` - Edit existing hotel areas

### Tasks
- `tasks.view` - View tasks list and details
- `tasks.manage` - Create, edit, and delete tasks
- `tasks.edit` - Edit existing tasks

---

## 7. REPORTS MODULE

- `reports.view` - Access all reports (sales, occupancy, stock)

---

## 8. USER & ROLE MANAGEMENT MODULE

### Users
- `users.view` - View list of users
- `users.manage` - Create, edit, and manage users
- `users.edit` - Edit existing users
- `users.activate` - Activate or deactivate user accounts

### Roles
- `roles.view` - View roles list and details
- `roles.manage` - Create, edit roles and assign permissions
- `roles.edit` - Edit existing roles and permissions

---

## 9. ACTIVITY LOGS MODULE

- `activity_logs.view` - View system activity logs and audit trail

---

## Default Role Permissions

### Manager Role
Has access to all hotel operations including:
- All rooms, bookings, POS, stock, payments, reports permissions
- All housekeeping permissions
- User management permissions (view, manage, edit, activate)
- Activity logs view

### Receptionist Role
Has access to:
- View rooms and room types
- View, create, and edit bookings
- View and sell POS
- View and create payments
- View housekeeping records and tasks

### Staff Role
Has access to:
- View and sell POS
- View stock and products

### Admin Role
Has **ALL** permissions (full system access)

---

## Permission Structure

Each module follows this pattern:
- **View** - Read-only access to list and details
- **Manage** - Full CRUD operations (Create, Read, Update, Delete)
- **Edit** - Update existing records (subset of manage)
- **Create** - Create new records (when separate from manage)
- **Delete** - Delete records (when separate from manage)

---

## Notes

- Super admins automatically have all permissions
- Permissions are hotel-scoped (checked against current hotel context)
- Some permissions (like `pos.edit`, `payments.edit`) are available but features are pending implementation
- All routes are protected with appropriate permission middleware
