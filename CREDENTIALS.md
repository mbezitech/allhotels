# Default Login Credentials

## Super Admin
- **Email:** `admin@hotels.com`
- **Password:** `admin123`
- **Access:** Full system access, can manage all hotels, users, and settings
- **Note:** Can login without selecting a hotel (global access)

## Hotel Owner
- **Email:** `owner@hotels.com`
- **Password:** `password`
- **Access:** Admin access to all hotels owned by this user
- **Note:** Must select a hotel when logging in

---

## Security Notes

⚠️ **IMPORTANT:** These are default credentials for initial setup. Please:

1. **Change passwords immediately** after first login
2. **Create new users** with strong passwords for production use
3. **Disable or delete** default accounts if not needed
4. **Use strong passwords** (minimum 8 characters, mix of letters, numbers, and symbols)

---

## Creating New Users

Super admins can create new users by:
1. Logging in as super admin
2. Going to **Administration → Users**
3. Clicking **"Add User"**
4. Filling in the user details

---

## Resetting Passwords

Super admins can reset any user's password by:
1. Going to **Administration → Users**
2. Clicking **"Edit"** on the user
3. Entering a new password (leave blank to keep current password)
4. Saving changes

---

## User Types

### Super Admin
- Full system access
- Can manage all hotels
- Can create/edit/delete users
- Can access all hotels without selection
- Can view activity logs across all hotels

### Hotel Owner
- Admin access to owned hotels
- Can manage rooms, bookings, staff, etc. for their hotels
- Must select a hotel when logging in

### Regular User
- Access based on assigned roles and permissions
- Scoped to specific hotels
- Must select a hotel when logging in


