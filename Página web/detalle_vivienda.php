<?php
session_start();
include("con_db.php");

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['email']);
}
?>

<?php
include("con_db.php");  // Incluir la conexión a la base de datos
include("calculos_aux.php");
if (isset($_GET['id'])) {
    $id_vivienda = $_GET['id'];

    // Consulta para obtener los detalles de la vivienda
    $consulta = "SELECT * FROM vivienda WHERE id_vivienda = $id_vivienda";
    $resultado = mysqli_query($conex, $consulta);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $vivienda = mysqli_fetch_assoc($resultado);

        $id_vivienda = $vivienda['id_vivienda'];
        $sale_price = $vivienda['sale_price'];
        $rent_prediction = $vivienda['rent_prediction'];
        $new_development = $vivienda['new_development'];
        $rooms = $vivienda['rooms'];
        $bathrooms = $vivienda['bathrooms'];
        $size = $vivienda['size'];
        $address = $vivienda['address'];
        $link_image = $vivienda['link_image'];
        $es_casa = $vivienda['es_casa'];
        $size = $vivienda ['size'];
        
        // Calcular el alquiler anual estimado del inmueble
        $alquiler_anual = $rent_prediction * 12;

        // Según Idealista
        $IBI = (int)($rent_prediction * 0.55);
        $seguros = (int)($rent_prediction * 0.384);
        $mantenimiento = (int)($rent_prediction * 0.54);
        $comunidad = (int)($es_casa == 1 ? $rent_prediction * 0.6 : $rent_prediction * 1.2);


        // Calcular los gastos operativos anuales y mensuales
        $gastos_operativos_anuales = $IBI + $seguros + $comunidad + $mantenimiento;
        $gastos_operativos_mensuales = $gastos_operativos_anuales / 12;

        // Estimar valor catastral para calcular precio del registro de la vivienda
        $tipo_impositivo = 0.00639; //en granada
        $valor_catastral = $IBI / $tipo_impositivo;

        // Aplicar el porcentaje según el precio de venta
        $ITPoIVA = (int)calcular_ITPoIVA($sale_price, $new_development);

        // Consulta para obtener el valor mínimo y máximo de size 
        $consulta_size = "SELECT MIN(size) as min_size, MAX(size) as max_size FROM vivienda";
        $resultado_size = mysqli_query($conex, $consulta_size);
        
        if ($resultado_size && mysqli_num_rows($resultado_size) > 0) {
            $size_data = mysqli_fetch_assoc($resultado_size);
            $min_size = $size_data['min_size'];
            $max_size = $size_data['max_size'];
            $notaria = (int)ponderar_notaria($size, $min_size, $max_size);
        } else {
            $min_size = $max_size = 0;
            $notaria = 315;
        }

        $registro = (int)calcular_gastos_registro($valor_catastral);
        $reforma = 0;
        $porcentaje_agencia= 0.03;
        $agencia_inmobiliaria = $sale_price * $porcentaje_agencia;

        $gastos_compra = $ITPoIVA + $notaria + $registro + $reforma + $agencia_inmobiliaria;

        // Calcular el coste total del inmueble (precio de venta + reformas + impuestos + gastos de compra)
        $coste_total = $sale_price + $gastos_compra;

        // Calcular mensualidad hipoteca
        $porcentaje_hipoteca=0.6;
        $hipoteca = $sale_price * $porcentaje_hipoteca;
        $inversion_inicial =(int) ($coste_total - $hipoteca);

        

        $TAE = 0.034;
        $interes_mensual = $TAE / 12;
        $num_anios = 30;
        $num_cuotas = 12 * $num_anios;

        $cuota_hip_mensual = ($hipoteca * $interes_mensual * pow(1 + $interes_mensual, $num_cuotas)) / (pow(1 + $interes_mensual, $num_cuotas) - 1);
        $cuota_hip_anual = $cuota_hip_mensual * 12;
        $interes_medio_mensual = (($cuota_hip_mensual * $num_cuotas) - $hipoteca) / $num_cuotas;
        $interes_medio_anual = $interes_medio_mensual * 12;
        $amortizacion_media_mensual = $cuota_hip_mensual - $interes_medio_mensual;
        $amortizacion_media_anual = $amortizacion_media_mensual * 12;

        // Calcular el flujo de caja
        $cashflow_anual = $alquiler_anual - $cuota_hip_anual - $gastos_operativos_anuales;
        $cashflow_mensual = $rent_prediction - $cuota_hip_mensual - $gastos_operativos_mensuales;

        // Calcular la rentabilidad bruta
        $rentabilidad_bruta = $alquiler_anual / $coste_total;

        // Calcular la rentabilidad neta (descontando los gastos operativos anuales)
        $rentabilidad_neta = ($alquiler_anual - $gastos_operativos_anuales - $interes_medio_anual) / $coste_total;

        // Calcular el ROCE (retorno sobre el capital invertido)
        $ROCE = ($cashflow_anual + $amortizacion_media_anual) / $inversion_inicial;
        $payback_period = number_format($inversion_inicial / $cashflow_anual, 1);

        // echo "ID de la vivienda: " . $id_vivienda . "<br>";
        // echo "Precio de venta: " . $sale_price . "€<br>";
        // echo "Alquiler anual estimado: " . $alquiler_anual . "€<br>";
        // echo "Nuevo desarrollo: " . $new_development . "<br>";
        // echo "Habitaciones: " . $rooms . "<br>";
        // echo "Baños: " . $bathrooms . "<br>";
        // echo "Tamaño: " . $size . " m²<br>";
        // echo "Dirección: " . $address . "<br>";
        // echo "Enlace de la imagen: " . $link_image . "<br>";
        // echo "Es una casa: " . $es_casa . "<br>";
        // echo "Tamaño: " . $size . " m²<br>";
        // echo "IBI: " . $IBI . "€<br>";
        // echo "Seguros: " . $seguros . "€<br>";
        // echo "Comunidad: " . $comunidad . "€<br>";
        // echo "Mantenimiento: " . $mantenimiento . "€<br>";
        // echo "Gastos operativos anuales: " . $gastos_operativos_anuales . "€<br>";
        // echo "Gastos operativos mensuales: " . $gastos_operativos_mensuales . "€<br>";
        // echo "ITP o IVA: " . $ITPoIVA . "€<br>";
        // echo "Notaría: " . $notaria . "€<br>";
        // echo "Registro: " . $registro . "€<br>";
        // echo "Reforma: " . $reforma . "€<br>";
        // echo "Porcentaje agencia: " . ($porcentaje_agencia * 100) . "%<br>";
        // echo "Agencia inmobiliaria: " . $agencia_inmobiliaria . "€<br>";
        // echo "Gastos de compra: " . $gastos_compra . "€<br>";
        // echo "Coste total: " . $coste_total . "€<br>";
        // echo "Hipoteca: " . $hipoteca . "€<br>";
        // echo "Inversión inicial: " . $inversion_inicial . "€<br>";
        // echo "rentabilidad bruta: " . $rentabilidad_bruta . "€<br>";

    } else {
        echo "No se encontró la vivienda.";
        exit;
    }
} else {
    echo "ID de vivienda no especificado.";
    exit;
}
?>





<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viviendas disponibles</title>
    <link rel="shortcut icon" href="./images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" /> <!-- flechas de paginación -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
        <section class="container detalle-vivienda">
            <div class="detalle-vivienda-container" data-id="<?php echo $id_vivienda; ?>">
                <div class="vivienda-imagen-detalles">
                    <img id= "imagen-vivienda-detalles" src="<?php echo $link_image; ?>"  alt="Imagen de la vivienda">
                    <div class="precio-venta2"><?php echo number_format($sale_price, 0, ',', '.'); ?>€</div> <!-- Precio de venta -->
                </div>
                <div class="detalle-vivienda-detalles">
                    <h1><?php echo ucwords(str_replace('calle', 'c/', $address)); ?>, <?php echo $vivienda['municipality']; ?></h1><br>
                    <div class="nav__logo">
                        <img src="./images/logo.png" alt="Logo de RentaRitmo" class="nav__logo-img">
                        <h2>Estima un alquiler de <span style="color: #FF9F01;" id="rentPrediction" data-value="<?php echo $rent_prediction; ?>"><?php echo number_format($rent_prediction, 0, ',', '.'); ?>€/mes</span><br>
                            e inversión inicial de 
                            <label for="inversion_inicial" class="label">
                                <span id="inversionInicialValue" class="slider-value" style="color: #FF9F01;"><?php echo $inversion_inicial; ?></span>
                                <span style="color: #FF9F01;">€</span>
                            </label>
                            <a class="tooltip">
                                <i class="fas fa-info-circle"></i>
                                <span class="tooltip-box">El valor de la <strong>inversión inicial</strong> (estimación del dinero que tienes que tener ahorrado para invertir en esta vivienda) se modificará automáticamente si deseas modificar los gastos asociados a la compra o el mantenimiento del inmueble</span>
                            </a>
                        </h2>
                    </div>
                    <br>
                    <p> <b>Habitaciones:</b> <?php echo $rooms; ?></p>
                    <p><b>Baños:</b>  <?php echo $bathrooms; ?></p>
                    <p><b>Superficie:</b> <?php echo $size; ?>m<sup>2</sup></p>
                    <p><b>Para estudiantes:</b> <?php echo ($vivienda['for_students'] == 1) ? 'Sí' : 'No'; ?></p>
                    <p><b>Ascensor:</b> <?php echo ($vivienda['has_lift'] == 1) ? 'Sí' : 'No'; ?></p>
                    <p><b>Parking:</b> <?php echo ($vivienda['has_parking'] == 1) ? 'Sí' : 'No'; ?></p>
                    <p><b>Piscina:</b> <?php echo ($vivienda['has_pool'] == 1) ? 'Sí' : 'No'; ?></p>
                    <p><b>Obra nueva:</b> <?php echo ($vivienda['new_development'] == 1) ? 'Sí' : 'No'; ?></p>
                    <p><b>URL:</b> <a href="<?php echo $vivienda['url']; ?>" target="_blank"><?php echo $vivienda['url']; ?></a></p>
                    <!-- necesitaremos este dato para calcular itpoiva en el javascript -->
                    <input type="hidden" id="new_development" value="<?php echo $vivienda['new_development']; ?>">                    
          
                    <input type="hidden" id="inversion_inicial" value="<?php echo $inversion_inicial; ?>">
                                
                </div>
                <div class="button-container">
                    <?php if (isAuthenticated()): ?>
                        <form id="likeForm" method="POST">
                            <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">
                            <input type="hidden" name="id_vivienda" id="id_vivienda" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
                            <?php
                            // Verificar si existe una mis_inversiones con el id_usuario y id_vivienda
                            $id_usuario = $_SESSION['id_usuario'];
                            $id_vivienda = isset($_GET['id']) ? $_GET['id'] : '';
                            
                            $stmt = $conex->prepare("SELECT * FROM mis_inversiones WHERE id_usuario = ? AND id_vivienda = ?");
                            if ($stmt) {
                                $stmt->bind_param("ss", $id_usuario, $id_vivienda);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    // Si existe una instancia, mostrar el botón con el icono de like relleno
                                    ?>
                                    <a class="tooltip" href="#" id="unlikeButton">
                                        <button type="button" class="unlike-button">
                                            <img src="./images/like_relleno.svg" alt="No me gusta" id="unlikeIcon">
                                        </button>
                                        <span class="tooltip-box">Haz click para quitar de favoritos esta inversión</span>
                                    </a>
                                    <?php
                                } else {
                                    // Si no, mostrar like vacío
                                    ?>
                                    <a class="tooltip" href="#" id="likeButton">
                                        <button type="button" class="like-button">
                                            <img src="./images/like_vacio.svg" alt="Me gusta" id="likeIcon">
                                        </button>
                                        <span class="tooltip-box">Puedes guardar esta posibilidad de inversión como favorita, se almacenará en "Mis Inversiones" con tu configuración personalizada (por ejemplo, si piensas que conseguirás rebajar el precio de venta)</span>
                                    </a>
                                    <?php
                                }
                                $stmt->close();
                            }
                            ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <div class= "partir-pantalla container">
            <section class="container gastos">
                
                <section class="gastos__container">
                    <!-- <h3 class="subtitle__2"></h3> -->
                    <h3 class="subtitle__2">Personaliza los gastos
                        <a class="tooltip">
                            <i class="fas fa-info-circle"></i>
                            <span class="tooltip-box">Puedes modificar los gastos estimados para esta vivienda y se recalculará la rentabilidad de tu inversión</span>
                        </a>
                    </h3>

                    <article class="gastos__padding">
                        <div class="gastos__answer">
                            <h3 class="gastos__title">Gastos de Hipoteca
                                <span class="gastos__arrow">
                                    <img src="./images/arrow.svg" class="gastos__img">
                                </span>
                            </h3>

                            <p class="gastos__show">
                                
                                <!-- Slider para E -->
                                <label for="tae" class="label">TAE: <span id="taeValue" class="slider-value"><?php echo number_format($TAE * 100, 1, ',', '.'); ?>%</span><br></label>
                                <input type="range" id="tae" min="0" max="0.06" step="0.001" value="<?php echo $TAE; ?>">
                                
                                <!-- Slider para Porcentaje Hipoteca -->
                                <label for="porcentaje_hipoteca" class="label">Porcentaje de Hipoteca: <span id="porcentajeHipotecaValue" class="slider-value"><?php echo number_format($porcentaje_hipoteca * 100, 0, ',', '.'); ?>%</span><br></label>
                                <input type="range" id="porcentaje_hipoteca" min="0" max="1" step="0.01" value="<?php echo $porcentaje_hipoteca; ?>">
                                
                                <!-- Slider para Número de Años -->
                                <label for="num_anios" class="label">Número de Años: <span id="numAniosValue" class="slider-value"><?php echo $num_anios; ?></span><br></label>
                                <input type="range" id="num_anios" min="0" max="50" value="<?php echo $num_anios; ?>">

                            </p>
                        </div>
                    </article>

                    <article class="gastos__padding">
                        <div class="gastos__answer">
                            <h3 class="gastos__title"> Gastos de Compra
                                <span class="gastos__arrow">
                                    <img src="./images/arrow.svg" class="gastos__img">
                                </span>
                            </h3>

                            <p class="gastos__show">

                                <!-- Slider para ITP o IVA -->
                                <label for="itpoiva" class="label">ITP o IVA (automático): <span id="itpoivaValue" class="slider-value"><?php echo $ITPoIVA; ?>€</span><br></label>
                                <input type="range" id="itpoiva" style="display: none;" min="0" max="<?php echo $ITPoIVA*3; ?>" value="<?php echo $ITPoIVA; ?>">
                                
                                <!-- Slider para Precio de Venta -->
                                <label for="sale_price" class="label">Precio de Venta: <span id="salePriceValue" class="slider-value"><?php echo $sale_price; ?>€</span><br></label>
                                <input type="range" id="sale_price" min="<?php echo $sale_price * 0.5; ?>" max="<?php echo $sale_price; ?>" step="500" value="<?php echo $sale_price;?>">
                                
                                <!-- Slider para Porcentaje Agencia -->
                                <label for="porcentaje_agencia" class="label">Porcentaje de Agencia (poner 0% si es de particular): <span id="porcentajeAgenciaValue" class="slider-value"><?php echo number_format($porcentaje_agencia * 100, 0, ',', '.'); ?>%</span><br></label>
                                <input type="range" id="porcentaje_agencia" min="0" max="0.04" step="0.001" value="<?php echo $porcentaje_agencia; ?>">
                                
                                <!-- Slider para Reforma -->
                                <label for="reforma" class="label">Reforma: <span id="reformaValue" class="slider-value"><?php echo $reforma; ?>€</span><br></label>
                                <input type="range" id="reforma" min="0" max="50000" step="100" value="<?php echo $reforma; ?>">
                                
                                <!-- Slider para Notaría -->
                                <label for="notaria" class="label">Notaría: <span id="notariaValue" class="slider-value"><?php echo $notaria; ?>€</span><br></label>
                                <input type="range" id="notaria" min="0" max="<?php echo $notaria * 3; ?>" value="<?php echo  $notaria; ?>">
                                
                                <!-- Slider para Registro -->
                                <label for="registro" class="label">Registro: <span id="registroValue" class="slider-value"><?php echo $registro; ?>€</span><br></label>
                                <input type="range" id="registro" min="0" max="<?php echo $registro * 3; ?>" value="<?php echo $registro; ?>">
                                
                            </p>
                        </div>
                    </article>

                    <article class="gastos__padding">
                        <div class="gastos__answer">
                            <h3 class="gastos__title">Gastos Anuales del Inmueble
                                <span class="gastos__arrow">
                                    <img src="./images/arrow.svg" class="gastos__img">
                                </span>
                            </h3>

                            <p class="gastos__show">
                                <!-- Slider para Comunidad -->
                                <label for="comunidad" class="label">Comunidad: <span id="comunidadValue" class="slider-value"><?php echo $comunidad; ?>€</span><br></label> 
                                <input type="range" id="comunidad" name="comunidad" min="0" max="<?php echo $comunidad * 3; ?>" value="<?php echo $comunidad; ?>">
                                
                                <!-- Slider para IBI -->
                                <label for="ibi" class="label">IBI: <span id="ibiValue" class="slider-value"><?php echo $IBI; ?>€</span><br></label>
                                <input type="range" id="ibi" min="0" max="<?php echo $IBI * 3; ?>" value="<?php echo $IBI; ?>">
                                
                                <!-- Slider para Mantenimiento -->
                                <label for="mantenimiento" class="label">Mantenimiento: <span id="mantenimientoValue" class="slider-value"><?php echo $mantenimiento; ?>€</span><br></label>
                                <input type="range" id="mantenimiento" min="0" max="<?php echo $mantenimiento * 3; ?>" value="<?php echo $mantenimiento; ?>">
                                
                                <!-- Slider para Seguros -->
                                <label for="seguros" class="label">Seguros: <span id="segurosValue" class="slider-value"><?php echo $seguros; ?>€</span><br></label>
                                <input type="range" id="seguros" min="0" max="<?php echo $seguros * 3; ?>" value="<?php echo $seguros; ?>">
                                
                            </p>
                        </div>
                    </article>
                </section>
                

            </section>
            <section class="container rentabilidades">
                <div class="renta">
                    <h3 class="subtitle__2">Rentabilidad de tu inversión
                        <a class="tooltip">
                            <i class="fas fa-info-circle"></i>
                            <span class="tooltip-box">
                                <strong>Rentabilidad Bruta:</strong> Es el porcentaje de lo que ganas anualmente comparado con el costo del inmueble, sin contar los gastos.<br><br>
                                <strong>Rentabilidad Neta:</strong> Es el porcentaje de lo que ganas anualmente comparado con el costo del inmueble, considerando todos los gastos e intereses.<br><br>
                                <strong>ROCE (Retorno del Capital Empleado):</strong> Es el porcentaje de lo que ganas anualmente comparado con el total del dinero que has invertido en el inmueble, midiendo la eficiencia de esa inversión.<br><br>
                                <strong>Cashflow Anual:</strong> Es el dinero que generarás o perderás en un año, después de considerar todos los ingresos y gastos, lo que se te queda en el bolsillo.<br><br>
                                <strong>Cashflow Mensual:</strong> Es el dinero que generarás o perderás cada mes, después de considerar todos los ingresos y gastos, lo que se te queda en el bolsillo.
                                <strong>Recuperas inversión:</strong> El número de años que tardarás en recuperar tu inversión inicial, es lo que se llama periodo de recuperación.
                            </span>
                        </a>
                    </h3>
                    <br>
                    
                    <p><b>Rentabilidad Bruta: </b> <span id="rentabilidadBrutaValue"> <?php echo number_format($rentabilidad_bruta * 100, 2, ',', '.'); ?>%</span> </p>
                    <p><b>Rentabilidad Neta: </b> <span id="rentabilidadNetaValue"> <?php echo number_format($rentabilidad_neta * 100, 2, ',', '.'); ?>%</span></p>
                    <p><b>ROCE: </b> <span id="ROCEValue"> <?php echo number_format($ROCE * 100, 2, ',', '.'); ?>%</span></p>
                    <p><b>Cashflow Anual: </b> <span id="cashflowAnualValue"> <?php echo $cashflow_anual; ?> €</span> </p>
                    <p><b>Cashflow Mensual: </b> <span id="cashflowMensualValue"> <?php echo $cashflow_mensual; ?> €</span></p>                   
                    <p><b>Recuperas inversión en: </b> <span id="paybackPeriodValue"> <?php echo $payback_period; ?></span></p>                   
                </div>
                
            </section>
        </div>
    
        

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
    <script src="./js/slider_gastos.js"></script>
    <script src="./js/likebutton.js"></script>
    <script src="./js/unlikebutton.js"></script>
    <script src="./js/menu.js"></script>
</body>

</html>
