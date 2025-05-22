<?php
// Ensure the path to bd.php is correct, assuming it's two levels up from current dir
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$servicio = null;
$id_servicio = null; // Will hold the ID from GET

// 1. Check for ID in GET request and validate
if (isset($_GET['id'])) {
    $id_servicio = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_servicio === false || $id_servicio <= 0) { // Check if filter_var failed or ID is not positive
        $error_message = "ID de servicio inválido o no proporcionado.";
        // No further database action if ID is invalid
    } else {
        // 2. Fetch the service from the database
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible. Verifica ../../bd.php";
            } else {
                $sql = "SELECT * FROM tbl_servicios WHERE id_servicio = :id_servicio";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
                $stmt->execute();
                $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$servicio) {
                    $error_message = "Servicio no encontrado con ID: " . htmlspecialchars($id_servicio) . ".";
                    // header("Location: index.php?mensaje=Servicio no encontrado"); // Optional: redirect
                    // exit;
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al buscar el servicio: " . $e->getMessage();
            // error_log("PDOException: " . $e->getMessage()); // Log error
        }
    }
} else {
    $error_message = "No se especificó ID de servicio para editar.";
    // header("Location: index.php?mensaje=ID no especificado"); // Optional: redirect
    // exit;
}

// 3. Handle POST request for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure $id_servicio (from GET) is valid and $servicio was fetched before processing POST
    if (!$id_servicio || !$servicio && !(isset($_POST['id_servicio']) && filter_var($_POST['id_servicio'], FILTER_VALIDATE_INT)) ) {
        $error_message = "No se puede procesar la actualización: el servicio original no se cargó correctamente o el ID es inválido.";
        // Use POSTed values for the form if there's an immediate error and original $servicio was not loaded
        $current_data_for_form = $_POST;
        $current_data_for_form['id_servicio'] = $id_servicio_post = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
    } else {
         // Retrieve and sanitize input values from POST
        $id_servicio_post = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT); // This is from hidden field
        $icono = filter_input(INPUT_POST, 'icono', FILTER_SANITIZE_SPECIAL_CHARS);
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
        $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);
        
        // Current data for form should reflect POST attempts, merge with original $servicio if it existed
        $current_data_for_form = array_merge($servicio ?? [], $_POST);


        // Basic validation for POSTed data
        if (empty($icono) || empty($titulo) || empty($descripcion)) {
            $error_message = "Por favor, completa todos los campos obligatorios (Icono, Título, Descripción).";
        } elseif ($id_servicio_post !== $id_servicio) { // Crucial check: ID from hidden form field must match ID from GET param in URL
            $error_message = "Error de consistencia de ID. El ID del formulario (".htmlspecialchars($id_servicio_post).") no coincide con el ID esperado (".htmlspecialchars($id_servicio)."). Inténtalo de nuevo.";
        } else {
            try {
                if (!isset($db) || $db === null) {
                    $error_message = "Error: La conexión a la base de datos no está disponible para la actualización.";
                } else {
                    $sql_update = "UPDATE tbl_servicios SET icono = :icono, titulo = :titulo, descripcion = :descripcion, orden = :orden WHERE id_servicio = :id_servicio_bind";
                    $stmt_update = $db->prepare($sql_update);
                    
                    $stmt_update->bindParam(':icono', $icono, PDO::PARAM_STR);
                    $stmt_update->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                    $stmt_update->bindParam(':orden', $orden, PDO::PARAM_INT);
                    $stmt_update->bindParam(':id_servicio_bind', $id_servicio, PDO::PARAM_INT); // Use $id_servicio from GET for binding

                    if ($stmt_update->execute()) {
                        header("Location: index.php?mensaje=Servicio '" . urlencode($titulo) . "' actualizado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al actualizar el servicio. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error: " . print_r($stmt_update->errorInfo(), true));
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error de base de datos al actualizar: " . $e->getMessage();
                // error_log("PDOException: " . $e->getMessage());
            }
        }
        // If there was an error during POST, $current_data_for_form holds the user's attempted changes.
        // $servicio might be outdated (from GET) or null, so use $current_data_for_form to populate the form
        $servicio = $current_data_for_form; // Ensure form shows latest attempt
    }
} else {
    // If not a POST request, $servicio (loaded from GET) is the data for the form
    // If $servicio is null (e.g. ID not found or invalid), the form might not show or show empty
    $current_data_for_form = $servicio;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Editar Servicio</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // Determine if the form should be displayed:
                    // - If $current_data_for_form is not null AND it has an 'id_servicio' (meaning it was loaded or is a valid POST attempt)
                    // - OR if $id_servicio is valid (from GET) and $servicio was successfully fetched
                    $display_form = ($current_data_for_form && isset($current_data_for_form['id_servicio'])) || ($id_servicio && $servicio);
                    if ($display_form):
                    ?>
                    <form action="editar.php?id=<?php echo htmlspecialchars($id_servicio); // ID in URL must be from original GET ?>" method="post" novalidate>
                        <!-- Hidden field for ID, crucial for the POST request. Value should be from the loaded service or current valid ID. -->
                        <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($current_data_for_form['id_servicio'] ?? $id_servicio); ?>">
                        
                        <div class="mb-3">
                            <label for="icono" class="form-label">Icono (Clase Font Awesome)</label>
                            <input type="text" class="form-control" id="icono" name="icono" placeholder="Ej: fas fa-cogs" value="<?php echo htmlspecialchars($current_data_for_form['icono'] ?? ''); ?>" required>
                            <small class="form-text text-muted">Ejemplo: <code>fas fa-briefcase</code>. Ver <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a>.</small>
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Servicio</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Desarrollo Web Avanzado" value="<?php echo htmlspecialchars($current_data_for_form['titulo'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($current_data_for_form['descripcion'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden de Visualización</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo htmlspecialchars($current_data_for_form['orden'] ?? '0'); ?>">
                            <small class="form-text text-muted">Menor número aparece primero.</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-save"></i> Guardar Cambios</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                    <?php elseif (empty($error_message)): // Only show if no specific error message is set and form is not displayed ?>
                        <div class="alert alert-info" role="alert">
                            Para editar un servicio, por favor selecciona uno desde el <a href="index.php">listado de servicios</a>. Si has llegado aquí desde un enlace de edición y ves este mensaje, el ID del servicio podría ser inválido o el servicio ya no existe.
                        </div>
                         <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                     <?php if ($error_message && !$display_form): // If there was an error and form is not displayed (e.g. service not found, ID invalid from start) ?>
                         <a href="index.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
