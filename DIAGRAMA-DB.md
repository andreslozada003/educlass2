# Diagrama de Base de Datos

## 📊 Modelo Entidad-Relación

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     users       │     │     roles       │     │  permissions    │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │────→│ id (PK)         │←────│ id (PK)         │
│ name            │     │ name            │     │ name            │
│ email           │     │ guard_name      │     │ guard_name      │
│ password        │     └─────────────────┘     └─────────────────┘
│ phone           │            ↑                         ↑
│ avatar          │            │                         │
│ is_active       │     ┌─────────────────┐     ┌─────────────────┐
│ timestamps      │     │  model_has_     │     │  role_has_      │
└─────────────────┘     │  roles          │     │  permissions    │
         ↑              ├─────────────────┤     ├─────────────────┤
         │              │ role_id (FK)    │     │ permission_id   │
         │              │ model_type      │     │ role_id (FK)    │
         │              │ model_id (FK)   │     └─────────────────┘
         │              └─────────────────┘
         │
         │              ┌─────────────────┐     ┌─────────────────┐
         │              │   companies     │     │  categories     │
         │              ├─────────────────┤     ├─────────────────┤
         │              │ id (PK)         │     │ id (PK)         │
         │              │ name            │     │ name            │
         │              │ nit_rut         │     │ slug            │
         │              │ logo            │     │ description     │
         │              │ address         │     │ parent_id (FK)  │←──┐
         │              │ phone           │     │ is_active       │   │
         │              │ email           │     └─────────────────┘   │
         │              │ currency        │            ↑              │
         │              │ tax_rate        │            │              │
         │              └─────────────────┘            └──────────────┘
         │
         │              ┌─────────────────┐     ┌─────────────────┐
         │              │     brands      │     │    products     │
         │              ├─────────────────┤     ├─────────────────┤
         │              │ id (PK)         │     │ id (PK)         │
         │              │ name            │     │ name            │
         │              │ slug            │     │ sku (UNIQUE)    │
         │              │ logo            │     │ barcode         │
         │              │ is_active       │     │ category_id(FK) │←──┐
         │              └─────────────────┘     │ brand_id (FK)   │←──┤
         │                         ↑            │ purchase_price  │   │
         │                         │            │ sale_price      │   │
         │                         └────────────│ stock_quantity  │   │
         │                                      │ min_stock       │   │
         │                                      │ is_service      │   │
         │                                      │ is_active       │   │
         │                                      └─────────────────┘   │
         │                                                   ↑        │
         │                                                   │        │
         │              ┌─────────────────┐     ┌─────────────────┐   │
         │              │    customers    │     │   sale_items    │   │
         │              ├─────────────────┤     ├─────────────────┤   │
         │              │ id (PK)         │     │ id (PK)         │   │
         │              │ first_name      │     │ sale_id (FK)    │   │
         │              │ last_name       │     │ product_id (FK) │←──┘
         │              │ document_number │     │ quantity        │
         │              │ email           │     │ unit_price      │
         │              │ phone           │     │ warranty_code   │
         │              │ is_active       │     └─────────────────┘
         │              └─────────────────┘              ↑
         │                         ↑                     │
         │                         │              ┌─────────────────┐
         │                         │              │     sales       │
         │                         │              ├─────────────────┤
         │                         │              │ id (PK)         │
         │                         │              │ sale_number(UQ) │
         │                         │              │ customer_id(FK) │←──┘
         │                         │              │ user_id (FK)    │←──┘
         │                         │              │ status          │
         │                         │              │ total           │
         │                         │              │ profit          │
         │                         │              └─────────────────┘
         │                         │
         │              ┌─────────────────┐     ┌─────────────────┐
         │              │    suppliers    │     │inventory_movemts│
         │              ├─────────────────┤     ├─────────────────┤
         │              │ id (PK)         │     │ id (PK)         │
         │              │ name            │     │ product_id (FK) │←──┐
         │              │ nit_rut         │     │ user_id (FK)    │←──┘
         │              │ phone           │     │ type            │
         │              │ is_active       │     │ quantity        │
         │              └─────────────────┘     │ stock_before    │
         │                         ↑            │ stock_after     │
         │                         │            │ supplier_id(FK) │←──┘
         │                         │            │ total_cost      │
         │                         │            └─────────────────┘
         │                         │
         │              ┌─────────────────┐     ┌─────────────────┐
         │              │    repairs      │     │expense_categories
         │              ├─────────────────┤     ├─────────────────┤
         │              │ id (PK)         │     │ id (PK)         │
         │              │ repair_code(UQ) │     │ name            │
         └──────────────│ user_id (FK)    │     │ slug            │
                        │ customer_id(FK) │←────│ is_active       │
                        │ technician_id   │←────└─────────────────┘
                        │ device_type     │              ↑
                        │ brand           │              │
                        │ model           │     ┌─────────────────┐
                        │ imei            │     │    expenses     │
                        │ status          │     ├─────────────────┤
                        │ total_cost      │     │ id (PK)         │
                        │ warranty_code   │     │ expense_number  │
                        └─────────────────┘     │ category_id(FK) │←──┘
                                 ↑              │ user_id (FK)    │←──┘
                                 │              │ amount          │
                                 │              │ expense_date    │
                                 │              │ supplier_id(FK) │←──┘
                                 │              └─────────────────┘
                                 │
                        ┌─────────────────┐
                        │   incidents     │
                        ├─────────────────┤
                        │ id (PK)         │
                        │ incident_code   │
                        │ user_id (FK)    │←──┘
                        │ assigned_to     │←──┘
                        │ title           │
                        │ type            │
                        │ priority        │
                        │ status          │
                        └─────────────────┘
```

## 📋 Tablas y Descripción

| Tabla | Descripción | Registros Estimados |
|-------|-------------|---------------------|
| `users` | Usuarios del sistema | 5-20 |
| `roles` | Roles (admin, seller, technician) | 3 |
| `permissions` | Permisos granulares | 30+ |
| `companies` | Configuración de la empresa | 1 |
| `categories` | Categorías de productos | 10-50 |
| `brands` | Marcas de productos | 10-100 |
| `products` | Catálogo de productos | 100-10000 |
| `customers` | Clientes registrados | 100-10000 |
| `suppliers` | Proveedores | 10-100 |
| `sales` | Ventas realizadas | 1000-100000 |
| `sale_items` | Items de cada venta | 5000-500000 |
| `repairs` | Órdenes de reparación | 100-10000 |
| `expenses` | Gastos del negocio | 100-10000 |
| `inventory_movements` | Movimientos de inventario | 1000-100000 |
| `incidents` | Incidencias reportadas | 10-1000 |

## 🔗 Relaciones Principales

### One-to-Many (1:N)
- `users` → `sales`
- `users` → `repairs`
- `users` → `expenses`
- `customers` → `sales`
- `customers` → `repairs`
- `products` → `sale_items`
- `categories` → `products`
- `brands` → `products`
- `suppliers` → `inventory_movements`

### Many-to-Many (N:M)
- `users` ↔ `roles` (via `model_has_roles`)
- `roles` ↔ `permissions` (via `role_has_permissions`)

### Self-Referencing
- `categories` → `categories` (parent_id)

## 🔍 Índices Importantes

```sql
-- Búsquedas frecuentes
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_sales_number ON sales(sale_number);
CREATE INDEX idx_repairs_code ON repairs(repair_code);

-- Filtrado por fechas
CREATE INDEX idx_sales_created_at ON sales(created_at);
CREATE INDEX idx_repairs_received_at ON repairs(received_at);

-- Filtrado por estado
CREATE INDEX idx_sales_status ON sales(status);
CREATE INDEX idx_repairs_status ON repairs(status);
```

## 📈 Optimizaciones

- Soft deletes en todas las tablas principales
- Índices en campos de búsqueda frecuente
- Relaciones con claves foráneas y restricciones
- Paginación en todos los listados
- Caché de consultas frecuentes
