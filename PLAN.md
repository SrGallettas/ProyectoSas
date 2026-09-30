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
- **Miembro del comercio**: relación entre usuario y comercio con rol y estado de acceso.
- **Invitación**: acceso temporal ligado a un correo y a un rol.
- **Turno de caja**: apertura, fondo inicial, responsable, cierre y diferencia.
- **Movimiento de caja**: entrada o salida manual con importe, motivo y responsable.
- **Devolución**: operación vinculada a una venta que conserva el ticket original.
- **Solicitud de ajuste**: petición de anulación o devolución pendiente de aprobación.
- **Registro de actividad**: historial inmutable de operaciones sensibles.

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

La aplicación ya supera el primer MVP. Dispone de TPV táctil, equipo con roles, caja por turnos, anulaciones, devoluciones, aprobación en dos pasos, informes netos y auditoría. Los datos están aislados por comercio y el comportamiento crítico tiene cobertura automática.

El proyecto está preparado para una validación manual completa en ordenador y tablet. Todavía no debe considerarse listo para vender ni utilizarse como software fiscal: faltan pruebas con usuarios reales, accesibilidad, infraestructura de producción y cumplimiento normativo.

Estado técnico verificado el 30/09/2026:

- 104 pruebas automáticas superadas.
- 382 verificaciones.
- Migraciones aplicadas correctamente.
- Frontend compilado.
- Rama `main` sincronizada con GitHub.
- Plan manual disponible en `/home/pau/Escritorio/PRUEBAS_MANUALES_PROYECTO_SAS.md`.

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

Ejecutar el plan de pruebas manuales completo en ordenador y tablet, registrar incidencias y corregir los problemas encontrados antes de iniciar mesas y comandas.

## Hoja de ruta de producto — versión 2

### Fase 1. Equipo, permisos y trazabilidad

- [x] Registrar el usuario responsable de cada venta nueva.
- [x] Registrar el usuario responsable del último guardado o corrección de un cierre.
- [x] Mostrar el responsable en tickets, historial, caja y exportaciones.
- [x] Permitir que un comercio tenga propietario, encargados y camareros.
- [x] Invitar empleados mediante un enlace temporal ligado a su correo.
- [x] Gestionar cambios de rol y desactivación de empleados.
- [x] Limitar acciones según el rol.
- [x] Crear un registro inmutable de operaciones sensibles.

Resultado entregado:

- Propietario, encargado y camarero con permisos distintos.
- Invitaciones temporales ligadas al correo.
- Cambio de rol, activación y desactivación de accesos.
- Responsable guardado en ventas, cierres y operaciones sensibles.
- Pantalla Actividad con descripciones legibles, fecha, usuario e IP.

### Fase 2. Caja profesional

- [x] Apertura de caja por turno.
- [x] Fondo inicial y movimientos de entrada o salida.
- [x] Cierre por turno y por empleado.
- [x] Anulación completa de ventas sin borrar el original.
- [x] Devolución total o parcial sin borrar la venta original.
- [x] Informes netos e historial específico de anulaciones y devoluciones.
- [x] Integrar el histórico del cierre diario antiguo y retirar su flujo de escritura.
- [x] Motivo obligatorio y autorización en dos pasos para operaciones solicitadas por camareros.
- [x] Historial completo de correcciones y decisiones en Actividad.

Resultado entregado:

- Una sola caja abierta por comercio.
- Fondo inicial, ventas en efectivo, entradas, salidas y devoluciones.
- Efectivo esperado, contado y diferencia por turno.
- Historial de turnos y conservación de cierres diarios antiguos en solo lectura.
- Anulaciones sin borrar la venta original y con reposición de stock.
- Devoluciones totales o parciales sin superar las unidades vendidas.
- Solicitudes de camareros aprobadas o rechazadas por encargado o propietario.
- Informes, ranking de productos y CSV basados en importes netos.
- Pantalla Ajustes para consultar anulaciones y devoluciones.

### Fase 2.5. Validación y experiencia de uso — siguiente fase

- [ ] Ejecutar todas las pruebas de `PRUEBAS_MANUALES_PROYECTO_SAS.md`.
- [ ] Probar el circuito completo con propietario, encargado y camarero reales.
- [ ] Probar apertura, movimientos, ventas, devoluciones y cierre en tablet.
- [ ] Verificar que una venta de tres productos tarda menos de diez segundos.
- [ ] Revisar contraste, foco visible, etiquetas, teclado y lectores de pantalla.
- [ ] Revisar mensajes de error y confirmaciones de operaciones sensibles.
- [ ] Añadir confirmación visual antes de anular, devolver o cerrar un turno.
- [ ] Revisar tablas y formularios en móvil vertical y tablet horizontal.
- [ ] Registrar y corregir todas las incidencias críticas encontradas.
- [ ] Recoger impresiones de al menos una persona que trabaje en hostelería.

### Fase 3. Servicio de sala

- [ ] Diseñar estados de una comanda: abierta, enviada, cobrada y cancelada.
- [ ] Mesas y zonas del local.
- [ ] Comandas abiertas y edición durante el servicio.
- [ ] Notas para cocina o barra.
- [ ] Mover productos entre mesas o comandas.
- [ ] División de cuenta y cobros parciales.
- [ ] Descuentos con permisos.
- [ ] Historial de cambios de cada comanda.
- [ ] Impresión de tickets y comandas.

### Fase 4. Preparación para producción

- [ ] Crear un entorno privado de pruebas separado del desarrollo local.
- [ ] PostgreSQL como base de datos de producción.
- [ ] Almacenamiento persistente para imágenes.
- [ ] Copias de seguridad y restauración comprobada.
- [ ] Correo transaccional y recuperación de acceso.
- [ ] HTTPS, gestión segura de secretos y cabeceras de seguridad.
- [ ] Seguimiento de errores, logs y métricas.
- [ ] Despliegue automático de una versión privada de pruebas.
- [ ] Documentar instalación, actualización y recuperación ante fallos.

### Fase 5. Producto SaaS

- [ ] Alta guiada y configuración inicial del negocio.
- [ ] Planes, suscripciones y límites por plan.
- [ ] Panel interno de administración y soporte.
- [ ] Privacidad, exportación y eliminación de datos.
- [ ] Condiciones de uso y política de privacidad.
- [ ] Gestión de periodos de prueba, cancelaciones y facturación de suscripciones.
- [ ] Soporte para varios propietarios o transferencia de propiedad.

### Fase 6. Facturación y normativa española

- [ ] Datos fiscales, impuestos y desglose de IVA.
- [ ] Facturas simplificadas, completas y rectificativas.
- [ ] Numeración, conservación e integridad de registros.
- [ ] Analizar e implementar VeriFactu con validación profesional.
- [ ] Validar el resultado con asesoría fiscal o profesional especializado.

## Orden acordado para continuar

1. Pruebas manuales y revisión en tablet.
2. Corrección de incidencias y accesibilidad.
3. Mesas, zonas y comandas abiertas.
4. División de cuentas, descuentos e impresión.
5. Preparación de infraestructura y despliegue privado.
6. Validación con un negocio real.
7. SaaS, suscripciones y administración interna.
8. Facturación fiscal y VeriFactu con asesoramiento profesional.

## Decisiones de seguridad y contabilidad

- Las ventas nunca se borran para corregirlas.
- Una anulación conserva el ticket, el motivo, el responsable y la fecha.
- Una devolución es un documento separado y puede ser total o parcial.
- No se puede devolver más cantidad de la vendida.
- Los turnos cerrados no se reescriben mediante anulaciones posteriores.
- Los camareros solicitan operaciones sensibles; encargado o propietario deciden.
- Los registros de Actividad no se pueden modificar ni eliminar desde la aplicación.
- El cierre diario antiguo se conserva únicamente como histórico de solo lectura.
- Los informes deben mostrar importes netos después de anulaciones y devoluciones.

## Criterio de trabajo

Cada entrega debe incluir migraciones reversibles, aislamiento entre comercios, permisos explícitos, pruebas automáticas, revisión en móvil/tablet y un commit independiente antes de empezar el siguiente bloque.
