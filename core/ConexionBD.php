<?php
/**
 * ConexionBD.php
 * Conexión centralizada a MySQL usando .env (sin librerías externas)
 * - Singleton
 * - utf8mb4
 * - collation y timezone configurables desde fuera
 * 
 * 
 *   require_once __DIR__ . '/../core/ConexionBD.php';
 * 
 *   ConexionBD::setModoDev(true);
 * 
 *   // Si algún día cambias zona/colación sin editar la clase:
 *   ConexionBD::setTimeZone('-08:00');
 *   ConexionBD::setCollation('utf8mb4_unicode_ci');
 *   ConexionBD::setCharset('utf8mb4');
 * 
 *   $cn = ConexionBD::obtener();
 * 
 */
class ConexionBD
{
    private static ?mysqli $conexion = null;

    // Cambia a false en producción
    private static bool $modoDev = true;

    // Defaults (puedes cambiarlos desde fuera)
    private static string $charset   = 'utf8mb4';
    private static string $collation = 'utf8mb4_unicode_ci';
    private static string $timeZone  = '-08:00';

    /**
     * Obtiene la conexión singleton.
     */
    public static function obtener(): mysqli
    {
        if (self::$conexion instanceof mysqli) {
            return self::$conexion;
        }

        self::cargarEnv();

        $host = self::env('DB_HOST');
        $user = self::env('DB_USER');
        $pass = self::env('DB_PASSWORD', '');
        $db   = self::env('DB_NAME');
        $port = (int) self::env('DB_PORT', '3306');

        if (!$host || !$user || !$db) {
            self::fallar('Variables de entorno incompletas: DB_HOST, DB_USER, DB_NAME');
        }

        mysqli_report(MYSQLI_REPORT_OFF);

        $cn = @new mysqli($host, $user, $pass, $db, $port);

        if ($cn->connect_errno) {
            self::fallar("Error MySQL ({$cn->connect_errno}): {$cn->connect_error}");
        }

        // Charset
        if (!$cn->set_charset(self::$charset)) {
            self::fallar("No se pudo establecer charset '" . self::$charset . "': {$cn->error}");
        }

        // Collation por conexión
        $collation = self::$collation;
        $cn->query("SET collation_connection = '{$collation}'");

        // Timezone por conexión
        $tz = self::$timeZone;
        $cn->query("SET time_zone = '{$tz}'");

        self::$conexion = $cn;
        return self::$conexion;
    }

    /**
     * Cierra la conexión (opcional).
     */
    public static function cerrar(): void
    {
        if (self::$conexion instanceof mysqli) {
            self::$conexion->close();
            self::$conexion = null;
        }
    }

    public static function setModoDev(bool $estado): void
    {
        self::$modoDev = $estado;
    }

    public static function setCharset(string $charset): void
    {
        // Ej: utf8mb4
        self::$charset = $charset;
    }

    public static function setCollation(string $collation): void
    {
        // Ej: utf8mb4_unicode_ci
        self::$collation = $collation;
    }

    public static function setTimeZone(string $timeZone): void
    {
        // Ej: -08:00 o +00:00
        self::$timeZone = $timeZone;
    }

    /**
     * Lectura robusta de .env (simple)
     * - Carga una sola vez por request
     */
    private static function cargarEnv(): void
    {
        static $cargado = false;
        if ($cargado) return;

        $rutaEnv = dirname(__DIR__) . '/.env';
        if (!file_exists($rutaEnv)) {
            self::fallar("Archivo .env no encontrado en: {$rutaEnv}");
        }

        $lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === '' || str_starts_with($linea, '#')) continue;

            // Permite: CLAVE=VALOR
            [$clave, $valor] = array_pad(explode('=', $linea, 2), 2, '');
            $clave = trim($clave);
            $valor = trim($valor);

            // Quita comillas si vienen "así" o 'así'
            if ($valor !== '' && (
                ($valor[0] === '"' && substr($valor, -1) === '"') ||
                ($valor[0] === "'" && substr($valor, -1) === "'")
            )) {
                $valor = substr($valor, 1, -1);
            }

            if ($clave !== '') {
                putenv($clave . '=' . $valor);
            }
        }

        $cargado = true;
    }

    private static function env(string $clave, ?string $default = null): ?string
    {
        $v = getenv($clave);
        if ($v === false || $v === '') return $default;
        return $v;
    }

    private static function fallar(string $mensaje): void
    {
        if (self::$modoDev) {
            http_response_code(500);
            die("<pre style='background:#111;color:#f2f2f2;padding:12px;border-radius:8px'>ConexionBD ERROR:\n{$mensaje}\n</pre>");
        }

        error_log($mensaje);
        http_response_code(500);
        die("Error interno del servidor.");
    }
}
