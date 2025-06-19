# Guía de Testing - Smart Ranks API

## Resumen

Esta guía detalla la estrategia de testing implementada en el proyecto Smart Ranks API, incluyendo tests unitarios, de integración y de aceptación.

## Estrategia de Testing

### Pirámide de Testing

```
    /\
   /  \     E2E Tests (Pocos)
  /____\    
 /      \   Integration Tests (Algunos)
/________\  Unit Tests (Muchos)
```

### Cobertura Objetivo

- **Cobertura Total**: 80%+
- **Tests Unitarios**: 60% del código
- **Tests de Integración**: 20% del código
- **Tests E2E**: Casos críticos de negocio

## Configuración de Testing

### 1. Configuración de Base de Datos

**Configuración para testing:**
```php
// config/database.php
'testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
],
```

**Variables de entorno para testing:**
```env
# .env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

### 2. Configuración de Pest

**phpunit.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

## Tests Unitarios

### 1. Tests de Servicios

**Ejemplo: ProductServiceTest**
```php
<?php

use App\Services\Api\V1\ProductServiceV1;
use App\Models\Product;
use App\Models\Category;
use App\DataTransferObjects\ProductDTO;

test('can create product', function () {
    // Arrange
    $category = Category::factory()->create();
    $productData = [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 99.99,
        'stock' => 100,
        'category_id' => $category->id
    ];
    
    $productDTO = new ProductDTO(
        $productData['name'],
        $productData['description'],
        $productData['price'],
        $productData['stock'],
        $productData['category_id']
    );
    
    $service = new ProductServiceV1();
    
    // Act
    $product = $service->create($productDTO);
    
    // Assert
    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Test Product');
    expect($product->price)->toBe(99.99);
    expect($product->category_id)->toBe($category->id);
});

test('can update product', function () {
    // Arrange
    $product = Product::factory()->create();
    $category = Category::factory()->create();
    
    $updateData = [
        'name' => 'Updated Product',
        'price' => 149.99,
        'category_id' => $category->id
    ];
    
    $service = new ProductServiceV1();
    
    // Act
    $updatedProduct = $service->update($product->id, $updateData);
    
    // Assert
    expect($updatedProduct->name)->toBe('Updated Product');
    expect($updatedProduct->price)->toBe(149.99);
    expect($updatedProduct->category_id)->toBe($category->id);
});

test('can delete product', function () {
    // Arrange
    $product = Product::factory()->create();
    $service = new ProductServiceV1();
    
    // Act
    $result = $service->delete($product->id);
    
    // Assert
    expect($result)->toBeTrue();
    expect(Product::find($product->id))->toBeNull();
});

test('throws exception when product not found', function () {
    // Arrange
    $service = new ProductServiceV1();
    
    // Act & Assert
    expect(fn() => $service->find(999))->toThrow(ProductException::class);
});
```

### 2. Tests de Repositories

**Ejemplo: ProductRepositoryTest**
```php
<?php

use App\Repositories\V1\ProductRepositoryV1;
use App\Models\Product;
use App\Models\Category;

test('can get all products with filters', function () {
    // Arrange
    $category = Category::factory()->create();
    Product::factory()->count(5)->create(['category_id' => $category->id]);
    Product::factory()->count(3)->create(['category_id' => Category::factory()->create()->id]);
    
    $repository = new ProductRepositoryV1();
    
    // Act
    $products = $repository->all(['category_id' => $category->id]);
    
    // Assert
    expect($products)->toHaveCount(5);
    expect($products->first()->category_id)->toBe($category->id);
});

test('can find product by id', function () {
    // Arrange
    $product = Product::factory()->create();
    $repository = new ProductRepositoryV1();
    
    // Act
    $foundProduct = $repository->find($product->id);
    
    // Assert
    expect($foundProduct)->toBeInstanceOf(Product::class);
    expect($foundProduct->id)->toBe($product->id);
});

test('can create product', function () {
    // Arrange
    $category = Category::factory()->create();
    $productData = [
        'name' => 'New Product',
        'description' => 'Description',
        'price' => 99.99,
        'stock' => 50,
        'category_id' => $category->id
    ];
    
    $repository = new ProductRepositoryV1();
    
    // Act
    $product = $repository->create($productData);
    
    // Assert
    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('New Product');
    expect($product->category_id)->toBe($category->id);
});
```

### 3. Tests de DTOs

**Ejemplo: ProductDTOTest**
```php
<?php

use App\DataTransferObjects\ProductDTO;

test('can create product dto', function () {
    // Act
    $dto = new ProductDTO(
        'Test Product',
        'Test Description',
        99.99,
        100,
        1
    );
    
    // Assert
    expect($dto->name)->toBe('Test Product');
    expect($dto->description)->toBe('Test Description');
    expect($dto->price)->toBe(99.99);
    expect($dto->stock)->toBe(100);
    expect($dto->categoryId)->toBe(1);
});

test('dto properties are readonly', function () {
    // Arrange
    $dto = new ProductDTO('Test', 'Desc', 99.99, 100, 1);
    
    // Act & Assert
    expect(fn() => $dto->name = 'Changed')->toThrow(Error::class);
});
```

## Tests de Integración

### 1. Tests de Autenticación

**Ejemplo: AuthTest**
```php
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can register', function () {
    // Arrange
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];
    
    // Act
    $response = $this->postJson('/api/register', $userData);
    
    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'assigned_role'
            ]
        ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
});

test('user can login', function () {
    // Arrange
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);
    
    // Act
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
            'token_type',
            'expires_in'
        ]);
});

test('user can logout', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    // Act
    $response = $this->postJson('/api/logout');
    
    // Assert
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('admin can register user with admin role', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $userData = [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin'
    ];
    
    // Act
    $response = $this->postJson('/api/register', $userData);
    
    // Assert
    $response->assertStatus(201);
    
    $newUser = User::where('email', 'admin@example.com')->first();
    expect($newUser->hasRole('admin'))->toBeTrue();
});
```

### 2. Tests de Productos

**Ejemplo: ProductTest**
```php
<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

test('can list products', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    Product::factory()->count(5)->create();
    
    // Act
    $response = $this->getJson('/api/v1/products');
    
    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'price', 'stock', 'category']
            ],
            'links',
            'meta'
        ]);
});

test('can get single product', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $product = Product::factory()->create();
    
    // Act
    $response = $this->getJson("/api/v1/products/{$product->id}");
    
    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => $product->name
            ]
        ]);
});

test('admin can create product', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $category = Category::factory()->create();
    
    $productData = [
        'name' => 'New Product',
        'description' => 'Product Description',
        'price' => 99.99,
        'stock' => 100,
        'category_id' => $category->id
    ];
    
    // Act
    $response = $this->postJson('/api/v1/products', $productData);
    
    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'description', 'price', 'stock']
        ]);
    
    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'price' => 99.99
    ]);
});

test('non-admin cannot create product', function () {
    // Arrange
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);
    
    $category = Category::factory()->create();
    
    $productData = [
        'name' => 'New Product',
        'description' => 'Product Description',
        'price' => 99.99,
        'stock' => 100,
        'category_id' => $category->id
    ];
    
    // Act
    $response = $this->postJson('/api/v1/products', $productData);
    
    // Assert
    $response->assertStatus(403);
});

test('admin can update product', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $product = Product::factory()->create();
    
    $updateData = [
        'name' => 'Updated Product',
        'price' => 149.99
    ];
    
    // Act
    $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);
    
    // Assert
    $response->assertStatus(200);
    
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
        'price' => 149.99
    ]);
});

test('admin can delete product', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $product = Product::factory()->create();
    
    // Act
    $response = $this->deleteJson("/api/v1/products/{$product->id}");
    
    // Assert
    $response->assertStatus(200);
    
    $this->assertSoftDeleted('products', [
        'id' => $product->id
    ]);
});
```

### 3. Tests de Validación

**Ejemplo: ProductValidationTest**
```php
<?php

use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

test('validates required fields when creating product', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    // Act
    $response = $this->postJson('/api/v1/products', []);
    
    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
});

test('validates price is numeric and positive', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $category = Category::factory()->create();
    
    // Act
    $response = $this->postJson('/api/v1/products', [
        'name' => 'Test Product',
        'price' => -10,
        'stock' => 100,
        'category_id' => $category->id
    ]);
    
    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});

test('validates category exists', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    // Act
    $response = $this->postJson('/api/v1/products', [
        'name' => 'Test Product',
        'price' => 99.99,
        'stock' => 100,
        'category_id' => 999
    ]);
    
    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);
});
```

## Tests de Auditoría

### 1. Tests de Observers

**Ejemplo: ProductObserverTest**
```php
<?php

use App\Models\User;
use App\Models\Product;
use App\Models\AuditLog;
use Laravel\Sanctum\Sanctum;

test('creates audit log when product is created', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    // Act
    $product = Product::factory()->create();
    
    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'created',
        'auditable_id' => $product->id,
        'auditable_type' => Product::class
    ]);
});

test('creates audit log when product is updated', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $product = Product::factory()->create(['name' => 'Original Name']);
    
    // Act
    $product->update(['name' => 'Updated Name']);
    
    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'updated',
        'auditable_id' => $product->id,
        'auditable_type' => Product::class
    ]);
    
    $auditLog = AuditLog::where('action', 'updated')->first();
    expect($auditLog->changes)->toContain('Updated Name');
});

test('creates audit log when product is deleted', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $product = Product::factory()->create();
    
    // Act
    $product->delete();
    
    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'deleted',
        'auditable_id' => $product->id,
        'auditable_type' => Product::class
    ]);
});
```

## Tests de Performance

### 1. Tests de Carga

**Ejemplo: PerformanceTest**
```php
<?php

use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;

test('can handle multiple concurrent requests', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    Product::factory()->count(100)->create();
    
    // Act & Assert
    $startTime = microtime(true);
    
    for ($i = 0; $i < 10; $i++) {
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200);
    }
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    // Assert execution time is reasonable (less than 5 seconds for 10 requests)
    expect($executionTime)->toBeLessThan(5.0);
});

test('database queries are optimized', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    Product::factory()->count(50)->create();
    
    // Act
    DB::enableQueryLog();
    
    $response = $this->getJson('/api/v1/products?per_page=20');
    
    $queries = DB::getQueryLog();
    
    // Assert
    $response->assertStatus(200);
    
    // Should not have N+1 queries
    expect(count($queries))->toBeLessThan(10);
});
```

## Tests de Seguridad

### 1. Tests de Autorización

**Ejemplo: SecurityTest**
```php
<?php

use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;

test('unauthenticated users cannot access protected endpoints', function () {
    // Act
    $response = $this->getJson('/api/v1/products');
    
    // Assert
    $response->assertStatus(401);
});

test('users cannot access admin endpoints', function () {
    // Arrange
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);
    
    // Act
    $response = $this->postJson('/api/v1/products', []);
    
    // Assert
    $response->assertStatus(403);
});

test('jwt token is required for protected routes', function () {
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalid-token'
    ])->getJson('/api/v1/products');
    
    // Assert
    $response->assertStatus(401);
});
```

## Configuración de CI/CD

### 1. GitHub Actions

**.github/workflows/tests.yml:**
```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: smart_ranks_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo, pdo_mysql, phar, tokenizer, xml, xmlwriter, zip, bcmath, soap, sockets, sodium, pcntl, curl, fileinfo, pdo_sqlite, sqlite3
        coverage: xdebug

    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"

    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

    - name: Generate key
      run: php artisan key:generate

    - name: Set Directory Permissions
      run: chmod -R 777 storage bootstrap/cache

    - name: Create Database
      run: |
        mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS smart_ranks_test;"
        php artisan migrate --force

    - name: Execute tests (Unit and Feature tests) via PHPUnit
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: smart_ranks_test
        DB_USERNAME: root
        DB_PASSWORD: password
      run: vendor/bin/phpunit --coverage-clover=coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        flags: unittests
        name: codecov-umbrella
        fail_ci_if_error: true
```

### 2. Comandos de Testing

**Ejecutar todos los tests:**
```bash
php artisan test
```

**Ejecutar tests específicos:**
```bash
# Tests unitarios
php artisan test --testsuite=Unit

# Tests de integración
php artisan test --testsuite=Feature

# Tests específicos
php artisan test --filter=ProductTest
```

**Ejecutar con coverage:**
```bash
php artisan test --coverage
```

**Ejecutar tests en paralelo:**
```bash
php artisan test --parallel
```

## Mejores Prácticas

### 1. Organización de Tests

- **Naming**: Usar nombres descriptivos que expliquen el comportamiento
- **Arrange-Act-Assert**: Seguir el patrón AAA
- **Isolation**: Cada test debe ser independiente
- **Database**: Usar factories y seeders para datos de prueba

### 2. Factories

**Ejemplo de Factory mejorada:**
```php
<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'category_id' => Category::factory()
        ];
    }
    
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
    
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 500, 2000),
        ]);
    }
}
```

### 3. Datasets

**Usar datasets para múltiples casos:**
```php
test('validates product price', function ($price, $shouldPass) {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);
    
    $category = Category::factory()->create();
    
    $response = $this->postJson('/api/v1/products', [
        'name' => 'Test Product',
        'price' => $price,
        'stock' => 100,
        'category_id' => $category->id
    ]);
    
    if ($shouldPass) {
        $response->assertStatus(201);
    } else {
        $response->assertStatus(422);
    }
})->with([
    'positive price' => [99.99, true],
    'zero price' => [0, false],
    'negative price' => [-10, false],
    'string price' => ['invalid', false],
]);
```

## Conclusión

Esta estrategia de testing asegura:

1. **Calidad del código**: Detección temprana de bugs
2. **Refactoring seguro**: Confianza para cambiar código
3. **Documentación viva**: Tests como documentación del comportamiento
4. **Integración continua**: Despliegue automático y seguro

Los tests deben mantenerse actualizados y ejecutarse regularmente como parte del proceso de desarrollo. 