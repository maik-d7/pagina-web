<?php 
include("../../db.php");
if($_POST){
    print_r($_POST);
    $icono = (isset($_POST["icon"])) ? $_POST["icon"] :"";
    $titulo = (isset($_POST["title"])) ? $_POST["title"] :"";
    $descripcion = (isset($_POST["descripcion"])) ? $_POST["descripcion"] :"";
    
}

include '../../templates/header.php';
?>

<div class="row">
    <div class="col-3">

    </div>
    <div class="col-6">
        <div class="card text-start">
            <div class="card-body">
                <h4 class="card-title">Nuevo Servicio</h4>
                <form action="" class="d-flex" enctype="multipart/form-data" method="post">
                    <div class="col">
                        <div class="mb-3">
                            <label for="icono" class="form-label">Icono</label>
                            <input type="text" name="icono" id="icono" class="form-control" placeholder="Icono" />
                        </div>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Titulo</label>
                            <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Titulo" />
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Descripcion" />
                        </div>
                        <button type="submit" class="mb-3 btn btn-success w-50">
                            Agregar
                        </button>

                        <a name="" id="" class="mb-3 btn btn-primary w-50" href="index.php" role="button">Cancelar</a>

                    </div>
                </form>


            </div>
        </div>

    </div>
    <div class="col-4"></div>
</div>

<?php include '../../templates/footer.php'; ?>