# Multi-Hotel Management System - Work Plan

## Project Overview
**Technology Stack:** Laravel + MySQL  
**Architecture:** Single Owner → Multiple Hotels with Dynamic RBAC  
**Key Principle:** Strict Hotel Context Enforcement on Every Request

---

## 📋 PHASE 1: FOUNDATION - AUTH + HOTEL SELECTION

### 1.1 Project Setup
- [ ] Initialize Laravel project
- [ ] Configure `.env` with database credentials
- [ ] Install required packages (if any)
- [ ] Set up project structure

### 1.2 Database Schema - Core Tables
- [ ] `users` table
  - id, name, email, password, is_super_admin, timestamps
- [ ] `hotels` table
  - id, name, address, phone, email, owner_id, timestamps
- [ ] `roles` table
  - id, name, slug, description, timestamps
- [ ] `permissions` table
  - id, name, slug, description, timestamps
- [ ] `user_roles` pivot table
  - user_id, role_id, hotel_id, timestamps
- [ ] `role_permissions` pivot table
  - role_id, permission_id, timestamps

### 1.3 Models & Relationships
- [ ] **User Model**
  - `roles()` - belongsToMany with pivot('hotel_id')
  - `isSuperAdmin()` - check method
- [ ] **Hotel Model**
  - `owner()` - belongsTo User
  - `users()` - belongsToMany through roles
- [ ] **Role Model**
  - `permissions()` - belongsToMany
  - `users()` - belongsToMany with pivot('hotel_id')
- [ ] **Permission Model**
  - `roles()` - belongsToMany

### 1.4 Authentication System
- [ ] **Login Controller**
  - Login form with hotel dropdown
  - Validate credentials + hotel access
  - Set session('hotel_id')
  - Redirect to dashboard
- [ ] **Login View**
  - Email input
  - Password input
  - Hotel selection dropdown
  - Error handling

### 1.5 Middleware
- [ ] **HotelContext Middleware**
  - Check if session('hotel_id') exists
  - Abort if no hotel context
  - Make hotel_id available to all requests
- [ ] **HasPermission Middleware**
  - Check user permission for current hotel
  - Support super admin bypass
  - Abort 403 if no permission
- [ ] Register middleware in `Kernel.php`

### 1.6 Routes Setup
- [ ] Public routes (login, logout)
- [ ] Protected routes group with `auth` + `hotel.context` middleware
- [ ] Permission-protected routes with `permission:slug` middleware

---

## 📋 PHASE 2: ROLES & PERMISSIONS (DYNAMIC RBAC)

### 2.1 Permissions Seeder
- [ ] Create `PermissionsSeeder`
  - rooms.manage
  - bookings.create
  - bookings.edit
  - bookings.delete
  - bookings.view
  - pos.sell
  - pos.view
  - stock.manage
  - stock.view
  - payments.create
  - payments.view
  - reports.view
  - users.manage
  - roles.manage

### 2.2 Roles Management
- [ ] **RoleController**
  - CRUD operations for roles
  - Assign permissions to roles
- [ ] **UserRoleController**
  - Assign roles to users (per hotel)
  - Remove roles from users
- [ ] **Views**
  - Role management interface
  - User-role assignment interface

### 2.3 Permission Checking
- [ ] Helper functions for permission checks
- [ ] Blade directives for UI visibility
- [ ] Route middleware integration

---

## 📋 PHASE 3: ROOMS & BOOKINGS (CORE HOTEL LOGIC)

### 3.1 Rooms Module
- [ ] **Room Migration**
  - hotel_id, room_number, room_type, status, floor, capacity, price_per_night, timestamps
- [ ] **Room Model**
  - Fillable fields
  - `bookings()` relationship
  - Hotel scope
- [ ] **RoomController**
  - index, create, store, edit, update, destroy
  - All queries scoped to hotel_id
- [ ] **Room Views**
  - List rooms
  - Create/edit form
  - Room status management

### 3.2 Bookings Module
- [ ] **Booking Migration**
  - hotel_id, room_id, guest_name, guest_email, guest_phone
  - check_in, check_out, adults, children
  - total_amount, status, notes, timestamps
- [ ] **Booking Model**
  - Fillable fields
  - `room()` relationship
  - `payments()` relationship
  - Date casting
- [ ] **BookingController**
  - index, create, store, edit, update, destroy, show
  - Availability validation (CRITICAL)
- [ ] **Booking Views**
  - List bookings
  - Create/edit form with date pickers
  - Booking details view

### 3.3 Booking Availability Validation
- [ ] **Comprehensive Overlap Check**
  - Check-in between existing booking
  - Check-out between existing booking
  - Booking completely contains another
  - All edge cases covered
- [ ] **Validation Rules**
  - Custom validation rule for availability
  - Error messages

### 3.4 Calendar View
- [ ] **Calendar Controller**
  - Monthly view endpoint
  - Filter by room (optional)
- [ ] **Calendar View**
  - FullCalendar integration OR custom grid
  - Color coding by room/status
  - Click to view/edit booking
  - Navigation (prev/next month)

---

## 📋 PHASE 4: POS & EXTRAS

### 4.1 Extras Module
- [ ] **Extra Migration**
  - hotel_id, name, description, price, category
  - stock_tracked (boolean), current_stock, min_stock
  - is_active, timestamps
- [ ] **Extra Model**
  - Fillable fields
  - `posSales()` relationship
  - `stockMovements()` relationship
- [ ] **ExtraController**
  - CRUD operations
  - Category management
- [ ] **Extra Views**
  - List extras by category
  - Create/edit form

### 4.2 POS Sales Module
- [ ] **PosSale Migration**
  - hotel_id, room_id (nullable), sale_date
  - total_amount, discount, final_amount
  - payment_status, notes, timestamps
- [ ] **PosSaleItem Migration**
  - pos_sale_id, extra_id, quantity, unit_price, subtotal
- [ ] **PosSale Model**
  - Fillable fields
  - `items()` relationship
  - `room()` relationship (nullable)
  - `payments()` relationship
- [ ] **PosSaleController**
  - create, store, show, index
  - Calculate totals
  - Stock reduction logic
- [ ] **POS Views**
  - POS interface (cart-based)
  - Select extras
  - Optional room attachment
  - Payment processing

---

## 📋 PHASE 5: STOCK MANAGEMENT

### 5.1 Stock Movements
- [ ] **StockMovement Migration**
  - hotel_id, product_id (extra_id), type (in/out)
  - quantity, reference_type, reference_id
  - notes, created_by, timestamps
- [ ] **StockMovement Model**
  - Fillable fields
  - `product()` relationship
  - `user()` relationship
- [ ] **StockMovementController**
  - Manual stock adjustments
  - Stock in/out operations
- [ ] **Stock Movement Views**
  - List movements
  - Add stock form
  - Remove stock form

### 5.2 Stock Balance Logic
- [ ] **Computed Stock Balance**
  - Helper method: getStockBalance($productId, $hotelId)
  - Sum of 'in' movements
  - Subtract sum of 'out' movements
  - No hardcoded stock column
- [ ] **Stock Alerts**
  - Low stock detection
  - Out of stock detection

---

## 📋 PHASE 6: PAYMENTS

### 6.1 Payments Module
- [ ] **Payment Migration**
  - hotel_id, booking_id (nullable), pos_sale_id (nullable)
  - amount, payment_method (cash/card/transfer)
  - reference_number, paid_at, notes, timestamps
- [ ] **Payment Model**
  - Fillable fields
  - `booking()` relationship (nullable)
  - `posSale()` relationship (nullable)
- [ ] **PaymentController**
  - create, store, index
  - Filter by booking/POS sale
- [ ] **Payment Views**
  - Payment form
  - Payment history

### 6.2 Payment Logic
- [ ] **Outstanding Balance Calculation**
  - For bookings: total_amount - sum(payments)
  - For POS sales: final_amount - sum(payments)
- [ ] **Payment Status**
  - Auto-update booking/POS payment status
  - Fully paid / Partial / Unpaid

---

## 📋 PHASE 7: REPORTS (MANAGEMENT)

### 7.1 Daily Sales Report
- [ ] **ReportController - Sales**
  - Daily sales (POS + Bookings)
  - Date range filtering
  - Group by date
  - Total calculations
- [ ] **Sales Report View**
  - Table/graph visualization
  - Export functionality (optional)

### 7.2 Occupancy Report
- [ ] **ReportController - Occupancy**
  - Occupied rooms count
  - Available rooms count
  - Occupancy rate calculation
  - Date range filtering
- [ ] **Occupancy Report View**
  - Visual dashboard
  - Trends over time

### 7.3 Stock Reports
- [ ] **ReportController - Stock**
  - Low stock items (below min_stock)
  - Fast-moving items (top sellers)
  - Stock value calculation
- [ ] **Stock Report View**
  - Alert list
  - Analytics table

---

## 📋 PHASE 8: ACTIVITY LOGS (SECURITY)

### 8.1 Activity Logs Module
- [ ] **ActivityLog Migration**
  - user_id, hotel_id, action, model_type, model_id
  - old_values (JSON), new_values (JSON)
  - ip_address, user_agent, timestamps
- [ ] **ActivityLog Model**
  - Fillable fields
  - `user()` relationship
  - `hotel()` relationship
- [ ] **ActivityLogController**
  - index (filtered by hotel)
  - show details
- [ ] **Activity Log Views**
  - Log list with filters
  - Detailed log view

### 8.2 Log Helper & Integration
- [ ] **Create logActivity() Helper**
  - Accept action, model, old/new values
  - Auto-capture user, hotel, IP
- [ ] **Integrate Logging**
  - Login/logout
  - Booking create/edit/delete
  - POS sale create
  - Payment create
  - Stock adjustments
  - User/role changes

---

## 📋 PHASE 9: UI/UX & POLISH

### 9.1 Dashboard
- [ ] **DashboardController**
  - Key metrics (occupancy, today's sales, low stock alerts)
  - Recent bookings
  - Recent POS sales
- [ ] **Dashboard View**
  - Modern card-based layout
  - Charts/graphs
  - Hotel context indicator
  - Quick actions

### 9.2 Navigation & Layout
- [ ] **Main Layout**
  - Sidebar navigation
  - Permission-based menu items
  - Hotel selector (if user has multiple hotels)
  - User profile dropdown
- [ ] **Responsive Design**
  - Mobile-friendly
  - Tablet optimization

### 9.3 Forms & Validation
- [ ] **Form Components**
  - Reusable form components
  - Date pickers
  - Select dropdowns
  - File uploads (if needed)
- [ ] **Validation Messages**
  - User-friendly error messages
  - Success notifications

### 9.4 Testing & Security
- [ ] **Security Checks**
  - CSRF protection
  - XSS prevention
  - SQL injection prevention (Eloquent)
  - Hotel context isolation testing
- [ ] **Basic Testing**
  - Test hotel context enforcement
  - Test permission checks
  - Test booking availability

---

## 📋 PHASE 10: FINALIZATION

### 10.1 Documentation
- [ ] **README.md**
  - Installation instructions
  - Configuration guide
  - User manual
- [ ] **API Documentation** (if needed)
- [ ] **Database Schema Diagram**

### 10.2 Deployment Preparation
- [ ] **Environment Configuration**
  - Production .env setup
  - Database optimization
- [ ] **Seeders**
  - Default admin user
  - Sample hotels
  - Default roles and permissions

---

## 🔑 Key Implementation Principles

1. **Hotel Context First**: Every query must include `where('hotel_id', session('hotel_id'))`
2. **Permission Checks**: All routes protected by appropriate permissions
3. **Super Admin Bypass**: Super admins can access all hotels
4. **No Hardcoded Values**: Use computed values (stock balance, outstanding payments)
5. **Activity Logging**: Log all critical actions for audit trail
6. **Validation**: Comprehensive validation, especially booking availability
7. **User Experience**: Clear hotel context indicator, intuitive navigation

---

## 📊 Database Relationships Summary

```
User
  ├── belongsToMany Role (via user_roles with hotel_id pivot)
  └── hasMany Hotel (as owner)

Hotel
  ├── belongsTo User (owner)
  └── hasMany (Room, Booking, Extra, PosSale, Payment, etc.)

Role
  ├── belongsToMany Permission (via role_permissions)
  └── belongsToMany User (via user_roles with hotel_id pivot)

Room
  ├── belongsTo Hotel
  └── hasMany Booking

Booking
  ├── belongsTo Hotel
  ├── belongsTo Room
  └── hasMany Payment

Extra
  ├── belongsTo Hotel
  ├── hasMany PosSaleItem
  └── hasMany StockMovement

PosSale
  ├── belongsTo Hotel
  ├── belongsTo Room (nullable)
  ├── hasMany PosSaleItem
  └── hasMany Payment

Payment
  ├── belongsTo Hotel
  ├── belongsTo Booking (nullable)
  └── belongsTo PosSale (nullable)

StockMovement
  ├── belongsTo Hotel
  ├── belongsTo Extra (product)
  └── belongsTo User (created_by)

ActivityLog
  ├── belongsTo User
  └── belongsTo Hotel
```

---

## 🚀 Development Order

1. **Foundation First**: Setup → Auth → Middleware → RBAC
2. **Core Features**: Rooms → Bookings → Calendar
3. **Business Logic**: POS → Stock → Payments
4. **Management**: Reports → Logs
5. **Polish**: UI/UX → Testing → Documentation

---

**Estimated Timeline**: 4-6 weeks (depending on team size and complexity)

**Priority**: Start with Phase 1-3 (Foundation + Core) for MVP, then add features incrementally.

