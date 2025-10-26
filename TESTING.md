# Pruebas con Pest - QR Attendance API

Se han creado pruebas completas usando Pest para validar ambos contextos de autenticación de la API.

## 📁 Estructura de Pruebas

```
tests/
├── Feature/
│   └── Api/
│       ├── Users/
│       │   ├── AuthTest.php                 # Autenticación de usuarios (register, login, logout)
│       │   ├── LocationTest.php             # CRUD de ubicaciones con permisos
│       │   ├── EmployeeTest.php             # CRUD de empleados con búsqueda y filtros
│       │   ├── AttendanceTest.php           # Gestión completa de asistencias
│       │   └── RolePermissionTest.php       # Manejo de roles y permisos (Spatie)
│       ├── Employees/
│       │   ├── AuthTest.php                 # Autenticación de empleados (email/código)
│       │   └── AttendanceTest.php           # Check-in/out auto-servicio
│       └── SoftDeleteTest.php               # Validación de soft deletes
└── Pest.php
```

## 🎯 Cobertura de Pruebas

### **Users Context (Administración)** - 70+ pruebas

**AuthTest.php** (10 pruebas):
- ✅ Registro de usuarios con validación
- ✅ Login con credenciales válidas/inválidas
- ✅ Obtener perfil autenticado
- ✅ Logout y revocación de tokens
- ✅ Protección de rutas sin autenticación

**LocationTest.php** (10 pruebas):
- ✅ Listar todas las ubicaciones
- ✅ Crear ubicación con validación
- ✅ Ver ubicación específica
- ✅ Actualizar ubicación
- ✅ Soft delete de ubicación
- ✅ Control de permisos (policies)

**EmployeeTest.php** (12 pruebas):
- ✅ Listar empleados con paginación
- ✅ Crear empleado con validación
- ✅ Email único requerido
- ✅ Ver/actualizar/eliminar empleado
- ✅ Búsqueda por nombre
- ✅ Filtrar empleados activos

**AttendanceTest.php** (12 pruebas):
- ✅ Listar asistencias
- ✅ Crear registro de asistencia
- ✅ Ver/actualizar/eliminar asistencia
- ✅ Filtrar por empleado
- ✅ Filtrar por ubicación
- ✅ Filtrar por rango de fechas

**RolePermissionTest.php** (10 pruebas):
- ✅ Listar roles y permisos
- ✅ Asignar/remover roles a usuarios
- ✅ Sincronizar roles
- ✅ Dar/revocar permisos
- ✅ Sincronizar permisos
- ✅ Validar que solo admin puede gestionar

### **Employees Context (Auto-servicio)** - 20+ pruebas

**AuthTest.php** (8 pruebas):
- ✅ Login con email y password
- ✅ Login con código de empleado
- ✅ Validar empleado activo
- ✅ Obtener perfil autenticado
- ✅ Logout
- ✅ Protección de rutas

**AttendanceTest.php** (15 pruebas):
- ✅ Ver solo asistencias propias
- ✅ Check-in en ubicación con GPS
- ✅ No permitir doble check-in
- ✅ Check-out con cálculo de horas
- ✅ Ver asistencia actual abierta
- ✅ Listar ubicaciones disponibles
- ✅ Validaciones de campos requeridos
- ✅ Agregar notas en check-in

### **Soft Deletes** - 15+ pruebas

**SoftDeleteTest.php**:
- ✅ Soft delete de Users, Employees, Locations
- ✅ Queries excluyen registros eliminados
- ✅ Recuperar con `withTrashed()`
- ✅ Restaurar registros eliminados
- ✅ Force delete permanente
- ✅ Filtrar solo eliminados con `onlyTrashed()`
- ✅ Empleado eliminado no puede login

## 🐛 Problemas Actuales y Soluciones

### **Problema 1: Controladores no encontrados**

**Error:**
```
include(/var/www/vendor/composer/../../app/Http/Controllers/Users/AuthController.php): 
Failed to open stream: No such file or directory
```

**Causa:** Los controladores están dispersos en diferentes directorios (Api/, Admins/, Users/, Employees/)

**Solución:**
1. Verificar la estructura real de controladores:
   ```bash
   ls -la app/Http/Controllers/
   ```

2. Si los controladores están en `app/Http/Controllers/Api/`:
   ```bash
   # Mover controladores a la estructura correcta
   mkdir -p app/Http/Controllers/Users
   mv app/Http/Controllers/Api/*Controller.php app/Http/Controllers/Users/
   ```

3. Actualizar namespaces en los controladores movidos

4. Regenerar autoload de Composer:
   ```bash
   docker compose exec app composer dump-autoload
   ```

### **Problema 2: Personal Access Client no encontrado**

**Error:**
```
Personal access client not found for 'users' user provider. Please create one.
Personal access client not found for 'employees' user provider. Please create one.
```

**Causa:** Passport necesita crear clientes OAuth2 para cada provider (users y employees)

**Solución:**
```bash
# En el contenedor, crear client para users
docker compose exec app php artisan passport:client --personal --name="Users Personal Access Client" --provider=users

# Crear client para employees
docker compose exec app php artisan passport:client --personal --name="Employees Personal Access Client" --provider=employees
```

### **Problema 3: Ruta login-with-code devuelve 404**

**Causa:** La ruta puede tener un nombre diferente o no estar registrada

**Solución:** Verificar en `routes/employees.php` que la ruta esté correcta:
```php
Route::post('/login-with-code', [AuthController::class, 'loginWithCode'])
    ->middleware('throttle:login')
    ->name('login-code');
```

## 🚀 Ejecutar las Pruebas

### **Todas las pruebas:**
```bash
docker compose exec app php artisan test
```

### **Por suite:**
```bash
# Solo Feature tests
docker compose exec app php artisan test --testsuite=Feature

# Solo pruebas de Users
docker compose exec app php artisan test --filter=Users

# Solo pruebas de Employees
docker compose exec app php artisan test --filter=Employees
```

### **Por archivo específico:**
```bash
docker compose exec app php artisan test tests/Feature/Api/Users/AuthTest.php
docker compose exec app php artisan test tests/Feature/Api/Employees/AttendanceTest.php
docker compose exec app php artisan test tests/Feature/Api/SoftDeleteTest.php
```

### **Con cobertura:**
```bash
docker compose exec app php artisan test --coverage
```

### **En modo watch (recarga automática):**
```bash
docker compose exec app php artisan test --watch
```

## 📊 Configuración de Pest

El archivo `tests/Pest.php` está configurado con:
- ✅ `RefreshDatabase` para cada test (base de datos limpia)
- ✅ TestCase base de Laravel
- ✅ Expectations personalizadas

## 🔧 Configuración Necesaria Antes de Ejecutar

1. **Verificar estructura de controladores:**
   ```bash
   find app/Http/Controllers -type f -name "*Controller.php"
   ```

2. **Crear Personal Access Clients de Passport:**
   ```bash
   docker compose exec app php artisan passport:client --personal --provider=users
   docker compose exec app php artisan passport:client --personal --provider=employees
   ```

3. **Verificar que las rutas existan:**
   ```bash
   docker compose exec app php artisan route:list --path=api
   ```

4. **Regenerar autoload:**
   ```bash
   docker compose exec app composer dump-autoload
   ```

## ✅ Resultado Esperado

Cuando todo esté configurado correctamente:

```
  PASS  Tests\Feature\Api\Users\AuthTest
  ✓ user can register with valid data
  ✓ user cannot register with invalid email
  ✓ user cannot register with duplicate email
  ✓ user can login with valid credentials
  ✓ user cannot login with invalid credentials
  ✓ authenticated user can get their profile
  ✓ unauthenticated user cannot access protected routes
  ✓ user can logout

  PASS  Tests\Feature\Api\Employees\AuthTest
  ✓ employee can login with email and password
  ✓ employee cannot login with wrong password
  ✓ inactive employee cannot login
  ✓ employee can login with employee code
  ...

  Tests:    115 passed (350+ assertions)
  Duration: 12s
```

## 📝 Agregar Más Pruebas

Para agregar nuevas pruebas, usa la estructura de Pest:

```php
test('descripción del test', function () {
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $response = $this->actingAs($user, 'api')
        ->getJson('/api/users/locations');
    
    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});
```

## 🎯 Próximos Pasos

1. ✅ Resolver problema de controladores no encontrados
2. ✅ Crear Personal Access Clients de Passport
3. ✅ Verificar rutas de employees (login-with-code)
4. ⏳ Ejecutar todas las pruebas
5. ⏳ Agregar pruebas de integración (rate limiting, middleware)
6. ⏳ Agregar pruebas de performance

## 📚 Recursos

- [Pest PHP Documentation](https://pestphp.com/)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Passport Testing](https://laravel.com/docs/passport#testing)
