<?php
/**
 * RepositorioBase.php
 * Clase base para todos los repositorios
 * Proporciona métodos CRUD genéricos y reutilizables
 */

abstract class RepositorioBase
{
    protected mysqli $conexion;
    protected string $tabla;
    protected string $pk = 'id'; // Primary key por defecto

    public function __construct()
    {
        $this->conexion = ConexionBD::obtener();
    }

    /**
     * Obtiene todos los registros de la tabla
     * 
     * @param string $condiciones WHERE clause (sin la palabra WHERE)
     * @param string $orden ORDER BY clause (sin ORDER BY)
     * @return array Array de registros
     */
    public function obtenerTodos(string $condiciones = '', string $orden = ''): array
    {
        $sql = "SELECT * FROM {$this->tabla}";
        
        if (!empty($condiciones)) {
            $sql .= " WHERE {$condiciones}";
        }
        
        if (!empty($orden)) {
            $sql .= " ORDER BY {$orden}";
        }
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            $this->manejarError("Error en obtenerTodos");
            return [];
        }
        
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene un registro por su ID
     * 
     * @param int $id ID del registro
     * @return array|null Registro encontrado o null
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->pk} = ? LIMIT 1";
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->manejarError("Error al preparar consulta en obtenerPorId");
            return null;
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $registro = $resultado->fetch_assoc();
        
        return $registro ?: null;
    }

    /**
     * Inserta un nuevo registro
     * 
     * @param array $datos Array asociativo [campo => valor]
     * @return int|false ID del registro insertado o false
     */
    public function insertar(array $datos)
    {
        if (empty($datos)) {
            return false;
        }
        
        $campos = array_keys($datos);
        $valores = array_values($datos);
        
        $camposStr = implode(', ', $campos);
        $placeholders = implode(', ', array_fill(0, count($campos), '?'));
        
        $sql = "INSERT INTO {$this->tabla} ({$camposStr}) VALUES ({$placeholders})";
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->manejarError("Error al preparar INSERT");
            return false;
        }
        
        // Determinar tipos de datos para bind_param
        $tipos = $this->determinarTipos($valores);
        
        $stmt->bind_param($tipos, ...$valores);
        
        if (!$stmt->execute()) {
            $this->manejarError("Error al ejecutar INSERT: " . $stmt->error);
            return false;
        }
        
        return $this->conexion->insert_id;
    }

    /**
     * Actualiza un registro existente
     * 
     * @param int $id ID del registro a actualizar
     * @param array $datos Array asociativo [campo => valor]
     * @return bool True si se actualizó correctamente
     */
    public function actualizar(int $id, array $datos): bool
    {
        if (empty($datos)) {
            return false;
        }
        
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            $campos[] = "{$campo} = ?";
            $valores[] = $valor;
        }
        
        // Agregar el ID al final
        $valores[] = $id;
        
        $camposStr = implode(', ', $campos);
        $sql = "UPDATE {$this->tabla} SET {$camposStr} WHERE {$this->pk} = ?";
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->manejarError("Error al preparar UPDATE");
            return false;
        }
        
        $tipos = $this->determinarTipos($valores);
        $stmt->bind_param($tipos, ...$valores);
        
        return $stmt->execute();
    }

    /**
     * Elimina un registro (borrado físico)
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM {$this->tabla} WHERE {$this->pk} = ?";
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->manejarError("Error al preparar DELETE");
            return false;
        }
        
        $stmt->bind_param('i', $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un registro (borrado lógico - cambia status a 0)
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminarLogico(int $id): bool
    {
        return $this->actualizar($id, ['status' => 0]);
    }

    /**
     * Cuenta registros en la tabla
     * 
     * @param string $condiciones WHERE clause (sin WHERE)
     * @return int Número de registros
     */
    public function contar(string $condiciones = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabla}";
        
        if (!empty($condiciones)) {
            $sql .= " WHERE {$condiciones}";
        }
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            return 0;
        }
        
        $fila = $resultado->fetch_assoc();
        return (int) $fila['total'];
    }

    /**
     * Ejecuta una consulta SQL personalizada
     * 
     * @param string $sql Consulta SQL
     * @param array $parametros Parámetros para prepared statement
     * @param string $tipos Tipos de datos (i, s, d, b)
     * @return array|bool Resultado de la consulta
     */
    protected function ejecutarConsulta(string $sql, array $parametros = [], string $tipos = ''): array|bool
    {
        if (empty($parametros)) {
            $resultado = $this->conexion->query($sql);
            
            if (!$resultado) {
                $this->manejarError("Error en consulta personalizada");
                return false;
            }
            
            // Si es SELECT, devolver resultados
            if ($resultado instanceof mysqli_result) {
                return $resultado->fetch_all(MYSQLI_ASSOC);
            }
            
            return true;
        }
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->manejarError("Error al preparar consulta personalizada");
            return false;
        }
        
        if (empty($tipos)) {
            $tipos = $this->determinarTipos($parametros);
        }
        
        $stmt->bind_param($tipos, ...$parametros);
        
        if (!$stmt->execute()) {
            $this->manejarError("Error al ejecutar consulta: " . $stmt->error);
            return false;
        }
        
        $resultado = $stmt->get_result();
        
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
        
        return true;
    }

    /**
     * Inicia una transacción
     */
    public function iniciarTransaccion(): void
    {
        $this->conexion->begin_transaction();
    }

    /**
     * Confirma una transacción
     */
    public function confirmarTransaccion(): void
    {
        $this->conexion->commit();
    }

    /**
     * Revierte una transacción
     */
    public function revertirTransaccion(): void
    {
        $this->conexion->rollback();
    }

    /**
     * Determina los tipos de datos para bind_param
     * i = entero, d = decimal, s = string, b = blob
     * 
     * @param array $valores Valores a analizar
     * @return string String de tipos (ej: "issd")
     */
    private function determinarTipos(array $valores): string
    {
        $tipos = '';
        
        foreach ($valores as $valor) {
            if (is_int($valor)) {
                $tipos .= 'i';
            } elseif (is_float($valor) || is_double($valor)) {
                $tipos .= 'd';
            } elseif (is_string($valor)) {
                $tipos .= 's';
            } else {
                // Por defecto, tratarlo como string
                $tipos .= 's';
            }
        }
        
        return $tipos;
    }

    /**
     * Maneja errores de la base de datos
     * 
     * @param string $mensaje Mensaje de error
     */
    protected function manejarError(string $mensaje): void
    {
        $error = $mensaje . ' - MySQL Error: ' . $this->conexion->error;
        
        if (MODO_DEV) {
            echo "<pre style='background:#c00;color:#fff;padding:15px;font-family:monospace;'>";
            echo "ERROR EN REPOSITORIO ({$this->tabla}):\n";
            echo $error;
            echo "</pre>";
        }
        
        error_log($error);
    }

    /**
     * Sanitiza datos para prevenir inyección SQL
     * (mysqli prepare ya protege, pero útil para validaciones)
     * 
     * @param string $dato Dato a sanitizar
     * @return string Dato sanitizado
     */
    protected function sanitizar(string $dato): string
    {
        return $this->conexion->real_escape_string($dato);
    }
}