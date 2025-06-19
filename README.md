# Smart Ranks API - Sistema de Gestión de Productos

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![JWT](https://img.shields.io/badge/JWT-Auth-green.svg)](https://jwt-auth.readthedocs.io/)
[![Spatie Permissions](https://img.shields.io/badge/Spatie-Permissions-orange.svg)](https://spatie.be/docs/laravel-permission)

API RESTful desarrollada en Laravel 12 para la gestión de productos y categorías con sistema de autenticación JWT, autorización basada en roles y auditoría completa de cambios.

## 🚀 Características Principales

- **Autenticación JWT**: Sistema robusto de autenticación con tokens JWT
- **Autorización por Roles**: Middleware de roles usando Spatie Laravel Permission
- **Auditoría Automática**: Sistema de logs automático con Observers
- **API RESTful**: Endpoints bien estructurados siguiendo convenciones REST
- **Validación Robusta**: Validación de datos con mensajes de error claros
- **Soft Deletes**: Eliminación lógica para preservar integridad de datos
- **Arquitectura Limpia**: Separación de responsabilidades con Repositories y Services

## 📋 Tabla de Contenidos

- [Configuración Local](#configuración-local)
- [Endpoints de la API](#endpoints-de-la-api)
- [Autenticación y Autorización](#autenticación-y-autorización)
- [Colección Postman](#colección-postman)
- [Documentación Swagger](#documentación-swagger)
- [Despliegue](#despliegue)
- [Decisiones de Diseño](#decisiones-de-diseño)
- [Estructura del Proyecto](#estructura-del-proyecto)

## 🛠️ Configuración Local

### Prerrequisitos

- PHP 8.2 o superior
- Composer 2.0 o superior
- MySQL 8.0 o PostgreSQL 12
- Node.js 18+ (para desarrollo frontend)

### Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd smart-ranks
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

Editar `.env` con tu configuración:
```env
APP_NAME="Smart Ranks API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_ranks
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=your-jwt-secret-here
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

4. **Generar claves de aplicación**
```bash
php artisan key:generate
php artisan jwt:secret
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Iniciar el servidor de desarrollo**
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

### Comandos Útiles

```bash
# Ejecutar tests
php artisan test

# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Regenerar autoload
composer dump-autoload
```

## 🔌 Endpoints de la API

### Autenticación y Usuarios

#### POST /api/register
Registra un nuevo usuario.

**Parámetros:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "user" // Opcional, solo para admins
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Registration success",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "assigned_role": "user"
    }
}
```

#### POST /api/login
Autentica un usuario y devuelve token JWT.

**Parámetros:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Respuesta:**
```json
{
    "message": "Authenticated",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### POST /api/logout
Invalida el token activo.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

### Gestión de Productos

#### GET /api/v1/products
Lista todos los productos con paginación.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de query (opcionales):**
- `page`: Número de página
- `per_page`: Elementos por página
- `search`: Búsqueda por nombre
- `category_id`: Filtrar por categoría

#### GET /api/v1/products/{id}
Muestra detalle de un producto específico.

#### POST /api/v1/products
Crea un nuevo producto (solo Admin).

**Parámetros:**
```json
{
    "name": "Producto Ejemplo",
    "description": "Descripción del producto",
    "price": 99.99,
    "stock": 100,
    "category_id": 1
}
```

#### PUT /api/v1/products/{id}
Actualiza un producto existente (solo Admin).

#### DELETE /api/v1/products/{id}
Elimina un producto (solo Admin).

### Gestión de Categorías

#### GET /api/v1/categories
Lista todas las categorías.

#### GET /api/v1/categories/{id}
Muestra detalle de una categoría.

#### POST /api/v1/categories
Crea una nueva categoría (solo Admin).

**Parámetros:**
```json
{
    "name": "Electrónicos",
    "description": "Productos electrónicos"
}
```

#### PUT /api/v1/categories/{id}
Actualiza una categoría (solo Admin).

#### DELETE /api/v1/categories/{id}
Elimina una categoría (solo Admin).

## 🔐 Autenticación y Autorización

### Sistema de Roles

El sistema utiliza **Spatie Laravel Permission** para manejar roles y permisos:

- **Admin**: Acceso completo a todos los endpoints
- **User**: Solo lectura de productos y categorías

### Middleware de Autorización

```php
// Rutas protegidas por autenticación
Route::middleware(['auth:api'])->group(function () {
    // Rutas de solo lectura
    Route::apiResource('categories', CategoryControllerV1::class)->only(['index', 'show']);
    Route::apiResource('products', ProductControllerV1::class)->only(['index', 'show']);
    
    // Rutas que requieren rol admin
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('categories', CategoryControllerV1::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('products', ProductControllerV1::class)->only(['store', 'update', 'destroy']);
    });
});
```

### Manejo de Errores

La API devuelve respuestas consistentes para errores:

```json
{
    "success": false,
    "message": "Unauthorized",
    "errors": {
        "detail": "You do not have permission to perform this action"
    }
}
```

## 📚 Colección Postman

### Importar Colección

1. Descarga el archivo `Smart_Ranks_API.postman_collection.json`
2. Abre Postman
3. Haz clic en "Import"
4. Selecciona el archivo descargado
5. La colección se importará con todos los endpoints configurados

### Variables de Entorno

Configura las siguientes variables en Postman:

- `base_url`: `http://localhost:8000`
- `token`: Se establece automáticamente después del login

### Uso de la Colección

1. Ejecuta primero el endpoint `POST /api/login`
2. El token se guardará automáticamente en la variable `token`
3. Todos los endpoints protegidos usarán este token automáticamente

## 📖 Documentación Swagger

### Acceso a la Documentación

La documentación Swagger estará disponible en:
```
http://localhost:8000/api/documentation
```

### Características

- Documentación interactiva de todos los endpoints
- Ejemplos de requests y responses
- Autenticación JWT integrada
- Pruebas directas desde el navegador

## 🚀 Despliegue

### URL de Producción

```
https://smart-ranks-api.herokuapp.com
```

### Variables de Entorno de Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smart-ranks-api.herokuapp.com

DB_CONNECTION=mysql
DB_HOST=your-production-db-host
DB_DATABASE=smart_ranks_prod
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

JWT_SECRET=your-production-jwt-secret
```

### Comandos de Despliegue

```bash
# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
php artisan migrate --force
```

## 🏗️ Decisiones de Diseño

### 1. Elección de Sistema de Roles

**Decisión**: Uso de **Spatie Laravel Permission** en lugar de enum o tabla simple.

**Justificación**:
- **Flexibilidad**: Permite agregar permisos granulares en el futuro
- **Escalabilidad**: Fácil extensión para nuevos roles y permisos
- **Mantenibilidad**: Librería bien mantenida y documentada
- **Integración**: Perfecta integración con Laravel y JWT

### 2. Middleware de Autorización

**Decisión**: Middleware `role:admin` personalizado.

**Implementación**:
```php
Route::middleware(['role:admin'])->group(function () {
    // Endpoints protegidos
});
```

**Ventajas**:
- Sintaxis clara y legible
- Fácil de mantener y extender
- Separación clara de responsabilidades

### 3. Sistema de Auditoría

**Decisión**: Implementación de Observers para auditoría automática.

**Características**:
- **Automático**: No requiere código adicional en controladores
- **Completo**: Registra creación, actualización y eliminación
- **Detallado**: Almacena cambios, IP, User Agent
- **Polimórfico**: Funciona con cualquier modelo

**Implementación**:
```php
class ProductObserver
{
    public function created(Product $product): void
    {
        $this->log('created', $product);
    }
    
    public function updated(Product $product): void
    {
        $this->log('updated', $product, $product->getChanges());
    }
}
```

### 4. Arquitectura de Capas

**Decisión**: Separación en Repositories, Services y Controllers.

**Estructura**:
```
Controllers/ → Services/ → Repositories/ → Models/
```

**Beneficios**:
- **Testabilidad**: Fácil mockeo de dependencias
- **Mantenibilidad**: Código organizado y reutilizable
- **Escalabilidad**: Fácil agregar nuevas funcionalidades

### 5. Autenticación JWT

**Decisión**: JWT en lugar de tokens de Sanctum.

**Justificación**:
- **Stateless**: No requiere almacenamiento en servidor
- **Escalabilidad**: Ideal para APIs distribuidas
- **Performance**: Menor overhead en cada request
- **Flexibilidad**: Fácil integración con frontend

## 📁 Estructura del Proyecto

```
smart-ranks/
├── app/
│   ├── Console/Commands/          # Comandos personalizados
│   ├── DataTransferObjects/       # DTOs para transferencia de datos
│   ├── Exceptions/                # Excepciones personalizadas
│   ├── Filters/                   # Filtros para queries
│   ├── Http/
│   │   ├── Controllers/Api/       # Controladores de API
│   │   ├── Requests/Api/          # Form Requests de validación
│   │   └── Resources/Api/         # API Resources
│   ├── Interfaces/                # Interfaces de contratos
│   ├── Models/                    # Modelos Eloquent
│   ├── Observers/                 # Observers para auditoría
│   ├── Repositories/              # Patrón Repository
│   └── Services/                  # Lógica de negocio
├── database/
│   ├── migrations/                # Migraciones de BD
│   ├── seeders/                   # Seeders de datos
│   └── factories/                 # Factories para testing
└── routes/
    ├── api.php                    # Rutas de autenticación
    └── api_v1.php                 # Rutas de API v1
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=ProductTest

# Con coverage
php artisan test --coverage
```

### Estructura de Tests

```
tests/
├── Feature/                       # Tests de integración
│   ├── AuthTest.php
│   ├── ProductTest.php
│   └── CategoryTest.php
└── Unit/                         # Tests unitarios
    ├── Services/
    └── Repositories/
```

## 🔧 Configuración Adicional

### Logs de Auditoría

Los logs de auditoría se almacenan en la tabla `audit_logs` con:

- **Usuario**: Quién realizó la acción
- **Acción**: created, updated, deleted
- **Modelo**: Tipo de entidad afectada
- **Cambios**: JSON con los cambios realizados
- **Metadatos**: IP, User Agent, timestamp

### Soft Deletes

Todos los modelos principales implementan soft deletes:

- **Preservación**: Los datos no se eliminan físicamente
- **Recuperación**: Posibilidad de restaurar registros eliminados
- **Integridad**: Mantiene referencias y auditoría

## 📞 Soporte

Para soporte técnico o preguntas sobre la implementación:

- **Email**: developer@smartranks.com
- **Documentación**: [Wiki del proyecto](https://github.com/smart-ranks/wiki)
- **Issues**: [GitHub Issues](https://github.com/smart-ranks/issues)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

**Desarrollado con ❤️ usando Laravel 12**
