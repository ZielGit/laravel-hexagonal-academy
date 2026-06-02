# 🎓 Laravel Hexagonal Academy

> **Learning Management System (LMS)** implementado con Arquitectura Hexagonal, DDD, CQRS y Event Sourcing

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Sistema de referencia educativo diseñado para enseñar arquitectura limpia a developers junior/mid-level y servir como template para proyectos empresariales.

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Arquitectura](#️-arquitectura)
- [Stack Tecnológico](#️-stack-tecnológico)
- [Instalación](#-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Bounded Contexts](#-bounded-contexts)
- [Patrones Implementados](#-patrones-implementados)
- [Testing](#-testing)
- [API Documentation](#-api-documentation)
- [Uso de Ejemplo](#-uso-de-ejemplo)
- [Roadmap](#️-roadmap)

---

## ✨ Características

### Funcionalidades Core
- 📚 **Gestión de Cursos**: Crear, editar, publicar y archivar cursos
- 👨‍🏫 **Gestión de Instructores**: Sistema de roles y permisos
- 📖 **Módulos y Lecciones**: Estructura jerárquica de contenido
- 📊 **Seguimiento de Progreso**: Track de avance de estudiantes
- ✅ **Sistema de Evaluaciones**: Quizzes y exámenes
- 🎓 **Certificaciones**: Emisión automática al completar cursos

### Características Técnicas
- ⬡ **Arquitectura Hexagonal** (Ports & Adapters)
- 📦 **Domain-Driven Design** (DDD)
- 🔄 **Event Sourcing** completo con Event Store
- 🎯 **CQRS** (Command Query Responsibility Segregation)
- 🔌 **Event Bus** para comunicación entre bounded contexts
- 🧪 **Testing Strategy** completa (Unit, Integration, Feature)
- 📖 **OpenAPI/Swagger** documentation
- 🐳 **Docker** ready

---

## 🏗️ Arquitectura

### Capas de la Arquitectura Hexagonal

```
┌───────────────────────────────────────────────────────────┐
│                    INFRASTRUCTURE LAYER                   │
│  (HTTP Controllers, Eloquent Models, Event Listeners)     │
│                                                           │
│  ┌──────────────────────────────────────────────────┐     │
│  │            APPLICATION LAYER                     │     │
│  │  (Use Cases, Commands, Queries, Handlers)        │     │
│  │                                                  │     │
│  │  ┌────────────────────────────────────────┐      │     │
│  │  │        DOMAIN LAYER                    │      │     │
│  │  │  (Entities, Value Objects, Events)     │      │     │
│  │  │  • Business Logic                      │      │     │
│  │  │  • Domain Events                       │      │     │
│  │  │  • Aggregates                          │      │     │
│  │  └────────────────────────────────────────┘      │     │
│  │                                                  │     │
│  └──────────────────────────────────────────────────┘     │
│                                                           │
└───────────────────────────────────────────────────────────┘
```

### Event Sourcing Flow

```
Command
   ↓
Handler
   ↓
Aggregate (genera eventos)
   ↓
Event Store (persiste eventos)
   ↓
Event Bus (publica eventos)
   ↓
Projector (actualiza Read Model)
   ↓
Query Handler (lee del Read Model)
```

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP** 8.2+
- **Laravel** 12.x
- **PostgreSQL** 15+ (Event Store + Read Models)
- **Redis** (Cache & Queues)

### Testing
- **PHPUnit** 11+
- **PHPStan** Level 8

### DevOps
- **Docker** & Docker Compose
- **GitHub Actions** (CI/CD)
- **PHP CS Fixer** (PSR-12)

---

## 🚀 Instalación

### Prerrequisitos
- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Redis (opcional)
- Docker (opcional)

### Paso 1: Clonar el repositorio
```bash
git clone https://github.com/ZielGit/laravel-hexagonal-academy.git
cd laravel-hexagonal-academy
```

### Paso 2: Instalar dependencias
```bash
composer install
```

### Paso 3: Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hexagonal_academy
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### Paso 4: Ejecutar migraciones y seeders
```bash
php artisan migrate
php artisan db:seed
```

### Paso 5: Iniciar servidor
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api/v1`

---

## 📁 Estructura del Proyecto

```
src/
├── Shared/                    # Shared Kernel
│   ├── Domain/
│   │   ├── Aggregate/        # AggregateRoot base
│   │   ├── Event/            # DomainEvent base, EventStore
│   │   └── ValueObject/      # Value Objects compartidos
│   ├── Application/
│   │   └── Bus/              # Command/Query/Event buses
│   └── Infrastructure/
│       ├── Bus/              # Implementaciones de buses
│       └── Persistence/      # Event Store implementation
│
└── BoundedContext/
    ├── CourseCatalog/        # Catálogo de cursos
    │   ├── Domain/
    │   │   ├── Model/        # Aggregates (Course, Module, Lesson)
    │   │   ├── ValueObject/  # VOs específicos del dominio
    │   │   ├── Event/        # Domain Events
    │   │   └── Repository/   # Repository interfaces
    │   ├── Application/
    │   │   ├── UseCase/      # Commands & Handlers
    │   │   ├── Query/        # Queries & Handlers
    │   │   └── Projection/   # Projectors para Read Models
    │   └── Infrastructure/
    │       ├── Persistence/  # Repository implementations
    │       ├── Http/         # Controllers, Requests, Resources
    │       └── Provider/     # Service Provider
    │
    ├── Enrollment/           # Inscripciones
    ├── Progress/             # Seguimiento de progreso
    ├── Assessment/           # Evaluaciones
    └── Instructor/           # Gestión de instructores
```

---

## 🎯 Bounded Contexts

### 1. CourseCatalog
**Responsabilidad**: Gestión del catálogo de cursos

**Entidades**:
- Course (Aggregate Root)
- Module
- Lesson

**Casos de Uso**:
- CreateCourse
- PublishCourse
- UpdateCourse
- AddModule
- AddLesson

### 2. Enrollment
**Responsabilidad**: Inscripciones de estudiantes

**Entidades**:
- Enrollment
- Student
- Waitlist

### 3. Progress
**Responsabilidad**: Seguimiento de progreso

**Entidades**:
- Progress
- LessonCompletion
- Certificate

### 4. Assessment
**Responsabilidad**: Evaluaciones y calificaciones

**Entidades**:
- Quiz
- Question
- Answer
- Grade

### 5. Instructor
**Responsabilidad**: Gestión de instructores

**Entidades**:
- Instructor
- Profile
- Review

---

## 🎨 Patrones Implementados

### SOLID Principles
✅ Single Responsibility
✅ Open/Closed
✅ Liskov Substitution
✅ Interface Segregation
✅ Dependency Inversion

### Design Patterns
- **Repository Pattern**: Abstracción de persistencia
- **CQRS**: Separación de lecturas y escrituras
- **Event Sourcing**: Estado derivado de eventos
- **Domain Events**: Comunicación desacoplada
- **Factory Pattern**: Creación de agregados
- **Strategy Pattern**: Algoritmos intercambiables
- **Observer Pattern**: Suscripción a eventos

### DDD Tactical Patterns
- **Entities**: Identidad única
- **Value Objects**: Inmutabilidad
- **Aggregates**: Límites transaccionales
- **Domain Services**: Lógica de dominio
- **Domain Events**: Notificaciones
- **Repositories**: Colecciones de agregados

---

## 🧪 Testing

### Ejecutar todos los tests
```bash
composer test
```

### Tests por tipo
```bash
# Unit Tests (Domain Layer)
composer test:unit

# Integration Tests (Application + Infrastructure)
composer test:integration

# Feature Tests (API E2E)
composer test:feature
```

### Coverage
```bash
composer test:coverage
```

### Static Analysis
```bash
composer stan
```

### Code Style
```bash
composer pint
composer pint:test
```

### Estructura de Tests
```
tests/
├── Unit/
│   ├── Shared/
│   └── CourseCatalog/
│       ├── Domain/
│       │   ├── CourseTest.php
│       │   └── ValueObjectsTest.php
│       └── Application/
│
├── Integration/
│   └── CourseCatalog/
│       ├── CreateCourseHandlerTest.php
│       └── CourseRepositoryTest.php
│
└── Feature/
    └── CourseCatalog/
        ├── CreateCourseApiTest.php
        ├── ListCoursesApiTest.php
        └── PublishCourseApiTest.php
```

---

## 📖 API Documentation

### Swagger/OpenAPI
Accede a la documentación interactiva:
```
http://localhost:8000/api/documentation
```

### Endpoints principales

#### Courses
```http
GET    /api/v1/courses              # Listar cursos públicos
GET    /api/v1/courses/{id}         # Detalle de curso
POST   /api/v1/instructor/courses   # Crear curso (instructor)
PUT    /api/v1/instructor/courses/{id}
POST   /api/v1/instructor/courses/{id}/publish
```

#### Authentication
```http
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
```

### Autenticación
La API usa **Laravel Sanctum** para autenticación JWT.

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@hexagonal-academy.test","password":"password"}'

# Usar token en requests
curl -X GET http://localhost:8000/api/v1/instructor/courses \
  -H "Authorization: Bearer {token}"
```

---

## 💡 Uso de Ejemplo

### Crear un curso (via API)

```bash
curl -X POST http://localhost:8000/api/v1/instructor/courses \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Laravel Best Practices",
    "description": "Learn industry best practices for Laravel development",
    "price": 79.99,
    "level": "intermediate"
  }'
```

### Crear un curso (via Código)

```php
use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseCommand;
use Shared\Application\Bus\CommandBusInterface;

$commandBus = app(CommandBusInterface::class);

$command = new CreateCourseCommand(
    courseId: Uuid::uuid4()->toString(),
    title: 'Laravel Best Practices',
    description: 'Learn industry best practices',
    price: 79.99,
    currency: 'USD',
    level: 'intermediate',
    instructorId: $instructorId,
);

$response = $commandBus->dispatch($command);
```

---

## 🗺️ Roadmap

### Fase 1: ✅ Foundation (Completado)
- [x] Arquitectura hexagonal base
- [x] Event Sourcing + CQRS
- [x] CourseCatalog bounded context
- [x] API REST completa
- [x] Tests unitarios e integración

### Fase 2: 📋 Próximamente
- [ ] Enrollment bounded context
- [ ] Progress tracking
- [ ] Authentication completa
- [ ] Notificaciones por email

### Fase 3: 📋 Próximamente
- [ ] Assessment & Quizzes
- [ ] Certificaciones
- [ ] Payment integration
- [ ] Video streaming
- [ ] Admin dashboard

### Fase 4: 🔮 Futuro
- [ ] Multi-tenancy
- [ ] GraphQL API
- [ ] Mobile app (React Native)
- [ ] AI-powered recommendations

---

## 📚 Recursos de Aprendizaje

### Libros Recomendados
- "Implementing Domain-Driven Design" - Vaughn Vernon
- "Domain-Driven Design" - Eric Evans
- "Clean Architecture" - Robert C. Martin
- "Patterns of Enterprise Application Architecture" - Martin Fowler

### Artículos
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)
- [Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
- [CQRS](https://martinfowler.com/bliki/CQRS.html)

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

---

## 👨‍💻 Autor

**Frans J. Vilcahuamán Rojas**

- GitHub: [@ZielGit](https://github.com/ZielGit)
- LinkedIn: [in/frans-vilcahuaman](https://www.linkedin.com/in/frans-vilcahuaman/)
