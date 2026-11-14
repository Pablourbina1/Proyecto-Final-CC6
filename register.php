<?php
    session_start();
    require __DIR__ . '/postsql.php';

    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nombre = pg_escape_string($conn, $_POST["Nombre"]);
        $correo = pg_escape_string($conn, $_POST["Correo"]);
        $contrasena = password_hash($_POST["Contrasena"], PASSWORD_DEFAULT);
        $rol = $_POST["Rol"] == "Lider" ? "Lider" : "Colaborador";

        $check = pg_query($conn, "SELECT * FROM Usuario WHERE Correo = '$correo'");
        if(pg_num_rows($check) > 0){
            $mensaje = "<p class='error'>Error: Ya existe una cuenta con ese correo</p>";
        } else {
            $query = "INSERT INTO Usuario (Nombre, Correo, Contrasena, Rol)
                VALUES ('$nombre', '$correo', '$contrasena', '$rol') RETURNING ID_usuario";
            $result = pg_query($conn, $query) or die('La query fallo: ' .pg_last_error($conn));
            $usuario = pg_fetch_assoc($result);

            $_SESSION["id"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $_POST["Nombre"];
            $_SESSION["rol"] = $rol;
            $_SESSION["lider"] = $rol == "Lider";
            header("Location: index.php");
            exit;
        }
        pg_close($conn);
    }
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="menu">
        <h1>Registrarse</h1>
        <?php
            echo $mensaje;
        ?>
        <form method = "post" autocomplete = "off">

            <b>Nombre</b>
            <input type = "text" name = "Nombre" maxlength = "50" required><br>

            <b>Correo</b>
            <input type = "email" name = "Correo" maxlength = "80" required><br>

            <b>Contraseña</b>
            <input type = "password" name = "Contrasena" required><br>

            <b>Rol</b>
            <select name = "Rol" required>
                <option value = "Colaborador">Colaborador</option>
                <option value = "Lider">Lider</option>
            </select><br>

            <button type = "submit">
                    Enviar
                </button>

        </form>

        <center>
             <a href="login.php"> Iniciar sesion </a><br>
         </center>
    </div>
</body>
</html>
