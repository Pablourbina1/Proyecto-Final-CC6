<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
    $id_proyecto = (int)$_GET["ID_proyecto"];
    require __DIR__ . '/../permisos.php';
    $id_fase = isset($_GET["ID_fase"]) ? (int)$_GET["ID_fase"] : 0;

    $mensaje = "";
    if(isset($_SESSION["mensaje"])){
        $mensaje = "<p class='error'>" . $_SESSION["mensaje"] . "</p>";
        unset($_SESSION["mensaje"]);
    }
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Kanban
     </title>
  </head>
  <body>
    <h1>Kanban - <?php echo htmlspecialchars($nombre_proyecto); ?></h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "GET" class = "filtro">
        <input type = "hidden" name = "ID_proyecto" value = "<?php echo $id_proyecto; ?>">
        <b>Fase</b>
        <select name = "ID_fase">
            <option value = "0">Todas</option>
            <?php
                $fases = pg_query($conn, "SELECT ID_fase, Nombre FROM Fase WHERE ID_proyecto = $id_proyecto ORDER BY Orden");
                while($f = pg_fetch_assoc($fases)){
                    $sel = $f["id_fase"] == $id_fase ? "selected" : "";
                    echo "<option value='{$f['id_fase']}' $sel>" . htmlspecialchars($f['nombre']) . "</option>";
                }
            ?>
        </select>
        <input type = "submit" value = "Filtrar">
    </form>

<?php
    $filtro = "";
    if($id_fase > 0){
        $filtro = "AND T.ID_fase = $id_fase";
    }

    $query = "SELECT T.ID_tarea, T.Titulo, T.Prioridad, T.Estado, F.Nombre AS fase,
                to_char(T.Fecha_limite, 'DD/MM HH24:MI') AS limite,
                CASE
                    WHEN T.Estado = 'Completado' THEN 'Completada'
                    WHEN T.Fecha_limite < NOW() THEN 'Atrasada'
                    WHEN T.Fecha_limite < NOW() + INTERVAL '48 hours' THEN 'Por vencer'
                    ELSE 'A tiempo'
                END AS alerta,
                COALESCE(string_agg(U.Nombre, ', '), 'Sin asignar') AS asignados,
                COALESCE(BOOL_OR(A.ID_usuario = $id), false) AS asignado
            FROM Tarea T
            JOIN Fase F ON T.ID_fase = F.ID_fase
            LEFT JOIN Asignacion A ON A.ID_tarea = T.ID_tarea
            LEFT JOIN Usuario U ON U.ID_usuario = A.ID_usuario
            WHERE F.ID_proyecto = $id_proyecto $filtro
            GROUP BY T.ID_tarea, F.Nombre, F.Orden
            ORDER BY F.Orden, T.Fecha_limite";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

    $estados = ["Por hacer", "En progreso", "En revision", "Completado"];
    $columnas = ["Por hacer" => [], "En progreso" => [], "En revision" => [], "Completado" => []];
    while($line = pg_fetch_assoc($result)){
        $columnas[$line["estado"]][] = $line;
    }

    $volver = "kanban.php?ID_proyecto=$id_proyecto&ID_fase=$id_fase";

    echo "<div class='kanban'>\n";
    foreach($estados as $estado){
        $total = count($columnas[$estado]);
        echo "<div class='columna'>\n";
        echo "<h3>$estado ($total)</h3>\n";

        foreach($columnas[$estado] as $t){
            $id_tarea = $t["id_tarea"];
            $titulo = htmlspecialchars($t["titulo"]);
            $prioridad = $t["prioridad"];
            $alerta = $t["alerta"];
            $clase = strtolower(str_replace(" ", "-", $alerta));

            echo "<div class='tarjeta $prioridad'>\n";
            echo "<a href='detalle.php?ID_tarea=$id_tarea'><b>$titulo</b></a><br>\n";
            echo "<small>" . htmlspecialchars($t["fase"]) . "</small><br>\n";
            echo "<span class='etiqueta $prioridad'>$prioridad</span>\n";
            echo "<span class='etiqueta $clase'>$alerta</span><br>\n";
            echo "<small>Limite: {$t['limite']}</small><br>\n";
            echo "<small>" . htmlspecialchars($t["asignados"]) . "</small>\n";

            if($es_dueno || $t["asignado"] == "t"){
                echo "<form method='post' action='cambiar_estado.php' class='mover'>\n";
                echo "<input type='hidden' name='ID_tarea' value='$id_tarea'>\n";
                echo "<input type='hidden' name='volver' value='$volver'>\n";
                echo "<select name='Estado'>\n";
                foreach($estados as $e){
                    $sel = $e == $estado ? "selected" : "";
                    echo "<option value='$e' $sel>$e</option>\n";
                }
                echo "</select>\n";
                echo "<button type='submit' class='mini'>Mover</button>\n";
                echo "</form>\n";
            }
            echo "</div>\n";
        }
        echo "</div>\n";
    }
    echo "</div>\n";

    pg_close($conn);
?>
    <center>
        <a href="../fases/listado.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Fases del proyecto </a><br>
        <a href="../proyectos/listado.php"> Listado de proyectos </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
