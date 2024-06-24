<?php
session_start();
include("con_db.php");

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis inversiones</title>
    <link rel="shortcut icon" href="./images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <meta name="theme-color" content="#2091F9">
    <meta name="title" content="Inversión inmobiliaria Granada">
    <meta name="description" content="Herramienta para buscar piso o casa en Granada, comprar para alquilar y sacar beneficio o rentabilidad">
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
                <li class="nav__items">
                    <a href="./mis_inversiones.php" class="nav__links">Mis inversiones</a>
                </li>
                <li class="nav__items">
                    <a href="./cerrar_sesion.php" class="nav__links">Cerrar sesión</a>
                </li>
                <img src="./images/close.svg" alt="Cerrar menu" class="nav__close">
            </ul>
            <div class="nav__menu">
                <img src="./images/menu.svg" class="nav__img">
            </div>
            <div class="nav__welcome">
                <div class="nav__user-initial"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
            </div>
        </nav>
    </header>

    <main>
        <section class="container titulo">
            <h2 class="titulo"> Mis oportunidades de inversión</h2>
        </section>
        <section class="container coleccionviviendas">
            <?php
            
            $id_usuario = $_SESSION['id_usuario'];
            $consulta = "SELECT * FROM mis_inversiones WHERE id_usuario = $id_usuario";
            $resultado_total_viviendas = mysqli_query($conex, $consulta);
            $num_inversiones = mysqli_num_rows($resultado_total_viviendas);

            // Impresión de información en la consola
            echo "<script>";
            echo "console.log('ID de usuario:', $id_usuario);";
            echo "console.log('Número de inversiones encontradas:', $num_inversiones);";
            echo "</script>";
            if ($resultado_total_viviendas) {
                while ($row = $resultado_total_viviendas->fetch_array()) {
                    $cashflow_anual = $row['cashflow_anual'];
                    $cashflow_mensual = $row['cashflow_mensual'];
                    $payback_period = $row['payback_period'];
                    if ($payback_period == -1){
                        $payback_period = 'Nunca';
                    }
                    else{
                        $payback_period = number_format($payback_period, 1, ',', '.');
                        $payback_period .= ' años';
                    }
                    $rentabilidad_bruta = $row['rentabilidad_bruta'];
                    $rentabilidad_neta = $row['rentabilidad_neta'];
                    $roce = $row['roce'];
                    $sale_price = $row['sale_price'];
                    $id_vivienda = $row['id_vivienda'];
                    $inversion_inicial = $row['inversion_inicial'];

                    $consulta_vivienda = "SELECT * FROM vivienda WHERE id_vivienda = $id_vivienda";
                    $resultado_vivienda = mysqli_query($conex, $consulta_vivienda);

                    if ($resultado_vivienda && mysqli_num_rows($resultado_vivienda) > 0) {
                        $vivienda = mysqli_fetch_assoc($resultado_vivienda);
                        $rent_prediction = $vivienda['rent_prediction'];
                        $rooms = $vivienda['rooms'];
                        $bathrooms = $vivienda['bathrooms'];
                        $size = $vivienda['size'];
                        $address = $vivienda['address'];
                        $link_image = $vivienda['link_image'];
                        $url = $vivienda['url'];
                    }
                    ?>
                    <section class="container detalle-vivienda" data-id="<?php echo $id_vivienda; ?>">
                        <div class="detalle-vivienda-container-modified">
                            <div class="vivienda-imagen-detalles-modified">
                                <img id="imagen-vivienda-detalles-modified" src="<?php echo $link_image; ?>" alt="Imagen de la vivienda">
                                <div class="precio-venta2"><?php echo number_format($sale_price, 0, ',', '.'); ?>€</div>
                            </div>
                            <div class="detalle-vivienda-detalles-modified">
                                <h3 class="subtitle__3"><?php echo ucwords(str_replace('calle', 'c/', $address)); ?>, <?php echo $vivienda['municipality']; ?></h3><br>
                                <div class="nav__logo">
                                    <img src="./images/logo.png" alt="Logo de RentaRitmo" class="nav__logo-img">
                                    <h2>Estima un alquiler de <span style="color: #FF9F01;" id="rentPrediction" data-value="<?php echo $rent_prediction; ?>"><?php echo number_format($rent_prediction, 0, ',', '.'); ?>€/mes</span><br>
                                        e inversión inicial de 
                                        <label for="inversion_inicial" class="label">
                                            <span id="inversionInicialValue" class="slider-value" style="color: #FF9F01;"><?php echo number_format($inversion_inicial, 0, ',', '.'); ?></span>
                                            <span style="color: #FF9F01;">€</span>
                                        </label>
                                        <a class="tooltip">
                                            <i class="fas fa-info-circle"></i>
                                            <span class="tooltip-box">El valor de la <strong>inversión inicial</strong> (estimación del dinero que tienes que tener ahorrado para invertir en esta vivienda) se modificará automáticamente si deseas modificar los gastos asociados a la compra o el mantenimiento del inmueble</span>
                                        </a>
                                    </h2>
                                </div>
                                <br>
                                <p><b>Habitaciones:</b> <?php echo $rooms; ?></p>
                                <p><b>Baños:</b> <?php echo $bathrooms; ?></p>
                                <p><b>Superficie:</b> <?php echo $size; ?>m<sup>2</sup></p>
                                <p>
                                    <div class="url-container">
                                        <b>URL:</b>
                                        <span id="urlLink"><a href="<?php echo $url; ?>"><?php echo $url; ?></a></span>
                                    </div>
                                </p>
                            </div>
                            <div class="detalle-vivienda-rentabilidades">
                                <div class="renta-modified">
                                    <h3 class="subtitle__2">Rentabilidad de tu inversión
                                        <a class="tooltip">
                                            <i class="fas fa-info-circle"></i>
                                            <span class="tooltip-box">
                                                <strong>Rentabilidad Bruta:</strong> Es el porcentaje de lo que ganas anualmente comparado con el costo del inmueble, sin contar los gastos.<br><br>
                                                <strong>Rentabilidad Neta:</strong> Es el porcentaje de lo que ganas anualmente comparado con el costo del inmueble, considerando todos los gastos e intereses.<br><br>
                                                <strong>ROCE (Retorno del Capital Empleado):</strong> Es el porcentaje de lo que ganas anualmente comparado con el total del dinero que has invertido en el inmueble, midiendo la eficiencia de esa inversión.<br><br>
                                                <strong>Cashflow Anual:</strong> Es el dinero que generarás o perderás en un año, después de considerar todos los ingresos y gastos, lo que se te queda en el bolsillo.<br><br>
                                                <strong>Cashflow Mensual:</strong> Es el dinero que generarás o perderás cada mes, después de considerar todos los ingresos y gastos, lo que se te queda en el bolsillo.
                                                <strong>Recuperas inversión:</strong>  el número de años que tardarás en recuperar tu inversión inicial, es lo que se llama periodo de recuperación
                                            
                                            </span>
                                        </a>
                                    </h3>
                                    <br>
                                    <p><b>Rentabilidad Bruta:</b> <?php echo number_format($rentabilidad_neta * 100, 2, ',', '.'); ?>%</p>
                                    <p><b>Rentabilidad Neta:</b> <?php echo number_format($rentabilidad_neta * 100, 2, ',', '.'); ?>%</p>
                                    <p><b>ROCE:</b> <?php echo number_format($roce * 100, 2, ',', '.'); ?>%</p>
                                    <p><b>Cashflow Anual:</b> <?php echo $cashflow_anual; ?>€</p>
                                    <p><b>Cashflow Mensual:</b> <?php echo $cashflow_mensual; ?>€</p>
                                    <p><b>Recuperas inversión:</b> <?php echo $payback_period; ?></p>
                                </div>
                            </div>
                            <div class="button-container">
                                <form id="likeForm" method="POST">
                                    <div class="button-container">
                                        <button type="button" class="open-button">
                                            <img src="./images/abrirvivienda.svg" alt="abrir vivienda" class="openIcon">
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                    <?php
                }
            } else {
                echo "No se encontraron resultados.";
            }
            
            ?>
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
                    <li class="nav__items">
                        <a href="./mis_inversiones.php" class="nav__links">Mis inversiones</a>
                    </li>
                    <li class="nav__items">
                        <a href="./cerrar_sesion.php" class="nav__links">Cerrar sesión</a>
                    </li>
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
    <script src="./js/abrirmivivienda.js"></script>
    <script>
    window.addEventListener('DOMContentLoaded', function() {  
        const urlLink = document.getElementById('urlLink').querySelector('a');

        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                urlLink.textContent = 'idealista.com'; // Cambiar el texto del enlace
            } else {
                urlLink.textContent = '<?php echo $url; ?>'; // Restaurar el texto del enlace
            }
        });
    });
    </script>
    <script src="./js/menu.js"></script>
</body>

</html>
