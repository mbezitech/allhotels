# Multi-Hotel Management System - Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    USER INTERFACE LAYER                      │
│  (Login → Hotel Selection → Dashboard → Modules)            │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Auth       │→ │ Hotel Context│→ │  Permission  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    CONTROLLER LAYER                          │
│  (All queries automatically scoped to session('hotel_id'))  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    MODEL LAYER                               │
│  (Relationships, Scopes, Business Logic)                    │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE LAYER                            │
│  (MySQL - All tables include hotel_id for isolation)       │
└─────────────────────────────────────────────────────────────┘
```

## Authentication Flow

```
┌─────────┐
│  Login  │
│  Page   │
└────┬────┘
     │
     │ User enters: email + password + selects hotel
     ▼
┌─────────────────────┐
│  Login Controller   │
│  1. Validate creds  │
│  2. Check hotel     │
│     access          │
│  3. Set session     │
│     hotel_id        │
└────┬────────────────┘
     │
     │ Success
     ▼
┌─────────────────────┐
│   Dashboard         │
│  (Hotel Context     │
│   Active)           │
└─────────────────────┘
```

## Hotel Context Enforcement

```
Every Request
     │
     ▼
┌─────────────────────┐
│  HotelContext       │
│  Middleware         │
│                     │
│  Check:             │
│  session('hotel_id')│
└────┬────────────────┘
     │
     │ Exists?
     ▼
┌─────────────────────┐
│  Continue           │
│  (hotel_id available│
│   to all queries)   │
└─────────────────────┘
     │
     │ Missing?
     ▼
┌─────────────────────┐
│  Abort 403          │
│  (No hotel context) │
└─────────────────────┘
```

## Permission Check Flow

```
Route Request
     │
     ▼
┌─────────────────────┐
│  HasPermission      │
│  Middleware         │
│                     │
│  Check:             │
│  - Is super admin?  │
│    → Allow          │
│  - Has permission   │
│    for hotel?       │
│    → Allow          │
│  - Else → Deny      │
└────┬────────────────┘
     │
     │ Allowed
     ▼
┌─────────────────────┐
│  Controller Action  │
└─────────────────────┘
```

## Database Schema Overview

### Core Tables
- `users` - System users
- `hotels` - Hotel properties
- `roles` - Role definitions
- `permissions` - Permission definitions
- `user_roles` - User-role assignments (with hotel_id)
- `role_permissions` - Role-permission mappings

### Business Tables
- `rooms` - Hotel rooms
- `bookings` - Room reservations
- `extras` - POS items (bar, pool, restaurant)
- `pos_sales` - Point of sale transactions
- `pos_sale_items` - POS sale line items
- `payments` - Payment records
- `stock_movements` - Stock tracking
- `activity_logs` - Audit trail

## Key Relationships

### User → Hotel Access
```
User
  └── belongsToMany Role
        └── via user_roles (pivot: hotel_id)
            └── Role has Permissions
```

### Hotel → All Data
```
Hotel
  ├── hasMany Room
  ├── hasMany Booking
  ├── hasMany Extra
  ├── hasMany PosSale
  ├── hasMany Payment
  ├── hasMany StockMovement
  └── hasMany ActivityLog
```

## Data Isolation Strategy

**Every table includes `hotel_id`:**
- Ensures complete data isolation
- All queries automatically filtered
- No cross-hotel data leakage
- Easy to add new modules

**Query Pattern:**
```php
Model::where('hotel_id', session('hotel_id'))
    ->where(...)
    ->get();
```

## Security Layers

1. **Authentication Layer**
   - Laravel's built-in auth
   - Custom hotel access validation

2. **Authorization Layer**
   - Role-based access control
   - Permission-based route protection
   - Hotel context enforcement

3. **Data Layer**
   - Hotel-scoped queries
   - Middleware enforcement
   - Model scopes

4. **Audit Layer**
   - Activity logging
   - User tracking
   - Change history

## Module Dependencies

```
Foundation (Phase 1)
  ├── Auth System
  ├── Hotel Context
  └── RBAC
       │
       ├── Rooms & Bookings (Phase 3)
       │     └── Calendar View
       │
       ├── POS & Extras (Phase 4)
       │     ├── Stock Management (Phase 5)
       │     └── Payments (Phase 6)
       │
       ├── Reports (Phase 7)
       │     └── Uses: Bookings, POS, Stock
       │
       └── Activity Logs (Phase 8)
             └── Tracks all modules
```

## Session Management

**Session Variables:**
- `hotel_id` - Current active hotel context
- `user_id` - Authenticated user (via Laravel auth)

**Session Lifecycle:**
1. User logs in → `hotel_id` set
2. All subsequent requests use `hotel_id`
3. User can switch hotels (if has access) → update `hotel_id`
4. User logs out → session cleared

## API Endpoints Structure (if needed)

```
/api/auth/login
/api/auth/logout

/api/hotels/{hotel}/rooms
/api/hotels/{hotel}/bookings
/api/hotels/{hotel}/pos-sales
/api/hotels/{hotel}/payments
/api/hotels/{hotel}/reports
```

## Error Handling Strategy

1. **Hotel Context Missing**
   - HTTP 403 Forbidden
   - Redirect to hotel selection

2. **Permission Denied**
   - HTTP 403 Forbidden
   - Show permission error message

3. **Validation Errors**
   - HTTP 422 Unprocessable Entity
   - Return validation messages

4. **Not Found**
   - HTTP 404 Not Found
   - Check hotel context first

## Performance Considerations

1. **Database Indexing**
   - Index on `hotel_id` in all tables
   - Composite indexes for common queries
   - Index on `user_roles` (user_id, hotel_id, role_id)

2. **Query Optimization**
   - Eager loading relationships
   - Use scopes for hotel filtering
   - Cache permissions (optional)

3. **Session Management**
   - Store minimal data in session
   - Use database sessions for scalability

## Scalability Path

1. **Current**: Single server, single database
2. **Future**: 
   - Database sharding by hotel
   - Redis for session/cache
   - Queue system for heavy operations
   - API versioning

