<?php
// Ensure the path to bd.php is correct, assuming it's two levels up from current dir
// For example, if crear.php is in admin/secciones/servicios/, then bd.php is in admin/
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';
// $success_message = ''; // Not strictly needed if redirecting, but good for potential future use

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    // Using filter_input for security
    $icono = filter_input(INPUT_POST, 'icono', FILTER_SANITIZE_SPECIAL_CHARS);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    // Validate 'orden' as an integer, default to 0 if not provided or invalid
    $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

    // Basic validation
    if (empty($icono) || empty($titulo) || empty($descripcion)) {
        $error_message = "Por favor, completa todos los campos obligatorios (Icono, Título, Descripción).";
    } else {
        try {
            // Check if $db is initialized from bd.php
            if (!isset($db) || $db === null) {
                // This might happen if bd.php has an issue or was not included correctly
                $error_message = "Error: La conexión a la base de datos no está disponible. Verifica ../../bd.php";
            } else {
                $sql = "INSERT INTO tbl_servicios (icono, titulo, descripcion, orden) VALUES (:icono, :titulo, :descripcion, :orden)";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(':icono', $icono, PDO::PARAM_STR);
                $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // Redirect to index.php with a success message
                    // Ensure the path to index.php is correct (it's in the same directory)
                    header("Location: index.php?mensaje=Servicio '%s' creado con éxito.", urlencode($titulo));
                    exit; // Important to prevent further script execution after redirect
                } else {
                    $error_message = "Error al crear el servicio. Por favor, inténtalo de nuevo.";
                    // Log detailed error: errorInfo() provides more details
                    // error_log("PDO Error: " . print_r($stmt->errorInfo(), true)); 
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos: " . $e->getMessage();
            // Log detailed error:
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
                    <h4 class="mb-0">Crear Nuevo Servicio</h4>
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
                            <label for="icono" class="form-label">Icono (Clase Font Awesome)</label>
                            <input type="text" class="form-control <?php if(isset($_POST['icono']) && empty(trim($_POST['icono']))) echo 'is-invalid'; ?>" id="icono" name="icono" placeholder="Ej: fas fa-cogs" value="<?php echo isset($_POST['icono']) ? htmlspecialchars($_POST['icono']) : ''; ?>" required>
                            <div class="invalid-feedback">
                                El icono es obligatorio.
                            </div>
                            <small class="form-text text-muted">Puedes encontrar iconos en <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a>. Ejemplo: <code>fas fa-briefcase</code>.</small>
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Servicio</label>
                            <input type="text" class="form-control <?php if(isset($_POST['titulo']) && empty(trim($_POST['titulo']))) echo 'is-invalid'; ?>" id="titulo" name="titulo" placeholder="Ej: Desarrollo Web Avanzado" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>
                            <div class="invalid-feedback">
                                El título es obligatorio.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control <?php if(isset($_POST['descripcion']) && empty(trim($_POST['descripcion']))) echo 'is-invalid'; ?>" id="descripcion" name="descripcion" rows="4" required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                            <div class="invalid-feedback">
                                La descripción es obligatoria.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden de Visualización</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo isset($_POST['orden']) ? htmlspecialchars($_POST['orden']) : '0'; ?>">
                            <small class="form-text text-muted">Determina la posición del servicio en la lista (menor número aparece primero). Por defecto es 0.</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-plus-circle"></i> Crear Servicio</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
