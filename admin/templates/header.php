<?php
session_start();

// Determine $url_base first as it's needed for potential redirect
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// SCRIPT_NAME for /admin/templates/header.php is /admin/templates/header.php
// dirname($_SERVER['SCRIPT_NAME']) is /admin/templates
// dirname(dirname($_SERVER['SCRIPT_NAME'])) is /admin
$admin_dynamic_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
// Ensure it ends with a slash for constructing base URL
if ($admin_dynamic_path === DIRECTORY_SEPARATOR) { // Check if it's the root directory
    $admin_dynamic_path = '/';
} elseif (substr($admin_dynamic_path, -1) !== '/') {
    $admin_dynamic_path .= '/';
}
$url_base = $protocol . $host . $admin_dynamic_path; // Example: http://localhost/admin/

// Session check: If not logged in and not on login.php, redirect to login.php
// SCRIPT_FILENAME is the absolute path, e.g., /var/www/html/admin/secciones/index.php
// We need to check if the current script is login.php.
// basename($_SERVER['SCRIPT_NAME']) gives the current script's filename.
$current_script_filename = basename($_SERVER['SCRIPT_NAME']);

if (!isset($_SESSION['user_id']) && $current_script_filename != 'login.php') {
    // Construct the login URL. $url_base should point to the admin root.
    // login.php is expected to be directly under admin root.
    header("Location: " . $url_base . "login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es"> <!-- Changed lang to Spanish -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Panel de Administración</title> <!-- Changed title -->
    <style>
        body { padding-top: 4.5rem; /* Adjust if navbar height changes */ }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top"> <!-- Added navbar-expand-lg and fixed-top -->
            <div class="container-fluid"> <!-- Changed to container-fluid for better responsiveness -->
                <a class="navbar-brand" href="<?php echo $url_base; ?>index.php"> <!-- Link to admin dashboard -->
                    <i class="fas fa-cog"></i> Admin Panel <!-- Icon and text -->
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0"> <!-- Changed structure for Bootstrap 5 nav -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "servicios")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/servicios/">Servicios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "portafolio")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/portafolio/">Portafolio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "entradas")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/entradas/">Entradas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "equipo")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/equipo/">Equipo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "configuraciones")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/configuraciones/">Configuraciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['SCRIPT_NAME'], "usuarios")) ? 'active' : '';?>" href="<?php echo $url_base;?>secciones/usuarios/">Usuarios</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto"> <!-- Right aligned items -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                                <li><a class="dropdown-item" href="<?php echo $url_base; ?>index.php"><i class="fas fa-tachometer-alt fa-fw"></i> Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $url_base; ?>cerrar.php"><i class="fas fa-sign-out-alt fa-fw"></i> Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4 mb-4"> <!-- Added margin top and bottom to main content -->