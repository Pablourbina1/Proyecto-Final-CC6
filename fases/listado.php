<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_proyecto = (int)$_GET["ID_proyecto"];
    require __DIR__ . '/../permisos.php';
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Fases - Listado
     </title>
  </head>
  <body>
    <h1>Fases de <?php echo htmlspecialchars($nombre_proyecto); ?></h1>

<?php
    $query = "SELECT F.ID_fase, F.Nombre, F.Orden,
                to_char(F.Fecha_inicio, 'DD/MM/YYYY') AS inicio,
                to_char(F.Fecha_limite, 'DD/MM/YYYY') AS limite,
                COUNT(T.ID_tarea) AS total,
                COUNT(T.ID_tarea) FILTER (WHERE T.Estado <> 'Completado' AND T.Fecha_limite < NOW()) AS atrasadas,
                COALESCE(ROUND(100.0 * COUNT(T.ID_tarea) FILTER (WHERE T.Estado = 'Completado')
                    / NULLIF(COUNT(T.ID_tarea), 0)), 0) AS avance,
                CASE
                    WHEN F.Fecha_inicio > CURRENT_DATE THEN 'Pendiente'
                    WHEN F.Fecha_limite < CURRENT_DATE THEN 'Cerrada'
                    ELSE 'En curso'
                END AS estado
            FROM Fase F
            LEFT JOIN Tarea T ON T.ID_fase = F.ID_fase
            WHERE F.ID_proyecto = $id_proyecto
            GROUP BY F.ID_fase
            ORDER BY F.Orden";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "El proyecto no tiene fases";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Orden</th>\n";
        echo "\t\t<th>Nombre</th>\n";
        echo "\t\t<th>Inicio</th>\n";
        echo "\t\t<th>Limite</th>\n";
        echo "\t\t<th>Estado</th>\n";
        echo "\t\t<th>Tareas</th>\n";
        echo "\t\t<th>Atrasadas</th>\n";
        echo "\t\t<th>Avance</th>\n";
        echo "\t\t<th></th>\n";
        if($es_dueno){
            echo "\t\t<th></th>\n";
            echo "\t\t<th></th>\n";
        }
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_fase = $line["id_fase"];
            $nombre = htmlspecialchars($line["nombre"]);
            $orden = $line["orden"];
            $inicio = $line["inicio"];
            $limite = $line["limite"];
            $estado = $line["estado"];
            $total = $line["total"];
            $atrasadas = $line["atrasadas"];
            $avance = $line["avance"];

            echo "\t<tr>\n";
            echo "\t\t<td>$orden</td>\n";
            echo "\t\t<td>$nombre</td>\n";
            echo "\t\t<td>$inicio</td>\n";
            echo "\t\t<td>$limite</td>\n";
            echo "\t\t<td>$estado</td>\n";
            echo "\t\t<td>$total</td>\n";
            echo "\t\t<td>$atrasadas</td>\n";
            echo "\t\t<td><div class='barra'><div class='progreso' style='width:$avance%'></div></div>$avance%</td>\n";
            echo "\t\t<td><a href='../tareas/listado.php?ID_fase=$id_fase'>Tareas</a> |
                <a href='../tareas/kanban.php?ID_proyecto=$id_proyecto&ID_fase=$id_fase'>Kanban</a></td>\n";
            if ($es_dueno){
                echo "\t\t<td><a href='editar.php?ID_fase=$id_fase'>
                    <button class='mini'> Editar </button></a></td>\n";
                echo "\t\t<td><a href='eliminar.php?ID_fase=$id_fase'>
                    <button class='mini peligro'> Eliminar </button></a></td>\n";
            }
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    }

    pg_close($conn);
?>
    <center>
        <?php
            if($es_dueno){
                echo "<a href='agregar.php?ID_proyecto=$id_proyecto'> Agregar fase </a><br>";
            }
        ?>
        <a href="../tareas/kanban.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Kanban del proyecto </a><br>
        <a href="../proyectos/listado.php"> Listado de proyectos </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
