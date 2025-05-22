<?php
include '../../bd.php';
include '../../templates/header.php';

$error_message = '';
$success_message = '';

// Display success message from GET parameter
if (isset($_GET['mensaje'])) {
    $success_message = htmlspecialchars($_GET['mensaje']);
}

// Handle Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_portafolio_to_delete = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($id_portafolio_to_delete) {
        try {
            if (!isset($db) || $db === null) {
                $error_message = "Error: La conexión a la base de datos no está disponible.";
            } else {
                // Optional: Check if item exists to give specific feedback
                $sql_check = "SELECT titulo FROM tbl_portafolio WHERE id_portafolio = :id_portafolio";
                $stmt_check = $db->prepare($sql_check);
                $stmt_check->bindParam(':id_portafolio', $id_portafolio_to_delete, PDO::PARAM_INT);
                $stmt_check->execute();
                $item_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($item_to_delete) {
                    $sql_delete = "DELETE FROM tbl_portafolio WHERE id_portafolio = :id_portafolio";
                    $stmt_delete = $db->prepare($sql_delete);
                    $stmt_delete->bindParam(':id_portafolio', $id_portafolio_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        header("Location: index.php?mensaje=Elemento del portafolio '" . urlencode($item_to_delete['titulo']) . "' eliminado con éxito");
                        exit;
                    } else {
                        $error_message = "Error al eliminar el elemento del portafolio.";
                    }
                } else {
                     $error_message = "Error: Elemento del portafolio con ID " . htmlspecialchars($id_portafolio_to_delete) . " no encontrado.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al eliminar: " . $e->getMessage();
        }
    } else {
        $error_message = "ID de portafolio inválido para eliminar.";
    }
}

// Fetch all portfolio items
$portafolio_items = [];
try {
    if (!isset($db) || $db === null) {
        $error_message .= ($error_message ? "<br>" : "") . "Error: Conexión a BD no disponible para listar.";
    } else {
        $sql_select = "SELECT * FROM tbl_portafolio ORDER BY orden ASC, fecha_creacion DESC";
        $stmt_select = $db->query($sql_select);
        if ($stmt_select) {
            $portafolio_items = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message .= ($error_message ? "<br>" : "") . "Error al obtener lista del portafolio.";
        }
    }
} catch (PDOException $e) {
    $error_message .= ($error_message ? "<br>" : "") . "Error de BD al listar portafolio: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Gestión del Portafolio</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="crear.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nuevo Elemento</a>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Listado de Elementos del Portafolio</h4>
        </div>
        <div class="card-body">
            <?php if (empty($portafolio_items) && empty($error_message)): ?>
                <div class="alert alert-info" role="alert">
                    No hay elementos en el portafolio actualmente. <a href="crear.php">Crea el primero</a>.
                </div>
            <?php elseif (!empty($portafolio_items)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Orden</th>
                                <th scope="col">Imagen</th>
                                <th scope="col">Título</th>
                                <th scope="col">Subtítulo</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Categoría</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portafolio_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['orden']); ?></td>
                                    <td>
                                        <?php if (!empty($item['imagen'])): ?>
                                            <img src="<?php echo htmlspecialchars(filter_var($item['imagen'], FILTER_SANITIZE_URL)); // Basic path, needs adjustment if full URL or specific base path ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>" style="width: 100px; height: auto; object-fit: cover;">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($item['subtitulo']); ?></td>
                                    <td><?php echo htmlspecialchars($item['cliente_modal']); ?></td>
                                    <td><?php echo htmlspecialchars($item['categoria_modal']); ?></td>
                                    <td class="text-center">
                                        <a href="editar.php?id=<?php echo htmlspecialchars($item['id_portafolio']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar: <?php echo htmlspecialchars(addslashes($item['titulo'])); ?>?');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($item['id_portafolio']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (empty($error_message)): ?>
                 <div class="alert alert-info" role="alert">
                    No hay elementos para mostrar en el portafolio.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
