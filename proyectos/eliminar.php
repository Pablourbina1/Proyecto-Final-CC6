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
        $accion = $_POST["decision"];
        if ($accion == "si"){
            $query = "DELETE FROM Proyecto WHERE ID_proyecto = $id_proyecto";
            $result = pg_query($conn, $query);

            if (!$result){
                $mensaje = "<p class='error'>Error: No se pudo eliminar el proyecto</p>";
            } else {
                header("Location: listado.php");
                exit;
            }
        } else {
            header("Location: listado.php");
            exit;
        }
    }

    $query = "SELECT P.*,
                (SELECT COUNT(*) FROM Fase F WHERE F.ID_proyecto = P.ID_proyecto) AS fases
            FROM Proyecto P WHERE P.ID_proyecto = $id_proyecto";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $line = pg_fetch_assoc($result);
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Proyectos - Eliminar
     </title>
  </head>
  <body>
    <h1>Eliminar proyecto</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            $nombre = htmlspecialchars($line["nombre"]);
            $descripcion = htmlspecialchars($line["descripcion"] ?? "");
            $fases = $line["fases"];

            echo "<b>Nombre: </b>$nombre<br>\n";
            echo "<b>Descripcion: </b>$descripcion<br>\n";
            echo "<b>Fases: </b>$fases<br>\n";
        ?>
        <h4>¿Quieres eliminar el proyecto? Se eliminaran tambien sus fases, tareas y bitacora.</h4>
        <button type = "submit" name = "decision" value = "si">
                Si
            </button>

            <button type = "submit" name = "decision" value = "no">
                No
            </button>

    </form>
    <center>
         <a href="listado.php"> Listado de proyectos </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
