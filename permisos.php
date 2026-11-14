<?php
//Requiere $id_proyecto definido antes de incluir este archivo
    $queryPerm = "SELECT ID_lider, Nombre FROM Proyecto WHERE ID_proyecto = $id_proyecto";
    $resultPerm = pg_query($conn, $queryPerm) or die('La query fallo: ' .pg_last_error($conn));
    if(pg_num_rows($resultPerm) == 0){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $proyecto = pg_fetch_assoc($resultPerm);
    $nombre_proyecto = $proyecto["nombre"];
    $es_dueno = $proyecto["id_lider"] == $_SESSION["id"];

    $queryPerm = "SELECT 1 FROM Miembro WHERE ID_proyecto = $id_proyecto AND ID_usuario = " . $_SESSION["id"];
    $resultPerm = pg_query($conn, $queryPerm) or die('La query fallo: ' .pg_last_error($conn));
    $es_miembro = pg_num_rows($resultPerm) > 0;

    if(!$es_dueno && !$es_miembro){
        header("Location: ../proyectos/listado.php");
        exit;
    }
?>