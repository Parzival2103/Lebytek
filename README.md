# 📋 CRM LEBYTEK - DOCUMENTACIÓN MAESTRA

**Versión:** 1.0  
**Última actualización:** 23 de enero de 2026  
**Estado:** En desarrollo - Fase inicial

---

## 🎯 VISIÓN DEL PROYECTO

### Propósito
Crear una **plantilla base reutilizable** para sistemas CRM que permita:
- Gestión de clientes, ventas y proveedores
- Sistema de citas y calendario de eventos
- Generación de reportes y estadísticas
- Versión pública/portal para clientes
- Administración robusta con RBAC

### Objetivo
Estandarizar el desarrollo de CRMs para diferentes clientes, con una base sólida que reduzca tiempo de desarrollo y garantice calidad.

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Patrón de Diseño
**MVC (Model-View-Controller)** con capa de Repositorio

### Estructura de Carpetas
```
/plantilla
├── /public                 # Punto de entrada público
│   ├── /assets
│   │   ├── /vendor        # Librerías de terceros
│   │   ├── /css           # Estilos personalizados
│   │   ├── /js            # Scripts personalizados
│   │   ├── /icons         # Iconografía
│   │   └── /images        # Imágenes del sistema
│   └── index.php          # Front controller
├── /vistas                # Plantillas visuales
│   ├── /principal
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   └── /usuarios
│       ├── /ajustes
│       ├── /login
│       ├── /logout
│       ├── /recuperacion
│       └── /registro
├── /core                  # Núcleo del framework
│   ├── ConexionBD.php
│   ├── RepositorioBase.php
│   ├── Seguridad.php
│   ├── Url.php
│   └── Utilidades.php
├── /app                   # Lógica de aplicación
│   ├── /controladores
│   ├── /modelos
│   ├── /repositorios
│   ├── /servicios
│   └── /ui
├── bootstrap.php          # Inicializador del sistema
└── .env                   # Variables de entorno
```

---

## 💻 STACK TECNOLÓGICO

### Backend
- **PHP** (7.0)
- **MySQL/MariaDB** (10.11.15)
- Queries directas (sin ORM)

### Frontend
- **HTML5**
- **CSS3**
- **JavaScript Vanilla**
- **Bootstrap 5**

### Servidor Web
- Compatible con Apache/Nginx
- PHP 8.4.16

---

## 🗄️ MODELO DE DATOS

### Tablas Core del Sistema

#### 1. Usuarios y Autenticación
**Usuarios**
- `id` INT PK AUTO_INCREMENT
- `usuario` VARCHAR(255)
- `nombre` VARCHAR(255)
- `apellidoPaterno` VARCHAR(255)
- `apellidoMaterno` VARCHAR(255)
- `password` VARCHAR(255) - Hash seguro
- `email` VARCHAR(255)
- `perfilId` INT FK -> Perfiles
- `fechaRegistro` DATETIME
- `ultimoLogin` DATETIME
- `ultimoIp` VARCHAR(255)
- `status` INT (1=activo, 0=inactivo)
- `extra1` VARCHAR(255) - Campo extensible
- `extra2` VARCHAR(255) - Campo extensible

**DatosFiscales**
- `id` INT PK
- `usuarioId` INT FK -> Usuarios
- `calle`, `numeroExterior`, `numeroInterior` VARCHAR(255)
- `colonia`, `codigoPostal` VARCHAR(255)
- `municipio`, `estado`, `pais` VARCHAR(255)
- `regimenFiscalId` INT
- `rfc`, `curp` VARCHAR(255)

#### 2. Control de Acceso (RBAC)
**Perfiles**
- `id` INT PK
- `nombre` VARCHAR(255)
- `descripcion` VARCHAR(255)
- `status` INT

**Permisos**
- `id` INT PK
- `nombre` VARCHAR(255)
- `descripcion` VARCHAR(255)
- `menuId` INT FK -> Menus
- `status` INT

**PerfilPermisos** (Relación N:M)
- `id` INT PK
- `perfilId` INT FK
- `permisoId` INT FK
- `status` INT

**UsuariosPermisos** (Permisos individuales)
- `id` INT PK
- `usuarioId` INT FK
- `permisoId` INT FK
- `efecto` INT (1=conceder, 0=denegar)
- `status` INT
- `fecharegistro` DATETIME

#### 3. Menús y Navegación
**Menus**
- `id` INT PK
- `descripcion` VARCHAR(255)
- `orden` INT
- `url` VARCHAR(255)
- `icono` VARCHAR(255)
- `clase` VARCHAR(255)
- `idPadre` INT (0=menú raíz)
- `tipo` INT
- `status` INT

#### 4. Clientes y Proveedores
**Clientes**
- `id` INT PK
- `nombre`, `apellidoPaterno`, `apellidoMaterno` VARCHAR(255)
- `rfc` VARCHAR(255)
- `telefono`, `correo` VARCHAR(255)
- `status` INT
- `fechaRegistro` DATETIME
- `registradoPor` INT FK -> Usuarios

**Proveedores**
- `id` INT PK
- `empresa`, `nombre` VARCHAR(255)
- `telefono`, `correo` VARCHAR(255)
- `descripcion` VARCHAR(255)
- `categoriaId` INT
- `impuestoRegular` DECIMAL(12,2)
- `fechaRegistro` DATETIME
- `registradoPor` INT FK -> Usuarios
- `status` INT

#### 5. Productos y Servicios
**Productos**
- `id` INT PK
- `nombre`, `descripcion` VARCHAR(255)
- `costo` DECIMAL(12,2)
- `porcentajeImpuesto` DECIMAL(12,2)
- `precioVenta` DECIMAL(12,2)
- `descuento`, `descuentoVencimiento` DECIMAL(12,2)
- `imagenRuta`, `imagenesRuta` VARCHAR(255)
- `fechaRegistro` DATETIME
- `registradoPor` INT FK -> Usuarios
- `status` INT

**Servicios**
- `id` INT PK
- `proveedorId` INT FK -> Proveedores
- `nombre`, `descripcion` VARCHAR(255)
- `descripcionLarga` VARCHAR(1000)
- `precioCliente`, `gastoInterno` DECIMAL(12,2)
- `fechaRegistro` DATETIME
- `registradoPor` INT FK

#### 6. Transacciones
**Ventas**
- `id` INT PK
- `clienteId` INT FK (nullable)
- `fechaRegistro` DATETIME
- `subTotal`, `iva`, `descuento`, `total` DECIMAL(12,2)
- `registradoPor` INT FK
- `status` INT
- `corteId` INT

**Compras**
- `id` INT PK
- `proveedorId` INT FK
- `fechaRegistro`, `fechaCompra` DATETIME
- `tipoPagoId` INT
- `subTotal`, `iva`, `descuento`, `total` DECIMAL(12,2)
- `registradoPor` INT FK
- `status` INT
- `corteId` INT

**Gastos**
- `id` INT PK
- `proveedorid` INT FK
- `usuarioId` INT FK
- `subTotal`, `iva`, `descuento`, `total` DECIMAL(12,2)
- `descripcion` VARCHAR(255)
- `fechaRegistro` DATETIME
- `status` INT

#### 7. Auditoría
**Logs**
- `id` INT PK
- `fecha` DATETIME
- `usuarioId` INT FK
- `pantalla` VARCHAR(255) - Módulo/Vista accedida
- `ip` VARCHAR(255)
- `navegador` VARCHAR(255)
- `tabla` VARCHAR(255) - Tabla afectada
- `accion` VARCHAR(255) - INSERT/UPDATE/DELETE
- `status` INT

---

## 🔑 CONVENCIONES Y ESTÁNDARES

### Nomenclatura de Base de Datos
**Tablas:**
- PascalCase (ej: `Usuarios`, `PerfilPermisos`)
- Plural cuando representa colección
- Singular para tablas de relación con concepto único

**Columnas:**
- camelCase (ej: `fechaRegistro`, `apellidoPaterno`)
- Sufijo `Id` para llaves foráneas
- Campos comunes:
  - `id` - Llave primaria
  - `status` - Estado (1=activo, 0=eliminado)
  - `fechaRegistro` - Timestamp de creación
  - `registradoPor` - Usuario que creó el registro

### Nomenclatura PHP
**Archivos:**
- PascalCase para clases (ej: `ConexionBD.php`, `RepositorioBase.php`)
- camelCase para vistas (ej: `header.php`, `sidebar.php`)

**Clases y Métodos:**
- Clases: PascalCase
- Métodos: camelCase
- Constantes: UPPER_SNAKE_CASE

### Estructura de URLs
**(A definir según sistema de routing)**

---

## 🔒 SEGURIDAD

### Autenticación
- Hash de contraseñas con algoritmo seguro (bcrypt/argon2) o md5
- Validación de sesiones
- Registro de intentos de login
- Recuperación de contraseña por email

### Autorización (RBAC)
- Sistema de perfiles (roles)
- Permisos granulares por menú/acción
- Permisos a nivel de perfil
- Override de permisos a nivel de usuario (UsuariosPermisos)
- Lógica de efecto (conceder/denegar)

### Auditoría
- Log de todas las acciones críticas
- Registro de IP y navegador
- Rastreo de cambios en datos

---

## 📦 COMPONENTES CORE

### /core/ConexionBD.php ✅ Implementado
**Funcionalidad:**
- Singleton pattern para una única instancia
- Carga automática de .env
- Configuración de charset (utf8mb4)
- Configuración de collation (utf8mb4_unicode_ci)
- Configuración de timezone por conexión
- Modo desarrollo/producción
- Manejo robusto de errores

**Métodos públicos:**
- `obtener()`: Obtiene la conexión singleton
- `cerrar()`: Cierra la conexión
- `setModoDev(bool)`: Activa/desactiva modo desarrollo
- `setCharset(string)`: Configura charset
- `setCollation(string)`: Configura collation
- `setTimeZone(string)`: Configura zona horaria

**Uso:**
```php
$cn = ConexionBD::obtener();
$resultado = $cn->query("SELECT * FROM Usuarios");
```

---

### /core/bootstrap.php ✅ Implementado
**Funcionalidad:**
- Define constantes de rutas (BASE_PATH, CORE_PATH, etc.)
- Carga .env PRIMERO antes que todo
- Configura entorno (dev/prod)
- Configuración de errores según entorno
- Configuración de zona horaria
- Inicialización segura de sesión
- Autoload de clases (sin Composer)
- Configuración de ConexionBD
- Helpers globales (env, e, estaAutenticado, etc.)

**Helpers globales incluidos:**
```php
env(string $clave, mixed $default = null): mixed
estaAutenticado(): bool
usuarioId(): ?int
redirigir(string $url): void
e(?string $texto): string // Escapar HTML (XSS protection)
```

---

### /core/Utilidades.php ✅ Implementado
**Métodos disponibles:**

**Formateo:**
- `formatearFecha(string, string)`: Formatea fechas MySQL
- `formatearDinero(float, bool)`: Formatea cantidades monetarias
- `formatearBytes(int, int)`: Convierte bytes a KB/MB/GB
- `truncar(string, int, string)`: Trunca texto con sufijo

**Seguridad:**
- `limpiarTexto(string)`: Sanitiza strings
- `hashPassword(string)`: Hash seguro (Argon2ID)
- `verificarPassword(string, string)`: Verifica contraseña
- `generarToken(int)`: Token aleatorio seguro
- `generarCodigo(int)`: Código alfanumérico

**Validación:**
- `validarEmail(string)`: Valida formato de email
- `validarRFC(string)`: Valida RFC mexicano

**Utilidades:**
- `slug(string)`: Genera slug para URLs
- `obtenerIP()`: IP del cliente
- `obtenerNavegador()`: Navegador del cliente
- `registrarLog(...)`: Registra en tabla Logs
- `arrayToXml(array, string)`: Convierte array a XML

**Uso:**
```php
$hash = Utilidades::hashPassword('mi_password');
$dinero = Utilidades::formatearDinero(1250.50); // $1,250.50
$ip = Utilidades::obtenerIP();
```

---

### /core/RepositorioBase.php ✅ Implementado
**Clase abstracta base para repositorios**

**Propiedades protegidas:**
- `$conexion`: Instancia de mysqli
- `$tabla`: Nombre de la tabla
- `$pk`: Nombre de la primary key (default: 'id')

**Métodos públicos:**

**CRUD Básico:**
- `obtenerTodos(string $condiciones, string $orden)`: Obtiene todos los registros
- `obtenerPorId(int $id)`: Obtiene un registro por ID
- `insertar(array $datos)`: Inserta registro, retorna ID
- `actualizar(int $id, array $datos)`: Actualiza registro
- `eliminar(int $id)`: Elimina físicamente
- `eliminarLogico(int $id)`: Cambia status a 0
- `contar(string $condiciones)`: Cuenta registros

**Transacciones:**
- `iniciarTransaccion()`
- `confirmarTransaccion()`
- `revertirTransaccion()`

**Avanzado:**
- `ejecutarConsulta(string, array, string)`: Query personalizado con prepared statements

**Uso - Crear repositorio:**
```php
class UsuariosRepositorio extends RepositorioBase
{
    public function __construct()
    {
        parent::__construct();
        $this->tabla = 'Usuarios';
    }
    
    public function obtenerPorEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE email = ? LIMIT 1";
        $resultado = $this->ejecutarConsulta($sql, [$email], 's');
        return $resultado[0] ?? null;
    }
}

// Uso
$repo = new UsuariosRepositorio();
$usuarios = $repo->obtenerTodos('status = 1', 'nombre ASC');
$id = $repo->insertar([
    'nombre' => 'Juan',
    'email' => 'juan@example.com',
    'status' => 1
]);
```

---

### /core/Url.php ✅ Implementado (revisar)
**Estado:** Verificar implementación actual
- Método `asset()` - Genera rutas a /public/assets
- **Por implementar:** `to()`, `route()` si se usa routing

---

### /core/Seguridad.php ⏳ Pendiente
**Funcionalidad requerida:**
- Verificación de sesión activa
- Verificación de permisos RBAC
- Protección CSRF
- Sanitización de inputs
- Headers de seguridad
- Rate limiting (opcional)

### /core/RepositorioBase.php
**Estado:** ⏳ Pendiente
- CRUD genérico
- Métodos base para todos los repositorios
- Manejo de transacciones

### /core/Seguridad.php
**Estado:** ⏳ Pendiente
- Validación de sesiones
- Verificación de permisos RBAC
- Sanitización de inputs
- Protección CSRF
- Headers de seguridad

### /core/Url.php
**Estado:** ⏳ Pendiente
- Manejo de rutas
- Generación de URLs
- Redirecciones seguras

### /core/Utilidades.php
**Estado:** ⏳ Pendiente
- Funciones auxiliares reutilizables
- Formateo de fechas
- Validaciones comunes
- Helpers de array/string

---

## 🎨 COMPONENTES UI

## 🎨 COMPONENTES UI

### app/ui/MenuBuilder.php ✅ Implementado

**Clase para generar sidebar dinámicamente**

**Funcionalidad:**
- Lee menús desde tabla `Menus` con jerarquía padre-hijo
- Filtra por permisos RBAC (Perfil + Individuales)
- Genera HTML compatible con Bootstrap 5 + Metismenu
- Marca menú activo según URL
- Maneja permisos con efecto (conceder/denegar)
- Incluye biblioteca de íconos SVG embebidos

**Constructor:**
```php
new MenuBuilder(?int $usuarioId = null)
```

**Métodos públicos:**
- `generar()`: Retorna HTML completo del sidebar

**Lógica de permisos:**
1. Obtiene permisos del perfil del usuario (PerfilPermisos)
2. Aplica permisos individuales con efecto:
   - `efecto = 1`: Conceder (agregar permiso)
   - `efecto = 0`: Denegar (quitar permiso)
3. Filtra menús que el usuario puede ver
4. Menús tipo 0 (títulos) se muestran siempre

**Tipos de menú soportados:**
- **Tipo 0**: Título de sección (sin link)
- **Tipo 1**: Menú normal o con submenús

**Estructura esperada en tabla Menus:**
```
id | descripcion | orden | url | icono | clase | idPadre | tipo | status
```
- `idPadre = 0`: Menú raíz
- `idPadre > 0`: Submenú del menú con ese ID

**Íconos incluidos:**
- dashboard, users, settings, products, sales, reports
- Extensible agregando más métodos `iconoXXX()`

**Uso:**
```php
<?php if (estaAutenticado()): ?>
    <?php $menuBuilder = new MenuBuilder(usuarioId()); ?>
    <div class="deznav">
        <div class="deznav-scroll">
            <?= $menuBuilder->generar() ?>
        </div>
    </div>
<?php endif; ?>
```

---

### Vistas Principales

#### vistas/principal/header.php ✅ Mejorado
**Cambios realizados:**
- ❌ Eliminada carga duplicada de .env
- ✅ Usa bootstrap.php para configuración
- ✅ Integra MenuBuilder para sidebar dinámico
- ✅ Usa helper `env()` para variables de entorno
- ✅ Usa helper `e()` para escapar HTML (XSS protection)
- ✅ Usa helper `estaAutenticado()` y `usuarioId()`
- ✅ Título de página dinámico desde sesión
- ✅ Datos de usuario desde $_SESSION

**Variables de sesión esperadas:**
- `$_SESSION['usuario_id']`: ID del usuario
- `$_SESSION['titulo_pagina']`: Título de página actual
- `$_SESSION['nombre_usuario']`: Nombre del usuario
- `$_SESSION['foto_usuario']`: Ruta de foto (opcional)
- `$_SESSION['perfil_nombre']`: Nombre del perfil/rol

**Uso en páginas:**
```php
<?php
require_once __DIR__ . '/bootstrap.php';

// Establecer título de página
$_SESSION['titulo_pagina'] = 'Gestión de Usuarios';

include VIEWS_PATH . '/principal/header.php';
?>

<!-- Contenido de la página aquí -->

<?php include VIEWS_PATH . '/principal/footer.php'; ?>
```

#### vistas/principal/sidebar.php ⚠️ DEPRECADO
**Este archivo ya NO se debe usar directamente**
- El sidebar ahora se genera en header.php usando MenuBuilder
- Mantener solo como referencia de estructura HTML

#### vistas/principal/footer.php ✅ Implementado
- Scripts vendor cargados
- Año dinámico con JavaScript
- Copyright configurable

**Por mejorar:**
- Configurar scripts desde tabla Ajustes
- Modo desarrollo (scripts sin minificar)
- Carga condicional de scripts según página

---

### Componentes Reutilizables (Por crear)

#### TableGenerator ⏳ Pendiente
**Funcionalidad propuesta:**
- Generar tablas DataTables desde configuración PHP
- Columnas configurables (nombre, tipo, formato)
- Acciones por fila (editar, eliminar, custom)
- Filtros avanzados
- Exportación (Excel, PDF, CSV)
- Paginación server-side

#### FormBuilder ⏳ Pendiente
**Funcionalidad propuesta:**
- Generar formularios seguros con validación
- Tipos de campo: text, email, password, select, textarea, file, date, etc.
- Protección CSRF automática
- Validación client-side (HTML5 + JS)
- Validación server-side
- Mensajes de error personalizables

#### ModalBuilder ⏳ Pendiente
**Funcionalidad propuesta:**
- Generar modales Bootstrap 5
- Tamaños (sm, md, lg, xl, fullscreen)
- Tipos (confirmación, formulario, información)
- Callbacks personalizables

#### NotificacionesBuilder ⏳ Pendiente
**Funcionalidad propuesta:**
- Sistema de alertas/notificaciones
- Tipos: success, error, warning, info
- Posición configurable
- Auto-dismiss
- Notificaciones desde BD (tabla Notificaciones)

---

## 📊 FUNCIONALIDADES CORE

### Sistema de Usuarios ⏳
- [ ] Login con validación
- [ ] Recuperación de contraseña
- [ ] Registro de usuarios (admin)
- [ ] Perfil de usuario
- [ ] Cambio de contraseña
- [ ] Gestión de sesiones
- [ ] Bloqueo por intentos fallidos

### Control de Acceso RBAC ⏳
- [ ] CRUD de Perfiles
- [ ] CRUD de Permisos
- [ ] Asignación Perfil-Permisos
- [ ] Asignación Usuario-Permisos individual
- [ ] Middleware de verificación
- [ ] Vista de administración de roles

### Menús Dinámicos ⏳
- [ ] CRUD de Menús
- [ ] Ordenamiento drag & drop
- [ ] Menús jerárquicos (padre-hijo)
- [ ] Renderizado según permisos
- [ ] Iconos personalizables

### Gestión de Clientes ⏳
- [ ] CRUD Clientes
- [ ] Historial de interacciones
- [ ] Datos fiscales
- [ ] Búsqueda y filtros

### Productos y Servicios ⏳
- [ ] CRUD Productos
- [ ] CRUD Servicios
- [ ] Gestión de precios
- [ ] Catálogo público

### Ventas y Compras ⏳
- [ ] Registro de ventas
- [ ] Registro de compras
- [ ] Cálculo de impuestos
- [ ] Descuentos
- [ ] Generación de tickets/facturas

### Reportes ⏳
- [ ] Reportes de ventas
- [ ] Reportes de compras
- [ ] Reportes de gastos
- [ ] Exportación a PDF
- [ ] Exportación a Excel
- [ ] Gráficas estadísticas

### Sistema de Logs ⏳
- [ ] Registro automático de acciones
- [ ] Vista de auditoría
- [ ] Filtros de búsqueda
- [ ] Retención de logs

---

## 🔄 PRÓXIMOS PASOS

### ✅ Fase 1: Fundamentos (COMPLETADA)
1. ✅ Definir estructura de carpetas
2. ✅ Crear esquema de base de datos inicial
3. ✅ ConexionBD.php con singleton y configuración
4. ✅ bootstrap.php centralizado con autoload
5. ✅ Utilidades.php con helpers comunes
6. ✅ RepositorioBase.php con CRUD genérico
7. ✅ .env completo y documentado
8. ✅ Helpers globales (env(), e(), estaAutenticado(), etc.)

### ✅ Fase 2: UI Dinámica (COMPLETADA)
9. ✅ **MenuBuilder** - Genera sidebar desde BD con RBAC
    - ✅ Lee jerarquía padre-hijo
    - ✅ Filtra por permisos (perfil + individuales)
    - ✅ Genera HTML con iconos SVG
    - ✅ Marca ítem activo según URL
10. ✅ **header.php mejorado** - Sin duplicación de .env
    - ✅ Usa bootstrap.php
    - ✅ Integra MenuBuilder
    - ✅ Datos de usuario desde sesión

### ⏳ Fase 3: Completar Sistema Base
11. ⏳ **Poblar tabla Menus** con menús iniciales
12. ⏳ **Crear Seguridad.php** - Middleware de permisos
13. ⏳ **Sistema de Login**
    - Login con validación
    - Registro de sesión
    - Recordar sesión
14. ⏳ **Recuperación de contraseña**
    - Envío de email con token
    - Reseteo seguro
15. ⏳ **Módulo de Usuarios** (CRUD completo)
    - Listado con DataTables
    - Formulario de creación/edición
    - Asignación de perfiles
    - Permisos individuales

### ⏳ Fase 4: Componentes Reutilizables
16. ⏳ **TableGenerator** - Genera tablas DataTables
17. ⏳ **FormBuilder** - Genera formularios seguros
18. ⏳ **ModalBuilder** - Genera modales Bootstrap
19. ⏳ **NotificacionesBuilder** - Sistema de alertas

### ⏳ Fase 5: Módulos de Negocio
20. ⏳ CRUD Clientes
21. ⏳ CRUD Productos
22. ⏳ CRUD Servicios
23. ⏳ Sistema de Ventas
24. ⏳ Generación de reportes PDF

### Fase 4: RBAC
15. ⏳ Middleware de autorización
16. ⏳ Módulo de administración de perfiles
17. ⏳ Módulo de permisos
18. ⏳ Testing de permisos

---

## 📝 GLOSARIO

**RBAC:** Role-Based Access Control - Control de acceso basado en roles  
**CRUD:** Create, Read, Update, Delete - Operaciones básicas de base de datos  
**FK:** Foreign Key - Llave foránea  
**PK:** Primary Key - Llave primaria  
**Middleware:** Capa intermedia que intercepta peticiones  
**Singleton:** Patrón de diseño que garantiza una única instancia  
**ORM:** Object-Relational Mapping - No se usa en este proyecto  
**MVC:** Model-View-Controller - Patrón arquitectónico  
**Routing:** Sistema de manejo de URLs y rutas

---

## 🔧 CONFIGURACIÓN DEL ENTORNO

### Archivo .env (Ejemplo)
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=plantilla
DB_USER=root
DB_PASS=
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/plantilla
```

### Requisitos del Servidor
- PHP >= 7.0
- MySQL >= 5.7 / MariaDB >= 10.3
- mod_rewrite habilitado (Apache)
- extensiones PHP: pdo_mysql, mbstring, openssl, json

---

## 📚 RECURSOS Y REFERENCIAS

### Documentación Externa
- Bootstrap 5: https://getbootstrap.com/docs/5.0/
- PHP Manual: https://www.php.net/manual/es/
- MySQL Docs: https://dev.mysql.com/doc/

### Convenciones de Código
- PSR-12: PHP Coding Standards (recomendado seguir)
- Comentarios en español
- DocBlocks en funciones públicas

---

**Fin del documento maestro v1.0**

*Este documento es vivo y se actualizará con cada cambio significativo en el proyecto.*