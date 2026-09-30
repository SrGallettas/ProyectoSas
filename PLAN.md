# Proyecto SaaS para cafeterías y bares

## Objetivo

Crear un mini-SaaS para cafeterías y bares pequeños mientras aprendemos Laravel. Cada usuario puede administrar varios locales con datos completamente separados.

El núcleo será un TPV táctil con el que un camarero pueda registrar una venta de varios productos en menos de diez segundos y sin teclado.

## Primer MVP

- Autenticación y perfil.
- Varios comercios por usuario y selección del comercio activo.
- Categorías, productos y stock opcional por comercio.
- Clientes opcionales.
- Venta rápida con varios productos.
- Totales calculados por el servidor.
- Historial y detalle tipo ticket.
- Separación de datos entre usuarios y locales.

## Decisiones tomadas

- El primer negocio objetivo es una cafetería o bar pequeño.
- Las ventas pueden registrarse sin identificar al cliente.
- `stock = null` significa que no se controlan existencias.
- Una venta tiene una cabecera y una o varias líneas.
- Cada línea conserva el nombre y precio históricos del producto.
- Los productos pueden quedar sin categoría.
- Eliminar una categoría no elimina sus productos.
- Precios, totales, pertenencia y stock se validan en el servidor.

## Modelo de datos

- **Usuario**: persona que accede.
- **Comercio**: local administrado por un usuario.
- **Categoría**: agrupación de productos del comercio.
- **Producto**: nombre, precio, categoría y stock opcionales.
- **Cliente**: nombre y contacto opcional.
- **Venta**: comercio, fecha, cliente opcional y total.
- **Línea de venta**: producto, cantidad, precio aplicado y subtotal.

## Trabajo completado

### Base técnica

- [x] Laravel 13, PHP, Composer, Node.js y SQLite.
- [x] Laravel Breeze: registro, acceso, cierre de sesión y perfil.
- [x] Laravel Boost y reglas de desarrollo.
- [x] Suite automática de pruebas.

### Comercios

- [x] Varios comercios por usuario.
- [x] Crear y listar comercios.
- [x] Seleccionar el comercio activo.
- [x] Protección frente al acceso a comercios ajenos.

### Productos y categorías

- [x] CRUD completo de productos.
- [x] Precio y stock opcional.
- [x] Subida, sustitución y visualización de imágenes de producto.
- [x] Imagen de respaldo para productos sin fotografía.
- [x] CRUD completo de categorías.
- [x] Asignación opcional de categoría.
- [x] Separación estricta por comercio.
- [x] Los productos sobreviven al eliminar su categoría.

### Clientes

- [x] CRUD completo.
- [x] Correo y teléfono opcionales.
- [x] Separación estricta por comercio.

### Ventas

- [x] Ventas y líneas de venta.
- [x] Cliente opcional.
- [x] Nombre y precio históricos.
- [x] Formulario funcional de nueva venta.
- [x] Productos agrupados por categoría.
- [x] Total calculado en el servidor.
- [x] Comprobación y descuento de stock.
- [x] Guardado transaccional.
- [x] Historial paginado.
- [x] Detalle tipo ticket.
- [x] Protección entre comercios.

### Demostración

- [x] Comercio `Café Alameda`.
- [x] Ocho categorías de cafetería/bar.
- [x] 46 productos habituales.
- [x] Clientes de ejemplo.
- [x] 45 ventas históricas.
- [x] Seeder reutilizable `BarDemoSeeder`.

## Estado actual

La aplicación ya tiene el motor de un TPV: catálogo, clientes, ventas, stock, historial y tickets. La pantalla de venta funciona, pero aún parece un formulario administrativo y no es suficientemente rápida para trabajar detrás de una barra.

## Próximos pasos

### 1. TPV táctil — siguiente paso

- [x] Botones grandes para categorías.
- [x] Cuadrícula visual de productos.
- [x] Añadir productos con un toque.
- [x] Ticket actual visible a la derecha.
- [x] Aumentar, reducir y eliminar cantidades sin teclado.
- [x] Total actualizado inmediatamente.
- [x] Botón grande de cobrar.
- [x] Diseño adaptable a ordenador y tablet.
- [x] Validación definitiva en el servidor.

### 2. Flujo de cobro

- [x] Método de pago: efectivo o tarjeta.
- [x] Confirmación clara de venta completada.
- [x] Evitar dobles envíos.
- [x] Iniciar rápidamente la siguiente venta.

### 3. Panel del local

- [x] Ventas y facturación de hoy.
- [x] Número de tickets.
- [x] Navegación diaria y mensual de la facturación.
- [x] Productos más vendidos por periodo.
- [x] Avisos de stock bajo.

### 4. Historial e informes

- [x] Filtros por fecha, cliente, método de pago y número de ticket.
- [x] Resúmenes diarios, semanales y mensuales con comparación.
- [x] Cierre de caja básico con historial y correcciones.
- [x] Exportación CSV respetando los filtros.

### 5. Revisión del MVP

- [x] Probar automáticamente con dos usuarios y varios locales.
- [ ] Probar en una pantalla táctil.
- [ ] Revisar accesibilidad y mensajes.
- [x] Ampliar pruebas del flujo completo de venta.
- [ ] Recoger impresiones de alguien de hostelería.

## Fuera del MVP

- Mesas y comandas abiertas.
- Envío de comandas a cocina.
- División de cuentas y reservas.
- Impresoras y cajón portamonedas.
- Facturación fiscal y VeriFactu.
- Pedidos online y reparto.
- Suscripciones del SaaS.
- Aplicación móvil, integraciones e IA.

## Definición de terminado

El MVP estará listo cuando dos usuarios puedan gestionar varios locales sin acceder a datos ajenos y un camarero pueda registrar una venta de tres productos en menos de diez segundos sin teclado.

## Siguiente acción concreta

Probar la aplicación en una pantalla táctil real y recoger impresiones de una persona de hostelería.
