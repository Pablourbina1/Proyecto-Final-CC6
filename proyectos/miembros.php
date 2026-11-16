<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_proyecto = (int)$_GET["ID_proyecto"];
    require __DIR__ . '/../permisos.php';
    $id = $_SESSION["id"];
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST" && $es_dueno){
        $id_usuario = (int)$_POST["ID_usuario"];
        $result = pg_query($conn, "SELECT Nombre FROM Usuario WHERE ID_usuario = $id_usuario");
        $usuario = pg_fetch_assoc($result);
        $nombre_usuario = pg_escape_string($conn, $usuario["nombre"]);

        if($_POST["accion"] == "agregar"){
            $query = "INSERT INTO Miembro VALUES ($id_proyecto, $id_usuario)";
            $result = pg_query($conn, $query);
            if(!$result){
                $mensaje = "<p class='error'>Error: El usuario ya es miembro del proyecto</p>";
            } else {
                $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                    VALUES ('agrego a $nombre_usuario al proyecto', $id, $id_proyecto)";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
                $mensaje = "<p class='exito'>Miembro agregado</p>";
            }
        } else if($_POST["accion"] == "quitar"){
            $query = "SELECT T.Titulo FROM Tarea T
                JOIN Fase F ON T.ID_fase = F.ID_fase
                JOIN Asignacion A ON A.ID_tarea = T.ID_tarea
                WHERE F.ID_proyecto = $id_proyecto AND A.ID_usuario = $id_usuario
                AND (SELECT COUNT(*) FROM Asignacion A2 WHERE A2.ID_tarea = T.ID_tarea) = 1";
            $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            if(pg_num_rows($result) > 0){
                $tareas = [];
                while($t = pg_fetch_assoc($result)){
                    $tareas[] = htmlspecialchars($t["titulo"]);
                }
                $mensaje = "<p class='error'>Error: No se puede quitar a " . htmlspecialchars($usuario["nombre"]) . " porque es el unico asignado de: "
                    . implode(", ", $tareas) . ". Reasigna esas tareas primero</p>";
            } else {
                $query = "DELETE FROM Asignacion WHERE ID_usuario = $id_usuario AND ID_tarea IN (
                    SELECT T.ID_tarea FROM Tarea T JOIN Fase F ON T.ID_fase = F.ID_fase
                    WHERE F.ID_proyecto = $id_proyecto)";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

                $query = "DELETE FROM Miembro WHERE ID_proyecto = $id_proyecto AND ID_usuario = $id_usuario";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

                $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                    VALUES ('quito a $nombre_usuario del proyecto', $id, $id_proyecto)";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
                $mensaje = "<p class='exito'>Miembro eliminado del proyecto</p>";
            }
        }
    }
?>
<html>
  <head>
   <link rel = "stylesheet" href = "../style.css">
   <meta charset="UTF-8">
     <title>
        Proyectos - Miembros
     </title>
  </head>
  <body>
    <h1>Miembros de <?php echo htmlspecialchars($nombre_proyecto); ?></h1>
    <?php
        echo $mensaje;
    ?>

<?php
    $query = "SELECT U.ID_usuario, U.Nombre, U.Correo, U.Rol
            FROM Miembro M
            JOIN Usuario U ON M.ID_usuario = U.ID_usuario
            WHERE M.ID_proyecto = $id_proyecto
            ORDER BY U.Nombre";

    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($result) == 0){
        echo "El proyecto no tiene miembros";
    } else {
        echo "<table>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Nombre</th>\n";
        echo "\t\t<th>Correo</th>\n";
        echo "\t\t<th>Rol</th>\n";
        echo "\t\t<th></th>\n";
        echo "\t</tr>\n";

        while ($line = pg_fetch_assoc($result)) {
            $id_usuario = $line["id_usuario"];
            $nombre = htmlspecialchars($line["nombre"]);
            $correo = htmlspecialchars($line["correo"]);
            $rol = $line["rol"];

            echo "\t<tr>\n";
            echo "\t\t<td>$nombre</td>\n";
            echo "\t\t<td>$correo</td>\n";
            echo "\t\t<td>$rol</td>\n";
            if($es_dueno){
                echo "\t\t<td><form method='post' class='en-linea'>
                    <input type='hidden' name='ID_usuario' value='$id_usuario'>
                    <button class='mini peligro' name='accion' value='quitar'>Quitar</button>
                    </form></td>\n";
            }
            echo "\t</tr>\n";
        }
        echo "</table>\n";
    }

    if($es_dueno){
        $query = "SELECT ID_usuario, Nombre, Correo FROM Usuario
            WHERE ID_usuario <> $id
            AND ID_usuario NOT IN (SELECT ID_usuario FROM Miembro WHERE ID_proyecto = $id_proyecto)
            ORDER BY Nombre";
        $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

        echo "<h2>Agregar miembro</h2>\n";
        if(pg_num_rows($result) == 0){
            echo "No hay mas usuarios para agregar";
        } else {
            echo "<form method='post'>\n";
            echo "<select name='ID_usuario' required>\n";
            while($u = pg_fetch_assoc($result)){
                echo "<option value='{$u['id_usuario']}'>" . htmlspecialchars($u['nombre']) . " - " . htmlspecialchars($u['correo']) . "</option>\n";
            }
            echo "</select>\n";
            echo "<button type='submit' name='accion' value='agregar'>Agregar</button>\n";
            echo "</form>\n";
        }
    }

    pg_close($conn);
?>
    <center>
        <a href="listado.php"> Listado de proyectos </a><br>
        <a href="../index.php"> Menu Principal</a>
     </center>
  </body>
</html>
