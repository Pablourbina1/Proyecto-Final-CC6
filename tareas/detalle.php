<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
    $id_tarea = (int)$_GET["ID_tarea"];

    $result = pg_query($conn, "SELECT F.ID_proyecto FROM Tarea T JOIN Fase F ON T.ID_fase = F.ID_fase
        WHERE T.ID_tarea = $id_tarea") or die('La query fallo: ' .pg_last_error($conn));
    $fila = pg_fetch_assoc($result);
    if(!$fila){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $id_proyecto = $fila["id_proyecto"];
    require __DIR__ . '/../permisos.php';

    $result = pg_query($conn, "SELECT 1 FROM Asignacion WHERE ID_tarea = $id_tarea AND ID_usuario = $id");
    $asignado = pg_num_rows($result) > 0;
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if($_POST["accion"] == "evidencia" && ($es_dueno || $asignado)){
            $link = pg_escape_string($conn, $_POST["Link_evidencia"]);
            $query = "UPDATE Tarea SET Link_evidencia = '$link' WHERE ID_tarea = $id_tarea";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                VALUES ('entrego evidencia en la tarea ' || (SELECT Titulo FROM Tarea WHERE ID_tarea = $id_tarea), $id, $id_proyecto)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
            $mensaje = "<p class='exito'>Evidencia guardada</p>";
        } else if($_POST["accion"] == "comentar"){
            $texto = pg_escape_string($conn, $_POST["Texto"]);
            $query = "INSERT INTO Comentario (Texto, ID_tarea, ID_usuario) VALUES ('$texto', $id_tarea, $id)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
        }
    }

    if(isset($_SESSION["mensaje"])){
        $mensaje = "<p class='error'>" . $_SESSION["mensaje"] . "</p>";
        unset($_SESSION["mensaje"]);
    }

    $query = "SELECT T.*, F.Nombre AS fase, F.ID_fase,
                to_char(T.Fecha_limite, 'DD/MM/YYYY HH24:MI') AS limite,
                CASE
                    WHEN T.Estado = 'Completado' THEN 'Completada'
                    WHEN T.Fecha_limite < NOW() THEN 'Atrasada'
                    WHEN T.Fecha_limite < NOW() + INTERVAL '48 hours' THEN 'Por vencer'
                    ELSE 'A tiempo'
                END AS alerta,
                (SELECT string_agg(U.Nombre, ', ') FROM Asignacion A
                    JOIN Usuario U ON A.ID_usuario = U.ID_usuario
                    WHERE A.ID_tarea = T.ID_tarea) AS asignados
            FROM Tarea T JOIN Fase F ON T.ID_fase = F.ID_fase
            WHERE T.ID_tarea = $id_tarea";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $tarea = pg_fetch_assoc($result);
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Tareas - Detalle
     </title>
  </head>
  <body>
    <h1><?php echo htmlspecialchars($tarea["titulo"]); ?></h1>
    <?php
        echo $mensaje;
    ?>

    <div class="panel">
    <?php
        $clase = strtolower(str_replace(" ", "-", $tarea["alerta"]));
        echo "<b>Proyecto: </b>" . htmlspecialchars($nombre_proyecto) . "<br>\n";
        echo "<b>Fase: </b>" . htmlspecialchars($tarea["fase"]) . "<br>\n";
        echo "<b>Descripcion: </b>" . nl2br(htmlspecialchars($tarea["descripcion"] ?? "")) . "<br>\n";
        echo "<b>Prioridad: </b><span class='etiqueta {$tarea['prioridad']}'>{$tarea['prioridad']}</span><br>\n";
        echo "<b>Estado: </b>{$tarea['estado']}<br>\n";
        echo "<b>Fecha limite: </b>{$tarea['limite']} <span class='etiqueta $clase'>{$tarea['alerta']}</span><br>\n";
        echo "<b>Asignados: </b>" . htmlspecialchars($tarea["asignados"] ?? "Sin asignar") . "<br>\n";
        if($tarea["link_evidencia"]){
            $link = htmlspecialchars($tarea["link_evidencia"]);
            echo "<b>Evidencia: </b><a href='$link' target='_blank'>$link</a><br>\n";
        }
    ?>
    </div>

    <?php
        if($es_dueno || $asignado){
            echo "<h2>Cambiar estado</h2>\n";
            echo "<form method='post' action='cambiar_estado.php'>\n";
            echo "<input type='hidden' name='ID_tarea' value='$id_tarea'>\n";
            echo "<input type='hidden' name='volver' value='detalle.php?ID_tarea=$id_tarea'>\n";
            echo "<select name='Estado'>\n";
            foreach(["Por hacer", "En progreso", "En revision", "Completado"] as $e){
                $sel = $e == $tarea["estado"] ? "selected" : "";
                echo "<option value='$e' $sel>$e</option>\n";
            }
            echo "</select>\n";
            echo "<button type='submit'>Guardar estado</button>\n";
            echo "</form>\n";

            $link = htmlspecialchars($tarea["link_evidencia"] ?? "");
            echo "<h2>Evidencia</h2>\n";
            echo "<form method='post'>\n";
            echo "<b>Link (Drive, GitHub, etc.)</b>\n";
            echo "<input type='url' name='Link_evidencia' maxlength='200' value='$link' required>\n";
            echo "<button type='submit' name='accion' value='evidencia'>Guardar evidencia</button>\n";
            echo "</form>\n";
        }
    ?>

    <h2>Comentarios</h2>
<?php
    $query = "SELECT C.Texto, U.Nombre, to_char(C.Fecha, 'DD/MM/YYYY HH24:MI') AS fecha
            FROM Comentario C JOIN Usuario U ON C.ID_usuario = U.ID_usuario
            WHERE C.ID_tarea = $id_tarea
            ORDER BY C.Fecha";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "<p>No hay comentarios</p>\n";
    }
    while($c = pg_fetch_assoc($result)){
        echo "<div class='comentario'>\n";
        echo "<b>" . htmlspecialchars($c["nombre"]) . "</b> <small>{$c['fecha']}</small><br>\n";
        echo nl2br(htmlspecialchars($c["texto"])) . "\n";
        echo "</div>\n";
    }
    pg_close($conn);
?>
    <form method = "post">
        <textarea name = "Texto" maxlength = "500" rows = "3" required></textarea>
        <button type = "submit" name = "accion" value = "comentar">
                Comentar
            </button>
    </form>

    <center>
        <?php
            if($es_dueno){
                echo "<a href='editar.php?ID_tarea=$id_tarea'> Editar tarea </a><br>";
            }
        ?>
        <a href="listado.php?ID_fase=<?php echo $tarea["id_fase"]; ?>"> Tareas de la fase </a><br>
        <a href="kanban.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Kanban del proyecto </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
