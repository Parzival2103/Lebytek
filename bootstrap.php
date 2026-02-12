<?php
/**
 * bootstrap.php
 * Punto de entrada centralizado del sistema
 * - Carga .env PRIMERO
 * - Configura errores, sesión, timezone
 * - Autoload de clases
 * - Inicializa conexión BD
 */

declare(strict_types=1);

// =====================================================
// 1) DEFINIR CONSTANTES DE RUTAS
// =====================================================
define('BASE_PATH', __DIR__);
define('CORE_PATH', BASE_PATH . '/core');
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', BASE_PATH . '/vistas');

// =====================================================
// 2) CARGAR .ENV PRIMERO (antes de todo)
// =====================================================
function cargarEnv(): void
{
    $rutaEnv = BASE_PATH . '/.env';
    
    if (!file_exists($rutaEnv)) {
        die('<pre style="background:#c00;color:#fff;padding:20px;font-family:monospace;">
ERROR CRÍTICO: Archivo .env no encontrado en: ' . $rutaEnv . '
Crea el archivo .env basándote en .env.example
</pre>');
    }

    $lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Ignorar comentarios y líneas vacías
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        // Separar CLAVE=VALOR
        if (strpos($linea, '=') === false) {
            continue;
        }

        [$clave, $valor] = array_pad(explode('=', $linea, 2), 2, '');
        $clave = trim($clave);
        $valor = trim($valor);

        // Remover comillas si existen
        if (!empty($valor) && (
            ($valor[0] === '"' && substr($valor, -1) === '"') ||
            ($valor[0] === "'" && substr($valor, -1) === "'")
        )) {
            $valor = substr($valor, 1, -1);
        }

        if ($clave !== '') {
            putenv($clave . '=' . $valor);
            $_ENV[$clave] = $valor; // También en $_ENV para mejor compatibilidad
        }
    }
}

cargarEnv();

// =====================================================
// 3) CONFIGURACIÓN DE ENTORNO
// =====================================================
define('APP_ENV', getenv('APP_ENV') ?: 'dev');
define('MODO_DEV', APP_ENV === 'dev');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));

// =====================================================
// 4) CONFIGURACIÓN DE ERRORES
// =====================================================
if (MODO_DEV) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    // En producción, los errores se escriben al log
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/logs/php_errors.log');
}

// =====================================================
// 5) CONFIGURACIÓN DE TIMEZONE
// =====================================================
$timezone = getenv('APP_TIMEZONE') ?: 'America/Tijuana';
date_default_timezone_set($timezone);

// =====================================================
// 6) SESIÓN (antes de cualquier output)
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    // Configuración segura de sesión
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    if (!MODO_DEV) {
        ini_set('session.cookie_secure', '1'); // Solo HTTPS en producción
    }
    
    session_start();
}

// =====================================================
// 7) AUTOLOAD DE CLASES
// =====================================================
spl_autoload_register(function (string $clase) {
    $rutas = [
        // Core
        CORE_PATH . '/' . $clase . '.php',
        
        // App
        APP_PATH . '/modelos/' . $clase . '.php',
        APP_PATH . '/repositorios/' . $clase . '.php',
        APP_PATH . '/servicios/' . $clase . '.php',
        APP_PATH . '/controladores/' . $clase . '.php',
        APP_PATH . '/ui/' . $clase . '.php',
    ];

    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

// =====================================================
// 8) CONFIGURAR CONEXIÓN A BASE DE DATOS
// =====================================================
ConexionBD::setModoDev(MODO_DEV);
ConexionBD::setTimeZone(getenv('APP_TIMEZONE') ?: '-08:00');
ConexionBD::setCollation(getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci');
ConexionBD::setCharset(getenv('DB_CHARSET') ?: 'utf8mb4');

// =====================================================
// 9) HELPERS GLOBALES (opcional)
// =====================================================

/**
 * Obtiene una variable de entorno
 */
if (!function_exists('env')) {
    function env(string $clave, mixed $default = null): mixed
    {
        $valor = getenv($clave);
        if ($valor === false) {
            return $default;
        }
        
        // Conversión de strings especiales
        if (is_string($valor)) {
            $lower = strtolower($valor);
            if ($lower === 'true') return true;
            if ($lower === 'false') return false;
            if ($lower === 'null') return null;
        }
        
        return $valor;
    }
}

/**
 * Verifica si el usuario está autenticado
 */
if (!function_exists('estaAutenticado')) {
    function estaAutenticado(): bool
    {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
}

/**
 * Obtiene el ID del usuario en sesión
 */
if (!function_exists('usuarioId')) {
    function usuarioId(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }
}

/**
 * Redirecciona a una URL
 */
if (!function_exists('redirigir')) {
    function redirigir(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Escapa HTML para prevenir XSS
 */
if (!function_exists('e')) {
    function e(?string $texto): string
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// =====================================================
// 10) LOG DE INICIO (solo en dev)
// =====================================================
if (MODO_DEV && APP_DEBUG) {
    error_log('[BOOTSTRAP] Sistema inicializado - Entorno: ' . APP_ENV);
}