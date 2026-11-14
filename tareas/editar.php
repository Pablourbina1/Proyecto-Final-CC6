<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_tarea = (int)$_GET["ID_tarea"];

    $query = "SELECT T.*, to_char(T.Fecha_limite, 'YYYY-MM-DD\"T\"HH24:MI') AS limite,
            F.ID_proyecto, F.Fecha_limite AS cierre_fase
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
        $id = $_SESSION["id"];
        $titulo = pg_escape_string($conn, $_POST["Titulo"]);
        $descripcion = pg_escape_string($conn, $_POST["Descripcion"]);
        $prioridad = pg_escape_string($conn, $_POST["Prioridad"]);
        $limite = pg_escape_string($conn, $_POST["Fecha_limite"]);
        $asignados = $_POST["Asignados"] ?? [];

        if(count($asignados) == 0){
            $mensaje = "<p class='error'>Error: La tarea debe tener al menos un usuario asignado</p>";
        } else if(substr($limite, 0, 10) > $line["cierre_fase"]){
            $mensaje = "<p class='error'>Error: La fecha limite no puede ser despues del cierre de la fase ({$line['cierre_fase']})</p>";
        } else {
            $query = "UPDATE Tarea SET Titulo = '$titulo', Descripcion = '$descripcion',
                Prioridad = '$prioridad', Fecha_limite = '$limite'
                WHERE ID_tarea = $id_tarea";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            pg_query($conn, "DELETE FROM Asignacion WHERE ID_tarea = $id_tarea") or die('La query fallo: ' .pg_last_error($conn));
            foreach($asignados as $id_usuario){
                $id_usuario = (int)$id_usuario;
                $query = "INSERT INTO Asignacion VALUES ($id_tarea, $id_usuario)";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
            }

            if($limite != $line["limite"]){
                $accion = "cambio la fecha limite de la tarea $titulo al " . str_replace("T", " ", $limite);
            } else {
                $accion = "edito la tarea $titulo";
            }
            $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                VALUES ('$accion', $id, $id_proyecto)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $mensaje = "<p class='exito'>La tarea fue editada exitosamente</p>";
            $query = "SELECT T.*, to_char(T.Fecha_limite, 'YYYY-MM-DD\"T\"HH24:MI') AS limite
                FROM Tarea T WHERE T.ID_tarea = $id_tarea";
            $result = pg_query($conn, $query);
            $line = pg_fetch_assoc($result);
        }
    }

    $query = "SELECT U.ID_usuario, U.Nombre,
            EXISTS (SELECT 1 FROM Asignacion A WHERE A.ID_tarea = $id_tarea AND A.ID_usuario = U.ID_usuario) AS asignado
        FROM Usuario U
        WHERE U.ID_usuario = (SELECT ID_lider FROM Proyecto WHERE ID_proyecto = $id_proyecto)
        OR U.ID_usuario IN (SELECT ID_usuario FROM Miembro WHERE ID_proyecto = $id_proyecto)
        ORDER BY U.Nombre";
    $usuarios = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Tareas - Editar
     </title>
  </head>
  <body>
    <h1>Editar tarea</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            $titulo = htmlspecialchars($line["titulo"]);
            $descripcion = htmlspecialchars($line["descripcion"] ?? "");
            $prioridad = $line["prioridad"];
            $limite = $line["limite"];

            echo "<b>Titulo</b>\n";
            echo "<input type = 'text' name = 'Titulo' maxlength = '80' value = '$titulo' required><br>\n";

            echo "<b>Descripcion</b>\n";
            echo "<textarea name = 'Descripcion' maxlength = '500' rows = '4'>$descripcion</textarea><br>\n";

            echo "<b>Prioridad</b>\n";
            echo "<select name = 'Prioridad' required>\n";
            foreach(["Alta", "Media", "Baja"] as $p){
                $sel = $p == $prioridad ? "selected" : "";
                echo "<option value = '$p' $sel>$p</option>\n";
            }
            echo "</select><br>\n";

            echo "<b>Fecha y hora limite</b>\n";
            echo "<input type = 'datetime-local' name = 'Fecha_limite' value = '$limite' required><br>\n";

            echo "<b>Asignar a</b>\n";
            echo "<div class = 'checks'>\n";
            while($u = pg_fetch_assoc($usuarios)){
                $chk = $u["asignado"] == "t" ? "checked" : "";
                echo "<label><input type='checkbox' name='Asignados[]' value='{$u['id_usuario']}' $chk> " . htmlspecialchars($u['nombre']) . "</label>\n";
            }
            echo "</div>\n";
        ?>

        <button type = "submit">
                Enviar
            </button>

    </form>
    <center>
         <a href="detalle.php?ID_tarea=<?php echo $id_tarea; ?>"> Ver tarea </a><br>
         <a href="listado.php?ID_fase=<?php echo $id_fase; ?>"> Listado de tareas </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
