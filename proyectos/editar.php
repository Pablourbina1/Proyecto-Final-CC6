<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_proyecto = (int)$_GET["ID_proyecto"];
    require __DIR__ . '/../permisos.php';
    if(!$es_dueno){
        header("Location: listado.php");
        exit;
    }
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id = $_SESSION["id"];
        $nombre = pg_escape_string($conn, $_POST["Nombre"]);
        $descripcion = pg_escape_string($conn, $_POST["Descripcion"]);

        $query = "UPDATE Proyecto SET Nombre = '$nombre', Descripcion = '$descripcion'
            WHERE ID_proyecto = $id_proyecto";
        $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

        $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
            VALUES ('edito los datos del proyecto $nombre', $id, $id_proyecto)";
        pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

        $mensaje = "<p class='exito'>El proyecto fue editado exitosamente</p>";
    }

    $query = "SELECT * FROM Proyecto WHERE ID_proyecto = $id_proyecto";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $line = pg_fetch_assoc($result);
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Proyectos - Editar
     </title>
  </head>
  <body>
    <h1>Editar proyecto</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            $nombre = htmlspecialchars($line["nombre"]);
            $descripcion = htmlspecialchars($line["descripcion"] ?? "");

            echo "<b>Nombre del proyecto</b>\n";
            echo "<input type = 'text' name = 'Nombre' maxlength = '60' value = '$nombre' required><br>\n";

            echo "<b>Descripcion</b>\n";
            echo "<textarea name = 'Descripcion' maxlength = '300' rows = '4'>$descripcion</textarea><br>\n";
        ?>

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
