<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id = $_SESSION["id"];
    $id_tarea = (int)$_POST["ID_tarea"];
    $estado = $_POST["Estado"];
    $volver = $_POST["volver"] ?? "mis_tareas.php";
    if(!str_starts_with($volver, "kanban.php") && !str_starts_with($volver, "detalle.php")
        && !str_starts_with($volver, "mis_tareas.php")){
        $volver = "mis_tareas.php";
    }

    $query = "SELECT T.Titulo, T.Estado, F.ID_proyecto
        FROM Tarea T JOIN Fase F ON T.ID_fase = F.ID_fase
        WHERE T.ID_tarea = $id_tarea";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $tarea = pg_fetch_assoc($result);
    if(!$tarea){
        header("Location: $volver");
        exit;
    }
    $id_proyecto = $tarea["id_proyecto"];
    require __DIR__ . '/../permisos.php';

    $result = pg_query($conn, "SELECT 1 FROM Asignacion WHERE ID_tarea = $id_tarea AND ID_usuario = $id");
    $asignado = pg_num_rows($result) > 0;

    if(!in_array($estado, ["Por hacer", "En progreso", "En revision", "Completado"])){
        $_SESSION["mensaje"] = "Estado no valido";
    } else if(!$es_dueno && !$asignado){
        $_SESSION["mensaje"] = "Solo los asignados o el lider pueden mover esta tarea";
    } else if(!$es_dueno && $estado == "Completado"){
        $_SESSION["mensaje"] = "Solo el lider puede marcar una tarea como Completado, muevela a En revision";
    } else if($estado != $tarea["estado"]){
        $query = "UPDATE Tarea SET Estado = '$estado' WHERE ID_tarea = $id_tarea";
        pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

        $titulo = pg_escape_string($conn, $tarea["titulo"]);
        $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
            VALUES ('movio la tarea $titulo a $estado', $id, $id_proyecto)";
        pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    }

    pg_close($conn);
    header("Location: $volver");
    exit;
?>