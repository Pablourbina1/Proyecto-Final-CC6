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
        } else if(substr($limite, 0, 10) > $fase["fecha_limite"]){
            $mensaje = "<p class='error'>Error: La fecha limite no puede ser despues del cierre de la fase ({$fase['fecha_limite']})</p>";
        } else {
            $query = "INSERT INTO Tarea (Titulo, Descripcion, Prioridad, Fecha_limite, ID_fase)
                VALUES ('$titulo', '$descripcion', '$prioridad', '$limite', $id_fase) RETURNING ID_tarea";
            $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
            $id_tarea = pg_fetch_assoc($result)["id_tarea"];

            foreach($asignados as $id_usuario){
                $id_usuario = (int)$id_usuario;
                $query = "INSERT INTO Asignacion VALUES ($id_tarea, $id_usuario)";
                pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
            }

            $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                VALUES ('creo la tarea $titulo', $id, $id_proyecto)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $mensaje = "<p class='exito'>La tarea fue creada exitosamente</p>";
        }
    }

    $query = "SELECT ID_usuario, Nombre FROM Usuario
        WHERE ID_usuario = (SELECT ID_lider FROM Proyecto WHERE ID_proyecto = $id_proyecto)
        OR ID_usuario IN (SELECT ID_usuario FROM Miembro WHERE ID_proyecto = $id_proyecto)
        ORDER BY Nombre";
    $usuarios = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Tareas - Agregar
     </title>
  </head>
  <body>
    <h1>Agregar tarea a <?php echo htmlspecialchars($fase["nombre"]); ?></h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <b>Titulo</b>
        <input type = "text" name = "Titulo" maxlength = "80" required><br>

        <b>Descripcion</b>
        <textarea name = "Descripcion" maxlength = "500" rows = "4"></textarea><br>

        <b>Prioridad</b>
        <select name = "Prioridad" required>
            <option value = "Alta">Alta</option>
            <option value = "Media" selected>Media</option>
            <option value = "Baja">Baja</option>
        </select><br>

        <b>Fecha y hora limite</b>
        <input type = "datetime-local" name = "Fecha_limite" required><br>

        <b>Asignar a</b>
        <div class = "checks">
        <?php
            while($u = pg_fetch_assoc($usuarios)){
                echo "<label><input type='checkbox' name='Asignados[]' value='{$u['id_usuario']}'> " . htmlspecialchars($u['nombre']) . "</label>\n";
            }
        ?>
        </div>

        <button type = "submit">
                Enviar
            </button>

    </form>
    <center>
         <a href="listado.php?ID_fase=<?php echo $id_fase; ?>"> Listado de tareas </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
