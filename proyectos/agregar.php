<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    if(!$_SESSION["lider"]){
        header("Location: listado.php");
        exit;
    }
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id = $_SESSION["id"];
        $nombre = pg_escape_string($conn, $_POST["Nombre"]);
        $descripcion = pg_escape_string($conn, $_POST["Descripcion"]);

        $query = "INSERT INTO Proyecto (Nombre, Descripcion, ID_lider)
            VALUES ('$nombre', '$descripcion', $id) RETURNING ID_proyecto";
        $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
        $fila = pg_fetch_assoc($result);
        $id_proyecto = $fila["id_proyecto"];

        $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
            VALUES ('creo el proyecto $nombre', $id, $id_proyecto)";
        pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

        $mensaje = "<p class='exito'>El proyecto fue creado exitosamente.
            <a href='../fases/agregar.php?ID_proyecto=$id_proyecto'>Agregar fases</a> |
            <a href='miembros.php?ID_proyecto=$id_proyecto'>Agregar miembros</a></p>";
        pg_close($conn);
    }
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Proyectos - Crear
     </title>
  </head>
  <body>
    <h1>Crear proyecto</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <b>Nombre del proyecto</b>
        <input type = "text" name = "Nombre" maxlength = "60" required><br>

        <b>Descripcion</b>
        <textarea name = "Descripcion" maxlength = "300" rows = "4"></textarea><br>

        <button type = "submit">
                Enviar
            </button>

    </form>
    <center>
         <a href="listado.php"> Listado de proyectos </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
