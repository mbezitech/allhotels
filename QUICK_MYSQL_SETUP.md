# Quick MySQL Setup Guide

## Quick Setup (Automated)

Run the setup script:
```bash
./setup-mysql.sh
```

The script will:
1. Ask for MySQL credentials
2. Update your .env file
3. Create the database
4. Run migrations
5. Optionally seed the database

## Manual Setup

### 1. Update .env File

Edit `.env` and change:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=allhotels
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE allhotels CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 3. Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

## Default Configuration

The application is now configured to use MySQL by default. The `config/database.php` has been updated to default to `mysql` instead of `sqlite`.

## Notes

- Make sure MySQL is running before running migrations
- The database name `allhotels` is just a suggestion - use any name you prefer
- All migrations are compatible with MySQL
- Foreign key constraints are enabled by default

