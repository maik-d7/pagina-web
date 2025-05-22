<?php 
// The header.php (which defines $url_base) should be in ./templates/ from admin/index.php
include './templates/header.php'; 
// Assuming bd.php is in the same directory as index.php (admin root)
include './bd.php'; 

// Define sections with their names, links, and suggested Font Awesome icons
$admin_sections = [
    ['name' => 'Servicios', 'link' => 'secciones/servicios/', 'icon' => 'fas fa-concierge-bell', 'color' => 'primary', 'description' => 'Gestiona los servicios ofrecidos.'],
    ['name' => 'Portafolio', 'link' => 'secciones/portafolio/', 'icon' => 'fas fa-briefcase', 'color' => 'success', 'description' => 'Administra los proyectos del portafolio.'],
    ['name' => 'Entradas', 'link' => 'secciones/entradas/', 'icon' => 'fas fa-newspaper', 'color' => 'info', 'description' => 'Crea y edita entradas del timeline o blog.'],
    ['name' => 'Equipo', 'link' => 'secciones/equipo/', 'icon' => 'fas fa-users', 'color' => 'warning', 'description' => 'Administra los miembros del equipo.'],
    ['name' => 'Configuraciones', 'link' => 'secciones/configuraciones/', 'icon' => 'fas fa-cogs', 'color' => 'danger', 'description' => 'Ajusta las configuraciones globales del sitio.'],
    ['name' => 'Usuarios', 'link' => 'secciones/usuarios/', 'icon' => 'fas fa-user-shield', 'color' => 'dark', 'description' => 'Gestiona los usuarios del panel de administración.']
];

// Attempt to get the username from session if login system is implemented and sets it
$username = "Administrador"; // Default username
// session_start() should be called in header.php if sessions are used
if (isset($_SESSION['username'])) { 
    $username = htmlspecialchars($_SESSION['username']);
}

?>

<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="p-4 bg-white rounded-3 shadow-sm border-start border-primary border-5">
                <h1 class="display-6 fw-bold">Bienvenido al Panel de Administración</h1>
                <p class="fs-5">
                    Hola, <strong><?php echo $username; ?></strong>. Desde aquí puedes gestionar el contenido y las configuraciones de tu sitio web.
                </p>
                <p class="text-muted">Utiliza los siguientes enlaces para navegar a las diferentes secciones.</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($admin_sections as $section): ?>
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-<?php echo $section['color']; ?>_custom">
                    <div class="card-body d-flex flex-column text-center">
                        <div class="mb-3">
                             <i class="<?php echo $section['icon']; ?> fa-3x text-<?php echo $section['color']; ?>"></i>
                        </div>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($section['name']); ?></h5>
                        <p class="card-text small text-muted flex-grow-1">
                            <?php echo htmlspecialchars($section['description']); ?>
                        </p>
                        <a href="<?php echo $url_base . htmlspecialchars($section['link']); ?>" class="btn btn-outline-<?php echo $section['color']; ?> mt-auto">
                            Gestionar <?php echo htmlspecialchars(explode(' (', $section['name'])[0]); ?> <i class="fas fa-arrow-alt-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .custom-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-left-width: 4px; /* Default border width */
    }
    .custom-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .border-primary_custom { border-left-color: var(--bs-primary) !important; }
    .border-success_custom { border-left-color: var(--bs-success) !important; }
    .border-info_custom { border-left-color: var(--bs-info) !important; }
    .border-warning_custom { border-left-color: var(--bs-warning) !important; }
    .border-danger_custom { border-left-color: var(--bs-danger) !important; }
    .border-dark_custom { border-left-color: var(--bs-dark) !important; } /* Changed from secondary for better contrast */
</style>

<?php include './templates/footer.php'; ?>
