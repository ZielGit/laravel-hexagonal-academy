# 📦 Installation Guide - Laravel Hexagonal Academy

Guía completa de instalación del proyecto con **tres métodos**: Docker (recomendado), Local, y Producción.

---

## 🎯 Método 1: Docker (Recomendado)

### Prerrequisitos
- Docker 20.10+
- Docker Compose 2.0+
- Git
- Make (opcional)

### Instalación Rápida

```bash
# 1. Clonar repositorio
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git
cd laravel-hexagonal-academy

# 2. Setup automático (con Make)
make setup
```

**¡Listo!** La aplicación estará corriendo en:
- **App**: http://localhost:8000
- **API**: http://localhost:8000/api/v1
- **Mailhog**: http://localhost:8025
- **pgAdmin**: http://localhost:5050

### Verificación
```bash
# Ver estado de servicios
make status

# Ver logs
make logs

# Ejecutar tests
make test
```

Para más detalles sobre Docker, consulta [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md)

---

## 💻 Método 2: Instalación Local

### Prerrequisitos
- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Redis 7+ (opcional)
- Node.js 18+ (si usas frontend)

### Paso a Paso

#### 1. Clonar repositorio
```bash
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git
cd laravel-hexagonal-academy
```

#### 2. Instalar dependencias PHP
```bash
composer install
```

#### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Editar `.env` con tus credenciales

```env
APP_NAME="Laravel Hexagonal Academy"
APP_ENV=local
APP_KEY=base64:... # generado automáticamente
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hexagonal_academy
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 5. Crear base de datos PostgreSQL

```bash
# Opción 1: psql
psql -U postgres
CREATE DATABASE hexagonal_academy;
CREATE USER hexagonal_user WITH PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE hexagonal_academy TO hexagonal_user;
\q

# Opción 2: GUI (pgAdmin, DBeaver, etc.)
```

#### 6. Ejecutar migraciones
```bash
php artisan migrate
```

#### 7. Ejecutar seeders (opcional)
```bash
php artisan db:seed
```

#### 8. Iniciar servicios

**Terminal 1 - Servidor Web:**
```bash
php artisan serve
# App en: http://localhost:8000
```

**Terminal 2 - Queue Worker (opcional):**
```bash
php artisan queue:work
```

**Terminal 3 - Scheduler (opcional):**
```bash
# En producción usar cron
# En desarrollo:
php artisan schedule:work
```

#### 9. Verificar instalación

```bash
# Health check
curl http://localhost:8000/api/health

# Ejecutar tests
php artisan test
```

---

## 🚀 Método 3: Instalación en Producción

### Prerrequisitos
- Servidor Linux (Ubuntu 22.04 LTS recomendado)
- Nginx o Apache
- PHP 8.2+ con extensiones requeridas
- PostgreSQL 15+
- Redis 7+
- Supervisor (para queues)
- Certbot (para SSL)

### Paso a Paso en Producción

#### 1. Preparar servidor

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.2
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common \
    php8.2-pgsql php8.2-redis php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Instalar Redis
sudo apt install -y redis-server

# Instalar Nginx
sudo apt install -y nginx
```

#### 2. Configurar PostgreSQL

```bash
sudo -u postgres psql

CREATE DATABASE hexagonal_academy_prod;
CREATE USER hexagonal_prod WITH PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE hexagonal_academy_prod TO hexagonal_prod;
\q
```

#### 3. Clonar y configurar aplicación

```bash
# Crear directorio
sudo mkdir -p /var/www/hexagonal-academy
sudo chown -R $USER:$USER /var/www/hexagonal-academy

# Clonar
cd /var/www/hexagonal-academy
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git .

# Instalar dependencias (sin dev)
composer install --no-dev --optimize-autoloader

# Configurar permisos
sudo chown -R www-data:www-data /var/www/hexagonal-academy
sudo chmod -R 755 /var/www/hexagonal-academy
sudo chmod -R 775 /var/www/hexagonal-academy/storage
sudo chmod -R 775 /var/www/hexagonal-academy/bootstrap/cache
```

#### 4. Configurar entorno de producción

```bash
# Copiar .env
cp .env.example .env

# Editar con datos de producción
nano .env
```

```env
APP_NAME="Laravel Hexagonal Academy"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hexagonal_academy_prod
DB_USERNAME=hexagonal_prod
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

```bash
# Generar key
php artisan key:generate

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Migrar base de datos
php artisan migrate --force
```

#### 5. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/hexagonal-academy
```

```nginx
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    root /var/www/hexagonal-academy/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/hexagonal-academy /etc/nginx/sites-enabled/

# Test configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

#### 6. Configurar SSL con Certbot

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Auto-renovación
sudo systemctl status certbot.timer
```

#### 7. Configurar Supervisor para Queues

```bash
sudo apt install -y supervisor

sudo nano /etc/supervisor/conf.d/hexagonal-academy-worker.conf
```

```ini
[program:hexagonal-academy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hexagonal-academy/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hexagonal-academy/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Recargar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hexagonal-academy-worker:*
```

#### 8. Configurar Cron para Scheduler

```bash
sudo crontab -e -u www-data
```

Agregar:
```
* * * * * cd /var/www/hexagonal-academy && php artisan schedule:run >> /dev/null 2>&1
```

#### 9. Configurar Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

#### 10. Verificación Final

```bash
# Health check
curl https://tu-dominio.com/api/health

# Ver logs
tail -f /var/www/hexagonal-academy/storage/logs/laravel.log

# Estado de servicios
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo supervisorctl status
```

---

## 🔐 Post-Instalación: Seguridad

### 1. Configurar `.env` seguro

```env
APP_DEBUG=false
APP_ENV=production

# Generar password fuerte
DB_PASSWORD=$(openssl rand -base64 32)
```

### 2. Configurar permisos correctos

```bash
# Archivos
find /var/www/hexagonal-academy -type f -exec chmod 644 {} \;

# Directorios
find /var/www/hexagonal-academy -type d -exec chmod 755 {} \;

# Storage y cache
chmod -R 775 /var/www/hexagonal-academy/storage
chmod -R 775 /var/www/hexagonal-academy/bootstrap/cache

# Owner
chown -R www-data:www-data /var/www/hexagonal-academy
```

### 3. Proteger archivos sensibles

```nginx
# En Nginx config
location ~ /\.(env|git) {
    deny all;
    return 404;
}
```

### 4. Rate Limiting

Ya está configurado en la API (ver `routes/api.php`)

### 5. Backups automatizados

```bash
# Script de backup
sudo nano /usr/local/bin/backup-hexagonal-academy.sh
```

```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/hexagonal-academy"

# Database backup
pg_dump -U hexagonal_prod hexagonal_academy_prod > $BACKUP_DIR/db_$TIMESTAMP.sql

# Files backup
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz /var/www/hexagonal-academy

# Delete old backups (older than 7 days)
find $BACKUP_DIR -type f -mtime +7 -delete
```

```bash
# Hacer ejecutable
sudo chmod +x /usr/local/bin/backup-hexagonal-academy.sh

# Agregar a cron (diario a las 2am)
sudo crontab -e
0 2 * * * /usr/local/bin/backup-hexagonal-academy.sh
```

---

## 🧪 Verificar Instalación

### Tests de Funcionamiento

```bash
# 1. Health check
curl http://localhost:8000/api/health

# 2. Base de datos
php artisan db:show

# 3. Cache
php artisan cache:clear
redis-cli ping

# 4. Tests
php artisan test

# 5. Static analysis
vendor/bin/phpstan analyse

# 6. Code style
vendor/bin/pint --test
```

### Benchmarking

```bash
# Instalar Apache Bench
sudo apt install apache2-utils

# Test simple
ab -n 1000 -c 10 http://localhost:8000/api/v1/courses

# Test con autenticación
ab -n 100 -c 5 -H "Authorization: Bearer YOUR_TOKEN" \
   http://localhost:8000/api/v1/instructor/courses
```

---

## 🆘 Troubleshooting

### Problema: Permisos 500 Error

```bash
# Solución
sudo chown -R www-data:www-data /var/www/hexagonal-academy
sudo chmod -R 755 /var/www/hexagonal-academy/storage
sudo chmod -R 755 /var/www/hexagonal-academy/bootstrap/cache
```

### Problema: No se conecta a PostgreSQL

```bash
# Verificar servicio
sudo systemctl status postgresql

# Ver logs
sudo tail -f /var/log/postgresql/postgresql-15-main.log

# Reiniciar
sudo systemctl restart postgresql
```

### Problema: Redis no funciona

```bash
# Verificar
redis-cli ping

# Ver config
redis-cli config get requirepass

# Reiniciar
sudo systemctl restart redis-server
```

### Problema: Queue no procesa trabajos

```bash
# Ver estado de worker
sudo supervisorctl status

# Reiniciar workers
sudo supervisorctl restart hexagonal-academy-worker:*

# Ver logs
tail -f /var/www/hexagonal-academy/storage/logs/worker.log
```

---

## 📚 Siguientes Pasos

Después de la instalación:

1. ✅ Configurar autenticación (users y roles)
2. ✅ Crear primer curso de prueba
3. ✅ Configurar email (SMTP)
4. ✅ Configurar backups
5. ✅ Configurar monitoring (opcional)
6. ✅ Configurar CI/CD (opcional)

---

## 🔗 Enlaces Útiles

- [README.md](README.md) - Documentación principal
- [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md) - Guía Docker
- [API Documentation](http://localhost:8000/api/documentation) - Swagger
- [Laravel Docs](https://laravel.com/docs)

---

**¡Instalación Completada!** 🎉

Si tienes problemas, revisa los logs o abre un issue en GitHub.
