<?php
function calcular_gastos_registro($valor_catastral) {
    if ($valor_catastral <= 6010.12) {
        return 24.04;
    } elseif ($valor_catastral <= 30050.61) {
        return 24.04 + (($valor_catastral - 6010.13) / 1000) * 1.75;
    } elseif ($valor_catastral <= 60101.21) {
        return 74.78 + (($valor_catastral - 30050.62) / 1000) * 1.25;
    } elseif ($valor_catastral <= 150253.03) {
        return 199.03 + (($valor_catastral - 60101.22) / 1000) * 0.75;
    } elseif ($valor_catastral <= 601012.10) {
        return 430.28 + (($valor_catastral - 150253.04) / 1000) * 0.30;
    } else {
        return 1642.91 + (($valor_catastral - 601012.10) / 1000) * 0.20;
    }
}

function calcular_ITPoIVA($precio_venta, $new_development) {
    if ($new_development == 0) {
        if ($precio_venta < 150000) {
            $itp_iva = $precio_venta * 0.072;
        } else {
            $itp_iva = $precio_venta * 0.082;
        }
    }
    if ($new_development == 1) {
        $itp_iva = $precio_venta * 0.1;
    }
    return $itp_iva;
}

function ponderar_notaria($valor, $min_val, $max_val, $valor_min_notaria = 270, $valor_max_notaria = 500) {
    // Caso especial si todos los tamaños de viviendas son iguales
    if ($min_val == $max_val) {
        return $valor_min_notaria;
    }
    return $valor_min_notaria + ($valor - $min_val) * ($valor_max_notaria - $valor_min_notaria) / ($max_val - $min_val);
}

?>