<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$portafolio_item = null;
$id_portafolio = null;

// 1. Check for ID in GET request and validate
if (isset($_GET['id'])) {
    $id_portafolio = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_portafolio === false || $id_portafolio <= 0) {
        $error_message = "ID de portafolio inválido o no proporcionado.";
    } else {
        // 2. Fetch the portfolio item from the database
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "SELECT * FROM tbl_portafolio WHERE id_portafolio = :id_portafolio";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_portafolio', $id_portafolio, PDO::PARAM_INT);
                $stmt->execute();
                $portafolio_item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$portafolio_item) {
                    $error_message = "Elemento del portafolio no encontrado con ID: " . htmlspecialchars($id_portafolio) . ".";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al buscar el elemento: " . $e->getMessage();
        }
    }
} else {
    $error_message = "No se especificó ID de portafolio para editar.";
}

// 3. Handle POST request for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$id_portafolio || !$portafolio_item && !(isset($_POST['id_portafolio']) && filter_var($_POST['id_portafolio'], FILTER_VALIDATE_INT))) {
        $error_message = "No se puede procesar la actualización: el elemento original no se cargó o el ID es inválido.";
        $current_data_for_form = $_POST; // Use POSTed values if original load failed
        $current_data_for_form['id_portafolio'] = filter_input(INPUT_POST, 'id_portafolio', FILTER_VALIDATE_INT);
    } else {
        $id_portafolio_post = filter_input(INPUT_POST, 'id_portafolio', FILTER_VALIDATE_INT);
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL);
        $descripcion_modal = filter_input(INPUT_POST, 'descripcion_modal', FILTER_SANITIZE_SPECIAL_CHARS);
        $cliente_modal = filter_input(INPUT_POST, 'cliente_modal', FILTER_SANITIZE_SPECIAL_CHARS);
        $categoria_modal = filter_input(INPUT_POST, 'categoria_modal', FILTER_SANITIZE_SPECIAL_CHARS);
        $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

        $current_data_for_form = array_merge($portafolio_item ?? [], $_POST);

        if (empty($titulo) || empty($subtitulo) || empty($imagen) || empty($descripcion_modal) || empty($cliente_modal) || empty($categoria_modal)) {
            $error_message = "Por favor, completa todos los campos obligatorios.";
        } elseif ($id_portafolio_post !== $id_portafolio) {
            $error_message = "Error de consistencia de ID. El ID del formulario (".htmlspecialchars($id_portafolio_post).") no coincide con el ID esperado (".htmlspecialchars($id_portafolio).").";
        } else {
            try {
                if (!isset($db) || $db === null) {
                    $error_message = "Error: La conexión a la base de datos no está disponible para la actualización.";
                } else {
                    $sql_update = "UPDATE tbl_portafolio SET 
                                    titulo = :titulo, subtitulo = :subtitulo, imagen = :imagen, 
                                    descripcion_modal = :descripcion_modal, cliente_modal = :cliente_modal, 
                                    categoria_modal = :categoria_modal, orden = :orden 
                                 WHERE id_portafolio = :id_portafolio_bind";
                    $stmt_update = $db->prepare($sql_update);
                    
                    $stmt_update->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':subtitulo', $subtitulo, PDO::PARAM_STR);
                    $stmt_update->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                    $stmt_update->bindParam(':descripcion_modal', $descripcion_modal, PDO::PARAM_STR);
                    $stmt_update->bindParam(':cliente_modal', $cliente_modal, PDO::PARAM_STR);
                    $stmt_update->bindParam(':categoria_modal', $categoria_modal, PDO::PARAM_STR);
                    $stmt_update->bindParam(':orden', $orden, PDO::PARAM_INT);
                    $stmt_update->bindParam(':id_portafolio_bind', $id_portafolio, PDO::PARAM_INT);

                    if ($stmt_update->execute()) {
                        header("Location: index.php?mensaje=Elemento del portafolio '" . urlencode($titulo) . "' actualizado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al actualizar el elemento. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error: " . print_r($stmt_update->errorInfo(), true));
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error de base de datos al actualizar: " . $e->getMessage();
                // error_log("PDOException: " . $e->getMessage());
            }
        }
        $portafolio_item = $current_data_for_form; // Reflect changes in form
    }
} else {
    $current_data_for_form = $portafolio_item; // Initial load for the form
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Editar Elemento del Portafolio</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $display_form = ($current_data_for_form && isset($current_data_for_form['id_portafolio'])) || ($id_portafolio && $portafolio_item);
                    if ($display_form): 
                    ?>
                    <form action="editar.php?id=<?php echo htmlspecialchars($id_portafolio); ?>" method="post" novalidate>
                        <input type="hidden" name="id_portafolio" value="<?php echo htmlspecialchars($current_data_for_form['id_portafolio'] ?? $id_portafolio); ?>">
                        
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($current_data_for_form['titulo'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitulo" class="form-label">Subtítulo</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="<?php echo htmlspecialchars($current_data_for_form['subtitulo'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/portfolio/1.jpg" value="<?php echo htmlspecialchars($current_data_for_form['imagen'] ?? ''); ?>" required>
                             <small class="form-text text-muted">Ruta relativa. Ej: <code>assets/img/portfolio/image.jpg</code></small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion_modal" class="form-label">Descripción (Modal)</label>
                            <textarea class="form-control" id="descripcion_modal" name="descripcion_modal" rows="3" required><?php echo htmlspecialchars($current_data_for_form['descripcion_modal'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="cliente_modal" class="form-label">Cliente (Modal)</label>
                            <input type="text" class="form-control" id="cliente_modal" name="cliente_modal" value="<?php echo htmlspecialchars($current_data_for_form['cliente_modal'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_modal" class="form-label">Categoría (Modal)</label>
                            <input type="text" class="form-control" id="categoria_modal" name="categoria_modal" value="<?php echo htmlspecialchars($current_data_for_form['categoria_modal'] ?? ''); ?>" required>
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
                            Selecciona un elemento desde el <a href="index.php">listado del portafolio</a> para editar.
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
