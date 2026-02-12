<?php
/**
 * header.php
 * Cabecera principal del sistema
 * NOTA: Ahora usa bootstrap.php para cargar .env y configuración
 */

// Verificar que bootstrap.php fue cargado
if (!defined('BASE_PATH')) {
    die('Error: Este archivo debe ser cargado después de bootstrap.php');
}

// Establecer título de página dinámicamente si existe en sesión
$tituloPagina = $_SESSION['titulo_pagina'] ?? env('Ventana_activa', 'Dashboard');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title><?= e(env('Titulo_Sistema')) ?></title>

    <meta charset="utf-8">
    <meta property="og:title" content="<?= e(env('Titulo_Sistema')) ?>">
    <meta property="og:description" content="<?= e(env('Descripcion')) ?>">
    <meta property="og:image" content="<?= e(env('Main_Logo')) ?>">
    <meta name="description" content="<?= e(env('Descripcion')) ?>">
    <meta name="keywords" content="<?= e(env('Keywords')) ?>">
    <meta name="twitter:title" content="<?= e(env('Titulo_Sistema')) ?>">
    <meta name="twitter:description" content="<?= e(env('Descripcion')) ?>">
    <meta name="twitter:image" content="<?= e(env('Main_Logo')) ?>">
    <meta name="robots" content="index, follow">
    <meta name="format-detection" content="telephone=no">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="<?= e(env('Favicon')) ?>" rel="shortcut icon" type="image/png">
    <link href="<?= Url::asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') ?>" rel="stylesheet">
    <link href="<?= Url::asset('vendor/swiper/css/swiper-bundle.min.css') ?>" rel="stylesheet">
    <link href="<?= Url::asset('vendor/datatables/css/jquery.dataTables.min.css') ?>" rel="stylesheet">
    <link href="<?= Url::asset('vendor/datatables/css/buttons.dataTables.min.css') ?>" rel="stylesheet">
    <link href="<?= Url::asset('vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') ?>" rel="stylesheet">
    <link href="<?= Url::asset('css/style.css') ?>" rel="stylesheet">
</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div>
            <img src="<?= e(env('Preloader')) ?>" alt="Cargando...">
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="index.php" class="brand-logo">
                <svg class="logo-abbr" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.525 18.3762L15.0112 10.8625L18.461 7.41268L28.894 17.8457L26.6106 20.1291L30.1441 23.6626L35.961 17.8457L18.461 0.345703L11.4777 7.32896L7.94422 10.8624L11.4777 14.396L15.219 18.1372L21.9107 24.829L18.461 28.2787L8.02796 17.8457L10.3113 15.5623L6.77783 12.0288L0.960938 17.8457L18.461 35.3457L25.4442 28.3625L28.9777 24.829L22.525 18.3762Z" fill="white"/>
                </svg>
                <svg class="brand-title" width="91" height="19" viewBox="0 0 91 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="yesh" d="M42.8285 15.0009H40.3569L44.1229 4.0918H47.0952L50.8558 15.0009H48.3842L45.6516 6.5847H45.5664L42.8285 15.0009ZM42.674 10.7129H48.5121V12.5133H42.674V10.7129ZM56.036 15.0009H52.1689V4.0918H56.068C57.165 4.0918 58.11 4.3102 58.902 4.747C59.694 5.1802 60.303 5.8035 60.729 6.6167C61.159 7.4299 61.373 8.4029 61.373 9.5357C61.373 10.6721 61.159 11.6486 60.729 12.4654C60.303 13.2821 59.69 13.9089 58.891 14.3457C58.096 14.7825 57.144 15.0009 56.036 15.0009ZM54.475 13.0247H55.94C56.622 13.0247 57.195 12.904 57.661 12.6625C58.129 12.4174 58.481 12.0393 58.715 11.5279C58.953 11.013 59.072 10.3489 59.072 9.5357C59.072 8.7296 58.953 8.0709 58.715 7.5595C58.481 7.0481 58.131 6.6717 57.666 6.4302C57.201 6.1888 56.627 6.068 55.945 6.068H54.475V13.0247ZM63.082 4.0918H65.926L68.931 11.4214H69.059L72.063 4.0918H74.907V15.0009H72.67V7.9004H72.579L69.756 14.9476H68.233L65.41 7.8738H65.319V15.0009H63.082V4.0918ZM79.114 4.0918V15.0009H76.808V4.0918H79.114ZM90.136 4.0918V15.0009H88.144L83.398 8.1348H83.318V15.0009H81.012V4.0918H83.036L87.745 10.9526H87.84V4.0918H90.136Z" fill="white"/>
                    <path class="admin2" d="M35 0H0V19H35V0Z" fill="white"/>
                    <path class="admin1" d="M10.4929 7.7836C10.4588 7.4398 10.3125 7.1728 10.054 6.9824C9.7955 6.7921 9.4446 6.6969 9.0014 6.6969C8.7003 6.6969 8.446 6.7395 8.2386 6.8248C8.0313 6.9071 7.87219 7.0222 7.76139 7.1699C7.65339 7.3177 7.5994 7.4853 7.5994 7.6728C7.5938 7.829 7.6264 7.9654 7.6974 8.0819C7.7713 8.1983 7.8722 8.2992 8 8.3844C8.1278 8.4668 8.2756 8.5393 8.4432 8.6018C8.6108 8.6614 8.78979 8.7125 8.98009 8.7552L9.76419 8.9427C10.1449 9.0279 10.4943 9.1415 10.8125 9.2836C11.1307 9.4256 11.4063 9.6003 11.6392 9.8077C11.8722 10.0151 12.0526 10.2594 12.1804 10.5407C12.3111 10.8219 12.3778 11.1444 12.3807 11.508C12.3778 12.0421 12.2415 12.5052 11.9716 12.8972C11.7045 13.2864 11.3182 13.589 10.8125 13.8049C10.3097 14.0179 9.7031 14.1245 8.9929 14.1245C8.2884 14.1245 7.6747 14.0165 7.152 13.8006C6.6321 13.5847 6.2259 13.2651 5.9332 12.8418C5.6435 12.4157 5.49149 11.8887 5.47729 11.2608H7.26279C7.28269 11.5535 7.36649 11.7978 7.51419 11.9938C7.66479 12.187 7.8651 12.3333 8.1151 12.4327C8.3679 12.5293 8.6534 12.5776 8.9716 12.5776C9.2841 12.5776 9.5554 12.5321 9.7855 12.4412C10.0185 12.3503 10.1989 12.2239 10.3267 12.062C10.4545 11.9 10.5185 11.714 10.5185 11.5037C10.5185 11.3077 10.4602 11.1429 10.3438 11.0094C10.2301 10.8759 10.0625 10.7623 9.8409 10.6685C9.6222 10.5748 9.3537 10.4895 9.0355 10.4128L8.0852 10.1742C7.3494 9.9952 6.7685 9.7154 6.3423 9.3347C5.9162 8.954 5.7045 8.4412 5.7074 7.7964C5.7045 7.2679 5.8452 6.8063 6.1293 6.4114C6.4162 6.0165 6.8097 5.7083 7.3097 5.4867C7.8097 5.2651 8.37779 5.1543 9.01419 5.1543C9.66189 5.1543 10.2273 5.2651 10.7102 5.4867C11.196 5.7083 11.5739 6.0165 11.8438 6.4114C12.1136 6.8063 12.2528 7.2637 12.2614 7.7836H10.4929Z" fill="black"/>
                </svg>
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line">
                        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.7468 5.58925C11.0722 5.26381 11.0722 4.73617 10.7468 4.41073C10.4213 4.0853 9.89369 4.0853 9.56826 4.41073L4.56826 9.41073C4.25277 9.72622 4.24174 10.2342 4.54322 10.5631L9.12655 15.5631C9.43754 15.9024 9.96468 15.9253 10.3039 15.6143C10.6432 15.3033 10.6661 14.7762 10.3551 14.4369L6.31096 10.0251L10.7468 5.58925Z" fill="#452B90"/>
                            <path opacity="0.3" d="M16.5801 5.58924C16.9056 5.26381 16.9056 4.73617 16.5801 4.41073C16.2547 4.0853 15.727 4.0853 15.4016 4.41073L10.4016 9.41073C10.0861 9.72622 10.0751 10.2342 10.3766 10.5631L14.9599 15.5631C15.2709 15.9024 15.798 15.9253 16.1373 15.6143C16.4766 15.3033 16.4995 14.7762 16.1885 14.4369L12.1443 10.0251L16.5801 5.58924Z" fill="#452B90"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->
        
        <?php
        // Generar sidebar dinámicamente
        if (estaAutenticado()) {
            $menuBuilder = new MenuBuilder(usuarioId());
        ?>
        <div class="deznav">
            <div class="deznav-scroll">
                <?= $menuBuilder->generar() ?>
                <div class="copyright">
                    <p><?= e(env('Nombre_empresa')) ?> © <span class="current-year"><?= date('Y') ?></span> Todos los derechos reservados</p>
                    <p>Powered by <span class="heart"></span> Lebytek</p>
                </div>
            </div>
        </div>
        <?php } ?>

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="dashboard_bar">
                                <?= e($tituloPagina) ?>
                            </div>
                        </div>
                        <div class="header-right d-flex align-items-center">
                            <div class="input-group search-area">
                                <input type="text" class="form-control" placeholder="Buscar...">
                                <span class="input-group-text"><a href="javascript:void(0);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_1_450)">
                                                <path opacity="0.3"
                                                    d="M14.2929 16.7071C13.9024 16.3166 13.9024 15.6834 14.2929 15.2929C14.6834 14.9024 15.3166 14.9024 15.7071 15.2929L19.7071 19.2929C20.0976 19.6834 20.0976 20.3166 19.7071 20.7071C19.3166 21.0976 18.6834 21.0976 18.2929 20.7071L14.2929 16.7071Z"
                                                    fill="#452B90" />
                                                <path
                                                    d="M11 16C13.7614 16 16 13.7614 16 11C16 8.23859 13.7614 6.00002 11 6.00002C8.23858 6.00002 6 8.23859 6 11C6 13.7614 8.23858 16 11 16ZM11 18C7.13401 18 4 14.866 4 11C4 7.13402 7.13401 4.00002 11 4.00002C14.866 4.00002 18 7.13402 18 11C18 14.866 14.866 18 11 18Z"
                                                    fill="#452B90" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_1_450">
                                                    <rect width="24" height="24" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </a></span>
                            </div>
                            <ul class="navbar-nav">
								 <!-- Botón de modo oscuro -->
                                <li class="nav-item dropdown notification_dropdown">
                                    <a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
                                        <svg id="icon-light" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" class="svg-main-icon">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path d="M12,15 C10.3431458,15 9,13.6568542 9,12 C9,10.3431458 10.3431458,9 12,9 C13.6568542,9 15,10.3431458 15,12 C15,13.6568542 13.6568542,15 12,15 Z" fill="#000000" fill-rule="nonzero" />
												<path d="M19.5,10.5 L21,10.5 C21.8284271,10.5 22.5,11.1715729 22.5,12 C22.5,12.8284271 21.8284271,13.5 21,13.5 L19.5,13.5 C18.6715729,13.5 18,12.8284271 18,12 C18,11.1715729 18.6715729,10.5 19.5,10.5 Z M16.0606602,5.87132034 L17.1213203,4.81066017 C17.7071068,4.22487373 18.6568542,4.22487373 19.2426407,4.81066017 C19.8284271,5.39644661 19.8284271,6.34619408 19.2426407,6.93198052 L18.1819805,7.99264069 C17.5961941,8.57842712 16.6464466,8.57842712 16.0606602,7.99264069 C15.4748737,7.40685425 15.4748737,6.45710678 16.0606602,5.87132034 Z M16.0606602,18.1819805 C15.4748737,17.5961941 15.4748737,16.6464466 16.0606602,16.0606602 C16.6464466,15.4748737 17.5961941,15.4748737 18.1819805,16.0606602 L19.2426407,17.1213203 C19.8284271,17.7071068 19.8284271,18.6568542 19.2426407,19.2426407 C18.6568542,19.8284271 17.7071068,19.8284271 17.1213203,19.2426407 L16.0606602,18.1819805 Z M3,10.5 L4.5,10.5 C5.32842712,10.5 6,11.1715729 6,12 C6,12.8284271 5.32842712,13.5 4.5,13.5 L3,13.5 C2.17157288,13.5 1.5,12.8284271 1.5,12 C1.5,11.1715729 2.17157288,10.5 3,10.5 Z M12,1.5 C12.8284271,1.5 13.5,2.17157288 13.5,3 L13.5,4.5 C13.5,5.32842712 12.8284271,6 12,6 C11.1715729,6 10.5,5.32842712 10.5,4.5 L10.5,3 C10.5,2.17157288 11.1715729,1.5 12,1.5 Z M12,18 C12.8284271,18 13.5,18.6715729 13.5,19.5 L13.5,21 C13.5,21.8284271 12.8284271,22.5 12,22.5 C11.1715729,22.5 10.5,21.8284271 10.5,21 L10.5,19.5 C10.5,18.6715729 11.1715729,18 12,18 Z M4.81066017,4.81066017 C5.39644661,4.22487373 6.34619408,4.22487373 6.93198052,4.81066017 L7.99264069,5.87132034 C8.57842712,6.45710678 8.57842712,7.40685425 7.99264069,7.99264069 C7.40685425,8.57842712 6.45710678,8.57842712 5.87132034,7.99264069 L4.81066017,6.93198052 C4.22487373,6.34619408 4.22487373,5.39644661 4.81066017,4.81066017 Z M4.81066017,19.2426407 C4.22487373,18.6568542 4.22487373,17.7071068 4.81066017,17.1213203 L5.87132034,16.0606602 C6.45710678,15.4748737 7.40685425,15.4748737 7.99264069,16.0606602 C8.57842712,16.6464466 8.57842712,17.5961941 7.99264069,18.1819805 L6.93198052,19.2426407 C6.34619408,19.8284271 5.39644661,19.8284271 4.81066017,19.2426407 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                            </g>
                                        </svg>
                                        <svg id="icon-dark" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" class="svg-main-icon">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path d="M12.0700837,4.0003006 C11.3895108,5.17692613 11,6.54297551 11,8 C11,12.3948932 14.5439081,15.9620623 18.9299163,15.9996994 C17.5467214,18.3910707 14.9612535,20 12,20 C7.581722,20 4,16.418278 4,12 C4,7.581722 7.581722,4 12,4 C12.0233848,4 12.0467462,4.00010034 12.0700837,4.0003006 Z" fill="#000000" />
                                            </g>
                                        </svg>
                                    </a>
                                </li>

                                <?php if (estaAutenticado()): 
                                    // Aquí puedes obtener datos del usuario de la sesión
                                    $nombreUsuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
                                    $fotoUsuario = $_SESSION['foto_usuario'] ?? 'images/user.jpg';
                                ?>
                                
                                <!-- Dropdown de usuario -->
                                <li class="nav-item ps-3">
                                    <div class="dropdown header-profile2">
                                        <a class="nav-link" href="javascript:void(0);" role="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <div class="header-info2 d-flex align-items-center">
                                                <div class="header-media">
                                                    <img src="<?= e($fotoUsuario) ?>" alt="<?= e($nombreUsuario) ?>">
                                                </div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <div class="card border-0 mb-0">
                                                <div class="card-header py-2">
                                                    <div class="products">
                                                        <img src="<?= e($fotoUsuario) ?>" class="avatar avatar-md" alt="">
                                                        <div>
                                                            <h6><?= e($nombreUsuario) ?></h6>
                                                            <span><?= e($_SESSION['perfil_nombre'] ?? 'Usuario') ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body px-0 py-2">
                                                    <a href="perfil.php" class="dropdown-item ai-icon">
                                                        <span class="ms-2">Mi Perfil</span>
                                                    </a>
                                                    <a href="configuracion.php" class="dropdown-item ai-icon">
                                                        <span class="ms-2">Configuración</span>
                                                    </a>
                                                </div>
                                                <div class="card-footer px-0 py-2">
                                                    <a href="logout.php" class="dropdown-item ai-icon">
                                                        <span class="ms-2 text-danger">Cerrar Sesión</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--**********************************
            Header end
        ***********************************-->