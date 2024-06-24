<?php

include("con_db.php");
session_start();

if (isset($_POST['IniciarSesion'])) {
    $email = trim($_POST['Email']);
    $contraseña = trim($_POST['Contraseña']);
    
    // Consulta a la base de datos para obtener el usuario
    $validar_login = mysqli_query($conex, "SELECT * FROM usuario WHERE email='$email'");

    if (mysqli_num_rows($validar_login) > 0) {
        $user = mysqli_fetch_assoc($validar_login);
        $nombre = $user['nombre'];
        $id_usuario = $user['id_usuario'];
        $hashed_password = $user['password'];

        // Verificar la contraseña usando password_verify
        if (password_verify($contraseña, $hashed_password)) {
            $_SESSION['email'] = $email;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['id_usuario'] = $id_usuario;
            header("Location: ./index.php");
            exit();
        } else {
            ?> 
                <h3 class="bad">Contraseña incorrecta. Por favor, inténtalo de nuevo.</h3>
            <?php
        }
    } else {
        ?> 
            <h3 class="bad">El correo electrónico no está registrado. Por favor, verifica tus credenciales e inténtalo de nuevo.</h3>
        <?php
    }
}
?>