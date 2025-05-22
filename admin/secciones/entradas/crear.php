<?php
include '../../bd.php'; 
include '../../templates/header.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $fecha_evento = filter_input(INPUT_POST, 'fecha_evento', FILTER_SANITIZE_SPECIAL_CHARS);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $subtitulo = filter_input(INPUT_POST, 'subtitulo', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
    $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL); // Or FILTER_SANITIZE_STRING
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    $alineacion_timeline_input = filter_input(INPUT_POST, 'alineacion_timeline', FILTER_SANITIZE_SPECIAL_CHARS);
    $tipo_entrada = filter_input(INPUT_POST, 'tipo_entrada', FILTER_SANITIZE_SPECIAL_CHARS, ["options" => ["default" => "timeline"]]);
    $orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

    // Map alineacion_timeline
    $alineacion_timeline_db_val = ($alineacion_timeline_input === 'Invertida') ? 'timeline-inverted' : '';

    // Basic validation
    if (empty($fecha_evento) || empty($titulo) || empty($imagen) || empty($descripcion)) {
        $error_message = "Por favor, completa los campos obligatorios (Fecha, Título, Imagen, Descripción).";
    } else {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                $sql = "INSERT INTO tbl_entradas (fecha_evento, titulo, subtitulo, imagen, descripcion, alineacion_timeline, tipo_entrada, orden) 
                        VALUES (:fecha_evento, :titulo, :subtitulo, :imagen, :descripcion, :alineacion_timeline, :tipo_entrada, :orden)";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(':fecha_evento', $fecha_evento, PDO::PARAM_STR);
                $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt->bindParam(':subtitulo', $subtitulo, PDO::PARAM_STR);
                $stmt->bindParam(':imagen', $imagen, PDO::PARAM_STR);
                $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':alineacion_timeline', $alineacion_timeline_db_val, PDO::PARAM_STR);
                $stmt->bindParam(':tipo_entrada', $tipo_entrada, PDO::PARAM_STR);
                $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    header("Location: index.php?mensaje=Entrada creada con éxito: " . urlencode($titulo));
                    exit;
                } else {
                    $error_message = "Error al crear la entrada. Por favor, inténtalo de nuevo.";
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
                    <h4 class="mb-0">Crear Nueva Entrada (Timeline/Blog)</h4>
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
                            <label for="fecha_evento" class="form-label">Fecha del Evento/Entrada</label>
                            <input type="text" class="form-control" id="fecha_evento" name="fecha_evento" placeholder="Ej: 2009-2011 o Marzo 2024" value="<?php echo isset($_POST['fecha_evento']) ? htmlspecialchars($_POST['fecha_evento']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subtitulo" class="form-label">Subtítulo (Opcional)</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="<?php echo isset($_POST['subtitulo']) ? htmlspecialchars($_POST['subtitulo']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Ruta de Imagen</label>
                            <input type="text" class="form-control" id="imagen" name="imagen" placeholder="Ej: assets/img/timeline/1.jpg" value="<?php echo isset($_POST['imagen']) ? htmlspecialchars($_POST['imagen']) : ''; ?>" required>
                            <small class="form-text text-muted">Ruta relativa a la imagen. Ej: <code>assets/img/about/1.jpg</code></small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="alineacion_timeline" class="form-label">Alineación en Timeline</label>
                            <select class="form-select" id="alineacion_timeline" name="alineacion_timeline">
                                <option value="Normal" <?php echo (isset($_POST['alineacion_timeline']) && $_POST['alineacion_timeline'] == 'Normal') ? 'selected' : ''; ?>>Normal (Imagen a la izquierda)</option>
                                <option value="Invertida" <?php echo (isset($_POST['alineacion_timeline']) && $_POST['alineacion_timeline'] == 'Invertida') ? 'selected' : ''; ?>>Invertida (Imagen a la derecha)</option>
                            </select>
                            <small class="form-text text-muted">Afecta la visualización si la entrada es de tipo 'timeline'.</small>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_entrada" class="form-label">Tipo de Entrada</label>
                            <input type="text" class="form-control" id="tipo_entrada" name="tipo_entrada" value="<?php echo isset($_POST['tipo_entrada']) ? htmlspecialchars($_POST['tipo_entrada']) : 'timeline'; ?>">
                             <small class="form-text text-muted">Generalmente 'timeline' para la sección "Acerca de" o 'blog' para futuras entradas de blog.</small>
                        </div>
                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo isset($_POST['orden']) ? htmlspecialchars($_POST['orden']) : '0'; ?>">
                            <small class="form-text text-muted">Determina la posición en la lista (menor número aparece primero).</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-success me-md-2"><i class="fas fa-plus-circle"></i> Crear Entrada</button>
                            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
