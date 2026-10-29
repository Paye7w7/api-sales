# API de Ventas – Laravel

**Desarrollador:** Manuel Paye  
**Rama:** `manuel.paye`

## Descripción

API REST desarrollada en Laravel para gestionar productos y ventas, con validación de stock, registro de detalles y cálculo de descuentos por producto.

## Funcionalidades desarrolladas

- Implementación de endpoints para registrar, consultar y eliminar ventas.
- Validación de productos existentes y cantidades solicitadas.
- Control y actualización automática del stock disponible.
- Registro de ventas con múltiples productos mediante transacciones.
- Cálculo de subtotales y total de ventas.
- Implementación de descuentos opcionales de 0 % a 10 % por producto.
- Registro de descuentos individuales y descuento total.
- Incorporación de una migración para almacenar los campos de descuentos.
- Eliminación lógica de ventas.

## Instalación y ejecución

**1. Clonar el repositorio**

```bash
git clone -b manuel.paye https://github.com/Paye7w7/api-sales.git
cd api-sales
```

**2. Instalar dependencias**

```bash
composer install
```

**3. Crear el archivo `.env`**

En Windows (CMD):

```cmd
copy .env.example .env
```

**4. Configurar la base de datos**

Crear una base de datos en MySQL y configurar el archivo `.env` con sus credenciales.

Ejemplo:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_sales
DB_USERNAME=root
DB_PASSWORD=
```

**5. Generar la clave de aplicación**

```bash
php artisan key:generate
```

**6. Ejecutar migraciones y seeders**

```bash
php artisan migrate --seed
```

**7. Iniciar la API**

```bash
php artisan serve
```

La API estará disponible por defecto en:

`http://127.0.0.1:8000`

## Endpoints

| Método | Ruta | Función |
|---|---|---|
| GET | `/api/products` | Listar productos |
| GET | `/api/sales` | Listar ventas |
| POST | `/api/sales` | Registrar ventas |
| DELETE | `/api/sales/{id}` | Eliminar venta |

## Integración con Angular

El frontend consume esta API mediante solicitudes HTTP para consultar productos, registrar ventas y mostrar los resultados.

Repositorio del frontend:

https://github.com/Paye7w7/app-sales/tree/manuel.paye