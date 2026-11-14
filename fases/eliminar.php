<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_fase = (int)$_GET["ID_fase"];

    $query = "SELECT F.*, (SELECT COUNT(*) FROM Tarea T WHERE T.ID_fase = F.ID_fase) AS tareas
        FROM Fase F WHERE F.ID_fase = $id_fase";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $line = pg_fetch_assoc($result);
    if(!$line){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $id_proyecto = $line["id_proyecto"];
    require __DIR__ . '/../permisos.php';
    if(!$es_dueno){
        header("Location: listado.php?ID_proyecto=$id_proyecto");
        exit;
    }
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $accion = $_POST["decision"];
        if ($accion == "si"){
            $id = $_SESSION["id"];
            $nombre = pg_escape_string($conn, $line["nombre"]);
            $query = "DELETE FROM Fase WHERE ID_fase = $id_fase";
            $result = pg_query($conn, $query);

            if (!$result){
                $mensaje = "<p class='error'>Error: No se pudo eliminar la fase</p>";
            } else {
                $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                    VALUES ('elimino la fase $nombre', $id, $id_proyecto)";
                pg_query($conn, $query);
                header("Location: listado.php?ID_proyecto=$id_proyecto");
                exit;
            }
        } else {
            header("Location: listado.php?ID_proyecto=$id_proyecto");
            exit;
        }
    }
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Fases - Eliminar
     </title>
  </head>
  <body>
    <h1>Eliminar fase</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            $nombre = htmlspecialchars($line["nombre"]);
            echo "<b>Nombre: </b>$nombre<br>\n";
            echo "<b>Orden: </b>{$line['orden']}<br>\n";
            echo "<b>Fecha de inicio: </b>{$line['fecha_inicio']}<br>\n";
            echo "<b>Fecha limite: </b>{$line['fecha_limite']}<br>\n";
            echo "<b>Tareas: </b>{$line['tareas']}<br>\n";
        ?>
        <h4>¿Quieres eliminar la fase? Se eliminaran tambien sus tareas.</h4>
        <button type = "submit" name = "decision" value = "si">
                Si
            </button>

            <button type = "submit" name = "decision" value = "no">
                No
            </button>

    </form>
    <center>
         <a href="listado.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Listado de fases </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
