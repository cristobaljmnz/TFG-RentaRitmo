<?php
session_start();
include("con_db.php");

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['email']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentaRitmo</title>
    <link rel="shortcut icon" href="./images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/estilos.css">

    <meta name="theme-color" content="#2091F9">

    <meta name="title" content="Inversión inmobiliaria Granada">
    <meta name="description"
        content="Herramienta para buscar piso o casa en Granada, comprar para alquilar y sacar beneficio o rentabilidad">
</head>

<body>

    <header class="navegador main-page">
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
                    <a id="viviendas_link" href="./coleccion_viviendas.php" class="nav__links">Viviendas</a>
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

        <section class="navegador__container container">
            <h1 class="navegador__title">Haz tu inversión inmobiliaria en Granada</h1>
            <p class="navegador__paragraph">Más fácil que nunca</p>
            <a href="./form.php" class="cta">Regístrate</a>
        </section>
    </header>

    <main>
        <section class="container about">
            <h2 class="subtitle">¿Qué ofrece RentaRitmo?</h2>
            <p class="about__paragraph">Asesoramiento para comprar una vivienda y obtener una rentabilidad económica al alquilarla</p>

            <div class="about__main">
                <article class="about__icons">
                    <img src="./images/code.svg" class="about__icon">
                    <h3 class="about__title">Predicción</h3>
                    <p class="about__paragrah">Algoritmo que estima el precio de alquiler que tendrá cada vivienda que está a la venta</p>
                </article>
                <article class="about__icons">
                    <img src="./images/filter.svg" class="about__icon">
                    <h3 class="about__title">Primer filtro</h3>
                    <p class="about__paragrah">En lugar de emplear innumerables horas en estudiar cada vivienda por separado, obtendrás el inmueble que más se ajuste a ti con un solo click</p>
                </article>
                <article class="about__icons">
                    <img src="./images/bank.svg" class="about__icon">
                    <h3 class="about__title">¿Cuánto dinero tengo que tener?</h3>
                    <p class="about__paragrah">Calculamos todos los gastos relacionados con la compra y podrás saber a cuáles de los inmuebles tienes acceso con tu presupuesto</p>
                </article>
            </div>
        </section>

        <section class="container mapa">
            <h2 class="subtitle">Mapa de viviendas</h2>
            <section class="mapa__container">
                <article class="mapa__padding">
                <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1Ghi7TLWyEMB8Th4LIrHbVcW34hee9NQ&ehbc=2E312F" width="640" height="640">
                 </iframe>
                </article>
            </section>
        </section>
        

        <section class="testimony">
            <div class="testimony__container container">
                <section class="testimony__body testimony__body--show" data-id="1">
                    <div class="testimony__texts">
                        <h2 class="subtitle">¿Quién soy? </h2>
                        <p class="testimony__course subtitle">Cristóbal Jiménez</p>

                        <p class="testimony__review">Estudiante del doble grado de Ingeniería Informática y Administración 
                            y Dirección de Empresas en la Universidad de Granada</p>
                    </div>

                    <figure class="testimony__picture">
                        <img src="./images/empresario.jpg" class="testimony__img">
                    </figure>
                </section>
            </div>
        </section>
       
        <section class="container questions ">
            <h2 class="subtitle">Preguntas frecuentes</h2>
            
            <section class="questions__container">
                <article class="questions__padding">
                    <div class="questions__answer">
                        <h3 class="questions__title">¿Es un consejo de inversión?
                            <span class="questions__arrow">
                                <img src="./images/arrow.svg" class="questions__img">
                            </span>
                        </h3>

                        <p class="questions__show">No, el objetivo de esta página web es ayudar a inversores principiantes que 
                            no tengan claro por dónde empezar. Es una ayuda para facilitar el difícil cálculo de la rentabilidad que 
                            podría tener cada vivienda. Antes de comprar un inmueble, asegúrese de hacer un estudio profundo y personalizado del mismo</p>
                    </div>
                </article>

                <article class="questions__padding">
                    <div class="questions__answer">
                        <h3 class="questions__title">¿Los gastos de compra están ajustados a cada vivienda?
                            <span class="questions__arrow">
                                <img src="./images/arrow.svg" class="questions__img">
                            </span>
                        </h3>

                        <p class="questions__show">No se pueden conocer algunos datos, como
                            los gastos de comunidad o del seguro de un inmueble o su valor catastral de una vivienda sin ser el propietario. 
                            Se ha hecho una estimación basada en un análisis estadístico
                        </p>
                    </div>
                </article>

                <article class="questions__padding">
                    <div class="questions__answer">
                        <h3 class="questions__title">¿Están todas las viviendas actualmente a la venta recogidas en esta página web?
                            <span class="questions__arrow">
                                <img src="./images/arrow.svg" class="questions__img">
                            </span>
                        </h3>

                        <p class="questions__show">Aunque disponemos de la mayoría de viviendas a la venta en el portal de Idealista.com, 
                            la forma legal de obtener sus datos (mediante su API) tiene restringido el número de peticiones a 100 al mes para un usuario, lo que significa poder obtener 5000 viviendas al mes en total.
                            En futuras actualizaciones se podría llegar a un trato con Idealista y pagar a cambio de un mayor número de peticiones. 
                        </p>
                    </div>
                </article>
            </section>

            <section class="questions__offer">
                <h2 class="subtitle">¿Ya te has decidido?</h2>
                <p class="questions__copy">Regístrate o inicia sesión para empezar a guardar las oportunidades de inversión que más se ajusten a tu perfil</p>
                <a href="#" class="cta">Empieza ya</a>
            </section>
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
                        <a id="viviendas_link" href="./coleccion_viviendas.php" class="nav__links">Viviendas</a>
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

    <script src="./js/questions.js"></script>
    <script src="./js/menu.js"></script>
    <script src="./js/sortby.js"></script>
</body>

</html>