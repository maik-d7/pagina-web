<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL); // Or FILTER_SANITIZE_STRING if it's just a path
    $descripcion_modal = filter_input(INPUT_POST, 'descripcion_modal', FILTER_SANITIZE_SPECIAL_CHARS);
    $cliente_modal = filter_input(INPUT_POST, 'cliente_modal', FILTER_SANITIZE_SPECIAL_CHARS);
    $categoria_modal = filter_input(INPUT_POST, 'categoria_modal', FILTER_SANITIZE_SPECIAL_CHARS);
    $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

    // Basic validation (imagen can be optional depending on requirements)
    if (empty($titulo) || empty($subtitulo) || empty($imagen) || empty($descripcion_modal) || empty($cliente_modal) || empty($categoria_modal)) {
        $error_message = "Por favor, completa todos los campos obligatorios.";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "INSERT INTO tbl_portafolio (titulo, subtitulo, imagen, descripcion_modal, cliente_modal, categoria_modal, orden) 
                        VALUES (:titulo, :subtitulo, :imagen, :descripcion_modal, :cliente_modal, :categoria_modal, :orden)";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt->bindParam(':subtitulo', $subtitulo, PDO::PARAM_STR);
                $stmt->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                $stmt->bindParam(':descripcion_modal', $descripcion_modal, PDO::PARAM_STR);
                $stmt->bindParam(':cliente_modal', $cliente_modal, PDO::PARAM_STR);
                $stmt->bindParam(':categoria_modal', $categoria_modal, PDO::PARAM_STR);
                $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    header("Location: index.php?mensaje=Elemento del portafolio creado con éxito");
                    exit;
                } else {
                    $error_message = "Error al crear el elemento del portafolio. Por favor, inténtalo de nuevo.";
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
                    <h4 class="mb-0">Crear Nuevo Elemento del Portafolio</h4>
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
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitulo" class="form-label">Subtítulo</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="<?php echo isset($_POST['subtitulo']) ? htmlspecialchars($_POST['subtitulo']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/portfolio/1.jpg" value="<?php echo isset($_POST['imagen']) ? htmlspecialchars($_POST['imagen']) : ''; ?>" required>
                            <small class="form-text text-muted">Por ahora, ingresa la ruta relativa a la imagen. La subida de archivos se implementará después.</small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion_modal" class="form-label">Descripción (Modal)</label>
                            <textarea class="form-control" id="descripcion_modal" name="descripcion_modal" rows="3" required><?php echo isset($_POST['descripcion_modal']) ? htmlspecialchars($_POST['descripcion_modal']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="cliente_modal" class="form-label">Cliente (Modal)</label>
                            <input type="text" class="form-control" id="cliente_modal" name="cliente_modal" value="<?php echo isset($_POST['cliente_modal']) ? htmlspecialchars($_POST['cliente_modal']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_modal" class="form-label">Categoría (Modal)</label>
                            <input type="text" class="form-control" id="categoria_modal" name="categoria_modal" value="<?php echo isset($_POST['categoria_modal']) ? htmlspecialchars($_POST['categoria_modal']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo isset($_POST['orden']) ? htmlspecialchars($_POST['orden']) : '0'; ?>">
                            <small class="form-text text-muted">Determina la posición en la lista (menor número aparece primero).</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-plus-circle"></i> Crear Elemento</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
