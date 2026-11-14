<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Proyectos - Listado
     </title>
  </head>
  <body>
    <h1>Mis proyectos</h1>

<?php
    $query = "SELECT P.ID_proyecto, P.Nombre, P.Descripcion, P.ID_lider, U.Nombre AS lider,
                (SELECT COUNT(*) FROM Miembro M WHERE M.ID_proyecto = P.ID_proyecto) AS miembros
            FROM Proyecto P
            JOIN Usuario U ON P.ID_lider = U.ID_usuario
            WHERE P.ID_lider = $id
                OR P.ID_proyecto IN (SELECT ID_proyecto FROM Miembro WHERE ID_usuario = $id)
            ORDER BY P.ID_proyecto";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "No hay proyectos";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Nombre</th>\n";
        echo "\t\t<th>Descripcion</th>\n";
        echo "\t\t<th>Lider</th>\n";
        echo "\t\t<th>Miembros</th>\n";
        echo "\t\t<th></th>\n";
        echo "\t\t<th></th>\n";
        echo "\t\t<th></th>\n";
        echo "\t\t<th></th>\n";
        echo "\t\t<th></th>\n";
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_proyecto = $line["id_proyecto"];
            $nombre = htmlspecialchars($line["nombre"]);
            $descripcion = htmlspecialchars($line["descripcion"] ?? "");
            $lider = htmlspecialchars($line["lider"]);
            $miembros = $line["miembros"];

            echo "\t<tr>\n";
            echo "\t\t<td>$nombre</td>\n";
            echo "\t\t<td>$descripcion</td>\n";
            echo "\t\t<td>$lider</td>\n";
            echo "\t\t<td>$miembros</td>\n";
            echo "\t\t<td><a href='../fases/listado.php?ID_proyecto=$id_proyecto'>Fases</a></td>\n";
            echo "\t\t<td><a href='../tareas/kanban.php?ID_proyecto=$id_proyecto'>Kanban</a></td>\n";
            echo "\t\t<td><a href='miembros.php?ID_proyecto=$id_proyecto'>Miembros</a></td>\n";
            if ($line["id_lider"] == $id){
                echo "\t\t<td><a href='editar.php?ID_proyecto=$id_proyecto'>
                    <button class='mini'> Editar </button></a></td>\n";
                echo "\t\t<td><a href='eliminar.php?ID_proyecto=$id_proyecto'>
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
            if($_SESSION["lider"]){
                echo "<a href=agregar.php> Crear proyecto </a><br>";
            }
        ?>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
