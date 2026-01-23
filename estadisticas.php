<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

include("pages/head.php");
include("pages/db.php");
include "functions.php";

$db = conexion();
$db->set_charset('utf8mb4');

// --- Configuración de fechas (rango) ---
date_default_timezone_set('America/Tijuana');
$hoy         = new DateTime('now');
$ini_default = (clone $hoy)->modify('first day of this month')->format('Y-m-d');
$fin_default = (clone $hoy)->modify('last day of this month')->format('Y-m-d');

$ini = isset($_GET['ini']) && $_GET['ini'] !== '' ? $_GET['ini'] : $ini_default;
$fin = isset($_GET['fin']) && $_GET['fin'] !== '' ? $_GET['fin'] : $fin_default;

// Para incluir todo el día final en columnas DATETIME:
$fin_next = (new DateTime($fin))->modify('+1 day')->format('Y-m-d');

function query_scalar($db, $sql, $params = []) {
  $stmt = $db->prepare($sql);
  if (!$stmt) { error_log('Prepare failed: '.$db->error.' SQL: '.$sql); return 0.0; }
  if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  $val = 0.0;
  if ($row = $res->fetch_row()) $val = (float)($row[0] ?? 0);
  $stmt->close();
  return $val;
}

function query_all($db, $sql, $params = []) {
  $stmt = $db->prepare($sql);
  if (!$stmt) { error_log('Prepare failed: '.$db->error.' SQL: '.$sql); return []; }
  if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  $stmt->close();
  return $rows;
}

// Formatting helpers
function n2($v) { return number_format((float)$v, 2, '.', ','); }
function n0($v) { return number_format((int)$v, 0, '.', ','); }

// ========================= KPIs (ya los tenías) =========================

// 1) Ventas por fecha de evento (bookings) en el rango + extras
$total_eventos_bookings = query_scalar(
  $db,
  "SELECT ROUND(SUM(e.total + COALESCE(x.total_extras,0)), 2)
   FROM Eventos e
   LEFT JOIN (
     SELECT eventoid, SUM(total) AS total_extras
     FROM Extraseventos
     WHERE statusid <> 0
     GROUP BY eventoid
   ) x ON x.eventoid = e.id
   WHERE e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?",
  [$ini, $fin_next]
);
// 1.1) Número de eventos en el rango
$eventos_count = (int) query_scalar(
  $db,
  "SELECT COUNT(*)
   FROM Eventos
   WHERE statusid < 8
     AND fecha >= ? AND fecha < ?",
  [$ini, $fin_next]
);

// 1.2) Clientes activos (distintos) en el rango    porr desactivar plantilla
$clientes_activos = (int) query_scalar(
  $db,
  "SELECT COUNT(DISTINCT personasid)
   FROM Eventos
   WHERE statusid < 8
     AND fecha >= ? AND fecha < ?",
  [$ini, $fin_next]
);

// 2) Cobrado (cash-in) relacionado a los EVENTOS del rango (sin filtrar Abonos.fecha)
$total_cobrado_cash = query_scalar(
  $db,
  "SELECT ROUND(COALESCE(SUM(a.monto),0),2)
   FROM Abonos a
   JOIN Eventos e ON e.id = a.eventoid
   WHERE e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?",
  [$ini, $fin_next]
);

// 3) Gastos (cash-out)
// - incluye gastos con fecha en el rango
// - y también gastos (de cualquier fecha) vinculados a eventos cuya fecha está en el rango
$total_gastos_cash = query_scalar(
  $db,
  "SELECT ROUND(SUM(g.monto),2)
   FROM Gastos g
   JOIN Eventos e ON e.id = g.eventoid
   WHERE (g.fecha >= ? AND g.fecha < ?)
      OR (e.statusid < 8 AND e.fecha >= ? AND e.fecha < ?)",
  [$ini, $fin_next, $ini, $fin_next]
);

// 4) Fondo (anticipos para eventos FUTUROS respecto al corte seleccionado)
// Suma abonos acumulados HASTA el cierre ($fin_next) de eventos cuya fecha es DESPUÉS del cierre.
$fondo_eventos_futuros = query_scalar(
  $db,
  "SELECT ROUND(SUM(a.monto),2)
   FROM Abonos a
   JOIN Eventos e ON e.id = a.eventoid
   WHERE e.statusid < 8
     AND a.fecha < ?
     AND e.fecha >= ?",
  [$fin_next, $fin_next]
);


// 5) Cartera al cierre del rango (pendiente de cobrar de eventos ya ocurridos)
// Considera: total del evento + extras del evento, menos abonos acumulados hasta el cierre.
$cartera = query_scalar(
  $db,
  "SELECT ROUND(
  SUM(
    GREATEST(
      0,
      (e.total + COALESCE(ex.total_extras, 0)) - COALESCE(a.abonos, 0)
    )
  ), 2
) AS cartera_periodo
FROM Eventos e
LEFT JOIN (
  SELECT eventoid, SUM(total) AS total_extras
  FROM Extraseventos
  WHERE statusid <> 0
  GROUP BY eventoid
) ex ON ex.eventoid = e.id
LEFT JOIN (
  SELECT eventoid, SUM(monto) AS abonos
  FROM Abonos
  WHERE fecha < ?   -- fin_next
  GROUP BY eventoid
) a ON a.eventoid = e.id
WHERE e.statusid < 8
  AND e.fecha >= ?  -- ini
  AND e.fecha < ?;  -- fin_next
",
  [$fin_next, $ini, $fin_next]
);

// 6) Comisiones estimadas
$comisiones_estimadas = query_scalar(
  $db,
  "SELECT ROUND(SUM(total * (comision/100)),2)
   FROM Eventos
   WHERE statusid < 8
     AND fecha >= ? AND fecha < ?",
  [$ini, $fin_next]
);

$flujo_neto_cash = round($total_cobrado_cash - $total_gastos_cash, 2);
$pct_cobrado_sobre_bookings = $total_eventos_bookings > 0 ? round(100 * $total_cobrado_cash / $total_eventos_bookings, 2) : 0.0;
$margen_caja_pct = $total_cobrado_cash > 0 ? round(100 * $flujo_neto_cash / $total_cobrado_cash, 2) : 0.0;

// ========================= NUEVO: Dashboard 3 inversores =========================
$INVERSORES = [1,2,3];
$partes = 3;

$inversores_rows = query_all(
  $db,
  "SELECT
      u.id AS usuarioid,
      u.nombre,
      COALESCE(ing.ingresos_cobrados,0) AS ingresos_cobrados,
      COALESCE(gas.gastos_pagados,0)    AS gastos_pagados
   FROM Usuarios u
   LEFT JOIN (
      SELECT a.usuarioid, ROUND(SUM(a.monto),2) AS ingresos_cobrados
      FROM Abonos a
      JOIN Eventos e ON e.id = a.eventoid
      WHERE e.statusid < 8
        AND e.fecha >= ? AND e.fecha < ?
      GROUP BY a.usuarioid
   ) ing ON ing.usuarioid = u.id
   LEFT JOIN (
      SELECT g.usuarioid, ROUND(SUM(g.monto),2) AS gastos_pagados
      FROM Gastos g
      JOIN Eventos e ON e.id = g.eventoid
      WHERE e.statusid < 8
        AND e.fecha >= ? AND e.fecha < ?
      GROUP BY g.usuarioid
   ) gas ON gas.usuarioid = u.id
   WHERE u.id IN (?,?,?)
   ORDER BY u.id ASC",
  [$ini, $fin_next, $ini, $fin_next, $INVERSORES[0], $INVERSORES[1], $INVERSORES[2]]
);


// Totales del periodo (cash basis)
$ingresos_total = (float)$total_cobrado_cash;
$gastos_total   = (float)$total_gastos_cash;
$utilidad_periodo = round($ingresos_total - $gastos_total, 2);

// Reparto igual 1/3
$deberia_quedarse = round($utilidad_periodo / $partes, 6);

// Para cuadrar: posición actual por usuario = ingresos_cobrados - gastos_pagados
// Transferencia necesaria = deberia_quedarse - posicion_actual
foreach ($inversores_rows as &$r) {
  $posicion_actual = (float)$r['ingresos_cobrados'] - (float)$r['gastos_pagados'];
  $r['ingresos_total']      = $ingresos_total;
  $r['gastos_total']        = $gastos_total;
  $r['utilidad_periodo']    = $utilidad_periodo;
  $r['deberia_quedarse']    = $deberia_quedarse;
  $r['diferencia_a_cuadrar']= round($deberia_quedarse - $posicion_actual, 6);
  $r['posicion_actual']     = round($posicion_actual, 2);
}
unset($r);

// ========================= Mini-listas (para auditar qué se está sumando) =========================

// Últimos abonos relacionados a eventos del rango (aunque se hayan pagado fuera del rango)
$ult_abonos = query_all(
  $db,
  "SELECT
      a.id,
      a.fecha,
      a.monto,
      a.referencia,
      a.eventoid,
      e.nombre AS evento,
      u.nombre AS usuario
   FROM Abonos a
   JOIN Eventos e ON e.id = a.eventoid
   LEFT JOIN Usuarios u ON u.id = a.recibeid
   WHERE e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?
   ORDER BY e.id DESC",
  [$ini, $fin_next]
);


// Últimos gastos relacionados a eventos dentro del rango (independiente de la fecha del gasto)
$ult_gastos = query_all(
  $db,
  "SELECT
      g.id,
      g.fecha,
      g.monto,
      g.concepto,
      g.eventoid,
      e.nombre AS evento,
      p.nombre AS proveedor,
      u.nombre AS usuario
   FROM Gastos g
   JOIN Eventos e ON e.id = g.eventoid
   LEFT JOIN Proveedores p ON p.id = g.proveedorid
   LEFT JOIN Usuarios u ON u.id = g.usuarioid
   WHERE (e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?)
     OR (g.fecha >= ? AND g.fecha < ?)
   ORDER BY g.fecha DESC",
  [$ini, $fin_next,$ini, $fin_next]
);

// Top proveedores por gasto (eventos dentro del rango)
$top_proveedores = query_all(
  $db,
  "SELECT
      p.nombre AS proveedor,
      ROUND(SUM(g.monto),2) AS total
   FROM Gastos g
   JOIN Eventos e ON e.id = g.eventoid
   JOIN Proveedores p ON p.id = g.proveedorid
   WHERE e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?
   GROUP BY p.id
   ORDER BY total DESC
   LIMIT 6",
  [$ini, $fin_next]
);

// Eventos del rango: total+extras, cobrado en rango, cobrado acumulado y saldo al corte
$eventos_auditoria = query_all(
  $db,
  "SELECT
      e.id,
      e.fecha,
      e.nombre,
      ROUND(e.total + COALESCE(ex.total_extras,0), 2) AS total_evento,

      ROUND(COALESCE((
        SELECT SUM(a1.monto)
        FROM Abonos a1
        WHERE a1.eventoid = e.id
          AND a1.fecha >= ? AND a1.fecha < ?
      ),0),2) AS cobrado_en_rango,

      ROUND(COALESCE((
        SELECT SUM(a2.monto)
        FROM Abonos a2
        WHERE a2.eventoid = e.id
          AND a2.fecha < ?
      ),0),2) AS cobrado_acum_hasta_corte,

      ROUND(GREATEST(
        0,
        (e.total + COALESCE(ex.total_extras,0)) - COALESCE((
          SELECT SUM(a3.monto)
          FROM Abonos a3
          WHERE a3.eventoid = e.id
            AND a3.fecha < ?
        ),0)
      ),2) AS saldo_al_corte

   FROM Eventos e
   LEFT JOIN (
      SELECT eventoid, SUM(total) AS total_extras
      FROM Extraseventos
      WHERE statusid <> 0
      GROUP BY eventoid
   ) ex ON ex.eventoid = e.id

   WHERE e.statusid < 8
     AND e.fecha >= ? AND e.fecha < ?
   ORDER BY e.fecha DESC
   LIMIT 8",
  [$ini, $fin_next, $fin_next, $fin_next, $ini, $fin_next]
);


// ========================= Permisos =========================
if (!isset($_SESSION['permisos'])) {
  echo "no existen permisos.";
  include("pages/foot.php");
  exit;
}

if ($_SESSION['permisos'] == 1) {
  header("Location: index.php");
  include("pages/foot.php");
  exit;
}

// ========================= UI =========================
?>
<style>
  .kpi-card{ border-radius:16px; }
  .kpi-title{ font-size:.9rem; color:#6c757d; margin-bottom:.25rem; }
  .kpi-value{ font-size:1.45rem; font-weight:700; letter-spacing:.2px; }
  .kpi-note{ font-size:.85rem; color:#6c757d; }
  .pill{ border-radius: 999px; padding: .15rem .55rem; font-size: .78rem; }
  .table-sm td, .table-sm th { padding: .45rem; }
  .mini-card{ border-radius:16px; }
  .muted{ color:#6c757d; }
  .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
</style>

<div class="col-md-12 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
          <h4 class="card-title mb-0">Estadísticas</h4>
          <div class="text-muted mt-1">
            Rango activo: <strong><?php echo htmlspecialchars($ini); ?></strong> a <strong><?php echo htmlspecialchars($fin); ?></strong>
          </div>
        </div>

        <div class="text-right mt-2 mt-md-0">
          <span class="badge badge-light pill">Corte: <?php echo htmlspecialchars($fin); ?></span>
          <span class="badge badge-light pill">Inversores: 3</span>
        </div>
      </div>

      <hr>

      <form class="form-inline row" method="get">
        <div class="col-12 col-md-4 mb-2">
          <label class="mr-2 mb-0">Desde</label>
          <input type="date" class="form-control w-100" name="ini" value="<?php echo htmlspecialchars($ini); ?>">
        </div>
        <div class="col-12 col-md-4 mb-2">
          <label class="mr-2 mb-0">Hasta</label>
          <input type="date" class="form-control w-100" name="fin" value="<?php echo htmlspecialchars($fin); ?>">
        </div>
        <div class="col-12 col-md-4 mb-2 d-flex align-items-end justify-content-md-end">
          <button class="btn btn-primary mr-2" type="submit">Aplicar</button>
          <button class="btn btn-outline-secondary" type="button" id="btnMesActual">Mes actual</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row">
  <!-- KPI: Flujo neto -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Ganancias (flujo de caja)</div>
        <div class="kpi-value">$ <?php echo n2($flujo_neto_cash); ?></div>
        <div class="kpi-note">Cobrado del rango − Gastos del rango.</div>
      </div>
    </div>
  </div>

  <!-- KPI: Ventas (bookings) -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Ventas del periodo (bookings)</div>
        <div class="kpi-value">$ <?php echo n2($total_eventos_bookings); ?></div>
        <div class="kpi-note">Por fecha del evento (Eventos statusid &lt; 8).</div>
      </div>
    </div>
  </div>

  <!-- KPI: Cobrado -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Cobrado (cash-in)</div>
        <div class="kpi-value">$ <?php echo n2($total_cobrado_cash); ?></div>
        <div class="kpi-note">Abonos del rango (sin filtro de statusid).</div>
      </div>
    </div>
  </div>

  <!-- KPI: Gastos -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Gastos (cash-out)</div>
        <div class="kpi-value">$ <?php echo n2($total_gastos_cash); ?></div>
        <div class="kpi-note">Gastos del rango (sin filtro de statusid).</div>
      </div>
    </div>
  </div>

  <!-- KPI: Cartera -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Cartera al cierre</div>
        <div class="kpi-value">$ <?php echo n2($cartera); ?></div>
        <div class="kpi-note">Pendiente de cobrar de eventos ocurridos hasta fin de rango.</div>
      </div>
    </div>
  </div>

  <!-- KPI: Fondo -->
  <div class="col-md-4 mb-3">
    <div class="card kpi-card shadow-sm">
      <div class="card-body">
        <div class="kpi-title">Fondo (anticipos a futuro)</div>
        <div class="kpi-value">$ <?php echo n2($fondo_eventos_futuros); ?></div>
        <div class="kpi-note">Abonos del rango cuyos eventos ocurren después del corte.</div>
      </div>
    </div>
  </div>

  <!-- KPI: Extras -->
  <div class="col-md-3 mb-3">
    <div class="card kpi-card shadow-sm"><div class="card-body">
      <div class="kpi-title">Eventos</div>
      <div class="kpi-value"><?php echo n0($eventos_count); ?></div>
      <div class="kpi-note">Eventos en el rango.</div>
    </div></div>
  </div>
  <!-- PAUSADOS POR AHORA 
  <div class="col-md-3 mb-3">
    <div class="card kpi-card shadow-sm"><div class="card-body">
      <div class="kpi-title">Clientes activos</div>
      <div class="kpi-value"><?php echo n0($clientes_activos); ?></div>
      <div class="kpi-note">Personas distintas con eventos.</div>
    </div></div>
  </div>

  <div class="col-md-3 mb-3">
    <div class="card kpi-card shadow-sm"><div class="card-body">
      <div class="kpi-title">% cobrado / bookings</div>
      <div class="kpi-value"><?php echo n2($pct_cobrado_sobre_bookings); ?>%</div>
      <div class="kpi-note">Relación caja vs devengo.</div>
    </div></div>
  </div>

  <div class="col-md-3 mb-3">
    <div class="card kpi-card shadow-sm"><div class="card-body">
      <div class="kpi-title">Comisiones estimadas</div>
      <div class="kpi-value">$ <?php echo n2($comisiones_estimadas); ?></div>
      <div class="kpi-note">Suma de total × (comisión%).</div>
    </div></div>
  </div>
</div>
 -->
<!-- ===================== NUEVO: Reparto 3 inversores ===================== -->
<div class="row">
  <div class="col-12 mb-3">
    <div class="card mini-card shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
          <div>
            <h5 class="mb-1">Reparto del periodo</h5>
            <div class="muted">
              <strong>posición actual</strong> = ingresos cobrados − gastos pagados.<br>
              <strong>Diferencia</strong> = lo que debe recibir (+) o pagar (−) para quedar en <strong>1/3</strong>
            </div>
          </div>
          <div class="text-right mt-2 mt-md-0">
            <div class="mono muted">Utilidad del periodo: $ <?php echo n2($utilidad_periodo); ?></div>
            <div class="mono"><strong>1/3:</strong> $ <?php echo n2($deberia_quedarse); ?></div>
          </div>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Inversor</th>
                <th class="text-right">Ingresos cobrados</th>
                <th class="text-right">Gastos pagados</th>
                <th class="text-right">Posición actual</th>
                <th class="text-right">Utilidad total</th>
                <th class="text-right">Debe quedar en</th>
                <th class="text-right">Diferencia a cuadrar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inversores_rows as $r): ?>
                <?php
                  $dif = (float)$r['diferencia_a_cuadrar'];
                  $badge = $dif > 0 ? 'badge-success' : ($dif < 0 ? 'badge-danger' : 'badge-secondary');
                  $label = $dif > 0 ? 'RECIBE' : ($dif < 0 ? 'PAGA' : 'OK');
                ?>
                <tr>
                  <td><?php echo (int)$r['usuarioid']; ?></td>
                  <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                  <td class="text-right">$ <?php echo n2($r['ingresos_cobrados']); ?></td>
                  <td class="text-right">$ <?php echo n2($r['gastos_pagados']); ?></td>
                  <td class="text-right">$ <?php echo n2($r['posicion_actual']); ?></td>
                  <td class="text-right">$ <?php echo n2($r['utilidad_periodo']); ?></td>
                  <td class="text-right">$ <?php echo n2($r['deberia_quedarse']); ?></td>
                  <td class="text-right">
                    <span class="badge <?php echo $badge; ?> pill mr-2"><?php echo $label; ?></span>
                    <strong>$ <?php echo n2($dif); ?></strong>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($inversores_rows)): ?>
                <tr><td colspan="8" class="text-center text-muted">Sin datos de inversores (usuarioid 1,2,3) en este rango.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="muted mt-2">
          la utilidad son los ingresos menos los gastos del periodo y los eventos relacionados
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===================== NUEVO: Mini-listas / Auditoría ===================== -->
<div class="row">
  <div class="col-lg-6 mb-3">
    <div class="card mini-card shadow-sm">
      <div class="card-body">
        <h6 class="mb-2">Últimos ingresos (Abonos)</h6>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Fecha</th>
                <th>Evento</th>
                <th>Usuario</th>
                <th class="text-right">Monto</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ult_abonos as $a): ?>
                <tr>
                  <td class="mono"><?php echo htmlspecialchars(substr((string)$a['fecha'], 0, 16)); ?></td>
                  <td><?php echo htmlspecialchars($a['evento'] ?? ('Evento #'.$a['eventoid'])); ?></td>
                  <td><?php echo htmlspecialchars($a['usuario'] ?? '—'); ?></td>
                  <td class="text-right">$ <?php echo n2($a['monto']); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($ult_abonos)): ?>
                <tr><td colspan="4" class="text-center text-muted">Sin abonos en el rango.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="muted mt-2">Abonos relacionados con eventos dentro del periodo.</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-3">
    <div class="card mini-card shadow-sm">
      <div class="card-body">
        <h6 class="mb-2">Últimos gastos</h6>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Usuario</th>
                <th class="text-right">Monto</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ult_gastos as $g): ?>
                <tr>
                  <td class="mono"><?php echo htmlspecialchars(substr((string)$g['fecha'], 0, 16)); ?></td>
                  <td><?php echo htmlspecialchars($g['proveedor'] ?? '—'); ?></td>
                  <td><?php echo htmlspecialchars($g['usuario'] ?? '—'); ?></td>
                  <td class="text-right">$ <?php echo n2($g['monto']); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($ult_gastos)): ?>
                <tr><td colspan="4" class="text-center text-muted">Sin gastos en el rango.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="muted mt-2"><span class="mono">Gastos</span> dentro del rango.</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-3">
    <div class="card mini-card shadow-sm">
      <div class="card-body">
        <h6 class="mb-2">Top proveedores por gasto</h6>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Proveedor</th>
                <th class="text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($top_proveedores as $p): ?>
                <tr>
                  <td><?php echo htmlspecialchars($p['proveedor']); ?></td>
                  <td class="text-right">$ <?php echo n2($p['total']); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($top_proveedores)): ?>
                <tr><td colspan="2" class="text-center text-muted">Sin gastos en el rango.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="muted mt-2">Agrupado por <span class="mono">Proveedor</span>.</div>
      </div>
    </div>
  </div>
</div>

<!-- ===================== NUEVO: Auditoría de eventos (qué se está cobrando) ===================== -->
<div class="row">
  <div class="col-12 mb-3">
    <div class="card mini-card shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
          <h6 class="mb-2 mb-md-0">Auditoría rápida de eventos</h6>
          <div class="muted">Muestra eventos dentro del rango.</div>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>Fecha evento</th>
                <th>Evento</th>
                <th class="text-right">Total</th>
                <th class="text-right">Cobrado en rango</th>
                <th class="text-right">Cobrado acumulado</th>
                <th class="text-right">Saldo al corte</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($eventos_auditoria as $e): ?>
                <tr>
                  <td class="mono"><?php echo htmlspecialchars(substr((string)$e['fecha'], 0, 10)); ?></td>
                  <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                  <td class="text-right">$ <?php echo n2($e['total_evento']); ?></td>
                  <td class="text-right">$ <?php echo n2($e['cobrado_en_rango']); ?></td>
                  <td class="text-right">$ <?php echo n2($e['cobrado_acum_hasta_corte']); ?></td>
                  <td class="text-right"><strong>$ <?php echo n2($e['saldo_al_corte']); ?></strong></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($eventos_auditoria)): ?>
                <tr><td colspan="6" class="text-center text-muted">Sin eventos en el rango.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="muted mt-2">
          Aquí puedes detectar rápido si los eventos estan liquidados y cuanto se cobro.
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Botón "Mes actual"
  document.getElementById('btnMesActual').addEventListener('click', function() {
    const hoy = new Date();
    const first = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const last  = new Date(hoy.getFullYear(), hoy.getMonth()+1, 0);
    const fmt = d => d.toISOString().slice(0,10);
    document.querySelector('input[name="ini"]').value = fmt(first);
    document.querySelector('input[name="fin"]').value = fmt(last);
  });
</script>



<?php
include("pages/foot.php");
?>
