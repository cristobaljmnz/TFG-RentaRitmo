<?php

if (isset($_POST['Regístrate'])) {
    include("con_db.php");
    $nombre = trim($_POST['Nombre']);
    $apellidos = trim($_POST['Apellidos']);
    $email = trim($_POST['Email']);
    $contraseña = trim($_POST['Contraseña']);
    $errores = [];

    // Validaciones
    if (empty($nombre)) {
        $errores[] = "Por favor, introduce tu nombre.";
    }

    if (empty($apellidos)) {
        $errores[] = "Por favor, introduce tus apellidos.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Por favor, introduce un correo electrónico válido.";
    }

    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $contraseña)) {
        $errores[] = "La contraseña debe ser alfanumérica y tener más de 8 caracteres.";
    }

    // Mostrar errores
    if (!empty($errores)) {
        foreach ($errores as $error) {
            echo "<h3 class='bad'>$error</h3>";
        }
    } else {
        // Comprobación de email duplicado
        $check_email = mysqli_query($conex, "SELECT * FROM usuario WHERE email='$email'");

        if (mysqli_num_rows($check_email) > 0) {
            echo "<h3 class='bad'>Lo sentimos, ese correo electrónico ya está registrado.</h3>";
        } else {
            // Hash de la contraseña
            $hashed_password = password_hash($contraseña, PASSWORD_DEFAULT);

            // Inserción en la base de datos
            $consulta = "INSERT INTO usuario (id_usuario, email, password, create_time, nombre, apellidos) VALUES (NULL, '$email', '$hashed_password', current_timestamp(), '$nombre', '$apellidos')";
            $resultado = mysqli_query($conex, $consulta);

            if ($resultado) {
                echo "<h3 class='ok'>Has sido registrado exitosamente.</h3>";
            } else {
                echo "<h3 class='bad'>Lo sentimos, ha ocurrido un error al procesar tu registro. Por favor, inténtalo de nuevo más tarde.</h3>";
                echo "Error: " . mysqli_error($conex);
            }
        }
    }
}

?>
