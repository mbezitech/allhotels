# MySQL Database Setup

## Step 1: Update .env File

Update your `.env` file with MySQL configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=allhotels
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

Replace:
- `DB_DATABASE=allhotels` with your desired database name
- `DB_USERNAME=root` with your MySQL username
- `DB_PASSWORD=your_password_here` with your MySQL password

## Step 2: Create MySQL Database

### Option A: Using MySQL Command Line

```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE allhotels CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Option B: Using Laravel Artisan (if you have database access)

The migrations will create the database automatically if your MySQL user has CREATE DATABASE privileges.

## Step 3: Run Migrations

```bash
php artisan migrate
```

## Step 4: Seed Database (Optional)

```bash
php artisan db:seed
```

This will populate:
- Permissions
- Roles
- Role-Permission relationships

## Verification

Test the connection:
```bash
php artisan tinker
```

Then in tinker:
```php
DB::connection()->getPdo();
// Should return PDO object without errors
```

## Troubleshooting

### Connection Refused
- Check MySQL is running: `mysql.server start` (Mac) or `sudo systemctl start mysql` (Linux)
- Verify host and port in .env

### Access Denied
- Check username and password in .env
- Verify MySQL user has privileges: `GRANT ALL PRIVILEGES ON allhotels.* TO 'username'@'localhost';`

### Database Doesn't Exist
- Create it manually using Option A above
- Or ensure your MySQL user has CREATE DATABASE privileges

