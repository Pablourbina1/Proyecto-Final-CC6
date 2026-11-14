<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
    $id_proyecto = isset($_GET["ID_proyecto"]) ? (int)$_GET["ID_proyecto"] : 0;
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Bitacora
     </title>
  </head>
  <body>
    <h1>Bitacora de actividad</h1>

    <form method = "GET" class = "filtro">
        <b>Proyecto</b>
        <select name = "ID_proyecto">
            <option value = "0">Todos</option>
            <?php
                $proyectos = "SELECT ID_proyecto, Nombre FROM Proyecto
                    WHERE ID_lider = $id
                    OR ID_proyecto IN (SELECT ID_proyecto FROM Miembro WHERE ID_usuario = $id)
                    ORDER BY Nombre";
                $resultP = pg_query($conn, $proyectos);
                while($p = pg_fetch_assoc($resultP)){
                    $sel = $p["id_proyecto"] == $id_proyecto ? "selected" : "";
                    echo "<option value='{$p['id_proyecto']}' $sel>" . htmlspecialchars($p['nombre']) . "</option>";
                }
            ?>
        </select>
        <input type = "submit" value = "Filtrar">
    </form>

<?php
    $filtro = "";
    if($id_proyecto > 0){
        $filtro = "AND B.ID_proyecto = $id_proyecto";
    }

    $query = "SELECT to_char(B.Fecha, 'DD/MM/YYYY HH24:MI') AS fecha, B.Accion,
                U.Nombre AS usuario, P.Nombre AS proyecto
            FROM Bitacora B
            JOIN Usuario U ON B.ID_usuario = U.ID_usuario
            JOIN Proyecto P ON B.ID_proyecto = P.ID_proyecto
            WHERE (P.ID_lider = $id
                OR P.ID_proyecto IN (SELECT ID_proyecto FROM Miembro WHERE ID_usuario = $id))
                $filtro
            ORDER BY B.Fecha DESC, B.ID_bitacora DESC
            LIMIT 200";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "No hay actividad registrada";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Fecha</th>\n";
        echo "\t\t<th>Proyecto</th>\n";
        echo "\t\t<th>Actividad</th>\n";
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $fecha = $line["fecha"];
            $proyecto = htmlspecialchars($line["proyecto"]);
            $usuario = htmlspecialchars($line["usuario"]);
            $accion = htmlspecialchars($line["accion"]);

            echo "\t<tr>\n";
            echo "\t\t<td>$fecha</td>\n";
            echo "\t\t<td>$proyecto</td>\n";
            echo "\t\t<td><b>$usuario</b> $accion</td>\n";
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    }

    pg_close($conn);
?>
    <center>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
