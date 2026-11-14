<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_tarea = (int)$_GET["ID_tarea"];

    $query = "SELECT T.*, F.ID_proyecto, F.Nombre AS fase
        FROM Tarea T JOIN Fase F ON T.ID_fase = F.ID_fase
        WHERE T.ID_tarea = $id_tarea";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $line = pg_fetch_assoc($result);
    if(!$line){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $id_proyecto = $line["id_proyecto"];
    $id_fase = $line["id_fase"];
    require __DIR__ . '/../permisos.php';
    if(!$es_dueno){
        header("Location: listado.php?ID_fase=$id_fase");
        exit;
    }
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $accion = $_POST["decision"];
        if ($accion == "si"){
            $id = $_SESSION["id"];
            $titulo = pg_escape_string($conn, $line["titulo"]);
            $query = "DELETE FROM Tarea WHERE ID_tarea = $id_tarea";
            $result = pg_query($conn, $query);

            if (!$result){
                $mensaje = "<p class='error'>Error: No se pudo eliminar la tarea</p>";
            } else {
                $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                    VALUES ('elimino la tarea $titulo', $id, $id_proyecto)";
                pg_query($conn, $query);
                header("Location: listado.php?ID_fase=$id_fase");
                exit;
            }
        } else {
            header("Location: listado.php?ID_fase=$id_fase");
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
         Tareas - Eliminar
     </title>
  </head>
  <body>
    <h1>Eliminar tarea</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            echo "<b>Titulo: </b>" . htmlspecialchars($line["titulo"]) . "<br>\n";
            echo "<b>Fase: </b>" . htmlspecialchars($line["fase"]) . "<br>\n";
            echo "<b>Prioridad: </b>{$line['prioridad']}<br>\n";
            echo "<b>Estado: </b>{$line['estado']}<br>\n";
            echo "<b>Fecha limite: </b>{$line['fecha_limite']}<br>\n";
        ?>
        <h4>¿Quieres eliminar la tarea?</h4>
        <button type = "submit" name = "decision" value = "si">
                Si
            </button>

            <button type = "submit" name = "decision" value = "no">
                No
            </button>

    </form>
    <center>
         <a href="listado.php?ID_fase=<?php echo $id_fase; ?>"> Listado de tareas </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
