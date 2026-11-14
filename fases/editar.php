<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_fase = (int)$_GET["ID_fase"];

    $query = "SELECT * FROM Fase WHERE ID_fase = $id_fase";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $line = pg_fetch_assoc($result);
    if(!$line){
        header("Location: ../proyectos/listado.php");
        exit;
    }
    $id_proyecto = $line["id_proyecto"];
    require __DIR__ . '/../permisos.php';
    if(!$es_dueno){
        header("Location: listado.php?ID_proyecto=$id_proyecto");
        exit;
    }
    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $id = $_SESSION["id"];
        $nombre = pg_escape_string($conn, $_POST["Nombre"]);
        $orden = (int)$_POST["Orden"];
        $inicio = pg_escape_string($conn, $_POST["Fecha_inicio"]);
        $limite = pg_escape_string($conn, $_POST["Fecha_limite"]);

        $query = "SELECT MAX(Fecha_limite) AS anterior FROM Fase
            WHERE ID_proyecto = $id_proyecto AND Orden < $orden AND ID_fase <> $id_fase";
        $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
        $anterior = pg_fetch_assoc($result)["anterior"];

        if($limite < $inicio){
            $mensaje = "<p class='error'>Error: La fecha limite debe ser despues de la fecha de inicio</p>";
        } else if($anterior != null && $inicio < $anterior){
            $mensaje = "<p class='error'>Error: La fase debe iniciar despues del $anterior (fin de la fase anterior)</p>";
        } else {
            $query = "UPDATE Fase SET Nombre = '$nombre', Orden = $orden,
                Fecha_inicio = '$inicio', Fecha_limite = '$limite'
                WHERE ID_fase = $id_fase";
            $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            if($limite != $line["fecha_limite"]){
                $accion = "cambio la fecha limite de la fase $nombre al $limite";
            } else {
                $accion = "edito la fase $nombre";
            }
            $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                VALUES ('$accion', $id, $id_proyecto)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $mensaje = "<p class='exito'>La fase fue editada exitosamente</p>";
            $result = pg_query($conn, "SELECT * FROM Fase WHERE ID_fase = $id_fase");
            $line = pg_fetch_assoc($result);
        }
    }
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Fases - Editar
     </title>
  </head>
  <body>
    <h1>Editar fase</h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <?php
            $nombre = htmlspecialchars($line["nombre"]);
            $orden = $line["orden"];
            $inicio = $line["fecha_inicio"];
            $limite = $line["fecha_limite"];

            echo "<b>Nombre de la fase</b>\n";
            echo "<input type = 'text' name = 'Nombre' maxlength = '50' value = '$nombre' required><br>\n";

            echo "<b>Orden</b>\n";
            echo "<input type = 'number' name = 'Orden' min = '1' value = '$orden' required><br>\n";

            echo "<b>Fecha de inicio</b>\n";
            echo "<input type = 'date' name = 'Fecha_inicio' value = '$inicio' required><br>\n";

            echo "<b>Fecha limite</b>\n";
            echo "<input type = 'date' name = 'Fecha_limite' value = '$limite' required><br>\n";
        ?>

        <button type = "submit">
                Enviar
            </button>

    </form>
    <center>
         <a href="listado.php?ID_proyecto=<?php echo $id_proyecto; ?>"> Listado de fases </a><br>
         <a href="../index.php"> Menu Principal </a>
     </center>
</body>
</html>
