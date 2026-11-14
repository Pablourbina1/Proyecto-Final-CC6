<?php
    require __DIR__ . '/../auth.php';
    require __DIR__ . '/../postsql.php';
    $id_proyecto = (int)$_GET["ID_proyecto"];
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

//La fase debe iniciar despues de que termine la fase anterior
        $query = "SELECT MAX(Fecha_limite) AS anterior FROM Fase
            WHERE ID_proyecto = $id_proyecto AND Orden < $orden";
        $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
        $anterior = pg_fetch_assoc($result)["anterior"];

        if($limite < $inicio){
            $mensaje = "<p class='error'>Error: La fecha limite debe ser despues de la fecha de inicio</p>";
        } else if($anterior != null && $inicio < $anterior){
            $mensaje = "<p class='error'>Error: La fase debe iniciar despues del $anterior (fin de la fase anterior)</p>";
        } else {
            $query = "INSERT INTO Fase (Nombre, Orden, Fecha_inicio, Fecha_limite, ID_proyecto)
                VALUES ('$nombre', $orden, '$inicio', '$limite', $id_proyecto)";
            $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $query = "INSERT INTO Bitacora (Accion, ID_usuario, ID_proyecto)
                VALUES ('creo la fase $nombre', $id, $id_proyecto)";
            pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));

            $mensaje = "<p class='exito'>La fase fue creada exitosamente</p>";
        }
    }

    $query = "SELECT COALESCE(MAX(Orden), 0) + 1 AS siguiente FROM Fase WHERE ID_proyecto = $id_proyecto";
    $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
    $siguiente = pg_fetch_assoc($result)["siguiente"];
    pg_close($conn);
?>

<html>
  <head>
    <link rel = "stylesheet" href = "../style.css">
    <meta charset="UTF-8">
     <title>
         Fases - Agregar
     </title>
  </head>
  <body>
    <h1>Agregar fase a <?php echo htmlspecialchars($nombre_proyecto); ?></h1>
    <?php
        echo $mensaje;
    ?>

    <form method = "post">
        <b>Nombre de la fase</b>
        <input type = "text" name = "Nombre" maxlength = "50" required><br>

        <b>Orden</b>
        <input type = "number" name = "Orden" min = "1" value = "<?php echo $siguiente; ?>" required><br>

        <b>Fecha de inicio</b>
        <input type = "date" name = "Fecha_inicio" required><br>

        <b>Fecha limite</b>
        <input type = "date" name = "Fecha_limite" required><br>

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
