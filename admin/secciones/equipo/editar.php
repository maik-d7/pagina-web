<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
$equipo_item = null;
$id_miembro = null;

// 1. Check for ID in GET request and validate
if (isset($_GET['id'])) {
    $id_miembro = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_miembro === false || $id_miembro <= 0) {
        $error_message = "ID de miembro inválido o no proporcionado.";
    } else {
        // 2. Fetch the team member from the database
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "SELECT * FROM tbl_equipo WHERE id_miembro = :id_miembro";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_miembro', $id_miembro, PDO::PARAM_INT);
                $stmt->execute();
                $equipo_item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$equipo_item) {
                    $error_message = "Miembro del equipo no encontrado con ID: " . htmlspecialchars($id_miembro) . ".";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al buscar el miembro: " . $e->getMessage();
        }
    }
} else {
    $error_message = "No se especificó ID de miembro para editar.";
}

// 3. Handle POST request for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$id_miembro || !$equipo_item && !(isset($_POST['id_miembro']) && filter_var($_POST['id_miembro'], FILTER_VALIDATE_INT))) {
        $error_message = "No se puede procesar la actualización: el miembro original no se cargó o el ID es inválido.";
        $current_data_for_form = $_POST; 
        $current_data_for_form['id_miembro'] = filter_input(INPUT_POST, 'id_miembro', FILTER_VALIDATE_INT);
    } else {
        $id_miembro_post = filter_input(INPUT_POST, 'id_miembro', FILTER_VALIDATE_INT);
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
        $puesto = filter_input(INPUT_POST, 'puesto', FILTER_SANITIZE_SPECIAL_CHARS);
        $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL);
        
        $twitter_url = filter_input(INPUT_POST, 'twitter_url', FILTER_SANITIZE_URL);
        $facebook_url = filter_input(INPUT_POST, 'facebook_url', FILTER_SANITIZE_URL);
        $linkedin_url = filter_input(INPUT_POST, 'linkedin_url', FILTER_SANITIZE_URL);
        
        $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);
        
        $current_data_for_form = array_merge($equipo_item ?? [], $_POST);

        if (empty($nombre) || empty($puesto) || empty($imagen)) {
            $error_message = "Por favor, completa los campos obligatorios (Nombre, Puesto, Imagen).";
        } elseif ($id_miembro_post !== $id_miembro) {
            $error_message = "Error de consistencia de ID. El ID del formulario (".htmlspecialchars($id_miembro_post).") no coincide con el ID esperado (".htmlspecialchars($id_miembro).").";
        } else {
            try {
                if (!isset($db) || $db === null) {
                    $error_message = "Error: La conexión a la base de datos no está disponible para la actualización.";
                } else {
                    $sql_update = "UPDATE tbl_equipo SET 
                                    nombre = :nombre, puesto = :puesto, imagen = :imagen, 
                                    twitter_url = :twitter_url, facebook_url = :facebook_url, 
                                    linkedin_url = :linkedin_url, orden = :orden 
                                 WHERE id_miembro = :id_miembro_bind";
                    $stmt_update = $db->prepare($sql_update);
                    
                    $stmt_update->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                    $stmt_update->bindParam(':puesto', $puesto, PDO::PARAM_STR);
                    $stmt_update->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                    $stmt_update->bindParam(':twitter_url', $twitter_url, PDO::PARAM_STR);
                    $stmt_update->bindParam(':facebook_url', $facebook_url, PDO::PARAM_STR);
                    $stmt_update->bindParam(':linkedin_url', $linkedin_url, PDO::PARAM_STR);
                    $stmt_update->bindParam(':orden', $orden, PDO::PARAM_INT);
                    $stmt_update->bindParam(':id_miembro_bind', $id_miembro, PDO::PARAM_INT);

                    if ($stmt_update->execute()) {
                        header("Location: index.php?mensaje=Miembro del equipo '" . urlencode($nombre) . "' actualizado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al actualizar el miembro del equipo. Por favor, inténtalo de nuevo.";
                        // error_log("PDO Error: " . print_r($stmt_update->errorInfo(), true));
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error de base de datos al actualizar: " . $e->getMessage();
                // error_log("PDOException: " . $e->getMessage());
            }
        }
        $equipo_item = $current_data_for_form; // Reflect changes in form
    }
} else {
    $current_data_for_form = $equipo_item; // Initial load for the form
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Editar Miembro del Equipo</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $display_form = ($current_data_for_form && isset($current_data_for_form['id_miembro'])) || ($id_miembro && $equipo_item);
                    if ($display_form): 
                    ?>
                    <form action="editar.php?id=<?php echo htmlspecialchars($id_miembro); ?>" method="post" novalidate>
                        <input type="hidden" name="id_miembro" value="<?php echo htmlspecialchars($current_data_for_form['id_miembro'] ?? $id_miembro); ?>">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($current_data_for_form['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="puesto" class="form-label">Puesto</label>
                            <input type="text" class="form-control" id="puesto" name="puesto" value="<?php echo htmlspecialchars($current_data_for_form['puesto'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/team/1.jpg" value="<?php echo htmlspecialchars($current_data_for_form['imagen'] ?? ''); ?>" required>
                             <small class="form-text text-muted">Ruta relativa. Ej: <code>assets/img/team/member.jpg</code></small>
                        </div>
                        <div class="mb-3">
                            <label for="twitter_url" class="form-label">Twitter URL (Opcional)</label>
                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/usuario" value="<?php echo htmlspecialchars($current_data_for_form['twitter_url'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="facebook_url" class="form-label">Facebook URL (Opcional)</label>
                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/usuario" value="<?php echo htmlspecialchars($current_data_for_form['facebook_url'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="linkedin_url" class="form-label">LinkedIn URL (Opcional)</label>
                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/usuario" value="<?php echo htmlspecialchars($current_data_for_form['linkedin_url'] ?? ''); ?>">
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
                            Selecciona un miembro desde el <a href="index.php">listado del equipo</a> para editar.
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
