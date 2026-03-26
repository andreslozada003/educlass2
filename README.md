# CellFix Pro - Sistema de Gestión para Ventas y Reparaciones de Celulares

Sistema completo para la gestión de ventas, reparaciones e inventario de celulares y accesorios. Desarrollado con **Laravel 12** y **PHP 8.2+**.

## 🚀 Características

### Módulos Principales

- **🔐 Autenticación y Roles**
  - Admin: Acceso total al sistema
  - Técnico: Gestión de reparaciones
  - Vendedor: Punto de venta y consulta de productos

- **📊 Dashboard**
  - Ventas del día/semana/mes
  - Reparaciones pendientes
  - Alertas de stock bajo
  - Gráficas de ingresos

- **👥 Clientes**
  - Registro completo de clientes
  - Historial de compras y reparaciones
  - Integración con WhatsApp

- **📦 Inventario**
  - Productos (celulares, accesorios, repuestos)
  - Control de stock en tiempo real
  - Alertas de stock bajo
  - Categorías y subcategorías

- **💰 Punto de Venta (POS)**
  - Interfaz rápida tipo caja
  - Múltiples métodos de pago
  - Generación de tickets
  - Descuentos y promociones

- **🔧 Reparaciones**
  - Órdenes de servicio con folio único
  - Flujo de estados: Recibido → Diagnóstico → En Reparación → Listo → Entregado
  - Fotos antes/después
  - Historial de cambios
  - Notificaciones al cliente

- **📈 Reportes**
  - Ventas por período
  - Productos más vendidos
  - Ganancias y márgenes
  - Reparaciones por técnico
  - Inventario valorizado

## 📋 Requisitos

- PHP 8.2 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Composer 2.0+
- Node.js 18+ (opcional, para assets)
- Extensiones PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## 🛠️ Instalación

### 1. Clonar el repositorio

```bash
cd /var/www/html
git clone <url-del-repositorio> cellfix-pro
cd cellfix-pro
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` con tus configuraciones:

```env
APP_NAME="CellFix Pro"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cellfix_pro
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 4. Crear la base de datos

```bash
mysql -u root -p
CREATE DATABASE cellfix_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 6. Configurar permisos

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 8. Configurar el servidor web

#### Apache

Asegúrate de que el DocumentRoot apunte a la carpeta `public`:

```apache
<VirtualHost *:80>
    ServerName cellfix.local
    DocumentRoot /var/www/html/cellfix-pro/public
    
    <Directory /var/www/html/cellfix-pro/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Habilita el módulo rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    server_name cellfix.local;
    root /var/www/html/cellfix-pro/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 👤 Usuarios por defecto

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@cellfix.com | password |
| Técnico | tecnico@cellfix.com | password |
| Vendedor | vendedor@cellfix.com | password |

## 📁 Estructura del Proyecto

```
cellfix-pro/
├── app/
│   ├── Http/
│   │   └── Controllers/    # Controladores
│   ├── Models/             # Modelos Eloquent
│   └── Providers/          # Service Providers
├── bootstrap/
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones
│   └── seeders/            # Seeders
├── public/                 # DocumentRoot
├── resources/
│   └── views/              # Vistas Blade
├── routes/
│   ├── web.php            # Rutas web
│   └── api.php            # Rutas API
├── storage/               # Logs, cache, uploads
└── vendor/                # Dependencias Composer
```

## 🔧 Comandos Útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Crear usuario admin
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@cellfix.com', 'password' => bcrypt('password')])->assignRole('admin');

# Backup de base de datos
php artisan db:backup

# Restaurar base de datos
php artisan db:restore
```

## 🌐 Acceso al Sistema

Una vez instalado, accede al sistema desde tu navegador:

```
http://tu-dominio.com/login
```

## 📱 Flujo de Trabajo

### Venta Rápida
1. Ir a "Punto de Venta"
2. Buscar y seleccionar productos
3. Seleccionar cliente (opcional)
4. Elegir método de pago
5. Cobrar e imprimir ticket

### Nueva Reparación
1. Ir a "Reparaciones" → "Nueva Orden"
2. Seleccionar o registrar cliente
3. Capturar datos del dispositivo
4. Describir el problema
5. Tomar fotos del estado
6. Asignar técnico y costo estimado
7. Guardar y entregar folio al cliente

### Seguimiento de Reparación
1. El técnico actualiza el estado
2. Sistema notifica cuando está "Listo"
3. Cliente recibe notificación
4. Se entrega el equipo y se marca como "Entregado"

## 📄 Licencia

Este software es propietario. Todos los derechos reservados.

## 🤝 Soporte

Para soporte técnico o consultas:
- Email: soporte@cellfix.com
- Teléfono: 555-123-4567

---

**CellFix Pro** - Simplificando la gestión de tu negocio de celulares 📱🔧
