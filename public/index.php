<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

// Layout base
require_once BASE_PATH . '/vistas/principal/header.php';
require_once BASE_PATH . '/vistas/principal/sidebar.php';
// body
echo '<div class="content-body"><div class="container-fluid"></div></div>';

require_once BASE_PATH . '/vistas/principal/footer.php';
