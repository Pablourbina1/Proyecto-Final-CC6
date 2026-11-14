<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
    $ver = $_GET["ver"] ?? "pendientes";

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
        Mis tareas
     </title>
  </head>
  <body>
    <h1>Mis tareas</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "GET" class = "filtro">
        <b>Mostrar</b>
        <select name = "ver">
            <option value = "pendientes" <?php if($ver == "pendientes") echo "selected"; ?>>Pendientes</option>
            <option value = "todas" <?php if($ver == "todas") echo "selected"; ?>>Todas</option>
        </select>
        <input type = "submit" value = "Filtrar">
    </form>

<?php
    $filtro = "";
    if($ver == "pendientes"){
        $filtro = "AND T.Estado <> 'Completado'";
    }

    $query = "SELECT T.ID_tarea, T.Titulo, T.Prioridad, T.Estado,
                P.Nombre AS proyecto, F.Nombre AS fase,
                to_char(T.Fecha_limite, 'DD/MM/YYYY HH24:MI') AS limite,
                CASE
                    WHEN T.Estado = 'Completado' THEN 'Completada'
                    WHEN T.Fecha_limite < NOW() THEN 'Atrasada'
                    WHEN T.Fecha_limite < NOW() + INTERVAL '48 hours' THEN 'Por vencer'
                    ELSE 'A tiempo'
                END AS alerta
            FROM Asignacion A
            JOIN Tarea T ON A.ID_tarea = T.ID_tarea
            JOIN Fase F ON T.ID_fase = F.ID_fase
            JOIN Proyecto P ON F.ID_proyecto = P.ID_proyecto
            WHERE A.ID_usuario = $id $filtro
            ORDER BY T.Fecha_limite";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "No tienes tareas asignadas";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Tarea</th>\n";
        echo "\t\t<th>Proyecto</th>\n";
        echo "\t\t<th>Fase</th>\n";
        echo "\t\t<th>Prioridad</th>\n";
        echo "\t\t<th>Estado</th>\n";
        echo "\t\t<th>Fecha limite</th>\n";
        echo "\t\t<th>Alerta</th>\n";
        echo "\t\t<th></th>\n";
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_tarea = $line["id_tarea"];
            $titulo = htmlspecialchars($line["titulo"]);
            $proyecto = htmlspecialchars($line["proyecto"]);
            $fase = htmlspecialchars($line["fase"]);
            $prioridad = $line["prioridad"];
            $estado = $line["estado"];
            $limite = $line["limite"];
            $alerta = $line["alerta"];
            $clase = strtolower(str_replace(" ", "-", $alerta));

            echo "\t<tr>\n";
            echo "\t\t<td>$titulo</td>\n";
            echo "\t\t<td>$proyecto</td>\n";
            echo "\t\t<td>$fase</td>\n";
            echo "\t\t<td><span class='etiqueta $prioridad'>$prioridad</span></td>\n";
            echo "\t\t<td>$estado</td>\n";
            echo "\t\t<td>$limite</td>\n";
            echo "\t\t<td><span class='etiqueta $clase'>$alerta</span></td>\n";
            echo "\t\t<td><a href='detalle.php?ID_tarea=$id_tarea'>Ver</a></td>\n";
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    }

    pg_close($conn);
?>
    <center>
        <a href="../proyectos/listado.php"> Mis proyectos </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
