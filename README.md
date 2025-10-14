# RSC Management System

Sistem manajemen gym/fitness center yang dibangun dengan Laravel 12, menyediakan fitur lengkap untuk mengelola member, absensi, pembayaran, dan laporan.

## 🚀 Fitur Utama

### 👥 Manajemen Member
- ✅ Registrasi member dengan auto-generate kode unik
- ✅ Pembayaran membership otomatis (1, 3, 6, 12 bulan)
- ✅ Auto-expire membership berdasarkan tanggal kedaluwarsa
- ✅ Status member (Aktif/Nonaktif) dengan validasi otomatis

### 📊 Sistem Absensi
- ✅ Check-in/Check-out member dengan timestamp
- ✅ Filter absensi berdasarkan tanggal (hari ini, kemarin, tanggal spesifik)
- ✅ Export data absensi ke CSV
- ✅ Pencarian member untuk check-in
- ✅ Auto check-out member yang sudah check-in lebih dari 5 jam

### 💰 Manajemen Pembayaran
- ✅ Multiple metode pembayaran (Cash, Transfer, E-Wallet)
- ✅ Auto-calculate harga berdasarkan durasi membership
- ✅ Riwayat pembayaran lengkap per member

### 📈 Dashboard & Laporan
- ✅ Statistik member aktif, absensi, dan revenue
- ✅ Grafik pertumbuhan member
- ✅ Aktivitas log sistem

## 🛠️ Teknologi

- **Framework**: Laravel 12
- **PHP**: 8.3.16
- **Database**: MySQL/PostgreSQL
- **Frontend**: Blade + Tailwind CSS v4
- **Assets**: Vite
- **Testing**: PHPUnit

## 📋 Persyaratan Sistem

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Web Server (Apache/Nginx)

## 🔧 Instalasi Development

### 1. Clone Repository
```bash
git clone <repository-url>
cd rsc-msystem-blade
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rsc_management
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 5. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 🚀 Deployment Production

### 1. Server Requirements

#### Minimal Requirements:
- **CPU**: 1 core
- **RAM**: 2GB
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04+ / CentOS 8+

#### Recommended:
- **CPU**: 2+ cores
- **RAM**: 4GB+
- **Storage**: 50GB+ SSD

### 2. Server Setup

#### Install Dependencies
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Install Nginx
sudo apt install nginx
```

### 3. Application Deployment

#### Clone & Setup
```bash
# Create application directory
sudo mkdir -p /var/www/rsc-management
cd /var/www/rsc-management

# Clone repository
sudo git clone <repository-url> .

# Set permissions
sudo chown -R www-data:www-data /var/www/rsc-management
sudo chmod -R 755 /var/www/rsc-management
sudo chmod -R 775 /var/www/rsc-management/storage
sudo chmod -R 775 /var/www/rsc-management/bootstrap/cache
```

#### Install Dependencies
```bash
# Install PHP dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
sudo -u www-data npm install

# Build production assets
sudo -u www-data npm run build
```

#### Environment Configuration
```bash
# Copy environment file
sudo cp .env.example .env

# Edit environment
sudo nano .env
```

**Production .env Configuration:**
```env
APP_NAME="RSC Management System"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rsc_management
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

#### Database Setup
```bash
# Generate application key
sudo -u www-data php artisan key:generate

# Run migrations
sudo -u www-data php artisan migrate --force

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 4. Web Server Configuration

#### Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/rsc-management
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/rsc-management/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/rsc-management /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 5. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Scheduled Tasks Setup

#### Setup Cron Job
```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/rsc-management && php artisan schedule:run >> /dev/null 2>&1
```

#### Verify Scheduled Tasks
```bash
# List scheduled tasks
sudo -u www-data php artisan schedule:list

# Test membership expiration command
sudo -u www-data php artisan memberships:expire --dry-run

# Test auto check-out command
sudo -u www-data php artisan attendance:auto-checkout --dry-run

# Test with custom hours
sudo -u www-data php artisan attendance:auto-checkout --hours=3 --dry-run
```

### 7. Database Optimization

#### MySQL Configuration
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M
query_cache_type = 1
```

```bash
# Restart MySQL
sudo systemctl restart mysql
```

### 8. Monitoring & Logging

#### Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/laravel
```

```
/var/www/rsc-management/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo systemctl reload php8.3-fpm
    endscript
}
```

#### Setup Monitoring
```bash
# Install htop for monitoring
sudo apt install htop

# Monitor application logs
sudo tail -f /var/www/rsc-management/storage/logs/laravel.log

# Monitor system resources
htop
```

## 🤖 Automated Tasks

### Membership Expiration
- **Command**: `php artisan memberships:expire`
- **Schedule**: Daily at 00:00
- **Function**: Auto-expire memberships that have passed their expiration date
- **Dry Run**: `php artisan memberships:expire --dry-run`

### Auto Check-Out
- **Command**: `php artisan attendance:auto-checkout`
- **Schedule**: Every hour
- **Function**: Auto check-out members who have been checked in for more than 5 hours
- **Options**: 
  - `--hours=X`: Custom hours threshold (default: 5)
  - `--dry-run`: Preview without changes
- **Examples**:
  ```bash
  # Default 5 hours
  php artisan attendance:auto-checkout --dry-run
  
  # Custom 3 hours
  php artisan attendance:auto-checkout --hours=3 --dry-run
  
  # Execute with confirmation
  php artisan attendance:auto-checkout
  ```

### Manual Testing
```bash
# List all scheduled tasks
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Test specific commands
php artisan memberships:expire --dry-run
php artisan attendance:auto-checkout --dry-run
```

## 🔧 Maintenance

### Daily Tasks
```bash
# Check scheduled tasks
sudo -u www-data php artisan schedule:list

# Monitor logs
sudo tail -f /var/www/rsc-management/storage/logs/laravel.log

# Check disk space
df -h
```

### Weekly Tasks
```bash
# Update application (if needed)
cd /var/www/rsc-management
sudo git pull origin main
sudo -u www-data composer install --no-dev
sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Backup database
mysqldump -u username -p rsc_management > backup_$(date +%Y%m%d).sql
```

### Monthly Tasks
```bash
# Clean old logs
sudo find /var/www/rsc-management/storage/logs -name "*.log" -mtime +30 -delete

# Update system packages
sudo apt update && sudo apt upgrade -y

# Restart services
sudo systemctl restart nginx php8.3-fpm mysql
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
sudo chown -R www-data:www-data /var/www/rsc-management
sudo chmod -R 755 /var/www/rsc-management
sudo chmod -R 775 /var/www/rsc-management/storage
sudo chmod -R 775 /var/www/rsc-management/bootstrap/cache
```

#### 2. Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Test database connection
sudo -u www-data php artisan tinker
# Then: DB::connection()->getPdo();
```

#### 3. Scheduled Tasks Not Running
```bash
# Check cron service
sudo systemctl status cron

# Test scheduler manually
sudo -u www-data php artisan schedule:run

# Check cron logs
sudo tail -f /var/log/syslog | grep CRON

# Test individual commands
sudo -u www-data php artisan memberships:expire --dry-run
sudo -u www-data php artisan attendance:auto-checkout --dry-run
```

#### 4. Asset Issues
```bash
# Rebuild assets
sudo -u www-data npm run build

# Clear Laravel caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan view:clear
```

## 📞 Support

Untuk bantuan teknis atau pertanyaan mengenai deployment:

1. **Check logs**: `/var/www/rsc-management/storage/logs/laravel.log`
2. **System logs**: `/var/log/syslog`
3. **Nginx logs**: `/var/log/nginx/error.log`
4. **MySQL logs**: `/var/log/mysql/error.log`

## 📝 Changelog

### Version 1.1.0
- ✅ Auto check-out system for long-duration check-ins
- ✅ Configurable hours threshold for auto check-out
- ✅ Enhanced logging for automated tasks
- ✅ Improved scheduled task monitoring

### Version 1.0.0
- ✅ Initial release
- ✅ Member management with auto-payment
- ✅ Attendance system with date filtering
- ✅ Dashboard with statistics
- ✅ Auto-expire membership system
- ✅ Scheduled tasks for maintenance

---

**© 2025 RSC Management System. All rights reserved.**