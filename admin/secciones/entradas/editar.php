<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$entrada_item = null;
$id_entrada = null;

// 1. Check for ID in GET request and validate
if (isset($_GET['id'])) {
    $id_entrada = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_entrada === false || $id_entrada <= 0) {
        $error_message = "ID de entrada inválido o no proporcionado.";
    } else {
        // 2. Fetch the entrada item from the database
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "SELECT * FROM tbl_entradas WHERE id_entrada = :id_entrada";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_entrada', $id_entrada, PDO::PARAM_INT);
                $stmt->execute();
                $entrada_item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$entrada_item) {
                    $error_message = "Entrada no encontrada con ID: " . htmlspecialchars($id_entrada) . ".";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al buscar la entrada: " . $e->getMessage();
        }
    }
} else {
    $error_message = "No se especificó ID de entrada para editar.";
}

// 3. Handle POST request for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$id_entrada || !$entrada_item && !(isset($_POST['id_entrada']) && filter_var($_POST['id_entrada'], FILTER_VALIDATE_INT))) {
        $error_message = "No se puede procesar la actualización: la entrada original no se cargó o el ID es inválido.";
        $current_data_for_form = $_POST; 
        $current_data_for_form['id_entrada'] = filter_input(INPUT_POST, 'id_entrada', FILTER_VALIDATE_INT);
    } else {
        $id_entrada_post = filter_input(INPUT_POST, 'id_entrada', FILTER_VALIDATE_INT);
        $fecha_evento = filter_input(INPUT_POST, 'fecha_evento', FILTER_SANITIZE_SPECIAL_CHARS);
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
        $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL);
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
        $alineacion_timeline_input = filter_input(INPUT_POST, 'alineacion_timeline', FILTER_SANITIZE_SPECIAL_CHARS);
        $tipo_entrada = filter_input(INPUT_POST, 'tipo_entrada', FILTER_SANITIZE_SPECIAL_CHARS, ["options" => ["default" => "timeline"]]);
        $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

        // Map alineacion_timeline from form value to DB value
        $alineacion_timeline_db_val = ($alineacion_timeline_input === 'Invertida') ? 'timeline-inverted' : '';
        
        $current_data_for_form = array_merge($entrada_item ?? [], $_POST);
        // Ensure the select value is correctly represented for sticky form
        $current_data_for_form['alineacion_timeline_select'] = $alineacion_timeline_input;


        if (empty($fecha_evento) || empty($titulo) || empty($imagen) || empty($descripcion)) {
            $error_message = "Por favor, completa los campos obligatorios (Fecha, Título, Imagen, Descripción).";
        } elseif ($id_entrada_post !== $id_entrada) {
            $error_message = "Error de consistencia de ID. El ID del formulario (".htmlspecialchars($id_entrada_post).") no coincide con el ID esperado (".htmlspecialchars($id_entrada).").";
        } else {
            try {
                if (!isset($db) || $db === null) {
                    $error_message = "Error: La conexión a la base de datos no está disponible para la actualización.";
                } else {
                    $sql_update = "UPDATE tbl_entradas SET 
                                    fecha_evento = :fecha_evento, titulo = :titulo, subtitulo = :subtitulo, 
                                    imagen = :imagen, descripcion = :descripcion, 
                                    alineacion_timeline = :alineacion_timeline, tipo_entrada = :tipo_entrada, orden = :orden 
                                 WHERE id_entrada = :id_entrada_bind";
                    $stmt_update = $db->prepare($sql_update);
                    
                    $stmt_update->bindParam(':fecha_evento', $fecha_evento, PDO::PARAM_STR);
                    $stmt_update->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':subtitulo', $subtitulo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                    $stmt_update->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                    $stmt_update->bindParam(':alineacion_timeline', $alineacion_timeline_db_val, PDO::PARAM_STR);
                    $stmt_update->bindParam(':tipo_entrada', $tipo_entrada, PDO::PARAM_STR);
                    $stmt_update->bindParam(':orden', $orden, PDO::PARAM_INT);
                    $stmt_update->bindParam(':id_entrada_bind', $id_entrada, PDO::PARAM_INT);

                    if ($stmt_update->execute()) {
                        header("Location: index.php?mensaje=Entrada '" . urlencode($titulo) . "' actualizada con éxito");
                        exit;
                    } else {
                        $error_message = "Error al actualizar la entrada. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error: " . print_r($stmt_update->errorInfo(), true));
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error de base de datos al actualizar: " . $e->getMessage();
                // error_log("PDOException: " . $e->getMessage());
            }
        }
        $entrada_item = $current_data_for_form; // Reflect changes in form
    }
} else {
    // Initial load for the form, map DB value to select value
    if ($entrada_item) {
        $entrada_item['alineacion_timeline_select'] = ($entrada_item['alineacion_timeline'] === 'timeline-inverted') ? 'Invertida' : 'Normal';
    }
    $current_data_for_form = $entrada_item;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Editar Entrada (Timeline/Blog)</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $display_form = ($current_data_for_form && isset($current_data_for_form['id_entrada'])) || ($id_entrada && $entrada_item);
                    if ($display_form): 
                    ?>
                    <form action="editar.php?id=<?php echo htmlspecialchars($id_entrada); ?>" method="post" novalidate>
                        <input type="hidden" name="id_entrada" value="<?php echo htmlspecialchars($current_data_for_form['id_entrada'] ?? $id_entrada); ?>">
                        
                        <div class="mb-3">
                            <label for="fecha_evento" class="form-label">Fecha del Evento/Entrada</label>
                            <input type="text" class="form-control" id="fecha_evento" name="fecha_evento" placeholder="Ej: 2009-2011 o Marzo 2024" value="<?php echo htmlspecialchars($current_data_for_form['fecha_evento'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($current_data_for_form['titulo'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitulo" class="form-label">Subtítulo (Opcional)</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="<?php echo htmlspecialchars($current_data_for_form['subtitulo'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/timeline/1.jpg" value="<?php echo htmlspecialchars($current_data_for_form['imagen'] ?? ''); ?>" required>
                            <small class="form-text text-muted">Ruta relativa. Ej: <code>assets/img/about/1.jpg</code></small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($current_data_for_form['descripcion'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="alineacion_timeline" class="form-label">Alineación en Timeline</label>
                            <select class="form-select" id="alineacion_timeline" name="alineacion_timeline">
                                <option value="Normal" <?php echo (isset($current_data_for_form['alineacion_timeline_select']) && $current_data_for_form['alineacion_timeline_select'] == 'Normal') ? 'selected' : ''; ?>>Normal (Imagen a la izquierda)</option>
                                <option value="Invertida" <?php echo (isset($current_data_for_form['alineacion_timeline_select']) && $current_data_for_form['alineacion_timeline_select'] == 'Invertida') ? 'selected' : ''; ?>>Invertida (Imagen a la derecha)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_entrada" class="form-label">Tipo de Entrada</label>
                            <input type="text" class="form-control" id="tipo_entrada" name="tipo_entrada" value="<?php echo htmlspecialchars($current_data_for_form['tipo_entrada'] ?? 'timeline'); ?>">
                            <small class="form-text text-muted">Generalmente 'timeline' o 'blog'.</small>
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo htmlspecialchars($current_data_for_form['orden'] ?? '0'); ?>">
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-save"></i> Guardar Cambios</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                    <?php elseif (empty($error_message)): ?>
                        <div class="alert alert-info" role="alert">
                            Selecciona una entrada desde el <a href="index.php">listado de entradas</a> para editar.
                        </div>
                        <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                    <?php if ($error_message && !$display_form): ?>
                         <a href="index.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
