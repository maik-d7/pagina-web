<?php
// Attempt to include the database connection file
// Assuming index.php is in the root, and bd.php is in admin/
$db_connection_error = '';
$db_path = __DIR__ . '/admin/bd.php'; // Use absolute path for reliability

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    $db_connection_error = "Error: El archivo de conexión a la base de datos (admin/bd.php) no se encontró.";
    $db = null; 
}

// Initialize variables
$config_titulo_sitio = "Agency - Dynamic Title";
$config_masthead_subheading = "Welcome To Our Studio!";
$config_masthead_heading = "It's Nice To Meet You";
$config_services_subheading = "Lorem ipsum dolor sit amet consectetur.";
$config_portfolio_heading = "Portafolio";
$config_portfolio_subheading = "Una muestra de nuestros proyectos recientes.";
$config_about_heading = "Nosotros"; 
$config_about_subheading = "Conoce nuestra historia y proceso."; 
$config_team_heading = "Nuestro Increíble Equipo"; // Default Team heading
$config_team_subheading = "Conoce a las personas que lo hacen posible."; // Default Team subheading
$config_team_description_text = "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut eaque, laboriosam veritatis, quos non quis ad perspiciatis, totam corporis ea, alias ut unde."; // Default team description


$servicios = [];
$portafolio_items = [];
$timeline_entries = []; 
$team_members = []; // Initialize team members array
$fetch_error = '';

if ($db) { 
    try {
        // Fetch configurations
        $config_keys = [
            'titulo_sitio', 'masthead_subheading', 'masthead_heading', 
            'services_subheading', 'portfolio_heading', 'portfolio_subheading',
            'about_heading', 'about_subheading',
            'team_heading', 'team_subheading', 'team_description_text' // Added Team section config keys
        ];
        $placeholders = implode(',', array_fill(0, count($config_keys), '?'));
        $sql_configs = "SELECT nombre_config, valor_config FROM tbl_configuraciones WHERE nombre_config IN ($placeholders)";
        $stmt_configs = $db->prepare($sql_configs);
        $stmt_configs->execute($config_keys);
        
        if ($stmt_configs) {
            $configs_db = $stmt_configs->fetchAll(PDO::FETCH_KEY_PAIR);
            if (isset($configs_db['titulo_sitio'])) $config_titulo_sitio = $configs_db['titulo_sitio'];
            if (isset($configs_db['masthead_subheading'])) $config_masthead_subheading = $configs_db['masthead_subheading'];
            if (isset($configs_db['masthead_heading'])) $config_masthead_heading = $configs_db['masthead_heading'];
            if (isset($configs_db['services_subheading'])) $config_services_subheading = $configs_db['services_subheading'];
            if (isset($configs_db['portfolio_heading'])) $config_portfolio_heading = $configs_db['portfolio_heading'];
            if (isset($configs_db['portfolio_subheading'])) $config_portfolio_subheading = $configs_db['portfolio_subheading'];
            if (isset($configs_db['about_heading'])) $config_about_heading = $configs_db['about_heading'];
            if (isset($configs_db['about_subheading'])) $config_about_subheading = $configs_db['about_subheading'];
            if (isset($configs_db['team_heading'])) $config_team_heading = $configs_db['team_heading'];
            if (isset($configs_db['team_subheading'])) $config_team_subheading = $configs_db['team_subheading'];
            if (isset($configs_db['team_description_text'])) $config_team_description_text = $configs_db['team_description_text'];
        } else {
            $fetch_error .= "Error al obtener configuraciones. ";
        }

        // Fetch services
        $stmt_services = $db->query("SELECT icono, titulo, descripcion FROM tbl_servicios ORDER BY orden ASC");
        if ($stmt_services) {
            $servicios = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fetch_error .= "Error al obtener servicios. ";
        }

        // Fetch portfolio items
        $stmt_portfolio = $db->query("SELECT id_portafolio, titulo, subtitulo, imagen, descripcion_modal, cliente_modal, categoria_modal FROM tbl_portafolio ORDER BY orden ASC");
        if ($stmt_portfolio) {
            $portafolio_items = $stmt_portfolio->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fetch_error .= "Error al obtener elementos del portafolio. ";
        }

        // Fetch timeline entries
        $stmt_timeline = $db->query("SELECT fecha_evento, titulo, subtitulo, imagen, descripcion, alineacion_timeline FROM tbl_entradas WHERE tipo_entrada = 'timeline' ORDER BY orden ASC, id_entrada ASC");
        if ($stmt_timeline) {
            $timeline_entries = $stmt_timeline->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fetch_error .= "Error al obtener entradas del timeline. ";
        }

        // Fetch team members
        $stmt_team = $db->query("SELECT nombre, puesto, imagen, twitter_url, facebook_url, linkedin_url FROM tbl_equipo ORDER BY orden ASC");
        if ($stmt_team) {
            $team_members = $stmt_team->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $fetch_error .= "Error al obtener miembros del equipo. ";
        }

    } catch (PDOException $e) {
        $fetch_error = "Error de base de datos: " . $e->getMessage();
        $config_titulo_sitio = "Error - Agency"; 
    }
} elseif(empty($db_connection_error)) {
    $fetch_error = "Error: La conexión a la base de datos no fue establecida correctamente por admin/bd.php.";
} else {
    $fetch_error = $db_connection_error;
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Agencia de marketing y diseño web" />
        <meta name="author" content="<?php echo htmlspecialchars($config_titulo_sitio); ?>" />
        <title><?php echo htmlspecialchars($config_titulo_sitio); ?></title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top"><img src="assets/img/navbar-logo.svg" alt="<?php echo htmlspecialchars($config_titulo_sitio); ?> Logo" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="#services">Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#portfolio">Portafolio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#about">Nosotros</a></li>
                        <li class="nav-item"><a class="nav-link" href="#team">Equipo</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading"><?php echo htmlspecialchars($config_masthead_subheading); ?></div>
                <div class="masthead-heading text-uppercase"><?php echo htmlspecialchars($config_masthead_heading); ?></div>
                <a class="btn btn-primary btn-xl text-uppercase" href="#services">Conoce Más</a>
            </div>
        </header>
        <section class="page-section" id="services">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Servicios</h2>
                    <h3 class="section-subheading text-muted"><?php echo htmlspecialchars($config_services_subheading); ?></h3>
                </div>
                <?php if (!empty($fetch_error) && empty($servicios)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Error al cargar los servicios: <?php echo htmlspecialchars($fetch_error); ?> <br>
                        Por favor, inténtalo de nuevo más tarde o contacta al administrador si el problema persiste.
                    </div>
                <?php elseif (empty($servicios)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nuestros servicios se listarán aquí próximamente. ¡Vuelve pronto!
                    </div>
                <?php else: ?>
                    <div class="row text-center">
                        <?php foreach ($servicios as $servicio): ?>
                            <div class="col-md-4 mb-4">
                                <span class="fa-stack fa-4x">
                                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                                    <i class="<?php echo htmlspecialchars($servicio['icono'] ?? 'fas fa-question'); ?> fa-stack-1x fa-inverse"></i>
                                </span>
                                <h4 class="my-3"><?php echo htmlspecialchars($servicio['titulo'] ?? 'Servicio Destacado'); ?></h4>
                                <p class="text-muted px-2"><?php echo nl2br(htmlspecialchars($servicio['descripcion'] ?? 'Descripción no disponible.')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section class="page-section bg-light" id="portfolio">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase"><?php echo htmlspecialchars($config_portfolio_heading); ?></h2>
                    <h3 class="section-subheading text-muted"><?php echo htmlspecialchars($config_portfolio_subheading); ?></h3>
                </div>
                <?php if (!empty($fetch_error) && empty($portafolio_items)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Error al cargar el portafolio: <?php echo htmlspecialchars($fetch_error); ?> <br>
                        Por favor, inténtalo de nuevo más tarde o contacta al administrador si el problema persiste.
                    </div>
                <?php elseif (empty($portafolio_items)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nuestro portafolio se mostrará aquí próximamente. ¡Vuelve pronto!
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($portafolio_items as $item): ?>
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="portfolio-item">
                                <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal<?php echo htmlspecialchars($item['id_portafolio']); ?>">
                                    <div class="portfolio-hover">
                                        <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                    </div>
                                    <img class="img-fluid" src="<?php echo htmlspecialchars($item['imagen'] ?? 'assets/img/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['titulo'] ?? 'Proyecto'); ?>" />
                                </a>
                                <div class="portfolio-caption">
                                    <div class="portfolio-caption-heading"><?php echo htmlspecialchars($item['titulo'] ?? 'Título del Proyecto'); ?></div>
                                    <div class="portfolio-caption-subheading text-muted"><?php echo htmlspecialchars($item['subtitulo'] ?? 'Subtítulo'); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section class="page-section" id="about">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase"><?php echo htmlspecialchars($config_about_heading); ?></h2>
                    <h3 class="section-subheading text-muted"><?php echo htmlspecialchars($config_about_subheading); ?></h3>
                </div>
                <?php if (!empty($fetch_error) && empty($timeline_entries)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Error al cargar nuestra historia: <?php echo htmlspecialchars($fetch_error); ?> <br>
                        Por favor, inténtalo de nuevo más tarde o contacta al administrador si el problema persiste.
                    </div>
                <?php elseif (empty($timeline_entries)): ?>
                     <div class="alert alert-info text-center" role="alert">
                        Nuestra historia se compartirá aquí próximamente. ¡Vuelve pronto!
                    </div>
                <?php else: ?>
                    <ul class="timeline">
                        <?php foreach ($timeline_entries as $index => $entrada): ?>
                            <li class="<?php echo !empty(htmlspecialchars($entrada['alineacion_timeline'])) ? htmlspecialchars($entrada['alineacion_timeline']) : ''; ?>">
                                <div class="timeline-image">
                                    <img class="rounded-circle img-fluid" src="<?php echo htmlspecialchars($entrada['imagen'] ?? 'assets/img/about/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($entrada['titulo'] ?? 'Imagen del evento'); ?>" />
                                </div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4><?php echo htmlspecialchars($entrada['fecha_evento'] ?? 'Fecha'); ?></h4>
                                        <h4 class="subheading"><?php echo htmlspecialchars($entrada['titulo'] ?? 'Título del Evento'); ?></h4>
                                    </div>
                                    <div class="timeline-body"><p class="text-muted"><?php echo nl2br(htmlspecialchars($entrada['descripcion'] ?? 'Descripción no disponible.')); ?></p></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li class="timeline-inverted"> 
                            <div class="timeline-image">
                                <h4>
                                    ¡Sé Parte
                                    <br />
                                    De Nuestra
                                    <br />
                                    Historia!
                                </h4>
                            </div>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
        <!-- Team-->
        <section class="page-section bg-light" id="team">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase"><?php echo htmlspecialchars($config_team_heading); ?></h2>
                    <h3 class="section-subheading text-muted"><?php echo htmlspecialchars($config_team_subheading); ?></h3>
                </div>
                <?php if (!empty($fetch_error) && empty($team_members)): ?>
                     <div class="alert alert-warning text-center" role="alert">
                        Error al cargar nuestro equipo: <?php echo htmlspecialchars($fetch_error); ?> <br>
                        Por favor, inténtalo de nuevo más tarde o contacta al administrador si el problema persiste.
                    </div>
                <?php elseif (empty($team_members)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        Nuestro increíble equipo se presentará aquí próximamente.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($team_members as $miembro): ?>
                        <div class="col-lg-4">
                            <div class="team-member">
                                <img class="mx-auto rounded-circle" src="<?php echo htmlspecialchars($miembro['imagen'] ?? 'assets/img/team/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($miembro['nombre'] ?? 'Miembro del equipo'); ?>" />
                                <h4><?php echo htmlspecialchars($miembro['nombre'] ?? 'Nombre no disponible'); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($miembro['puesto'] ?? 'Puesto no disponible'); ?></p>
                                <?php if (!empty($miembro['twitter_url'])): ?>
                                    <a class="btn btn-dark btn-social mx-2" href="<?php echo htmlspecialchars($miembro['twitter_url']); ?>" aria-label="<?php echo htmlspecialchars($miembro['nombre']); ?> Twitter Profile"><i class="fab fa-twitter"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($miembro['facebook_url'])): ?>
                                    <a class="btn btn-dark btn-social mx-2" href="<?php echo htmlspecialchars($miembro['facebook_url']); ?>" aria-label="<?php echo htmlspecialchars($miembro['nombre']); ?> Facebook Profile"><i class="fab fa-facebook-f"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($miembro['linkedin_url'])): ?>
                                    <a class="btn btn-dark btn-social mx-2" href="<?php echo htmlspecialchars($miembro['linkedin_url']); ?>" aria-label="<?php echo htmlspecialchars($miembro['nombre']); ?> LinkedIn Profile"><i class="fab fa-linkedin-in"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-lg-8 mx-auto text-center"><p class="large text-muted"><?php echo nl2br(htmlspecialchars($config_team_description_text)); ?></p></div>
                </div>
            </div>
        </section>
        <!-- Clients, Contact, Footer, Modals sections remain unchanged -->
        <div class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/microsoft.svg" alt="..." aria-label="Microsoft Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/google.svg" alt="..." aria-label="Google Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/facebook.svg" alt="..." aria-label="Facebook Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/ibm.svg" alt="..." aria-label="IBM Logo" /></a>
                    </div>
                </div>
            </div>
        </div>
        <section class="page-section" id="contact">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Contáctanos</h2>
                    <h3 class="section-subheading text-muted">Envíanos tus preguntas o comentarios.</h3>
                </div>
                <form id="contactForm" data-sb-form-api-token="API_TOKEN_REEMPLAZAR">
                    <div class="row align-items-stretch mb-5">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input class="form-control" id="name" type="text" placeholder="Tu Nombre *" data-sb-validations="required" />
                                <div class="invalid-feedback" data-sb-feedback="name:required">El nombre es obligatorio.</div>
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="email" type="email" placeholder="Tu Email *" data-sb-validations="required,email" />
                                <div class="invalid-feedback" data-sb-feedback="email:required">El email es obligatorio.</div>
                                <div class="invalid-feedback" data-sb-feedback="email:email">El email no es válido.</div>
                            </div>
                            <div class="form-group mb-md-0">
                                <input class="form-control" id="phone" type="tel" placeholder="Tu Teléfono *" data-sb-validations="required" />
                                <div class="invalid-feedback" data-sb-feedback="phone:required">El teléfono es obligatorio.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-textarea mb-md-0">
                                <textarea class="form-control" id="message" placeholder="Tu Mensaje *" data-sb-validations="required"></textarea>
                                <div class="invalid-feedback" data-sb-feedback="message:required">El mensaje es obligatorio.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-none" id="submitSuccessMessage">
                        <div class="text-center text-white mb-3">
                            <div class="fw-bolder">¡Mensaje enviado con éxito!</div>
                            Para activar este formulario, regístrate en
                            <br />
                            <a href="https://startbootstrap.com/solution/contact-forms">https://startbootstrap.com/solution/contact-forms</a>
                        </div>
                    </div>
                    <div class="d-none" id="submitErrorMessage"><div class="text-center text-danger mb-3">Error al enviar el mensaje.</div></div>
                    <div class="text-center"><button class="btn btn-primary btn-xl text-uppercase" id="submitButton" type="submit">Enviar Mensaje</button></div>
                </form>
            </div>
        </section>
        <footer class="footer py-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 text-lg-start">Copyright &copy; <?php echo htmlspecialchars($config_titulo_sitio); ?> <?php echo date("Y"); ?></div>
                    <div class="col-lg-4 my-3 my-lg-0">
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="link-dark text-decoration-none me-3" href="#!">Política de Privacidad</a>
                        <a class="link-dark text-decoration-none" href="#!">Términos de Uso</a>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Portfolio Modals-->
        <?php if (!empty($portafolio_items)): ?>
            <?php foreach ($portafolio_items as $item): ?>
            <div class="portfolio-modal modal fade" id="portfolioModal<?php echo htmlspecialchars($item['id_portafolio']); ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="close-modal" data-bs-dismiss="modal"><img src="assets/img/close-icon.svg" alt="Close modal" /></div>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <div class="modal-body">
                                        <h2 class="text-uppercase"><?php echo htmlspecialchars($item['titulo'] ?? 'Título del Proyecto'); ?></h2>
                                        <p class="item-intro text-muted"><?php echo htmlspecialchars($item['subtitulo'] ?? 'Subtítulo'); ?></p>
                                        <img class="img-fluid d-block mx-auto mb-3" src="<?php echo htmlspecialchars($item['imagen'] ?? 'assets/img/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['titulo'] ?? 'Imagen del proyecto'); ?>" />
                                        <p><?php echo nl2br(htmlspecialchars($item['descripcion_modal'] ?? 'Descripción no disponible.')); ?></p>
                                        <ul class="list-inline">
                                            <li>
                                                <strong>Cliente:</strong>
                                                <?php echo htmlspecialchars($item['cliente_modal'] ?? 'No especificado'); ?>
                                            </li>
                                            <li>
                                                <strong>Categoría:</strong>
                                                <?php echo htmlspecialchars($item['categoria_modal'] ?? 'No especificada'); ?>
                                            </li>
                                        </ul>
                                        <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                            <i class="fas fa-xmark me-1"></i>
                                            Cerrar Proyecto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
