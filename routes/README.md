# Estructura de Rutas API

Este proyecto organiza las rutas API en archivos separados por contexto de usuario para mejor mantenibilidad y claridad.

## 📁 Estructura de Archivos

```
routes/
├── api.php          # Archivo principal que incluye los demás
├── users.php        # Rutas para Users (administración)
├── employees.php    # Rutas para Employees (auto-servicio)
├── web.php          # Rutas web (si las hay)
└── console.php      # Comandos de consola
```

## 🔄 Flujo de Carga

```
bootstrap/app.php
    ↓
routes/api.php
    ├── require routes/users.php
    └── require routes/employees.php
```

## 📝 routes/api.php

Archivo **orquestador** que define los prefijos y carga los archivos específicos:

```php
// Base: /api/users/*
Route::prefix('users')->group(function () {
    require __DIR__.'/users.php';
});

// Base: /api/employees/*
Route::prefix('employees')->group(function () {
    require __DIR__.'/employees.php';
});
```

## 👥 routes/users.php

**Propósito:** Rutas para administración completa del sistema

**Guard:** `auth:api`

**Audiencia:** Administrators, Managers, Supervisors

**Base URL:** `/api/users`

### Rutas Públicas
- `POST /api/users/register` - Crear nueva cuenta de usuario
- `POST /api/users/login` - Login de usuario

### Rutas Protegidas (auth:api)

#### Autenticación
- `GET /api/users/me` - Obtener usuario autenticado
- `POST /api/users/logout` - Cerrar sesión

#### Locations (CRUD completo)
- `GET /api/users/locations` - Listar locations
- `POST /api/users/locations` - Crear location
- `GET /api/users/locations/{id}` - Ver location
- `PUT /api/users/locations/{id}` - Actualizar location
- `DELETE /api/users/locations/{id}` - Eliminar location

#### Employees (CRUD completo)
- `GET /api/users/employees` - Listar employees (con búsqueda)
- `POST /api/users/employees` - Crear employee
- `GET /api/users/employees/{id}` - Ver employee
- `PUT /api/users/employees/{id}` - Actualizar employee
- `DELETE /api/users/employees/{id}` - Eliminar employee

#### Attendances (Gestión completa)
- `GET /api/users/attendances` - Listar attendances (con filtros)
- `POST /api/users/attendances` - Crear attendance
- `GET /api/users/attendances/{id}` - Ver attendance
- `PUT /api/users/attendances/{id}` - Actualizar attendance
- `DELETE /api/users/attendances/{id}` - Eliminar attendance

#### Roles & Permissions (Solo admins)
- `GET /api/users/roles` - Listar roles
- `GET /api/users/permissions` - Listar permisos
- `POST /api/users/manage/{user}/assign-role` - Asignar rol
- `POST /api/users/manage/{user}/remove-role` - Remover rol
- `POST /api/users/manage/{user}/sync-roles` - Sincronizar roles
- `POST /api/users/manage/{user}/give-permission` - Dar permiso
- `POST /api/users/manage/{user}/revoke-permission` - Revocar permiso

## 👷 routes/employees.php

**Propósito:** Rutas para auto-servicio de empleados

**Guard:** `auth:employee`

**Audiencia:** Employees (mobile apps, kiosks, terminals)

**Base URL:** `/api/employees`

### Rutas Públicas
- `POST /api/employees/login` - Login con email/password
- `POST /api/employees/login-with-code` - Login con código de empleado

### Rutas Protegidas (auth:employee)

#### Autenticación
- `GET /api/employees/me` - Obtener empleado autenticado
- `POST /api/employees/logout` - Cerrar sesión

#### Attendances (Solo lectura y check-in/out)
- `GET /api/employees/attendances` - Listar propias attendances
- `GET /api/employees/attendances/current` - Ver attendance abierta actual
- `POST /api/employees/attendances/check-in` - Registrar entrada
- `POST /api/employees/attendances/check-out` - Registrar salida

#### Locations
- `GET /api/employees/locations` - Listar locations disponibles para check-in

## 🔐 Guards y Seguridad

### Guard `api` (Users)
```php
// Middleware
Route::middleware('auth:api')->group(function () {
    // Rutas protegidas para users
});

// En el controlador
$user = $request->user(); // Devuelve User instance
```

### Guard `employee` (Employees)
```php
// Middleware
Route::middleware('auth:employee')->group(function () {
    // Rutas protegidas para employees
});

// En el controlador
$employee = $request->user(); // Devuelve Employee instance
```

## 🎯 Separación de Responsabilidades

### Users Context
- ✅ Gestión completa del sistema
- ✅ CRUD de locations, employees, attendances
- ✅ Administración de roles y permisos
- ✅ Reportes y analytics
- ❌ No puede hacer check-in directo como employee

### Employees Context
- ✅ Check-in y check-out en locations
- ✅ Ver sus propias attendances
- ✅ Ver su información de empleado
- ✅ Listar locations disponibles
- ❌ No puede crear/editar locations
- ❌ No puede gestionar otros employees
- ❌ No puede modificar attendances de otros

## 🔧 Agregar Nuevas Rutas

### Para Users
Editar `routes/users.php`:

```php
// Dentro del grupo middleware('auth:api')
Route::get('reports/monthly', [ReportController::class, 'monthly']);
```

Resultado: `GET /api/users/reports/monthly`

### Para Employees
Editar `routes/employees.php`:

```php
// Dentro del grupo middleware('auth:employee')
Route::get('/profile/settings', [ProfileController::class, 'settings']);
```

Resultado: `GET /api/employees/profile/settings`

## 📊 Ventajas de esta Estructura

### 1. **Claridad**
Cada archivo de rutas tiene un propósito específico y bien definido.

### 2. **Mantenibilidad**
Fácil encontrar y modificar rutas según el contexto.

### 3. **Escalabilidad**
Agregar nuevos contextos es tan simple como crear un nuevo archivo:

```php
// routes/customers.php
// routes/suppliers.php
// routes/api.php
Route::prefix('customers')->group(function () {
    require __DIR__.'/customers.php';
});
```

### 4. **Documentación Implícita**
La estructura del código documenta la arquitectura del sistema.

### 5. **Testing**
Facilita las pruebas al tener rutas organizadas por dominio.

## 🧪 Testing

### Verificar rutas cargadas
```bash
php artisan route:list --path=api/users
php artisan route:list --path=api/employees
```

### Verificar middleware
```bash
php artisan route:list --path=api/users --columns=uri,middleware
php artisan route:list --path=api/employees --columns=uri,middleware
```

## 🔍 Debugging

### Ver todas las rutas API
```bash
php artisan route:list --path=api
```

### Ver rutas de un controlador específico
```bash
php artisan route:list --path=api | grep AuthController
```

### Ver rutas con un middleware específico
```bash
php artisan route:list --path=api | grep "auth:employee"
```

## 📝 Notas Importantes

1. **Orden de Carga**: Las rutas se cargan en el orden que aparecen en `api.php`

2. **Prefijos Anidados**: Los prefijos se acumulan:
   ```php
   // En api.php
   Route::prefix('users')->group(function () {
       // En users.php
       Route::prefix('admin')->group(function () {
           // Resulta en: /api/users/admin/*
       });
   });
   ```

3. **Middleware Compartido**: Se puede aplicar middleware a nivel de grupo en `api.php`:
   ```php
   Route::prefix('users')
       ->middleware(['throttle:api'])
       ->group(function () {
           require __DIR__.'/users.php';
       });
   ```

4. **Route Names**: Se recomienda nombrar las rutas con prefijo del contexto:
   ```php
   Route::get('/me', [AuthController::class, 'user'])->name('users.me');
   Route::get('/me', [AuthController::class, 'me'])->name('employees.me');
   ```

## 🚀 Próximos Pasos

- [ ] Agregar route names a todas las rutas
- [ ] Implementar rate limiting por contexto
- [ ] Agregar versioning de API (v1, v2)
- [ ] Documentar con OpenAPI/Swagger
- [ ] Agregar tests para cada grupo de rutas
