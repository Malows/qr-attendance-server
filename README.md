# QR Attendance API Server

**API REST pura** para registro de asistencia mediante QR con OAuth2, PostgreSQL/PostGIS y control de acceso por roles.

> 🎯 **Aplicación Backend-Only**: Esta es una API REST sin frontend. Los clientes (móviles, web, kioscos) se conectan vía HTTP/JSON.

## 📋 Características

- **API REST Pura**: Sin frontend, solo endpoints JSON
- **OAuth2 Authentication**: Laravel Passport con dos guards independientes
- **Dual Context**: Separación completa entre Users (administración) y Employees (auto-servicio)
- **Roles & Permissions**: Sistema RBAC con 3 niveles (Administrator, Manager, Supervisor)
- **Rate Limiting**: Protección diferenciada por contexto (users: 120/min, employees: 30/min)
- **Locations**: Gestión de ubicaciones con coordenadas GPS (PostGIS)
- **Employees**: Gestión de empleados con autenticación independiente
- **Attendances**: Check-in/check-out con geolocalización y cálculo de horas
- **Base de datos**: PostgreSQL 15 + PostGIS para datos geoespaciales
- **Arquitectura Limpia**: RouteServiceProvider, guards separados, named routes

## 🏗️ Stack Tecnológico

- **Backend**: Laravel 12 + PHP 8.4
- **Autenticación**: Laravel Passport (OAuth2)
- **Base de Datos**: PostgreSQL 15 + PostGIS
- **Permisos**: Spatie Laravel Permission
- **Containerización**: Docker + Docker Compose + Nginx

## 🚀 Instalación Rápida con Docker

### Requisitos Previos
- Docker & Docker Compose
- Make (opcional)

### Instalación

```bash
# Con Make
make install

# Sin Make
docker-compose build
docker-compose up -d
sleep 10
docker-compose exec app php artisan migrate
docker-compose exec app php artisan passport:install
```

**API Base URL**: http://localhost:8080/api

## 🔐 Autenticación OAuth2

El sistema cuenta con **dos contextos de autenticación separados**:

### 1. Users API (`auth:api`)
Para administradores, managers y supervisores. Gestión completa del sistema.

**Base:** `/api/users/*`

### 2. Employees API (`auth:employee`)
Para empleados. Auto-servicio de check-in/check-out únicamente.

**Base:** `/api/employees/*`

---

### Registrar usuario (Users)

```bash
POST /api/users/register
Content-Type: application/json

{
  "name": "Juan Perez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login Usuario

```bash
POST /api/users/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "password123"
}
```

### Login Empleado

```bash
POST /api/employees/login
Content-Type: application/json

{
  "email": "empleado@empresa.com",
  "password": "password123"
}
```

**Respuesta incluye**: `access_token`, `user`/`employee`, `token_type`

### Usar el token

```bash
# Para rutas de Users
Authorization: Bearer {user_access_token}

# Para rutas de Employees
Authorization: Bearer {employee_access_token}
```

## 📱 API Endpoints

### Locations

- `GET /api/locations` - Listar locations
- `POST /api/locations` - Crear location
- `GET /api/locations/{id}` - Ver location
- `PUT /api/locations/{id}` - Actualizar location
- `DELETE /api/locations/{id}` - Eliminar location

**Ejemplo:**
```json
{
  "name": "Oficina Central",
  "address": "Av. Principal 123",
  "city": "Ciudad",
  "latitude": -12.0464,
  "longitude": -77.0428,
  "description": "Oficina principal",
  "is_active": true
}
```

### Employees

- `GET /api/employees` - Listar employees (filtros: `?search=&is_active=`)
- `POST /api/employees` - Crear employee
- `GET /api/employees/{id}` - Ver employee
- `PUT /api/employees/{id}` - Actualizar employee
- `DELETE /api/employees/{id}` - Eliminar employee

**Ejemplo:**
```json
{
  "first_name": "Juan",
  "last_name": "Perez",
  "email": "juan.perez@company.com",
  "phone": "+51999999999",
  "employee_code": "EMP-001",
  "hire_date": "2025-01-15",
  "position": "Desarrollador",
  "is_active": true
}
```

### Attendances

- `GET /api/attendances` - Listar attendances (filtros: `?employee_id=&location_id=&date_from=&date_to=`)
- `POST /api/attendances` - Crear attendance manual
- `GET /api/attendances/{id}` - Ver attendance
- `PUT /api/attendances/{id}` - Actualizar attendance
- `DELETE /api/attendances/{id}` - Eliminar attendance
- `POST /api/attendances/check-in` - Check-in automático
- `POST /api/attendances/{id}/check-out` - Check-out automático

**Check-in:**
```json
{
  "employee_id": 1,
  "location_id": 1,
  "latitude": -12.0464,
  "longitude": -77.0428
}
```

**Check-out:**
```json
{
  "latitude": -12.0464,
  "longitude": -77.0428,
  "notes": "Día completado"
}
```

## 📊 Modelos

### User
- Gestiona Locations y Employees
- OAuth2 con Laravel Passport

### Location
- Ubicaciones con coordenadas GPS
- Relación con Attendances

### Employee
- Información de empleados
- Código único, email, posición, etc.

### Attendance
- Registro de check-in/check-out
- Geolocalización
- Cálculo automático de horas trabajadas

## � Roles & Permisos

El sistema implementa un control de acceso basado en roles (RBAC) con 3 niveles jerárquicos utilizando Spatie Laravel Permission.

### Roles Disponibles

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Administrator** | Acceso total al sistema | Todos los permisos (47) |
| **Manager** | Gestión de locations, employees y attendances | 35 permisos |
| **Supervisor** | Acceso de solo lectura + operaciones básicas | 14 permisos |

### Categorías de Permisos

#### 1. User Management (12 permisos)
- `view-users`, `create-users`, `edit-users`, `delete-users`
- `view-own-profile`, `edit-own-profile`
- `assign-roles`, `revoke-roles`, `view-roles`, `edit-roles`
- `assign-permissions`, `revoke-permissions`

#### 2. Location Management (7 permisos)
- `view-locations`, `view-any-locations`, `create-locations`
- `edit-locations`, `delete-locations`, `restore-locations`, `force-delete-locations`

#### 3. Employee Management (7 permisos)
- `view-employees`, `view-any-employees`, `create-employees`
- `edit-employees`, `delete-employees`, `restore-employees`, `force-delete-employees`

#### 4. Attendance Management (8 permisos)
- `view-attendances`, `view-any-attendances`, `create-attendances`
- `edit-attendances`, `delete-attendances`, `check-in`, `check-out`, `edit-own-attendance`

#### 5. Reports (4 permisos)
- `view-reports`, `export-reports`, `view-analytics`, `export-data`

#### 6. System Administration (9 permisos)
- `manage-system-settings`, `view-audit-logs`, `manage-api-keys`
- `manage-integrations`, `backup-database`, `restore-database`
- `clear-cache`, `view-system-health`, `manage-maintenance-mode`

### Permisos por Rol

#### Administrator (47 permisos)
✅ **Todos los permisos** del sistema

#### Manager (35 permisos)
✅ User Management: `view-users`, `create-users`, `edit-users`, `view-own-profile`, `edit-own-profile`, `view-roles`
✅ Location Management: Todos (7)
✅ Employee Management: Todos (7)
✅ Attendance Management: Todos (8)
✅ Reports: Todos (4)
✅ System: `view-audit-logs`, `clear-cache`, `view-system-health`

❌ No puede: Asignar roles, eliminar usuarios, gestionar configuración del sistema, backups

#### Supervisor (14 permisos)
✅ User Management: `view-own-profile`, `edit-own-profile`
✅ Location Management: `view-locations`, `view-any-locations`
✅ Employee Management: `view-employees`, `view-any-employees`
✅ Attendance Management: `view-attendances`, `view-any-attendances`, `create-attendances`, `check-in`, `check-out`, `edit-own-attendance`
✅ Reports: `view-reports`

❌ No puede: Crear/editar locations o employees, modificar attendances de otros, exportar reportes, administración

### API Endpoints de Roles

**Listar todos los roles**
```bash
GET /api/roles
Authorization: Bearer {token}
```

**Listar todos los permisos**
```bash
GET /api/permissions
Authorization: Bearer {token}
```

**Asignar rol a usuario**
```bash
POST /api/users/{userId}/assign-role
Authorization: Bearer {token}
Content-Type: application/json

{
  "role": "manager"
}
```

**Remover rol de usuario**
```bash
POST /api/users/{userId}/remove-role
Authorization: Bearer {token}
Content-Type: application/json

{
  "role": "supervisor"
}
```

**Sincronizar roles (reemplaza todos los roles)**
```bash
POST /api/users/{userId}/sync-roles
Authorization: Bearer {token}
Content-Type: application/json

{
  "roles": ["manager"]
}
```

**Dar permiso específico a usuario**
```bash
POST /api/users/{userId}/give-permission
Authorization: Bearer {token}
Content-Type: application/json

{
  "permission": "export-reports"
}
```

**Revocar permiso de usuario**
```bash
POST /api/users/{userId}/revoke-permission
Authorization: Bearer {token}
Content-Type: application/json

{
  "permission": "export-reports"
}
```

### Asignación Automática de Roles

- **Registro de nuevos usuarios**: Automáticamente se asigna el rol `supervisor`
- **Seeders**: Crea usuarios de prueba con cada rol:
  - `admin@example.com` / `password` → Administrator
  - `manager@example.com` / `password` → Manager
  - `supervisor@example.com` / `password` → Supervisor

### Verificación de Permisos

Los permisos se verifican en dos niveles:

1. **Políticas (Policies)**: Verifican permisos antes de la lógica de ownership
2. **Middleware**: Protege rutas con `auth:api` middleware

Ejemplo en código:
```php
// En LocationPolicy
public function view(User $user, Location $location): bool
{
    // Primero verifica el permiso
    if (!$user->hasPermissionTo('view-locations')) {
        return false;
    }
    
    // Luego verifica ownership
    return $user->id === $location->user_id;
}
```

## �🛠️ Comandos Docker

```bash
make up           # Iniciar
make down         # Detener
make logs         # Ver logs
make shell        # Shell app
make migrate      # Migraciones
make test         # Tests
```

## 💡 Casos de Uso

1. **App Móvil**: Check-in/out con QR y geolocalización
2. **Panel Web**: Administración y reportes
3. **Kiosko**: Terminal táctil de entrada
4. **Integración**: API REST para sistemas externos

## 🔒 Seguridad

- **OAuth2 con Laravel Passport**: Autenticación basada en tokens
- **Roles & Permisos**: Control de acceso granular con Spatie Permission (47 permisos)
- **3 Niveles de Usuario**: Administrator, Manager, Supervisor
- **Tokens con expiración**: Access tokens (15 días), Refresh tokens (30 días)
- **Políticas de autorización**: Verificación de permisos + ownership en todas las operaciones
- **Validación de datos**: Request validation en todos los endpoints
- **Encriptación bcrypt**: Para contraseñas de usuarios
- **API Guard**: Protección con middleware `auth:api`

## 📝 Licencia

MIT
# qr-attendance-server
