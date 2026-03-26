# Guía de Instalación Rápida

## ⚡ Instalación en 5 pasos

### Paso 1: Instalar dependencias
```bash
composer install
npm install
npm run build
```

### Paso 2: Configurar base de datos
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env`:
```env
DB_DATABASE=cellphone_store
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### Paso 3: Crear base de datos
```bash
mysql -u root -p -e "CREATE DATABASE cellphone_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Paso 4: Migrar y poblar datos
```bash
php artisan migrate --seed
php artisan storage:link
```

### Paso 5: Iniciar servidor
```bash
php artisan serve
```

🎉 **Listo!** Accede a: http://localhost:8000

---

## 🔑 Credenciales por defecto

| Usuario | Email | Contraseña |
|---------|-------|------------|
| **Admin** | admin@cellstore.com | password |
| **Vendedor** | vendedor@cellstore.com | password |
| **Técnico** | tecnico@cellstore.com | password |

---

## 📁 Estructura de archivos creados

```
cellphone-store-system/
├── app/
│   ├── Exports/              # Exportaciones Excel
│   ├── Http/Controllers/     # 15+ Controladores
│   ├── Http/Middleware/      # Middleware de roles
│   ├── Models/               # 15 Modelos Eloquent
│   └── Providers/            # Service Providers
├── config/                   # Configuración Laravel
├── database/
│   ├── migrations/           # 14 Migraciones
│   └── seeders/              # 6 Seeders
├── routes/
│   └── web.php               # Todas las rutas
├── README.md                 # Documentación completa
└── composer.json             # Dependencias
```

---

## 🛠️ Solución de problemas comunes

### Error: "No such file or directory"
```bash
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Error: "Access denied for user"
Verificar credenciales en archivo `.env`

### Error: "Table doesn't exist"
```bash
php artisan migrate:fresh --seed
```

---

## 📊 Módulos incluidos

✅ Dashboard con estadísticas en tiempo real  
✅ Control de inventario con Kardex  
✅ Punto de venta con comprobantes PDF  
✅ Sistema de reparaciones con garantías  
✅ Gestión de clientes y proveedores  
✅ Control de gastos  
✅ Reportes en PDF y Excel  
✅ Sistema de roles y permisos  
✅ Configuración de empresa  

---

## 🚀 Para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Configurar servidor web (Apache/Nginx) apuntando a la carpeta `public/`
