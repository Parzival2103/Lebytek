<?php
class Url
{
    /**
     * URL base del proyecto
     * Ej: http://localhost/nuevosistema/public
     */
    public static function base(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $proto = $https ? 'https://' : 'http://';

        $host = $_SERVER['HTTP_HOST'];

        // Carpeta pública (ajusta si cambias nombre)
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        return $proto . $host . $basePath;
    }

    /**
     * Genera URL a assets públicos
     */
    public static function asset(string $ruta): string
    {
        return self::base() . '/assets/' . ltrim($ruta, '/');
    }
}
