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
    <title>Viviendas disponibles</title>
    <link rel="shortcut icon" href="./images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" /> <!-- flechas de paginación -->
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
        <section class="container titulo">
            <h2 class="titulo"> Viviendas a la venta en Granada</h2>
        </section>
        
        
        <section class="container filtro">
            <form id="filtroForm" class="filtro-form">
                <div class="filtro-row">
                    <div class="filters-container">
                        <div class="filter-item">
                            <label for="rentabilidadfiltro">Rentabilidad min:</label>
                            <select id="rentabilidadfiltro" name="filter_rentabilidad">
                                <option value="Elegir">-</option>
                                <option value="1%">1%</option>
                                <option value="2%">2%</option>
                                <option value="3%">3%</option>
                                <option value="4%">4%</option>
                                <option value="5%">5%</option>
                                <option value="6%">6%</option>
                                <option value="7%">7%</option>
                                <option value="8%">8%</option>
                                <option value="9%">9%</option>
                                <option value="10%">10%</option>
                                <option value="11%">11%</option>
                                <option value="12%">12%</option>
                                <option value="13%">13%</option>
                                <option value="14%">14%</option>
                                <option value="15%">15%</option>
                                <option value="16%">16%</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="rangoinversionfiltro">Inversión inicial:</label>
                            <select id="rangoinversionfiltro" name="filter_rangoinversion">
                                <option value="Elegir">-</option>
                                <option value="0-25.000">0-25.000</option>
                                <option value="25.000-65.000">25.000-65.000</option>
                                <option value="65.000-90.000">65.000-90.000</option>
                                <option value="90.000-100.000">90.000-100.000</option>
                                <option value="100.000-115.000">100.000-115.000</option>
                                <option value="115.000-130.000">115.000-130.000</option>
                                <option value="130.000-150.000">130.000-150.000</option>
                                <option value="150.000-175.000">150.000-175.000</option>
                                <option value="175.000-215.000">175.000-215.000</option>
                                <option value="215.000+">215.000+</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="rooms">Habitaciones:</label>
                            <select id="rooms" name="filter_rooms">
                                <option value="Elegir">-</option>
                                <option value="1+">1+</option>
                                <option value="2+">2+</option>
                                <option value="3+">3+</option>
                                <option value="4+">4+</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="bathrooms">Baños:</label>
                            <select id="bathrooms" name="filter_bathrooms">
                                <option value="Elegir">-</option>
                                <option value="1+">1+</option>
                                <option value="2+">2+</option>
                                <option value="3+">3+</option>
                                <option value="4+">4+</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="has_lift">Ascensor:</label>
                            <select id="has_lift" name="filter_has_lift">
                                <option value="Elegir">-</option>
                                <option value="Si">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="tipo_vivienda">Tipo Vivienda:</label>
                            <select id="tipo_vivienda" name="filter_tipo_vivienda">
                                <option value="Elegir">-</option>
                                <option value="Piso">Piso</option>
                                <option value="Casa">Casa</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="estudiantes">Para estudiantes:</label>
                            <select id="estudiantes" name="filter_estudiantes">
                                <option value="Elegir">-</option>
                                <option value="Si">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                    </div>
                    <div class="boton">
                        <button type="submit">Aplicar filtros</button>
                    </div>
                </div>
                <hr class="separator-line"> <!-- Línea separadora añadida -->
                <div class="sort_by-container">
                    <label for="sort_by">Ordenar por:</label>
                    <select id="sort_by" name="sort_by" class="sort_by">
                        <option value="Elegir">-</option>
                        <option value="sale_price_ASC">Precio - ASC</option>
                        <option value="sale_price_DESC">Precio - DESC</option>
                        <option value="inversion_inicial_ASC">Inversión Inicial - ASC</option>
                        <option value="inversion_inicial_DESC">Inversión Inicial - DESC</option>
                        <option value="rentabilidad_bruta">Rentabilidad - DESC</option>
                    </select>
                </div>
            </form>
        </section>
        
        <section class="container coleccionviviendas">
        
        
            <?php
            $inc = include("con_db.php");
            
            if ($inc) {

                $viviendas_por_pagina = 40;

                $consulta_total_viviendas = "SELECT COUNT(*) as total_viviendas FROM vivienda";
                // Inicializar array de condiciones
                $conditions = array();

                $rooms_filter = isset($_GET['filter_rooms']) ? $_GET['filter_rooms'] : '';
                $bathrooms_filter = isset($_GET['filter_bathrooms']) ? $_GET['filter_bathrooms'] : '';
                $rentabilidad_filter = isset($_GET['filter_rentabilidad']) ? $_GET['filter_rentabilidad'] : '';
                $inversion_inicial_filter = isset($_GET['filter_rangoinversion']) ? $_GET['filter_rangoinversion'] : '';
                $has_lift_filter = isset($_GET['filter_has_lift']) ? $_GET['filter_has_lift'] : '';
                $tipo_vivienda_filter = isset($_GET['filter_tipo_vivienda']) ? $_GET['filter_tipo_vivienda'] : '';
                $estudiantes_filter = isset($_GET['filter_estudiantes']) ? $_GET['filter_estudiantes'] : '';

                if (!empty($rooms_filter) && $rooms_filter != 'Elegir') {
                    if ($rooms_filter == '4+') {
                        $conditions[] = "rooms >= 4";
                    } elseif ($rooms_filter == '3+') {
                        $conditions[] = "rooms >= 3";
                    }elseif ($rooms_filter == '2+') {
                        $conditions[] = "rooms >= 2";
                    } else{
                        $conditions[] = "rooms >= 1";
                    }
                }

                if (!empty($bathrooms_filter) && $bathrooms_filter != 'Elegir') {
                    if ($bathrooms_filter == '4+') {
                        $conditions[] = "bathrooms >= 4";
                    } elseif ($bathrooms_filter == '3+') {
                        $conditions[] = "bathrooms >= 3";
                    }elseif ($bathrooms_filter == '2+') {
                        $conditions[] = "bathrooms >= 2";
                    } else {
                        $conditions[] = "bathrooms >= 1";
                    }
                }

                if (!empty($rentabilidad_filter) && $rentabilidad_filter != 'Elegir') {
                    switch ($rentabilidad_filter) {
                        case '1%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.01";
                            break;
                        case '2%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.02";
                            break;
                        case '3%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.03";
                            break;
                        case '4%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.04";
                            break;
                        case '5%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.05";
                            break;
                        case '6%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.06";
                            break;
                        case '7%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.07";
                            break;
                        case '8%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.08";
                            break;
                        case '9%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.09";
                            break;
                        case '10%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.10";
                            break;
                        case '11%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.11";
                            break;
                        case '12%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.12";
                            break;
                        case '13%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.13";
                            break;
                        case '14%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.14";
                            break;
                        case '15%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.15";
                            break;
                        case '16%':
                            $conditions[] = "rentabilidad_bruta_inicial >= 0.16";
                            break;
                    }
                }

                if (!empty($inversion_inicial_filter) && $inversion_inicial_filter != 'Elegir') {
                    switch ($inversion_inicial_filter) {
                        case '0-25.000':
                            $conditions[] = "inversion_inicial_inicial <= 25000";
                            break;
                        case '25.000-65.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 25000 AND 65000";
                            break;
                        case '65.000-90.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 65000 AND 90000";
                            break;
                        case '90.000-100.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 90000 AND 100000";
                            break;
                        case '100.000-115.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 100000 AND 115000";
                            break;
                        case '115.000-130.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 115000 AND 130000";
                            break;
                        case '130.000-150.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 130000 AND 150000";
                            break;
                        case '150.000-175.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 150000 AND 175000";
                            break;
                        case '175.000-215.000':
                            $conditions[] = "inversion_inicial_inicial BETWEEN 175000 AND 215000";
                            break;
                        case '215.000+':
                            $conditions[] = "inversion_inicial_inicial >= 215000";
                            break;
                    }
                }
                

                if (!empty($has_lift_filter) && $has_lift_filter != 'Elegir') {
                    if ($has_lift_filter == 'Si') {
                        $conditions[] = "has_lift = 1";
                    } else {
                        $conditions[] = "has_lift = 0";
                    }
                }

                if (!empty($tipo_vivienda_filter) && $tipo_vivienda_filter != 'Elegir') {
                    if ($tipo_vivienda_filter == 'Casa') {
                        $conditions[] = "es_casa = 1";
                    } else {
                        $conditions[] = "es_casa = 0";
                    }
                }

                if (!empty($estudiantes_filter) && $estudiantes_filter != 'Elegir') {
                    if ($estudiantes_filter == 'Si') {
                        $conditions[] = "for_students = 1";
                    } else {
                        $conditions[] = "for_students= 0";
                    }
                }

                // Unir las condiciones en la consulta
                if (count($conditions) > 0) {
                    $consulta_total_viviendas .= " WHERE " . implode(' AND ', $conditions);
                }

                $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
                $order = '';
                if ($sort_by === 'sale_price_ASC') {
                    $order = 'sale_price ASC';
                } elseif ($sort_by === 'sale_price_DESC') {
                    $order = 'sale_price DESC';
                } elseif ($sort_by === 'inversion_inicial_ASC') {
                    $order = 'inversion_inicial_inicial ASC';
                } elseif ($sort_by === 'inversion_inicial_DESC') {
                    $order = 'inversion_inicial_inicial DESC';
                } elseif ($sort_by === 'rentabilidad_bruta') {
                    $order = 'rentabilidad_bruta_inicial DESC';
                }
                
            
                if ($order != '') {
                    $consulta_total_viviendas .= " ORDER BY $order";
                }
                $resultado_total_viviendas = mysqli_query($conex, $consulta_total_viviendas);
                $row_total_viviendas = $resultado_total_viviendas->fetch_array();
                $total_viviendas = $row_total_viviendas['total_viviendas'];
                if ($total_viviendas == 0) {
                    echo "<div style='display: flex; justify-content: center; align-items: center; height: 200px; width: 100%;'>
                            <div style='text-align: center; color: #333333; font-size: 18px; font-weight: bold;'>
                                No existe ninguna vivienda con los filtros seleccionados.
                            </div>
                          </div>";
                }else {
                    $total_paginas = ceil($total_viviendas / $viviendas_por_pagina);

                    $pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

                    $inicio = ($pagina_actual - 1) * $viviendas_por_pagina;
                    $fin = $inicio + $viviendas_por_pagina;
                    $consulta = "SELECT link_image, sale_price, rent_prediction, size, rooms, bathrooms, address, id_vivienda, has_lift,rentabilidad_bruta_inicial, inversion_inicial_inicial FROM vivienda";
                    
                    // Unir las condiciones en la consulta
                    if (count($conditions) > 0) {
                        $consulta .= " WHERE " . implode(' AND ', $conditions);
                    }

                    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
                    $order = '';
                    if ($sort_by === 'sale_price_ASC') {
                        $order = 'sale_price ASC';
                    } elseif ($sort_by === 'sale_price_DESC') {
                        $order = 'sale_price DESC';
                    } elseif ($sort_by === 'inversion_inicial_ASC') {
                        $order = 'inversion_inicial_inicial ASC';
                    } elseif ($sort_by === 'inversion_inicial_DESC') {
                        $order = 'inversion_inicial_inicial DESC';
                    } elseif ($sort_by === 'rentabilidad_bruta') {
                        $order = 'rentabilidad_bruta_inicial DESC';
                    }

                    if ($order != '') {
                        $consulta .= " ORDER BY $order";
                    }
                    //LIMIT para que salgan 40 por pag
                    $consulta .= " LIMIT $inicio, $viviendas_por_pagina";

                    $resultado = mysqli_query($conex, $consulta);
                    
                    if ($resultado) {
                        while ($row = $resultado->fetch_array()) {
                            $link_image = $row['link_image'];
                            $sale_price = $row['sale_price'];
                            $rent_prediction = $row['rent_prediction'];
                            $size = $row['size'];
                            $rooms = $row['rooms'];
                            $bathrooms = $row['bathrooms'];
                            $address = $row['address'];
                            $id_vivienda = $row['id_vivienda'];
                            $has_lift = $row['has_lift'];
                            $rentabilidad_bruta = $row['rentabilidad_bruta_inicial'];
                            $inversion_inicial = $row['inversion_inicial_inicial'];
                            ?>
                            <div class="vivienda" data-id="<?php echo $id_vivienda; ?>">
                                <div class="vivienda-imagen">
                                    <img src="<?php echo $link_image; ?>" style="margin-bottom:-2px; width: 100%; height:auto;" alt="Imagen de la vivienda">
                                    <div class="linea-horizontal"></div> <!-- Línea horizontal -->
                                    <div class="precio-venta"><?php echo $sale_price; ?>€</div> <!-- Precio de venta -->
                                    <div class="datos-inferiores">
                                        <img class="icono" src="./images/bed.svg" alt="Icono de habitaciones"> <?php echo $rooms; ?><br>
                                        <img class="icono" src="./images/bath.svg" alt="Icono de baños"> <?php echo $bathrooms; ?><br>
                                        <img class="icono" src="./images/area.svg" alt="Icono de size"> <?php echo $size; ?><br>
                                    </div>
                                </div>
                                <div class="vivienda-descripcion">
                                    <h2 class="vivienda-descripcion__address"><?php echo ucwords(str_replace('calle', 'c/', $address)); ?></h2>
                                    <p style="margin-bottom: 5px;" class="custom-tooltip">
                                        <b>Predicción Alquiler: </b> <?php echo number_format($rent_prediction, 0, ',', '.'); ?>€
                                        <span class="tooltip-box">Según el  <strong>algoritmo de predicción </strong> desarrollado en <strong>RentaRitmo </strong>, este es el precio de alquiler que tendría este inmueble</span>
                                    </p>
                                    <p style="margin-bottom: 5px;" class="custom-tooltip">
                                        <b>Rentabilidad: </b> <?php echo number_format($rentabilidad_bruta * 100, 2, ',', '.'); ?>%
                                        <span class="tooltip-box">Una  <strong>estimación inicial </strong> de la  <strong>rentabilidad bruta </strong> de este inmueble (porcentaje de lo que ganas anualmente comparado con el costo del inmueble,  <strong>sin contar los gastos </strong>)</span>
                                    </p>
                                    <p class="custom-tooltip">
                                        <b>Inversión inicial: </b> <?php echo number_format($inversion_inicial, 0, ',', '.'); ?>€
                                        <span class="tooltip-box">Haciendo una estimación de todos los gastos asociados con la compra, este es el  <strong>dinero </strong> que tendrías que <strong>ahorrar para invertir </strong> en este inmueble</span>
                                    </p>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }

                echo '<div class="pagination" data-total-pages="' . $total_paginas . '">';

            
                ?>

                <button class="button" id="startBtn" <?php if ($pagina_actual == 1) echo "disabled"; ?>>
                    <i class="fa-solid fa-angles-left"></i>
                </button>
                <button class="button prevNext" id="prev" <?php if ($pagina_actual == 1) echo "disabled"; ?>>
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <div class="links">
                    <?php
                    // Mostrar páginas anteriores
                    $inicio = max(1, $pagina_actual - 4);
                    $fin = min($total_paginas, $inicio + 8);

                    // Si estamos en la última página, mostrar las cinco páginas anteriores
                    if ($pagina_actual >= $total_paginas - 4) {
                        $inicio = max(1, $total_paginas - 8);
                        $fin = $total_paginas;
                    }

                    // Construir los enlaces de paginación
                    for ($i = $inicio; $i <= $fin; $i++) {
                        $urlParams['pagina'] = $i; 
                        // Incluir parámetros de filtro en el enlace
                        $rooms_filter = isset($_GET['filter_rooms']) ? $_GET['filter_rooms'] : '';
                        $bathrooms_filter = isset($_GET['filter_bathrooms']) ? $_GET['filter_bathrooms'] : '';
                        $has_lift_filter = isset($_GET['filter_has_lift']) ? $_GET['filter_has_lift'] : '';
                        $tipo_vivienda_filter = isset($_GET['filter_tipo_vivienda']) ? $_GET['filter_tipo_vivienda'] : '';
                        $rentabilidad_filter = isset($_GET['filter_rentabilidad']) ? $_GET['filter_rentabilidad'] : '';
                        $estudiantes_filter = isset($_GET['filter_estudiantes']) ? $_GET['filter_estudiantes'] : '';
                        $inversion_inicial_filter = isset($_GET['filter_rangoinversion']) ? $_GET['filter_rangoinversion'] : '';

                        if (!empty($rooms_filter)) {
                            $urlParams['filter_rooms'] = $rooms_filter;
                        }
                        if (!empty($bathrooms_filter)) {
                            $urlParams['filter_bathrooms'] = $bathrooms_filter;
                        }
                        if (!empty($has_lift_filter)) {
                            $urlParams['filter_has_lift'] = $has_lift_filter;
                        }
                        if (!empty($tipo_vivienda_filter)) {
                            $urlParams['filter_tipo_vivienda'] = $tipo_vivienda_filter;
                        }
                        if (!empty($estudiantes_filter)) {
                            $urlParams['filter_estudiantes'] = $estudiantes_filter;
                        }
                        if (!empty($rentabilidad_filter)) {
                            $urlParams['filter_rentabilidad'] = $rentabilidad_filter;
                        }
                        if (!empty($inversion_inicial_filter)) {
                            $urlParams['filter_rangoinversion'] = $inversion_inicial_filter;
                        }
                        // Incluir 'sort_by' en el enlace si está presente en la URL
                        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
                        if (!empty($sort_by)) {
                            $urlParams['sort_by'] = $sort_by;
                        }
                        $page_link = '?' . http_build_query($urlParams);
                        if ($i == $pagina_actual) {
                            echo "<a href='$page_link' class='link active'>$i</a>";
                        } else {
                            echo "<a href='$page_link' class='link'>$i</a>";
                        }
                    }
                    ?>
                </div>
                <button class="button prevNext" id="next" <?php if ($pagina_actual == $total_paginas) echo "disabled"; ?>>
                    <i class="fa-solid fa-angle-right"></i>
                </button>
                <button class="button" id="endBtn" <?php if ($pagina_actual == $total_paginas) echo "disabled"; ?>>
                    <i class="fa-solid fa-angles-right"></i>
                </button>

                <?php
                echo '</div>';
                                
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

    <script src="./js/menu.js"></script>
    <script src="./js/pagination.js" defer></script>
    <script src="./js/abrirvivienda.js" defer></script>
</body>

</html>