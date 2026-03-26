# Instrucciones de Instalación - CellFix Pro

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

1. **PHP 8.2 o superior**
   - Extensiones requeridas: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD

2. **MySQL 8.0 o superior** (o MariaDB 10.3+)

3. **Composer** (gestor de dependencias PHP)
   - Descargar desde: https://getcomposer.org/

4. **Node.js 18+ y NPM**
   - Descargar desde: https://nodejs.org/

5. **Servidor Web** (Apache o Nginx)

---

## 🚀 Instalación Paso a Paso

### Paso 1: Preparar el Proyecto

```bash
# Navegar al directorio del proyecto
cd /ruta/donde/está/el/proyecto/celulares-reparaciones

# Copiar el archivo de configuración
cp .env.example .env
```

### Paso 2: Instalar Dependencias de PHP

```bash
composer install
```

> **Nota:** Si no tienes Composer instalado globalmente, descárgalo y ejecútalo:
> ```bash
> php composer.phar install
> ```

### Paso 3: Generar Clave de Aplicación

```bash
php artisan key:generate
```

### Paso 4: Configurar Base de Datos

Edita el archivo `.env` con tus credenciales de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cellfix_pro
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Crea la base de datos en MySQL:

```sql
CREATE DATABASE cellfix_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 5: Ejecutar Migraciones y Seeders

```bash
php artisan migrate --seed
```

Esto creará todas las tablas e insertará los datos iniciales:
- Roles y permisos
- Usuario administrador
- Configuraciones por defecto

### Paso 6: Instalar Dependencias de JavaScript

```bash
npm install
```

### Paso 7: Compilar Assets

```bash
npm run build
```

Para desarrollo (con hot reload):
```bash
npm run dev
```

### Paso 8: Crear Enlace Simbólico para Storage

```bash
php artisan storage:link
```

### Paso 9: Configurar Permisos (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Paso 10: Iniciar el Servidor

```bash
php artisan serve
```

El sistema estará disponible en: **http://localhost:8000**

---

## 👤 Credenciales de Acceso

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@cellfix.com | password |
| Vendedor | vendedor@cellfix.com | password |
| Técnico | tecnico@cellfix.com | password |

> **Importante:** Cambia las contraseñas después del primer inicio de sesión.

---

## ⚙️ Configuración Adicional

### Configuración de Correo (Opcional)

Para enviar notificaciones por email, configura en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME="CellFix Pro"
```

### Configuración de WhatsApp

Los botones de WhatsApp usan el formato `wa.me`. Asegúrate de guardar los teléfonos en formato internacional:
- México: +521234567890
- Colombia: +573001234567

### Configuración de Zona Horaria

En `.env`:
```env
APP_TIMEZONE=America/Mexico_City
```

---

## 🐛 Solución de Problemas

### Error: "No such file or directory"
```bash
# Asegúrate de estar en el directorio correcto
cd /ruta/completa/celulares-reparaciones
```

### Error: "Permission denied"
```bash
# En Linux/Mac
sudo chmod -R 777 storage bootstrap/cache
```

### Error: "Class not found"
```bash
# Regenerar autoload
composer dump-autoload
```

### Error: "Vite manifest not found"
```bash
# Recompilar assets
npm run build
```

### Error de conexión a base de datos
1. Verifica que MySQL esté corriendo
2. Revisa las credenciales en `.env`
3. Asegúrate de que la base de datos exista

---

## 📁 Estructura de Archivos Creados

```
celulares-reparaciones/
├── app/
│   ├── Http/Controllers/     # Controladores
│   └── Models/               # Modelos
├── database/
│   ├── migrations/           # Migraciones
│   └── seeders/              # Seeders
├── resources/
│   ├── css/app.css           # Estilos Tailwind
│   ├── js/                   # JavaScript
│   └── views/                # Vistas Blade
│       ├── auth/             # Login, registro
│       ├── clientes/         # Gestión de clientes
│       ├── productos/        # Inventario
│       ├── categorias/       # Categorías
│       ├── ventas/           # POS y ventas
│       ├── reparaciones/     # Reparaciones
│       ├── reportes/         # Reportes
│       ├── usuarios/         # Usuarios
│       ├── configuracion/    # Configuración
│       ├── layouts/          # Layouts
│       ├── partials/         # Componentes
│       └── errors/           # Páginas de error
├── routes/
│   └── web.php               # Rutas
├── .env.example              # Ejemplo de configuración
├── composer.json             # Dependencias PHP
├── package.json              # Dependencias JS
├── tailwind.config.js        # Config Tailwind
└── vite.config.js            # Config Vite
```

---

## 🔄 Actualización del Sistema

Para actualizar el sistema en el futuro:

```bash
# Obtener últimos cambios
git pull

# Actualizar dependencias
composer update
npm update

# Ejecutar migraciones nuevas
php artisan migrate

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Recompilar assets
npm run build
```

---

## 📞 Soporte

Si encuentras algún problema durante la instalación:

1. Revisa los logs en `storage/logs/laravel.log`
2. Verifica que todos los requisitos estén instalados
3. Consulta la documentación de Laravel: https://laravel.com/docs

---

**¡Listo! Tu sistema CellFix Pro debería estar funcionando correctamente.** 🎉