# Documentación de la API - Smart Ranks

## Información General

- **Base URL**: `https://api.smartranks.com`
- **Versión**: v1
- **Formato**: JSON
- **Autenticación**: JWT Bearer Token

## Autenticación

### Obtener Token

**POST** `/api/login`

Autentica un usuario y devuelve un token JWT válido.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Respuesta Exitosa (200):**
```json
{
    "message": "Authenticated",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

**Respuesta de Error (401):**
```json
{
    "error": "Unauthorized"
}
```

### Registrar Usuario

**POST** `/api/register`

Registra un nuevo usuario en el sistema.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "user"
}
```

**Nota:** El campo `role` es opcional y solo puede ser establecido por usuarios admin.

**Respuesta Exitosa (201):**
```json
{
    "success": true,
    "message": "Registration success",
    "data": {
        "user": {
            "id": 2,
            "name": "Jane Doe",
            "email": "jane@example.com",
            "created_at": "2024-01-15T10:35:00.000000Z",
            "updated_at": "2024-01-15T10:35:00.000000Z"
        },
        "assigned_role": "user"
    }
}
```

**Respuesta de Error (422):**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password confirmation does not match."
        ]
    }
}
```

### Cerrar Sesión

**POST** `/api/logout`

Invalida el token JWT activo.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Logout success"
}
```

## Gestión de Productos

### Listar Productos

**GET** `/api/v1/products`

Obtiene una lista paginada de productos.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (opcional): Número de página (default: 1)
- `per_page` (opcional): Elementos por página (default: 15)
- `search` (opcional): Búsqueda por nombre
- `category_id` (opcional): Filtrar por categoría
- `min_price` (opcional): Precio mínimo
- `max_price` (opcional): Precio máximo
- `sort_by` (opcional): Campo para ordenar (name, price, created_at)
- `sort_order` (opcional): Orden (asc, desc)

**Ejemplo de Request:**
```
GET /api/v1/products?page=1&per_page=10&search=laptop&category_id=1&sort_by=price&sort_order=desc
```

**Respuesta Exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Laptop Gaming Pro",
            "description": "Laptop de alto rendimiento para gaming",
            "price": "1299.99",
            "stock": 50,
            "category": {
                "id": 1,
                "name": "Electrónicos",
                "description": "Productos electrónicos"
            },
            "created_at": "2024-01-15T10:00:00.000000Z",
            "updated_at": "2024-01-15T10:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://api.smartranks.com/api/v1/products?page=1",
        "last": "http://api.smartranks.com/api/v1/products?page=5",
        "prev": null,
        "next": "http://api.smartranks.com/api/v1/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 10,
        "to": 10,
        "total": 50
    }
}
```

### Obtener Producto

**GET** `/api/v1/products/{id}`

Obtiene los detalles de un producto específico.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Laptop Gaming Pro",
        "description": "Laptop de alto rendimiento para gaming",
        "price": "1299.99",
        "stock": 50,
        "category": {
            "id": 1,
            "name": "Electrónicos",
            "description": "Productos electrónicos"
        },
        "created_at": "2024-01-15T10:00:00.000000Z",
        "updated_at": "2024-01-15T10:00:00.000000Z"
    }
}
```

**Respuesta de Error (404):**
```json
{
    "success": false,
    "message": "Product not found"
}
```

### Crear Producto (Admin)

**POST** `/api/v1/products`

Crea un nuevo producto. Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Nuevo Producto",
    "description": "Descripción del nuevo producto",
    "price": 99.99,
    "stock": 100,
    "category_id": 1
}
```

**Validaciones:**
- `name`: Requerido, máximo 255 caracteres
- `description`: Opcional, texto
- `price`: Requerido, numérico, mínimo 0
- `stock`: Requerido, entero, mínimo 0
- `category_id`: Requerido, debe existir en la tabla categories

**Respuesta Exitosa (201):**
```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "id": 2,
        "name": "Nuevo Producto",
        "description": "Descripción del nuevo producto",
        "price": "99.99",
        "stock": 100,
        "category": {
            "id": 1,
            "name": "Electrónicos"
        },
        "created_at": "2024-01-15T11:00:00.000000Z",
        "updated_at": "2024-01-15T11:00:00.000000Z"
    }
}
```

**Respuesta de Error (403):**
```json
{
    "success": false,
    "message": "Unauthorized",
    "errors": {
        "detail": "You do not have permission to perform this action"
    }
}
```

**Respuesta de Error (422):**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "price": [
            "The price must be a number."
        ],
        "category_id": [
            "The selected category id is invalid."
        ]
    }
}
```

### Actualizar Producto (Admin)

**PUT** `/api/v1/products/{id}`

Actualiza un producto existente. Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Producto Actualizado",
    "description": "Nueva descripción",
    "price": 149.99,
    "stock": 75,
    "category_id": 2
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Product updated successfully",
    "data": {
        "id": 1,
        "name": "Producto Actualizado",
        "description": "Nueva descripción",
        "price": "149.99",
        "stock": 75,
        "category": {
            "id": 2,
            "name": "Tecnología"
        },
        "created_at": "2024-01-15T10:00:00.000000Z",
        "updated_at": "2024-01-15T11:30:00.000000Z"
    }
}
```

### Eliminar Producto (Admin)

**DELETE** `/api/v1/products/{id}`

Elimina un producto (soft delete). Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Product deleted successfully"
}
```

## Gestión de Categorías

### Listar Categorías

**GET** `/api/v1/categories`

Obtiene una lista de todas las categorías.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (opcional): Número de página
- `per_page` (opcional): Elementos por página
- `search` (opcional): Búsqueda por nombre

**Respuesta Exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Electrónicos",
            "description": "Productos electrónicos y tecnología",
            "products_count": 15,
            "created_at": "2024-01-15T09:00:00.000000Z",
            "updated_at": "2024-01-15T09:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://api.smartranks.com/api/v1/categories?page=1",
        "last": "http://api.smartranks.com/api/v1/categories?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 15,
        "to": 5,
        "total": 5
    }
}
```

### Obtener Categoría

**GET** `/api/v1/categories/{id}`

Obtiene los detalles de una categoría específica.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Electrónicos",
        "description": "Productos electrónicos y tecnología",
        "products": [
            {
                "id": 1,
                "name": "Laptop Gaming Pro",
                "price": "1299.99",
                "stock": 50
            }
        ],
        "created_at": "2024-01-15T09:00:00.000000Z",
        "updated_at": "2024-01-15T09:00:00.000000Z"
    }
}
```

### Crear Categoría (Admin)

**POST** `/api/v1/categories`

Crea una nueva categoría. Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Nueva Categoría",
    "description": "Descripción de la nueva categoría"
}
```

**Validaciones:**
- `name`: Requerido, máximo 255 caracteres, único
- `description`: Opcional, texto

**Respuesta Exitosa (201):**
```json
{
    "success": true,
    "message": "Category created successfully",
    "data": {
        "id": 6,
        "name": "Nueva Categoría",
        "description": "Descripción de la nueva categoría",
        "created_at": "2024-01-15T12:00:00.000000Z",
        "updated_at": "2024-01-15T12:00:00.000000Z"
    }
}
```

### Actualizar Categoría (Admin)

**PUT** `/api/v1/categories/{id}`

Actualiza una categoría existente. Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
    "name": "Categoría Actualizada",
    "description": "Nueva descripción de la categoría"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Category updated successfully",
    "data": {
        "id": 1,
        "name": "Categoría Actualizada",
        "description": "Nueva descripción de la categoría",
        "created_at": "2024-01-15T09:00:00.000000Z",
        "updated_at": "2024-01-15T12:30:00.000000Z"
    }
}
```

### Eliminar Categoría (Admin)

**DELETE** `/api/v1/categories/{id}`

Elimina una categoría (soft delete). Requiere rol de administrador.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Category deleted successfully"
}
```

## Auditoría

### Listar Logs de Auditoría

**GET** `/api/v1/audit-logs`

Obtiene una lista paginada de logs de auditoría.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (opcional): Número de página
- `per_page` (opcional): Elementos por página
- `user_id` (opcional): Filtrar por usuario
- `action` (opcional): Filtrar por acción (created, updated, deleted)
- `auditable_type` (opcional): Filtrar por tipo de modelo
- `date_from` (opcional): Fecha desde (YYYY-MM-DD)
- `date_to` (opcional): Fecha hasta (YYYY-MM-DD)

**Respuesta Exitosa (200):**
```json
{
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "action": "created",
            "auditable_type": "App\\Models\\Product",
            "auditable_id": 1,
            "changes": null,
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "created_at": "2024-01-15T10:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://api.smartranks.com/api/v1/audit-logs?page=1",
        "last": "http://api.smartranks.com/api/v1/audit-logs?page=10",
        "prev": null,
        "next": "http://api.smartranks.com/api/v1/audit-logs?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

### Obtener Log de Auditoría

**GET** `/api/v1/audit-logs/{id}`

Obtiene los detalles de un log de auditoría específico.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**
```json
{
    "data": {
        "id": 1,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "action": "updated",
        "auditable_type": "App\\Models\\Product",
        "auditable_id": 1,
        "changes": {
            "name": {
                "old": "Producto Original",
                "new": "Producto Actualizado"
            },
            "price": {
                "old": "99.99",
                "new": "149.99"
            }
        },
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        "created_at": "2024-01-15T11:30:00.000000Z"
    }
}
```

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Request exitoso |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Datos inválidos |
| 401 | Unauthorized - Token inválido o faltante |
| 403 | Forbidden - Sin permisos para la acción |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Validación fallida |
| 429 | Too Many Requests - Rate limit excedido |
| 500 | Internal Server Error - Error del servidor |

## Manejo de Errores

### Estructura de Error

Todos los errores siguen una estructura consistente:

```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "field_name": [
            "Mensaje de error específico"
        ]
    }
}
```

### Ejemplos de Errores Comunes

**Validación Fallida (422):**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required.",
            "The email must be a valid email address."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

**No Autorizado (401):**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

**Sin Permisos (403):**
```json
{
    "success": false,
    "message": "Unauthorized",
    "errors": {
        "detail": "You do not have permission to perform this action"
    }
}
```

**Recurso No Encontrado (404):**
```json
{
    "success": false,
    "message": "Product not found"
}
```

## Rate Limiting

La API implementa rate limiting para proteger contra abuso:

- **Endpoints de autenticación**: 5 requests por minuto
- **Endpoints generales**: 60 requests por minuto
- **Endpoints de administración**: 30 requests por minuto

Cuando se excede el límite, se devuelve un error 429:

```json
{
    "success": false,
    "message": "Too Many Attempts.",
    "errors": {
        "detail": "Please try again in 60 seconds."
    }
}
```

## Paginación

Todos los endpoints de listado implementan paginación con los siguientes parámetros:

- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 15, máximo: 100)

La respuesta incluye metadatos de paginación:

```json
{
    "data": [...],
    "links": {
        "first": "http://api.smartranks.com/api/v1/products?page=1",
        "last": "http://api.smartranks.com/api/v1/products?page=5",
        "prev": null,
        "next": "http://api.smartranks.com/api/v1/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

## Filtros y Búsqueda

### Filtros Disponibles

**Productos:**
- `search`: Búsqueda por nombre
- `category_id`: Filtrar por categoría
- `min_price`: Precio mínimo
- `max_price`: Precio máximo
- `in_stock`: Solo productos con stock (true/false)

**Categorías:**
- `search`: Búsqueda por nombre

**Auditoría:**
- `user_id`: Filtrar por usuario
- `action`: Filtrar por acción
- `auditable_type`: Filtrar por tipo de modelo
- `date_from`: Fecha desde
- `date_to`: Fecha hasta

### Ordenamiento

Los endpoints soportan ordenamiento con los parámetros:

- `sort_by`: Campo para ordenar
- `sort_order`: Orden (asc, desc)

**Ejemplo:**
```
GET /api/v1/products?sort_by=price&sort_order=desc
```

## Ejemplos de Uso

### Flujo Completo de Autenticación

```bash
# 1. Login
curl -X POST https://api.smartranks.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@smartranks.com",
    "password": "password123"
  }'

# Respuesta: {"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."}

# 2. Usar token en requests
curl -X GET https://api.smartranks.com/api/v1/products \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"

# 3. Logout
curl -X POST https://api.smartranks.com/api/logout \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

### Crear Producto Completo

```bash
# 1. Crear categoría
curl -X POST https://api.smartranks.com/api/v1/categories \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electrónicos",
    "description": "Productos electrónicos"
  }'

# 2. Crear producto
curl -X POST https://api.smartranks.com/api/v1/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop Gaming",
    "description": "Laptop de alto rendimiento",
    "price": 1299.99,
    "stock": 50,
    "category_id": 1
  }'

# 3. Listar productos
curl -X GET "https://api.smartranks.com/api/v1/products?category_id=1&sort_by=price&sort_order=desc" \
  -H "Authorization: Bearer {token}"
```

## Soporte

Para soporte técnico o preguntas sobre la API:

- **Email**: api-support@smartranks.com
- **Documentación**: https://docs.smartranks.com
- **Status Page**: https://status.smartranks.com
- **GitHub Issues**: https://github.com/smart-ranks/api/issues 