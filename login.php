<?php
    session_start();
    if(isset($_SESSION["id"])){
        header("Location: index.php");
        exit;
    }
    require __DIR__ . '/postsql.php';

    $mensaje = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $correo = pg_escape_string($conn, $_POST["Correo"]);
        $contrasena = $_POST["Contrasena"];

        $query = "SELECT * FROM Usuario WHERE Correo = '$correo'";
        $result = pg_query($conn, $query) or die ('La query fallo: ' .pg_last_error($conn));
        $usuario = pg_fetch_assoc($result);

        if(!$usuario || !password_verify($contrasena, $usuario["contrasena"])){
            $mensaje = "<p class='error'>Error: Las credenciales son incorrectas</p>";
        } else {
            $_SESSION["id"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            $_SESSION["lider"] = $usuario["rol"] == "Lider";

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
    <title>Login</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="menu">
        <h1>WinniTask</h1>
        <?php
            echo $mensaje;
        ?>
        <form method = "post" autocomplete = "off">
            <b>Correo</b>
            <input type = "text" name = "Correo" required><br>

            <b>Contraseña</b>
            <input type = "password" name = "Contrasena" required><br>

            <button type = "submit">
                    Enviar
                </button>

        </form>

        <center>
             <a href="register.php"> Registrarse </a><br>
         </center>
    </div>
</body>
</html>
