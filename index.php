<?php
    require __DIR__ . '/auth.php';
    require __DIR__ . '/postsql.php';
    $id = $_SESSION["id"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WinniTask</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="menu">

        <h1>WinniTask</h1>
        <?php
            echo "<p>Bienvenido " . htmlspecialchars($_SESSION["nombre"]) . " (" . $_SESSION["rol"] . ")</p>";
        ?>

        <a href="proyectos/listado.php" class = "button"><button>Mis proyectos</button></a><br>

        <?php
            if($_SESSION["lider"]){
                echo "<a href=proyectos/agregar.php class=button><button>Crear proyecto</button></a><br>";
            }
        ?>

        <a href="tareas/mis_tareas.php" class = "button"><button>Mis tareas</button></a><br>

        <a href="bitacora/listado.php" class = "button"><button>Bitacora</button></a><br>

        <a href="logout.php" class="button"><button>Cerrar sesion</button></a><br>
    </div>

    <div class="panel">
        <h2>Progreso de proyectos</h2>
<?php
    $query = "SELECT P.ID_proyecto, P.Nombre,
                COUNT(T.ID_tarea) AS total,
                COUNT(T.ID_tarea) FILTER (WHERE T.Estado = 'Completado') AS completadas,
                COUNT(T.ID_tarea) FILTER (WHERE T.Estado <> 'Completado' AND T.Fecha_limite < NOW()) AS atrasadas,
                COALESCE(ROUND(100.0 * COUNT(T.ID_tarea) FILTER (WHERE T.Estado = 'Completado')
                    / NULLIF(COUNT(T.ID_tarea), 0)), 0) AS avance
            FROM Proyecto P
            LEFT JOIN Fase F ON F.ID_proyecto = P.ID_proyecto
            LEFT JOIN Tarea T ON T.ID_fase = F.ID_fase
            WHERE P.ID_lider = $id
                OR P.ID_proyecto IN (SELECT ID_proyecto FROM Miembro WHERE ID_usuario = $id)
            GROUP BY P.ID_proyecto, P.Nombre
            ORDER BY P.Nombre";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "No tienes proyectos todavia";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Proyecto</th>\n";
        echo "\t\t<th>Tareas</th>\n";
        echo "\t\t<th>Completadas</th>\n";
        echo "\t\t<th>Atrasadas</th>\n";
        echo "\t\t<th>Avance</th>\n";
        echo "\t\t<th></th>\n";
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_proyecto = $line["id_proyecto"];
            $nombre = htmlspecialchars($line["nombre"]);
            $total = $line["total"];
            $completadas = $line["completadas"];
            $atrasadas = $line["atrasadas"];
            $avance = $line["avance"];

            echo "\t<tr>\n";
            echo "\t\t<td>$nombre</td>\n";
            echo "\t\t<td>$total</td>\n";
            echo "\t\t<td>$completadas</td>\n";
            if($atrasadas > 0){
                echo "\t\t<td><span class='etiqueta atrasada'>$atrasadas</span></td>\n";
            } else {
                echo "\t\t<td>0</td>\n";
            }
            echo "\t\t<td><div class='barra'><div class='progreso' style='width:$avance%'></div></div>$avance%</td>\n";
            echo "\t\t<td><a href='fases/listado.php?ID_proyecto=$id_proyecto'>Fases</a> |
                <a href='tareas/kanban.php?ID_proyecto=$id_proyecto'>Kanban</a></td>\n";
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    }

    pg_close($conn);
?>
    </div>

</body>
<footer>
      <p>Pablo David Urbina - 24001508</p>
      <p>Angel David Camargo - 24003664</p>
      <p>David Ortega - 24000532</p>
   </footer>
</html>
