<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_fase = (int)$_GET["ID_fase"];

    $result = pg_query($conn, "SELECT * FROM Fase WHERE ID_fase = $id_fase") or die('La query fallo: ' .pg_last_error($conn));
    $fase = pg_fetch_assoc($result);
    if(!$fase){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $id_proyecto = $fase["id_proyecto"];
    require __DIR__ . '/../permisos.php';
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Tareas - Listado
     </title>
  </head>
  <body>
    <h1><?php echo htmlspecialchars($nombre_proyecto) . " - " . htmlspecialchars($fase["nombre"]); ?></h1>
    <p>Fase del <?php echo $fase["fecha_inicio"] . " al " . $fase["fecha_limite"]; ?></p>

<?php
    $query = "SELECT T.ID_tarea, T.Titulo, T.Prioridad, T.Estado,
                to_char(T.Fecha_limite, 'DD/MM/YYYY HH24:MI') AS limite,
                CASE
                    WHEN T.Estado = 'Completado' THEN 'Completada'
                    WHEN T.Fecha_limite < NOW() THEN 'Atrasada'
                    WHEN T.Fecha_limite < NOW() + INTERVAL '48 hours' THEN 'Por vencer'
                    ELSE 'A tiempo'
                END AS alerta,
                COALESCE(string_agg(U.Nombre, ', '), 'Sin asignar') AS asignados
            FROM Tarea T
            LEFT JOIN Asignacion A ON A.ID_tarea = T.ID_tarea
            LEFT JOIN Usuario U ON U.ID_usuario = A.ID_usuario
            WHERE T.ID_fase = $id_fase
            GROUP BY T.ID_tarea
            ORDER BY T.Fecha_limite";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "La fase no tiene tareas";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Tarea</th>\n";
        echo "\t\t<th>Prioridad</th>\n";
        echo "\t\t<th>Estado</th>\n";
        echo "\t\t<th>Fecha limite</th>\n";
        echo "\t\t<th>Alerta</th>\n";
        echo "\t\t<th>Asignados</th>\n";
        echo "\t\t<th></th>\n";
        if($es_dueno){
            echo "\t\t<th></th>\n";
            echo "\t\t<th></th>\n";
        }
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_tarea = $line["id_tarea"];
            $titulo = htmlspecialchars($line["titulo"]);
            $prioridad = $line["prioridad"];
            $estado = $line["estado"];
            $limite = $line["limite"];
            $alerta = $line["alerta"];
            $clase = strtolower(str_replace(" ", "-", $alerta));
            $asignados = htmlspecialchars($line["asignados"]);

            echo "\t<tr>\n";
            echo "\t\t<td>$titulo</td>\n";
            echo "\t\t<td><span class='etiqueta $prioridad'>$prioridad</span></td>\n";
            echo "\t\t<td>$estado</td>\n";
            echo "\t\t<td>$limite</td>\n";
            echo "\t\t<td><span class='etiqueta $clase'>$alerta</span></td>\n";
            echo "\t\t<td>$asignados</td>\n";
            echo "\t\t<td><a href='detalle.php?ID_tarea=$id_tarea'>Ver</a></td>\n";
            if ($es_dueno){
                echo "\t\t<td><a href='editar.php?ID_tarea=$id_tarea'>
                    <button class='mini'> Editar </button></a></td>\n";
                echo "\t\t<td><a href='eliminar.php?ID_tarea=$id_tarea'>
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
                echo "<a href='agregar.php?ID_fase=$id_fase'> Agregar tarea </a><br>";
            }
        ?>
        <a href="kanban.php?ID_proyecto=<?php echo $id_proyecto; ?>&ID_fase=<?php echo $id_fase; ?>"> Kanban de la fase </a><br>
        <a href="../fases/listado.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Listado de fases </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
