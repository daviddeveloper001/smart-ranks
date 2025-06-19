# Guía de Despliegue - Smart Ranks API

## Resumen

Esta guía detalla el proceso de despliegue de la API Smart Ranks en diferentes entornos, desde desarrollo local hasta producción.

## Entornos de Despliegue

### 1. Desarrollo Local

#### Prerrequisitos
- PHP 8.2+
- Composer 2.0+
- MySQL 8.0+ o PostgreSQL 12+
- Node.js 18+ (opcional, para frontend)

#### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd smart-ranks
```

2. **Instalar dependencias**
```bash
composer install --no-dev --optimize-autoloader
npm install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

Editar `.env`:
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

4. **Generar claves**
```bash
php artisan key:generate
php artisan jwt:secret
```

5. **Configurar base de datos**
```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE smart_ranks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed
```

6. **Iniciar servidor**
```bash
php artisan serve
```

### 2. Entorno de Staging

#### Configuración del Servidor

**Requisitos del servidor:**
- Ubuntu 20.04+ o CentOS 8+
- PHP 8.2+ con extensiones requeridas
- MySQL 8.0+ o PostgreSQL 12+
- Nginx o Apache
- SSL Certificate

#### Instalación en Ubuntu

1. **Actualizar sistema**
```bash
sudo apt update && sudo apt upgrade -y
```

2. **Instalar PHP y extensiones**
```bash
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis -y
```

3. **Instalar Composer**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

4. **Instalar MySQL**
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

5. **Configurar Nginx**
```bash
sudo apt install nginx -y

# Crear configuración del sitio
sudo nano /etc/nginx/sites-available/smart-ranks
```

Configuración Nginx:
```nginx
server {
    listen 80;
    server_name staging.smartranks.com;
    root /var/www/smart-ranks/public;

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

6. **Habilitar sitio**
```bash
sudo ln -s /etc/nginx/sites-available/smart-ranks /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Despliegue de Código

1. **Clonar en servidor**
```bash
cd /var/www
sudo git clone <repository-url> smart-ranks
sudo chown -R www-data:www-data smart-ranks
cd smart-ranks
```

2. **Configurar aplicación**
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Editar `.env` para staging:
```env
APP_NAME="Smart Ranks API"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.smartranks.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_ranks_staging
DB_USERNAME=smart_ranks_user
DB_PASSWORD=secure_password_here

JWT_SECRET=staging-jwt-secret-here
JWT_TTL=60
JWT_REFRESH_TTL=20160

LOG_CHANNEL=daily
LOG_LEVEL=warning
```

3. **Configurar base de datos**
```bash
# Crear usuario y base de datos
mysql -u root -p
```

```sql
CREATE DATABASE smart_ranks_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'smart_ranks_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON smart_ranks_staging.* TO 'smart_ranks_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

4. **Ejecutar migraciones**
```bash
php artisan key:generate
php artisan jwt:secret
php artisan migrate --force
php artisan db:seed
```

5. **Optimizar para producción**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6. **Configurar permisos**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Entorno de Producción

#### Configuración de Alta Disponibilidad

**Arquitectura recomendada:**
- Load Balancer (HAProxy/Nginx)
- Múltiples servidores de aplicación
- Base de datos replicada
- Redis para caché y sesiones
- CDN para assets estáticos

#### Configuración del Servidor

1. **Configuración de seguridad**
```bash
# Firewall
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable

# Fail2ban
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

2. **Configuración de SSL con Let's Encrypt**
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d api.smartranks.com
```

3. **Configuración de Redis**
```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
```

#### Variables de Entorno de Producción

```env
APP_NAME="Smart Ranks API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.smartranks.com

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_PORT=3306
DB_DATABASE=smart_ranks_prod
DB_USERNAME=smart_ranks_prod_user
DB_PASSWORD=very_secure_password_here

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

JWT_SECRET=production-jwt-secret-here
JWT_TTL=60
JWT_REFRESH_TTL=20160

LOG_CHANNEL=daily
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@smartranks.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Script de Despliegue Automatizado

Crear `deploy.sh`:
```bash
#!/bin/bash

# Variables
APP_DIR="/var/www/smart-ranks"
BACKUP_DIR="/var/backups/smart-ranks"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Iniciando despliegue..."

# Crear backup
echo "Creando backup..."
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/backup_$DATE.tar.gz -C $APP_DIR .

# Pull del código
echo "Actualizando código..."
cd $APP_DIR
git pull origin main

# Instalar dependencias
echo "Instalando dependencias..."
composer install --no-dev --optimize-autoloader

# Limpiar caché
echo "Limpiando caché..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Optimizar
echo "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar servicios
echo "Reiniciando servicios..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "Despliegue completado exitosamente!"
```

Hacer ejecutable:
```bash
chmod +x deploy.sh
```

#### Monitoreo y Logs

1. **Configurar logrotate**
```bash
sudo nano /etc/logrotate.d/smart-ranks
```

```
/var/www/smart-ranks/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

2. **Configurar monitoreo con Supervisor**
```bash
sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/smart-ranks.conf
```

```
[program:smart-ranks-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/smart-ranks/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/smart-ranks/storage/logs/worker.log
stopwaitsecs=3600
```

3. **Habilitar supervisor**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start smart-ranks-worker:*
```

## Despliegue en la Nube

### Heroku

1. **Crear aplicación**
```bash
heroku create smart-ranks-api
```

2. **Configurar add-ons**
```bash
heroku addons:create heroku-postgresql:hobby-dev
heroku addons:create heroku-redis:hobby-dev
```

3. **Configurar variables**
```bash
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set JWT_SECRET=your-production-jwt-secret
```

4. **Desplegar**
```bash
git push heroku main
heroku run php artisan migrate --force
```

### AWS (EC2 + RDS)

1. **Configurar EC2**
```bash
# Conectar a instancia
ssh -i key.pem ubuntu@your-ec2-ip

# Instalar dependencias
sudo apt update
sudo apt install nginx php8.2-fpm mysql-client -y
```

2. **Configurar RDS**
- Crear instancia RDS MySQL
- Configurar security groups
- Obtener endpoint de conexión

3. **Configurar aplicación**
```bash
# Variables de entorno
DB_HOST=your-rds-endpoint.region.rds.amazonaws.com
DB_DATABASE=smart_ranks
DB_USERNAME=admin
DB_PASSWORD=your-rds-password
```

### DigitalOcean App Platform

1. **Crear app en DigitalOcean**
2. **Conectar repositorio Git**
3. **Configurar variables de entorno**
4. **Configurar base de datos MySQL**

## Verificación Post-Despliegue

### Checklist de Verificación

- [ ] La aplicación responde en la URL configurada
- [ ] Los endpoints de autenticación funcionan
- [ ] Las migraciones se ejecutaron correctamente
- [ ] Los logs se están generando
- [ ] El SSL está configurado correctamente
- [ ] Los permisos de archivos son correctos
- [ ] El caché está funcionando
- [ ] Las colas están procesando jobs

### Comandos de Verificación

```bash
# Verificar estado de la aplicación
php artisan about

# Verificar configuración
php artisan config:show

# Verificar rutas
php artisan route:list

# Verificar logs
tail -f storage/logs/laravel.log

# Verificar permisos
ls -la storage/
ls -la bootstrap/cache/
```

## Rollback

En caso de problemas, seguir estos pasos:

1. **Revertir código**
```bash
git reset --hard HEAD~1
git push --force origin main
```

2. **Restaurar backup de base de datos**
```bash
mysql -u username -p database_name < backup.sql
```

3. **Limpiar caché**
```bash
php artisan config:clear
php artisan cache:clear
```

## Seguridad en Producción

### Configuraciones de Seguridad

1. **Ocultar información de Laravel**
```php
// config/app.php
'debug' => false,
'env' => 'production',
```

2. **Configurar headers de seguridad**
```nginx
# Nginx
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header X-XSS-Protection "1; mode=block";
```

3. **Rate limiting**
```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // Rutas de API
});
```

4. **Validar inputs**
```php
// Siempre validar en Form Requests
public function rules(): array
{
    return [
        'email' => 'required|email|max:255',
        'password' => 'required|min:8|confirmed',
    ];
}
```

## Monitoreo y Alertas

### Configurar alertas

1. **Uptime Robot** - Monitoreo de endpoints
2. **Laravel Telescope** - Debugging en desarrollo
3. **Laravel Horizon** - Monitoreo de colas
4. **New Relic** - Performance monitoring

### Métricas importantes

- Response time promedio
- Error rate
- Throughput (requests/segundo)
- Uso de memoria y CPU
- Tiempo de respuesta de base de datos

## Conclusión

Este proceso de despliegue asegura una implementación robusta y escalable de la API Smart Ranks, con consideraciones de seguridad, performance y mantenibilidad apropiadas para un entorno de producción. 