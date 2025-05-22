<?php
include '../../bd.php';
include '../../templates/header.php';

$error_message = '';
$success_message = '';

// Initial default configurations
$default_configs = [
    ['nombre_config' => 'titulo_sitio', 'valor_config' => 'My Awesome Agency', 'descripcion_config' => 'Título principal del sitio web'],
    ['nombre_config' => 'email_contacto', 'valor_config' => 'contact@example.com', 'descripcion_config' => 'Email de contacto principal'],
    ['nombre_config' => 'telefono_contacto', 'valor_config' => '+1 234 567 890', 'descripcion_config' => 'Teléfono de contacto principal'],
    ['nombre_config' => 'direccion_contacto', 'valor_config' => '123 Main Street, Anytown, USA', 'descripcion_config' => 'Dirección física de contacto'],
    ['nombre_config' => 'copyright_footer', 'valor_config' => 'Copyright © Your Website {ANIO}', 'descripcion_config' => 'Texto de Copyright en el pie de página ({ANIO} se reemplazará)'],
    ['nombre_config' => 'masthead_subheading', 'valor_config' => 'Welcome To Our Studio!', 'descripcion_config' => 'Subtítulo del Masthead (sección principal de bienvenida)'],
    ['nombre_config' => 'masthead_heading', 'valor_config' => 'It\'s Nice To Meet You', 'descripcion_config' => 'Título principal del Masthead'],
    ['nombre_config' => 'masthead_button_text', 'valor_config' => 'Tell Me More', 'descripcion_config' => 'Texto del botón en el Masthead'],
    ['nombre_config' => 'about_us_heading', 'valor_config' => 'About Us', 'descripcion_config' => 'Encabezado de la sección "Acerca de Nosotros"'],
    ['nombre_config' => 'contact_us_heading', 'valor_config' => 'Contact Us', 'descripcion_config' => 'Encabezado de la sección de Contacto'],
];

try {
    if (!isset($db) || $db === null) {
        $error_message = "Error: La conexión a la base de datos no está disponible.";
    } else {
        // Check if table is empty and insert defaults if needed
        $stmt_check_empty = $db->query("SELECT COUNT(*) as count FROM tbl_configuraciones");
        $result_check = $stmt_check_empty->fetch(PDO::FETCH_ASSOC);

        if ($result_check && $result_check['count'] == 0) {
            $sql_insert_default = "INSERT INTO tbl_configuraciones (nombre_config, valor_config, descripcion_config) VALUES (:nombre_config, :valor_config, :descripcion_config)";
            $stmt_insert_default = $db->prepare($sql_insert_default);
            foreach ($default_configs as $config) {
                $stmt_insert_default->bindParam(':nombre_config', $config['nombre_config']);
                $stmt_insert_default->bindParam(':valor_config', $config['valor_config']);
                $stmt_insert_default->bindParam(':descripcion_config', $config['descripcion_config']);
                $stmt_insert_default->execute();
            }
            $success_message = "Configuraciones iniciales cargadas. ";
        }

        // Handle POST request for updating configurations
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['configs'])) {
            $posted_configs = $_POST['configs'];
            $update_errors = 0;

            $sql_update = "UPDATE tbl_configuraciones SET valor_config = :valor_config WHERE nombre_config = :nombre_config";
            $stmt_update = $db->prepare($sql_update);

            foreach ($posted_configs as $config_item) {
                $nombre_config = $config_item['nombre_config'];
                // Sanitize valor_config - allow some HTML for flexibility, but be cautious
                // For very sensitive fields or if no HTML is needed, use FILTER_SANITIZE_SPECIAL_CHARS
                $valor_config = filter_var($config_item['valor_config'], FILTER_DEFAULT); 

                $stmt_update->bindParam(':valor_config', $valor_config, PDO::PARAM_STR);
                $stmt_update->bindParam(':nombre_config', $nombre_config, PDO::PARAM_STR);
                
                if (!$stmt_update->execute()) {
                    $update_errors++;
                    // error_log("Error updating config " . $nombre_config . ": " . print_r($stmt_update->errorInfo(), true));
                }
            }

            if ($update_errors == 0) {
                $success_message .= "Todas las configuraciones se guardaron con éxito.";
            } else {
                $error_message .= "Error al guardar " . $update_errors . " configuración(es). Revisa los logs para más detalles.";
            }
        }

        // Fetch all configurations to display
        $sql_select = "SELECT * FROM tbl_configuraciones ORDER BY id_config ASC";
        $stmt_select = $db->query($sql_select);
        $configuraciones = $stmt_select ? $stmt_select->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!$stmt_select) {
             $error_message .= ($error_message ? "<br>" : "") . "Error al obtener las configuraciones.";
        }
    }
} catch (PDOException $e) {
    $error_message .= ($error_message ? "<br>" : "") . "Error de base de datos: " . $e->getMessage();
    $configuraciones = []; // Ensure $configuraciones is an array even on DB error
    // error_log("PDOException in Configuraciones: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Gestión de Configuraciones del Sitio</h2>
            <p>Modifica los valores de configuración del sitio web. Estos valores se utilizan en varias partes de la página pública.</p>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Configuraciones Generales</h4>
        </div>
        <div class="card-body">
            <?php if (empty($configuraciones) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay configuraciones definidas. Esto no debería ocurrir si la conexión a la BD es correcta y las iniciales se cargaron.
                </div>
            <?php elseif (!empty($configuraciones)): ?>
                <form action="index.php" method="POST">
                    <?php foreach ($configuraciones as $index => $config): ?>
                        <div class="mb-3 row">
                            <label for="config_<?php echo htmlspecialchars($config['nombre_config']); ?>" class="col-sm-4 col-form-label">
                                <?php echo htmlspecialchars($config['descripcion_config']); ?>
                                <small class="d-block text-muted">(<code><?php echo htmlspecialchars($config['nombre_config']); ?></code>)</small>
                            </label>
                            <div class="col-sm-8">
                                <input type="hidden" name="configs[<?php echo $index; ?>][nombre_config]" value="<?php echo htmlspecialchars($config['nombre_config']); ?>">
                                <?php
                                $valor = htmlspecialchars($config['valor_config'] ?? '');
                                $rows = substr_count($valor, "\n") + 1; // Basic row count for textarea
                                if (strlen($valor) > 100 || $rows > 1) { // Use textarea for longer values or multiline
                                ?>
                                <textarea class="form-control" id="config_<?php echo htmlspecialchars($config['nombre_config']); ?>" name="configs[<?php echo $index; ?>][valor_config]" rows="<?php echo max(3, min(8, $rows)); ?>"><?php echo $valor; ?></textarea>
                                <?php } else { ?>
                                <input type="text" class="form-control" id="config_<?php echo htmlspecialchars($config['nombre_config']); ?>" name="configs[<?php echo $index; ?>][valor_config]" value="<?php echo $valor; ?>">
                                <?php } ?>
                            </div>
                        </div>
                        <?php if ($index < count($configuraciones) - 1): ?>
                            <hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Guardar Todas las Configuraciones</button>
                    </div>
                </form>
            <?php elseif (empty($error_message)): ?>
                 <div class="alert alert-warning" role="alert">
                    No hay configuraciones para mostrar.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
