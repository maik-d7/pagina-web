<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
    $puesto = filter_input(INPUT_POST, 'puesto', FILTER_SANITIZE_SPECIAL_CHARS);
    $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL); // Or FILTER_SANITIZE_STRING
    
    // Optional fields: Sanitize if present, allow empty strings
    $twitter_url = filter_input(INPUT_POST, 'twitter_url', FILTER_SANITIZE_URL);
    $facebook_url = filter_input(INPUT_POST, 'facebook_url', FILTER_SANITIZE_URL);
    $linkedin_url = filter_input(INPUT_POST, 'linkedin_url', FILTER_SANITIZE_URL);
    
    $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

    // Basic validation (imagen can be optional depending on requirements)
    if (empty($nombre) || empty($puesto) || empty($imagen)) {
        $error_message = "Por favor, completa los campos obligatorios (Nombre, Puesto, Imagen).";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "INSERT INTO tbl_equipo (nombre, puesto, imagen, twitter_url, facebook_url, linkedin_url, orden) 
                        VALUES (:nombre, :puesto, :imagen, :twitter_url, :facebook_url, :linkedin_url, :orden)";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':puesto', $puesto, PDO::PARAM_STR);
                $stmt->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                
                // Bind optional parameters, allow NULL if empty string after sanitization
                $stmt->bindParam(':twitter_url', $twitter_url, PDO::PARAM_STR);
                $stmt->bindParam(':facebook_url', $facebook_url, PDO::PARAM_STR);
                $stmt->bindParam(':linkedin_url', $linkedin_url, PDO::PARAM_STR);
                
                $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    header("Location: index.php?mensaje=Miembro del equipo '" . urlencode($nombre) . "' creado con éxito");
                    exit;
                } else {
                    $error_message = "Error al crear el miembro del equipo. Por favor, inténtalo de nuevo.";
                    // error_log("PDO Error: " . print_r($stmt->errorInfo(), true));
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos: " . $e->getMessage();
            // error_log("PDOException: " . $e->getMessage());
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Agregar Nuevo Miembro al Equipo</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="crear.php" method="post" novalidate>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="puesto" class="form-label">Puesto</label>
                            <input type="text" class="form-control" id="puesto" name="puesto" value="<?php echo isset($_POST['puesto']) ? htmlspecialchars($_POST['puesto']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/team/1.jpg" value="<?php echo isset($_POST['imagen']) ? htmlspecialchars($_POST['imagen']) : ''; ?>" required>
                            <small class="form-text text-muted">Ruta relativa a la imagen. Ej: <code>assets/img/team/member.jpg</code></small>
                        </div>
                        <div class="mb-3">
                            <label for="twitter_url" class="form-label">Twitter URL (Opcional)</label>
                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/usuario" value="<?php echo isset($_POST['twitter_url']) ? htmlspecialchars($_POST['twitter_url']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="facebook_url" class="form-label">Facebook URL (Opcional)</label>
                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/usuario" value="<?php echo isset($_POST['facebook_url']) ? htmlspecialchars($_POST['facebook_url']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="linkedin_url" class="form-label">LinkedIn URL (Opcional)</label>
                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/usuario" value="<?php echo isset($_POST['linkedin_url']) ? htmlspecialchars($_POST['linkedin_url']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo isset($_POST['orden']) ? htmlspecialchars($_POST['orden']) : '0'; ?>">
                            <small class="form-text text-muted">Determina la posición en la lista (menor número aparece primero).</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-user-plus"></i> Agregar Miembro</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
