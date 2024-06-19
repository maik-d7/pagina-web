<?php $url_base="http://localhost:8000/admin/";?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Principal</title>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand navbar-light bg-light">
            <div class="nav navbar-nav">
                <a class="nav-item nav-link active" href="#" aria-current="page"
                    >Administrar <span class="visually-hidden">(current)</span></a
                >
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/servicios/ ">Servivios</a>
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/portafolio/">Portafolio</a>
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/entradas/">Entrada</a>
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/equipo/">Equipo</a>
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/configuraciones/">Configuraciones</a>
                <a class="nav-item nav-link" href="<?php echo $url_base;?>secciones/usuarios/">Usuarios</a>
            </div>
        </nav>
    </header>

    <main class="container">