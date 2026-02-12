<?php
/**
 * MenuBuilder.php
 * Genera el menú lateral (sidebar) dinámicamente desde la BD
 * - Lee tabla Menus con jerarquía padre-hijo
 * - Filtra por permisos del usuario (RBAC)
 * - Genera HTML compatible con el tema Bootstrap
 */

class MenuBuilder
{
    private mysqli $conexion;
    private ?int $usuarioId;
    private ?int $perfilId;
    private array $permisosUsuario = [];
    private string $urlActual;

    public function __construct(?int $usuarioId = null)
    {
        $this->conexion = ConexionBD::obtener();
        $this->usuarioId = $usuarioId;
        $this->urlActual = $_SERVER['REQUEST_URI'] ?? '';
        
        if ($usuarioId) {
            $this->cargarPerfilYPermisos();
        }
    }

    /**
     * Genera el HTML completo del sidebar
     * 
     * @return string HTML del sidebar
     */
    public function generar(): string
    {
        $menus = $this->obtenerMenus();
        
        if (empty($menus)) {
            return '<ul class="metismenu" id="menu"><li>No hay menús disponibles</li></ul>';
        }
        
        $html = '<ul class="metismenu" id="menu">';
        $html .= $this->generarMenusRecursivo($menus);
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Obtiene los menús desde la BD filtrados por permisos
     * 
     * @return array Menús organizados jerárquicamente
     */
    private function obtenerMenus(): array
    {
        // Si no hay usuario logueado, retornar vacío
        if (!$this->usuarioId) {
            return [];
        }

        $sql = "SELECT m.* 
                FROM Menus m
                WHERE m.status = 1";
        
        // Si hay permisos configurados, filtrar
        if (!empty($this->permisosUsuario)) {
            $permisosStr = implode(',', $this->permisosUsuario);
            $sql .= " AND (
                        m.id IN (
                            SELECT menuId FROM Permisos WHERE id IN ({$permisosStr})
                        )
                        OR m.tipo = 0
                      )";
        }
        
        $sql .= " ORDER BY m.orden ASC, m.id ASC";
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            error_log("Error al obtener menús: " . $this->conexion->error);
            return [];
        }
        
        $menus = $resultado->fetch_all(MYSQLI_ASSOC);
        
        // Organizar en estructura jerárquica
        return $this->organizarJerarquia($menus);
    }

    /**
     * Organiza los menús en estructura jerárquica padre-hijo
     * 
     * @param array $menus Menús planos de la BD
     * @return array Menús organizados
     */
    private function organizarJerarquia(array $menus): array
    {
        $arbol = [];
        $referencias = [];
        
        // Primera pasada: crear referencias
        foreach ($menus as $menu) {
            $id = $menu['id'];
            $referencias[$id] = $menu;
            $referencias[$id]['hijos'] = [];
        }
        
        // Segunda pasada: construir árbol
        foreach ($referencias as $id => $menu) {
            $idPadre = (int) $menu['idPadre'];
            
            if ($idPadre === 0) {
                // Es un menú raíz
                $arbol[] = &$referencias[$id];
            } else {
                // Es un submenú
                if (isset($referencias[$idPadre])) {
                    $referencias[$idPadre]['hijos'][] = &$referencias[$id];
                }
            }
        }
        
        return $arbol;
    }

    /**
     * Genera el HTML de los menús recursivamente
     * 
     * @param array $menus Menús a renderizar
     * @param int $nivel Nivel de profundidad
     * @return string HTML generado
     */
    private function generarMenusRecursivo(array $menus, int $nivel = 0): string
    {
        $html = '';
        
        foreach ($menus as $menu) {
            $tipo = (int) $menu['tipo'];
            
            // Tipo 0 = Título
            if ($tipo === 0) {
                $html .= $this->generarTitulo($menu);
                continue;
            }
            
            $tieneHijos = !empty($menu['hijos']);
            $estaActivo = $this->menuEstaActivo($menu);
            
            // Tipo 1 = Menú normal
            if ($tipo === 1) {
                $html .= $this->generarMenuItem($menu, $tieneHijos, $estaActivo, $nivel);
            }
        }
        
        return $html;
    }

    /**
     * Genera HTML de un título de menú
     * 
     * @param array $menu Datos del menú
     * @return string HTML del título
     */
    private function generarTitulo(array $menu): string
    {
        return '<li class="menu-title">' . htmlspecialchars($menu['descripcion']) . '</li>';
    }

    /**
     * Genera HTML de un ítem de menú
     * 
     * @param array $menu Datos del menú
     * @param bool $tieneHijos Si tiene submenús
     * @param bool $estaActivo Si es el menú actual
     * @param int $nivel Nivel de profundidad
     * @return string HTML del ítem
     */
    private function generarMenuItem(array $menu, bool $tieneHijos, bool $estaActivo, int $nivel): string
    {
        $html = '<li>';
        
        $claseActiva = $estaActivo ? 'mm-active' : '';
        $claseHijos = $tieneHijos ? 'has-arrow' : '';
        $href = $tieneHijos ? 'javascript:void(0);' : htmlspecialchars($menu['url']);
        
        $html .= sprintf(
            '<a class="%s %s" href="%s" aria-expanded="false">',
            $claseHijos,
            $claseActiva,
            $href
        );
        
        // Ícono
        $html .= '<div class="menu-icon">';
        $html .= $this->obtenerIcono($menu['icono']);
        $html .= '</div>';
        
        // Texto del menú
        $html .= '<span class="nav-text">' . htmlspecialchars($menu['descripcion']) . '</span>';
        $html .= '</a>';
        
        // Si tiene hijos, generar submenú
        if ($tieneHijos) {
            $html .= '<ul aria-expanded="false">';
            $html .= $this->generarMenusRecursivo($menu['hijos'], $nivel + 1);
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        
        return $html;
    }

    /**
     * Obtiene el SVG del ícono
     * 
     * @param string $nombreIcono Nombre del ícono
     * @return string SVG del ícono
     */
    private function obtenerIcono(string $nombreIcono): string
    {
        // Mapeo de nombres a SVGs
        // Por ahora retornamos un ícono por defecto
        // TODO: Crear biblioteca de íconos o usar iconos desde BD
        
        if (empty($nombreIcono)) {
            return $this->iconoPorDefecto();
        }
        
        // Aquí puedes crear un switch/case o mapeo de íconos
        // Por ejemplo:
        $iconos = [
            'dashboard' => $this->iconoDashboard(),
            'users' => $this->iconoUsuarios(),
            'settings' => $this->iconoConfiguracion(),
            'products' => $this->iconoProductos(),
            'sales' => $this->iconoVentas(),
            'reports' => $this->iconoReportes(),
        ];
        
        return $iconos[strtolower($nombreIcono)] ?? $this->iconoPorDefecto();
    }

    /**
     * Verifica si un menú está activo según la URL actual
     * 
     * @param array $menu Datos del menú
     * @return bool True si está activo
     */
    private function menuEstaActivo(array $menu): bool
    {
        if (empty($menu['url'])) {
            return false;
        }
        
        // Comparar URL del menú con URL actual
        $urlMenu = $menu['url'];
        
        // Exacta
        if ($this->urlActual === $urlMenu) {
            return true;
        }
        
        // Contiene
        if (strpos($this->urlActual, $urlMenu) === 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Carga el perfil y permisos del usuario
     */
    private function cargarPerfilYPermisos(): void
    {
        if (!$this->usuarioId) {
            return;
        }
        
        // Obtener perfil del usuario
        $sql = "SELECT perfilId FROM Usuarios WHERE id = ? AND status = 1 LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $this->usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        
        if (!$usuario) {
            return;
        }
        
        $this->perfilId = (int) $usuario['perfilId'];
        
        // Obtener permisos del perfil
        $sql = "SELECT permisoId 
                FROM PerfilPermisos 
                WHERE perfilId = ? AND status = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $this->perfilId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($fila = $resultado->fetch_assoc()) {
            $this->permisosUsuario[] = (int) $fila['permisoId'];
        }
        
        // Agregar permisos individuales del usuario
        $sql = "SELECT permisoId, efecto 
                FROM UsuariosPermisos 
                WHERE usuarioId = ? AND status = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('i', $this->usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($fila = $resultado->fetch_assoc()) {
            $permisoId = (int) $fila['permisoId'];
            $efecto = (int) $fila['efecto'];
            
            if ($efecto === 1) {
                // Conceder permiso
                if (!in_array($permisoId, $this->permisosUsuario)) {
                    $this->permisosUsuario[] = $permisoId;
                }
            } else {
                // Denegar permiso (remover)
                $key = array_search($permisoId, $this->permisosUsuario);
                if ($key !== false) {
                    unset($this->permisosUsuario[$key]);
                }
            }
        }
    }

    // ============================================
    // ÍCONOS SVG (ejemplos)
    // ============================================
    
    private function iconoPorDefecto(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="3" width="18" height="18" rx="2" stroke="#90959F" stroke-width="2"/>
        </svg>';
    }
    
    private function iconoDashboard(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.13478 20.7733V17.7156C9.13478 16.9351 9.77217 16.3023 10.5584 16.3023H13.4326C13.8102 16.3023 14.1723 16.4512 14.4393 16.7163C14.7063 16.9813 14.8563 17.3408 14.8563 17.7156V20.7733C14.8539 21.0978 14.9821 21.4099 15.2124 21.6402C15.4427 21.8705 15.756 22 16.0829 22H18.0438C18.9596 22.0024 19.8388 21.6428 20.4872 21.0008C21.1356 20.3588 21.5 19.487 21.5 18.5778V9.86686C21.5 9.13246 21.1721 8.43584 20.6046 7.96467L13.934 2.67587C12.7737 1.74856 11.1111 1.7785 9.98539 2.74698L3.46701 7.96467C2.87274 8.42195 2.51755 9.12064 2.5 9.86686V18.5689C2.5 20.4639 4.04738 22 5.95617 22H7.87229C8.55123 22 9.103 21.4562 9.10792 20.7822L9.13478 20.7733Z" fill="#90959F"/>
        </svg>';
    }
    
    private function iconoUsuarios(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path opacity="0.5" d="M9.34933 14.8577C5.38553 14.8577 2 15.47 2 17.9174C2 20.3666 5.364 21 9.34933 21C13.3131 21 16.6987 20.3877 16.6987 17.9404C16.6987 15.4911 13.3347 14.8577 9.34933 14.8577Z" fill="white"/>
            <path opacity="0.4" d="M9.34935 12.5248C12.049 12.5248 14.2124 10.4062 14.2124 7.76241C14.2124 5.11865 12.049 3 9.34935 3C6.65072 3 4.48633 5.11865 4.48633 7.76241C4.48633 10.4062 6.65072 12.5248 9.34935 12.5248Z" fill="white"/>
        </svg>';
    }
    
    private function iconoConfiguracion(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.8066 7.62355L20.1842 6.54346C19.6576 5.62954 18.4907 5.31426 17.5755 5.83866C17.1399 6.09528 16.6201 6.16809 16.1307 6.04103C15.6413 5.91396 15.2226 5.59746 14.9668 5.16131C14.8023 4.88409 14.7139 4.56833 14.7105 4.24598C14.7254 3.72916 14.5304 3.22834 14.17 2.85761C13.8096 2.48688 13.3145 2.2778 12.7975 2.27802H11.5435C11.0369 2.27801 10.5513 2.47985 10.194 2.83888C9.83666 3.19791 9.63714 3.68453 9.63958 4.19106C9.62457 5.23686 8.77245 6.07675 7.72654 6.07664C7.40418 6.07329 7.08843 5.98488 6.8112 5.82035C5.89603 5.29595 4.72908 5.61123 4.20251 6.52516L3.53432 7.62355C3.00838 8.53633 3.31937 9.70255 4.22997 10.2322C4.82187 10.574 5.1865 11.2055 5.1865 11.889C5.1865 12.5725 4.82187 13.204 4.22997 13.5457C3.32053 14.0719 3.0092 15.2353 3.53432 16.1453L4.16589 17.2345C4.41262 17.6797 4.82657 18.0082 5.31616 18.1474C5.80575 18.2865 6.33061 18.2248 6.77459 17.976C7.21105 17.7213 7.73116 17.6515 8.21931 17.7821C8.70746 17.9128 9.12321 18.233 9.37413 18.6716C9.53867 18.9488 9.62708 19.2646 9.63043 19.5869C9.63043 20.6435 10.4869 21.5 11.5435 21.5H12.7975C13.8505 21.5 14.7055 20.6491 14.7105 19.5961C14.7081 19.088 14.9088 18.6 15.2681 18.2407C15.6274 17.8814 16.1154 17.6806 16.6236 17.6831C16.9451 17.6917 17.2596 17.7797 17.5389 17.9393C18.4517 18.4653 19.6179 18.1543 20.1476 17.2437L20.8066 16.1453C21.0617 15.7074 21.1317 15.1859 21.0012 14.6963C20.8706 14.2067 20.5502 13.7893 20.111 13.5366C19.6717 13.2839 19.3514 12.8665 19.2208 12.3769C19.0902 11.8872 19.1602 11.3658 19.4153 10.9279C19.5812 10.6383 19.8213 10.3981 20.111 10.2322C21.0161 9.70283 21.3264 8.54343 20.8066 7.63271V7.62355Z" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12.175" cy="11.889" r="2.63616" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';
    }
    
    private function iconoProductos(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path opacity="0.4" d="M16.0755 2H19.4615C20.8637 2 22 3.14585 22 4.55996V7.97452C22 9.38864 20.8637 10.5345 19.4615 10.5345H16.0755C14.6732 10.5345 13.537 9.38864 13.537 7.97452V4.55996C13.537 3.14585 14.6732 2 16.0755 2Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2Z" fill="white"/>
        </svg>';
    }
    
    private function iconoVentas(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.7161 16.2234H8.49609" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15.7161 12.0369H8.49609" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M11.2521 7.86011H8.49707" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9085 2.74982C15.9085 2.74982 8.23149 2.75382 8.21949 2.75382C5.45949 2.77082 3.75049 4.58682 3.75049 7.35682V16.5528C3.75049 19.3368 5.47249 21.1598 8.25649 21.1598C8.25649 21.1598 15.9325 21.1568 15.9455 21.1568C18.7055 21.1398 20.4155 19.3228 20.4155 16.5528V7.35682C20.4155 4.57282 18.6925 2.74982 15.9085 2.74982Z" stroke="#90959F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';
    }
    
    private function iconoReportes(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path opacity="0.4" d="M16.191 2H7.81C4.77 2 3 3.78 3 6.83V17.16C3 20.26 4.77 22 7.81 22H16.191C19.28 22 21 20.26 21 17.16V6.83C21 3.78 19.28 2 16.191 2Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.08002 6.64999V6.65999C7.64902 6.65999 7.30002 7.00999 7.30002 7.43999C7.30002 7.86999 7.64902 8.21999 8.08002 8.21999H11.069C11.5 8.21999 11.85 7.86999 11.85 7.42899C11.85 6.99999 11.5 6.64999 11.069 6.64999H8.08002Z" fill="white"/>
        </svg>';
    }
}