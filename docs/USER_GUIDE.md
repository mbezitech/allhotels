# AllHotels — User Documentation Guide

This guide explains how to use the AllHotels hotel management system. It is intended for staff and administrators who work with bookings, payments, rooms, POS, and day-to-day operations.

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started](#2-getting-started)
3. [Dashboard](#3-dashboard)
4. [Rooms & Bookings](#4-rooms--bookings)
5. [Housekeeping](#5-housekeeping)
6. [Products & POS](#6-products--pos)
7. [Financial](#7-financial)
8. [Reports](#8-reports)
9. [Administration](#9-administration)
10. [User Management](#10-user-management)
11. [System](#11-system)
12. [Public Booking (Guests)](#12-public-booking-guests)
13. [Tips & Reference](#13-tips--reference)

---

## 1. Introduction

AllHotels is a web-based hotel management system that supports:

- **Multiple hotels** — Super admins can manage several properties; staff see only their assigned hotel(s).
- **Bookings** — Create, edit, check-in, check-out, and view bookings on a calendar.
- **Guest billing** — Attach POS charges (products/services) to a guest’s room and booking; checkout is blocked until the balance is paid.
- **Payments & expenses** — Record payments and expenses with filters and audit trails.
- **Rooms & room types** — Manage rooms and room types per hotel.
- **Housekeeping** — Cleaning records, areas, tasks, and reports.
- **Roles & permissions** — Control who can see and do what in each hotel.

Each hotel has its own **timezone** setting. When you select a hotel at login (or switch hotel), all dates and times in the app are shown in that hotel’s timezone. Super admins set the timezone when creating or editing a hotel (default is Africa/Nairobi).

---

## 2. Getting Started

### 2.1 Logging In

1. Open the application URL in your browser.
2. On the login page:
   - Enter your **email** and **password**.
   - Choose your **hotel** from the dropdown.  
     Only **active** hotels appear; disabled hotels are hidden.
3. Click **Login**.

- If you are a **super admin**, you can select any active hotel or work in “Super Admin Mode” (no specific hotel).
- If you are a **regular user**, you must select a hotel you have access to. You cannot log in to a disabled hotel.

### 2.2 After Login

- The **sidebar** on the left shows the main menu. You only see items you have permission for.
- The **top bar** shows the current page title and your name; you can switch hotel (if allowed) or log out.
- **Mobile:** Use the **☰** menu button to open or close the sidebar.

### 2.3 Sidebar and Mobile

- **Collapsible sections:** Click a section title (e.g. “Rooms & Bookings”, “Financial”) to expand or collapse it.
- **Mobile:** Tap the hamburger icon (☰) to open the sidebar; tap outside to close it.

### 2.4 Profile

- Use **Profile** (from the user menu or sidebar if available) to view and edit your own profile. You cannot disable your own user account.

---

## 3. Dashboard

The dashboard gives a quick overview of the current hotel (or all hotels for super admins).

You typically see:

- **Today’s check-ins** — Guests due to check in today.
- **Today’s check-outs** — Guests due to check out or who have already checked out today.
- **Occupancy** — Current occupancy or key metrics.
- **Other widgets** — Depends on your role and configuration.

Use the dashboard to plan the day and spot upcoming arrivals and departures.

---

## 4. Rooms & Bookings

### 4.1 Link References

- **Link References** is a reference list used in the system (e.g. for linking or categorising). Use it as instructed by your administrator.

### 4.2 Rooms

- **Rooms** lists all rooms for the current hotel.
- You can **add**, **edit**, **view**, and **delete** rooms (if you have permission).
- **Soft delete:** “Delete” hides the room from normal lists; you can **View Deleted** and **Restore** or **Permanently Delete** if your role allows.
- Rooms are linked to **room types** and **bookings**; ensure rooms are set up before creating bookings.

### 4.3 Room Types

- **Room Types** define categories (e.g. Single, Double, Suite) and usually include name, capacity, and rate.
- Manage room types under **Rooms & Bookings → Room Types** (if you have “manage” permission).

### 4.4 Bookings

**List**

- **Bookings** shows all bookings for the current hotel. Use filters (dates, status, room, etc.) to find specific bookings.

**Create booking**

1. Go to **Bookings** and click **Create** (or equivalent).
2. Fill in:
   - **Guest name**
   - **Contact:** Use the **country code** search (type country name or code, e.g. “US”, “+1”, “United States”) and enter the **phone number**. The system validates the number for the selected country.
   - **Check-in / Check-out** dates
   - **Room** and any other required fields (e.g. room type, number of guests, notes).
3. Save the booking.

**Edit booking**

- Open the booking and use **Edit** to change details. The same country code search and phone validation apply.

**Booking statuses**

- Bookings can be **pending payment**, **paid in full**, **checked in**, or **checked out**, etc. The exact labels are shown in the list and on the **Booking Calendar**.

**Check-in / Check-out**

- From the booking detail page, use **Check-in** when the guest arrives and **Check-out** when they leave.
- **Checkout is blocked** if there is any **outstanding balance** (room balance or unpaid POS charges). You must record payments until the balance is zero before checkout is allowed. The system shows the pending amount.

### 4.5 Booking Calendar

- **Bookings → Calendar** (or the calendar link) shows a **calendar view** of bookings.
- You can see:
  - **Pending payment**
  - **Paid in full**
  - **Currently checked in**
  - **POS outstanding** (e.g. 🛒) when the booking has unpaid POS charges
- Each entry can show **guest name**, **room number**, and **room type** for quick identification.
- Use the calendar to plan housekeeping and see occupancy at a glance.

---

## 5. Housekeeping

Available if you have housekeeping permissions.

- **Cleaning Records** — Create and manage cleaning records for rooms (e.g. status, assignee, time).
- **Hotel Areas** — Define areas/zones used for housekeeping.
- **Reports** — Housekeeping reports and summaries.
- **Issues & Resolutions** — Log and resolve housekeeping issues (if enabled).
- **Tasks** — Assign and track tasks; assignees are limited to **users who belong to the current hotel**.

---

## 6. Products & POS

### 6.1 Products (Extras)

- **Products** are sellable items or services (e.g. minibar, laundry, breakfast).
- You can **add**, **edit**, **view**, and **delete** products. Deleted products are soft-deleted; you can **View Deleted**, **Restore**, or **Permanently Delete** (if allowed). Products that are used in sales may not be permanently deletable.
- **Product Categories** — Organise products into categories (if you have stock/category permission).

### 6.2 POS Sales

- **POS Sales** is where you record sales of products/services.
- **New sale:**
  1. Go to **POS Sales** and create a new sale.
  2. Choose **room** (optional) and **Attach to Guest Booking**.
  3. **Attach to Guest Booking** only lists bookings that are **currently checked in**. Confirmed or checked-out bookings do not appear.
  4. Add items (products/extras), quantities, and amounts. Save the sale.

- Sales attached to a **booking** are included in that guest’s **outstanding balance**. Until this balance is paid, **checkout is blocked** for that booking.

### 6.3 Stock Movements

- **Stock Movements** — Record stock in/out for products if your hotel uses inventory. Available to users with stock permission.

---

## 7. Financial

### 7.1 Payments

- **Payments** lists all payments for the current hotel.
- **Filters:** You can filter by hotel, payment type, payment method, date range, amount range, and search to find specific transactions.
- **Create payment:** Record a payment (e.g. against a booking) to reduce outstanding balance. Paying off room and POS charges allows the guest to check out.
- **Soft delete:** Deleted payments can be hidden; use **View Deleted** to see them, then **Restore** or **Permanently Delete** if your role allows.

### 7.2 Expenses

- **Expenses** — Record and track expenses for the hotel.
- You can create, edit, view, and delete expenses. Deleted expenses are soft-deleted; **View Deleted**, **Restore**, and **Permanently Delete** behave like in other modules.
- **Expense Categories** and **Expense Reports** may be available depending on permissions.

---

## 8. Reports

- **Reports** (under the Reports section) provide summaries and reports as configured for your role. Use them for occupancy, revenue, or other analytics.

---

## 9. Administration

### 9.1 Users

- **Users** lists only **users who belong to the current hotel** (via roles or as hotel owner). You do not see users from other hotels.
- You can:
  - **Create** users (if allowed).
  - **Edit** user details and **Enable/Disable** a user.  
    **You cannot disable your own account;** the option is hidden or blocked.
  - **Delete** (soft delete) users. Then use **View Deleted** to **Restore** or **Permanently Delete** (subject to permissions and rules, e.g. no permanent delete of yourself or super admins in some setups).

### 9.2 Hotels (Super Admin only)

- **Hotels** is available only to **super admins**.
- You can create, edit, and list hotels.
- **Timezone:** Each hotel has a **timezone** (e.g. Africa/Nairobi, America/New_York). Set it when creating or editing a hotel. When staff work with that hotel selected, all dates and times are shown in this timezone.
- **Enable/Disable:** You can **enable** or **disable** a hotel. **Disabled hotels** do not appear in the login dropdown and are not accessible to regular users.
- **Deletion:** **A hotel that has any bookings cannot be deleted.** The system blocks deletion and shows an error. Disable the hotel instead if you want to hide it.

---

## 10. User Management

### 10.1 Roles

- **Roles** define permissions (e.g. rooms.view, payments.view, users.manage). Only users with role management permission can create or edit roles.

### 10.2 User Roles

- **User Roles** is where you **assign roles to users** for a hotel. The user list here shows only **users who belong to the current hotel**, so you assign roles in the correct context.

---

## 11. System

### 11.1 Email Settings

- **Email Settings** (if visible) let you configure email-related options for the hotel.

### 11.2 Activity Logs

- **Activity Logs** show an audit trail of actions in the system (e.g. who created/updated/deleted what and when). You can filter by user, hotel, date, or action. Users in the filter list are scoped to the selected hotel (or all non–super-admin users when no hotel is selected).

---

## 12. Public Booking (Guests)

Guests can book directly on the **public booking** page without logging in.

- They typically:
  1. Search or select the hotel (e.g. by slug/URL).
  2. Choose a room and dates.
  3. Enter guest name, **country code** (via the same search: country name or code), and **phone number** (with country-specific validation).
  4. Submit the booking and receive a confirmation (e.g. booking reference).

Staff see these bookings in the admin **Bookings** list and can check them in, add POS charges, and receive payments as usual.

---

## 13. Tips & Reference

### 13.1 Country Code and Phone (Admin & Public Forms)

- Use the **country code** field to search by country name or code (e.g. “Kenya”, “+254”, “US”).
- Select the correct country from the dropdown; the **phone number** is validated for that country (length and format).
- The country input is compact; the phone number field is larger for easier typing.

### 13.2 POS and Checkout

- POS charges attached to a **checked-in** booking increase the guest’s **outstanding balance**.
- **Checkout is blocked** until the full balance (room + POS) is paid. Clear the balance via **Payments**, then perform **Check-out**.

### 13.3 Soft Delete (Users, Payments, Expenses, Rooms, Products)

- Many modules use **soft delete**: “Delete” hides the record but does not remove it from the database.
- Use **View Deleted** (or similar) to list deleted records. From there you can **Restore** or **Permanently Delete** (where allowed).
- Permanently deleting may be blocked in some cases (e.g. rooms with bookings, products used in sales, your own user).

### 13.4 Filters

- **Payments** and other list pages offer **filters** (date, type, method, amount, search). Use them to narrow results and export or review specific data.

### 13.5 Timezone

- Each **hotel has its own timezone** (set by a super admin in Hotels → Edit). When you are working with a hotel selected, all dates and times are displayed in that hotel’s timezone. If no hotel is selected (e.g. super admin without a hotel), the default application timezone (Africa/Nairobi) is used.

### 13.6 Permissions

- If you do not see a menu item or button, your role may not have the required permission. Contact your administrator to get the right role or permissions for your hotel.

---

## Quick Reference — Main Menu (sidebar)

| Section            | Items you may see                                      |
|--------------------|--------------------------------------------------------|
| **Dashboard**      | Dashboard                                              |
| **Rooms & Bookings** | Link References, Rooms, Room Types, Bookings        |
| **Housekeeping**   | Cleaning Records, Hotel Areas, Reports, Issues, Tasks  |
| **Products & POS** | Products, Product Categories, POS Sales, Stock Movements |
| **Financial**      | Payments, Expenses, Expense Categories, Expense Reports |
| **Reports**        | Reports                                                |
| **Administration** | Users, Hotels (super admin only)                       |
| **User Management**| Roles, User Roles                                      |
| **System**         | Email Settings, Activity Logs                         |

---

*For technical setup, deployment, or developer information, refer to the project README and Laravel documentation.*
