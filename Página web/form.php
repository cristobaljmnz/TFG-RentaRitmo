<?php
session_start();
include("con_db.php");

function isAuthenticated() {
    return isset($_SESSION['email']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title> Registrarse</title>
    <meta charset = "utf-8">
</head>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="shortcut icon" href="./images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/estilos.css">

    <meta name="theme-color" content="#2091F9">

    <meta name="title" content="Inversión inmobiliaria Granada">
    <meta name="description"
        content="Herramienta para buscar piso o casa en Granada, comprar para alquilar y sacar beneficio o rentabilidad">

</head>

<body>

    <header class="navegador pag-aux">
        <nav class="nav container">
            <div class="nav__logo">
                <a href="./index.php">
                    <img src="./images/logo.png" alt="Logo de RentaRitmo" class="nav__logo-img">
                </a>
                <h2 class="nav__title">RentaRitmo</h2>
          
            </div>

            <ul class="nav__link nav__link--menu">
                <li class="nav__items">
                    <a href="./index.php" class="nav__links">Inicio</a>
                </li>
                <li class="nav__items">
                    <a href="./coleccion_viviendas.php" class="nav__links">Viviendas</a>
                </li>
                <!-- <li class="nav__items">
                    <a href="#" class="nav__links">Acerca de</a>
                </li> -->
                <?php if (isAuthenticated()): ?>
                    <li class="nav__items">
                        <a href="./mis_inversiones.php" class="nav__links">Mis inversiones</a>
                    </li>
                    <li class="nav__items">
                        <a href="./cerrar_sesion.php" class="nav__links">Cerrar sesión</a>
                    </li>
                    
                <?php else: ?>
                    <li class="nav__items">
                        <a href="./form_login.php" class="nav__links">Iniciar sesión</a>
                    </li>
                <?php endif; ?>
                <img src="./images/close.svg" alt="Cerrar menu" class="nav__close">
            </ul>

            <div class="nav__menu">
                <img src="./images/menu.svg" class="nav__img">
            </div>
            <?php if (isAuthenticated()): ?>
                <div class="nav__welcome">
                    <div class="nav__user-initial"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
                </div>
            <?php endif; ?>
        </nav>

        
    </header>
    
    <main>
        <section class="formulario">
            <form method="post" id="registerForm">
                <h1> Registrarse como usuario </h1>
                <input type= "text" name= "Nombre" placeholder="Nombre">
                <input type= "text" name= "Apellidos" placeholder="Apellidos">
                <input type= "email" name= "Email" placeholder="Email *">
                <input type= "password" name= "Contraseña" placeholder="Contraseña *">
                <input type= "submit" name= "Regístrate">
            </form>
            <?php 
            include("registrar.php");
            ?>
            <h3 class="ok" >¿Ya tienes cuenta? <a href="./form_login.php" style="color: #333333;">Inicia sesión aquí</a></h3>
        </section>
    </main>
        
    <footer class="footer">
        <section class="footer__container container">
            <nav class="nav nav--footer">
                <h2 class="footer__title">RentaRitmo</h2>

                <ul class="nav__link nav__link--footer">
                    <li class="nav__items">
                        <a href="./index.php" class="nav__links">Inicio</a>
                    </li>
                    <li class="nav__items">
                        <a href="./coleccion_viviendas.php" class="nav__links">Viviendas</a>
                    </li>
                    <!-- <li class="nav__items">
                        <a href="#" class="nav__links">Acerca de</a>
                    </li> -->
                    <?php if (isAuthenticated()): ?>
                        <li class="nav__items">
                            <a href="./mis_inversiones.php" class="nav__links">Mis inversiones</a>
                        </li>
                        <li class="nav__items">
                            <a href="./cerrar_sesion.php" class="nav__links">Cerrar sesión</a>
                        </li>
                        
                    <?php else: ?>
                        <li class="nav__items">
                            <a href="./form_login.php" class="nav__links">Iniciar sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </section>

        <section class="footer__copy container">
            <div class="footer__social">
                <a href="https://www.linkedin.com/in/cristobaljmnz/" class="footer__icons"><img src="./images/linkedIn.svg" class="footer__img"></a>
                <a href="https://drive.google.com/file/d/14oWTSOa__KZPGSz8ML08Ur96P7tPZXoP/view?usp=share_link" class="footer__icons"><img src="./images/cv.svg" class="footer__img"></a>
            </div>

            <h3 class="footer__copyright">Creado por &copy; Cristóbal Jiménez Álvarez</h3>
        </section>
    </footer>

    <script src="./js/submit.js"></script>
    <script src="./js/questions.js"></script>
    <script src="./js/menu.js"></script>
</body>

</html>