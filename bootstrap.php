<?php
// bootstrap.php
declare(strict_types=1);

// =====================
// 1) Config general
// =====================
define('BASE_PATH', __DIR__);
define('APP_ENV', getenv('APP_ENV') ?: 'dev'); // dev | prod
define('MODO_DEV', APP_ENV === 'dev');

// =====================
// 2) Sesión (siempre antes de output)
// =====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================
// 3) Autoload simple (sin Composer)
// =====================
spl_autoload_register(function (string $clase) {
    $rutas = [
        BASE_PATH . '/core/' . $clase . '.php',
        BASE_PATH . '/app/modelos/' . $clase . '.php',
        BASE_PATH . '/app/servicios/' . $clase . '.php',
        BASE_PATH . '/app/controladores/' . $clase . '.php',
        BASE_PATH . '/app/ui/' . $clase . '.php',
    ];

    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

// =====================
// 4) Errores (controlados)
// =====================
if (MODO_DEV) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// =====================
// 5) Configurar conexión BD
// =====================
//ConexionBD::setModoDev(MODO_DEV);
ConexionBD::setTimeZone(getenv('APP_TIMEZONE') ?: '-08:00');
ConexionBD::setCollation(getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci');
ConexionBD::setCharset(getenv('DB_CHARSET') ?: 'utf8mb4');
