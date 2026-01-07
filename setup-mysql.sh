#!/bin/bash

# MySQL Database Setup Script for AllHotels

echo "========================================="
echo "AllHotels MySQL Database Setup"
echo "========================================="
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "Error: .env file not found!"
    echo "Please copy .env.example to .env first:"
    echo "  cp .env.example .env"
    echo "  php artisan key:generate"
    exit 1
fi

# Get database details
read -p "Enter MySQL host [127.0.0.1]: " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

read -p "Enter MySQL port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Enter MySQL username [root]: " DB_USERNAME
DB_USERNAME=${DB_USERNAME:-root}

read -s -p "Enter MySQL password: " DB_PASSWORD
echo ""

read -p "Enter database name [allhotels]: " DB_DATABASE
DB_DATABASE=${DB_DATABASE:-allhotels}

echo ""
echo "Updating .env file..."

# Update .env file
sed -i.bak "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i.bak "s/^# DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i.bak "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i.bak "s/^# DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i.bak "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i.bak "s/^# DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
sed -i.bak "s/^DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
sed -i.bak "s/^# DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
sed -i.bak "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
sed -i.bak "s/^# DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
sed -i.bak "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

# Remove backup file
rm -f .env.bak

echo "✓ .env file updated"
echo ""

# Test MySQL connection
echo "Testing MySQL connection..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ MySQL connection successful"
    echo ""
    
    # Create database
    echo "Creating database '$DB_DATABASE'..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✓ Database created successfully"
        echo ""
        echo "Running migrations..."
        php artisan migrate --force
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "✓ Migrations completed"
            echo ""
            read -p "Do you want to seed the database? (y/n) [y]: " SEED_DB
            SEED_DB=${SEED_DB:-y}
            
            if [ "$SEED_DB" = "y" ] || [ "$SEED_DB" = "Y" ]; then
                echo "Seeding database..."
                php artisan db:seed --force
                echo "✓ Database seeded"
            fi
            
            echo ""
            echo "========================================="
            echo "Setup Complete!"
            echo "========================================="
            echo ""
            echo "Database: $DB_DATABASE"
            echo "Host: $DB_HOST:$DB_PORT"
            echo "Username: $DB_USERNAME"
            echo ""
            echo "You can now start the application:"
            echo "  php artisan serve"
        else
            echo "✗ Migration failed. Please check the errors above."
            exit 1
        fi
    else
        echo "✗ Failed to create database. Please check MySQL credentials."
        exit 1
    fi
else
    echo "✗ MySQL connection failed. Please check your credentials."
    echo ""
    echo "You can manually:"
    echo "1. Update .env file with correct MySQL settings"
    echo "2. Create database: CREATE DATABASE $DB_DATABASE;"
    echo "3. Run: php artisan migrate"
    exit 1
fi

