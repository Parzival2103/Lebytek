<?php
/**
 * Utilidades.php
 * Funciones auxiliares reutilizables del sistema
 */

class Utilidades
{
    /**
     * Formatea una fecha de MySQL a formato legible
     * 
     * @param string $fecha Fecha en formato MySQL (Y-m-d H:i:s)
     * @param string $formato Formato de salida (default: d/m/Y H:i)
     * @return string Fecha formateada
     */
    public static function formatearFecha(string $fecha, string $formato = 'd/m/Y H:i'): string
    {
        if (empty($fecha) || $fecha === '0000-00-00 00:00:00') {
            return '-';
        }
        
        $timestamp = strtotime($fecha);
        return date($formato, $timestamp);
    }

    /**
     * Formatea un número a moneda mexicana
     * 
     * @param float $cantidad Cantidad a formatear
     * @param bool $simbolo Si debe incluir el símbolo $
     * @return string Cantidad formateada
     */
    public static function formatearDinero(float $cantidad, bool $simbolo = true): string
    {
        $formato = number_format($cantidad, 2, '.', ',');
        return $simbolo ? '$' . $formato : $formato;
    }

    /**
     * Genera un token aleatorio seguro
     * 
     * @param int $longitud Longitud del token
     * @return string Token generado
     */
    public static function generarToken(int $longitud = 32): string
    {
        return bin2hex(random_bytes($longitud / 2));
    }

    /**
     * Sanitiza un string para evitar inyecciones
     * 
     * @param string $texto Texto a sanitizar
     * @return string Texto limpio
     */
    public static function limpiarTexto(string $texto): string
    {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida un email
     * 
     * @param string $email Email a validar
     * @return bool True si es válido
     */
    public static function validarEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida un RFC mexicano (básico)
     * 
     * @param string $rfc RFC a validar
     * @return bool True si tiene formato válido
     */
    public static function validarRFC(string $rfc): bool
    {
        // RFC persona física: 13 caracteres
        // RFC persona moral: 12 caracteres
        $patron = '/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/';
        return preg_match($patron, strtoupper($rfc)) === 1;
    }

    /**
     * Genera un hash seguro de contraseña
     * 
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verifica una contraseña contra su hash
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool True si coincide
     */
    public static function verificarPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Genera un slug amigable para URLs
     * 
     * @param string $texto Texto a convertir
     * @return string Slug generado
     */
    public static function slug(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        
        // Reemplazar caracteres especiales
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'n', 'u'];
        $texto = str_replace($buscar, $reemplazar, $texto);
        
        // Remover caracteres no alfanuméricos
        $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
        
        // Reemplazar espacios y guiones múltiples por un solo guion
        $texto = preg_replace('/[\s-]+/', '-', $texto);
        
        return trim($texto, '-');
    }

    /**
     * Trunca un texto a cierta longitud
     * 
     * @param string $texto Texto a truncar
     * @param int $longitud Longitud máxima
     * @param string $sufijo Sufijo a agregar (ej: ...)
     * @return string Texto truncado
     */
    public static function truncar(string $texto, int $longitud = 100, string $sufijo = '...'): string
    {
        if (mb_strlen($texto, 'UTF-8') <= $longitud) {
            return $texto;
        }
        
        return mb_substr($texto, 0, $longitud, 'UTF-8') . $sufijo;
    }

    /**
     * Obtiene la IP del cliente
     * 
     * @return string IP del cliente
     */
    public static function obtenerIP(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Si hay múltiples IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Obtiene el navegador del cliente
     * 
     * @return string Nombre del navegador
     */
    public static function obtenerNavegador(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (strpos($userAgent, 'Firefox') !== false) {
            return 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Google Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Apple Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Microsoft Edge';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            return 'Opera';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            return 'Internet Explorer';
        }

        return 'Desconocido';
    }

    /**
     * Convierte un array a XML
     * 
     * @param array $array Array a convertir
     * @param string $rootElement Elemento raíz
     * @return string XML generado
     */
    public static function arrayToXml(array $array, string $rootElement = 'root'): string
    {
        $xml = new SimpleXMLElement("<{$rootElement}/>");
        
        $arrayToXml = function($data, $xml) use (&$arrayToXml) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $subnode = $xml->addChild($key);
                    $arrayToXml($value, $subnode);
                } else {
                    $xml->addChild($key, htmlspecialchars((string)$value));
                }
            }
        };
        
        $arrayToXml($array, $xml);
        
        return $xml->asXML();
    }

    /**
     * Convierte bytes a formato legible
     * 
     * @param int $bytes Bytes a convertir
     * @param int $precision Decimales
     * @return string Tamaño formateado
     */
    public static function formatearBytes(int $bytes, int $precision = 2): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($unidades) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $unidades[$i];
    }

    /**
     * Registra un log en el sistema
     * 
     * @param int $usuarioId ID del usuario
     * @param string $pantalla Pantalla/módulo
     * @param string $tabla Tabla afectada
     * @param string $accion Acción realizada
     * @return bool True si se registró correctamente
     */
    public static function registrarLog(
        int $usuarioId,
        string $pantalla,
        string $tabla,
        string $accion
    ): bool {
        try {
            $cn = ConexionBD::obtener();
            
            $ip = self::obtenerIP();
            $navegador = self::obtenerNavegador();
            $fecha = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO Logs 
                    (fecha, usuarioId, pantalla, ip, navegador, tabla, accion, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $cn->prepare($sql);
            $stmt->bind_param(
                'sissss',
                $fecha,
                $usuarioId,
                $pantalla,
                $ip,
                $navegador,
                $tabla,
                $accion
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log('Error al registrar log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un código alfanumérico aleatorio
     * 
     * @param int $longitud Longitud del código
     * @return string Código generado
     */
    public static function generarCodigo(int $longitud = 8): string
    {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        return $codigo;
    }
}