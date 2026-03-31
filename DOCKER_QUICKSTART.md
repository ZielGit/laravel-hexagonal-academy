# 🐳 Docker Quick Start Guide

Guía rápida para levantar el proyecto **Laravel Hexagonal Academy** usando Docker.

---

## 📋 Prerrequisitos

- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Make** (opcional, pero recomendado)

### Verificar instalación
```bash
docker --version
docker-compose --version
make --version  # opcional
```

---

## 🚀 Inicio Rápido (con Make)

### Opción 1: Setup Automático (Recomendado)
```bash
# Clona el repositorio
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git
cd laravel-hexagonal-academy

# Setup completo (build + migrate + seed)
make setup
```

Eso es todo! 🎉

### URLs disponibles:
- **App**: http://localhost:8000
- **API**: http://localhost:8000/api/v1
- **Mailhog**: http://localhost:8025
- **pgAdmin**: http://localhost:5050

---

## 🔧 Inicio Manual (sin Make)

### Paso 1: Clonar repositorio
```bash
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git
cd laravel-hexagonal-academy
```

### Paso 2: Copiar archivo de entorno
```bash
cp .env.example .env
```

### Paso 3: Construir imágenes
```bash
docker-compose build
```

### Paso 4: Levantar contenedores
```bash
docker-compose up -d
```

### Paso 5: Instalar dependencias
```bash
docker-compose exec app composer install
```

### Paso 6: Generar key de Laravel
```bash
docker-compose exec app php artisan key:generate
```

### Paso 7: Ejecutar migraciones
```bash
docker-compose exec app php artisan migrate
```

### Paso 8: Ejecutar seeders
```bash
docker-compose exec app php artisan db:seed
```

---

## 🎯 Comandos Útiles

### Gestión de Contenedores
```bash
# Ver estado de contenedores
make ps
# o
docker-compose ps

# Ver logs
make logs
# o
docker-compose logs -f

# Reiniciar contenedores
make restart
# o
docker-compose restart

# Detener contenedores
make down
# o
docker-compose down
```

### Comandos de Laravel
```bash
# Acceder a bash del contenedor
make bash
# o
docker-compose exec app sh

# Ejecutar Artisan
make artisan cmd="route:list"
# o
docker-compose exec app php artisan route:list

# Ejecutar Composer
make composer cmd="require package/name"
# o
docker-compose exec app composer require package/name

# Tinker
make tinker
# o
docker-compose exec app php artisan tinker
```

### Base de Datos
```bash
# Fresh database (borra todo y recrea)
make fresh
# o
docker-compose exec app php artisan migrate:fresh --seed

# Solo migraciones
make migrate
# o
docker-compose exec app php artisan migrate

# Consola PostgreSQL
make db-console
# o
docker-compose exec postgres psql -U postgres -d hexagonal_academy

# Dump de base de datos
make db-dump
# o
docker-compose exec postgres pg_dump -U postgres hexagonal_academy > backup.sql
```

### Testing
```bash
# Ejecutar todos los tests
make test
# o
docker-compose exec app php artisan test

# Tests por tipo
make test-unit
make test-integration
make test-feature

# Con coverage
make test-coverage

# Static analysis
make stan
# o
docker-compose exec app vendor/bin/phpstan analyse

# Code style
make pint
# o
docker-compose exec app vendor/bin/pint
```

### Caché
```bash
# Limpiar todos los caches
make cache-clear
# o
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Optimizar para producción
make optimize
# o
docker-compose exec app php artisan optimize
```

---

## 🏗️ Arquitectura de Contenedores

```
┌───────────────────────────────────────────────────────┐
│                    Docker Network                     │
│                  hexagonal_network                    │
│                                                       │
│  ┌──────────┐   ┌──────────┐   ┌────────────┐         │
│  │  Nginx   │───│   App    │───│ PostgreSQL │         │
│  │  :80     │   │ PHP-FPM  │   │  :5432     │         │
│  │          │   │  :9000   │   │            │         │
│  └──────────┘   └──────────┘   └────────────┘         │
│       │              │                 │              │
│       │              ├─────────────────┤              │
│       │              │                 │              │
│  ┌────────┐    ┌─────────┐      ┌──────────┐          │
│  │  Redis │    │  Queue  │      │ Mailhog  │          │
│  │ :6379  │    │ Worker  │      │ :1025    │          │
│  └────────┘    └─────────┘      │ :8025    │          │
│                                 └──────────┘          │
└───────────────────────────────────────────────────────┘
```

### Servicios incluidos:

1. **app** - PHP 8.2 + Laravel (PHP-FPM)
2. **nginx** - Servidor web (puerto 8000)
3. **postgres** - Base de datos PostgreSQL 15 (puerto 5432)
4. **redis** - Cache y colas (puerto 6379)
5. **queue** - Worker de Laravel para procesar colas
6. **scheduler** - Cron de Laravel
7. **mailhog** - Servidor SMTP para testing (puerto 8025)
8. **pgadmin** - UI para PostgreSQL (puerto 5050) *opcional*

---

## 🔐 Credenciales por Defecto

### PostgreSQL
- **Host**: localhost
- **Port**: 5432
- **Database**: hexagonal_academy
- **Username**: postgres
- **Password**: secret

### pgAdmin (opcional)
- **URL**: http://localhost:5050
- **Email**: admin@hexagonal-academy.test
- **Password**: admin

### Usuarios de prueba (después de seed)

**Admin**:
- Email: admin@hexagonal-academy.test
- Password: password

**Instructor 1**:
- Email: john@hexagonal-academy.test
- Password: password

**Instructor 2**:
- Email: jane@hexagonal-academy.test
- Password: password

---

## 🧪 Testing en Docker

### Ejecutar tests
```bash
# Todos los tests
make test

# Por tipo
make test-unit        # Domain layer
make test-integration # Application + Infrastructure
make test-feature     # API E2E

# Con coverage
make test-coverage
```

### Análisis estático
```bash
make stan
make pint-test
```

### Todo junto
```bash
make quality
```

---

## 🐛 Troubleshooting

### Problema: Permisos en archivos
```bash
# Solución
make permissions
# o
docker-compose exec app chown -R appuser:appgroup /var/www
docker-compose exec app chmod -R 755 /var/www/storage
```

### Problema: Puerto ya en uso
```bash
# Ver qué está usando el puerto
lsof -i :8000  # macOS/Linux
netstat -ano | findstr :8000  # Windows

# Cambiar puerto en .env
APP_PORT=8080

# Reconstruir
docker-compose down
docker-compose up -d
```

### Problema: Cambios no se reflejan
```bash
# Limpiar caches
make cache-clear

# Reconstruir contenedores
make rebuild
```

### Problema: Base de datos corrupta
```bash
# Recrear desde cero
docker-compose down -v  # ⚠️ Borra volúmenes!
docker-compose up -d
make fresh
```

### Ver logs de errores
```bash
# Logs de Laravel
make watch-logs
# o
docker-compose exec app tail -f storage/logs/laravel.log

# Logs de Nginx
make logs-nginx

# Logs de PostgreSQL
make logs-postgres
```

---

## 🔄 Workflow de Desarrollo

### 1. Iniciar el día
```bash
# Levantar servicios
make up

# Ver estado
make status
```

### 2. Durante el desarrollo
```bash
# Hacer cambios en el código...

# Si modificas .env
make restart

# Si instalas packages
make composer cmd="require vendor/package"

# Si agregas migraciones
make migrate

# Ejecutar tests
make test
```

### 3. Antes de commit
```bash
# Verificar calidad
make quality

# Si todo pasa, commit
git add .
git commit -m "Feature: ..."
git push
```

### 4. Finalizar el día
```bash
# Detener servicios (opcional)
make down
```

---

## 🚀 Deployment a Producción

### Usando Docker
```bash
# En el servidor
git pull
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --build
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

### O usando Make
```bash
make prod-deploy
```

---

## 📚 Recursos Adicionales

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)
- [Project README](README.md)

---

## 💡 Tips y Best Practices

### 1. Usar Make para consistencia
Los comandos Make son cross-platform y más fáciles de recordar.

### 2. No modificar archivos en /vendor desde el contenedor
Siempre usa `composer` para instalar packages.

### 3. Backups periódicos
```bash
# Automatizar con cron
0 2 * * * cd /path/to/project && make db-dump
```

### 4. Monitorear recursos
```bash
docker stats
```

### 5. Limpiar regularmente
```bash
# Liberar espacio
docker system prune -a
```

---

## 🆘 Obtener Ayuda

```bash
# Ver todos los comandos disponibles
make help

# O revisar el Makefile
cat Makefile
```

---

**¡Happy Coding con Docker!** 🐳🚀
