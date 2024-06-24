<?php
include("con_db.php");  // Incluir la conexión a la base de datos
include("calcular_registro.php");
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

        $rooms = $vivienda['rooms'];
        $bathrooms = $vivienda['bathrooms'];
        $size = $vivienda['size'];
        $address = $vivienda['address'];
        $link_image = $vivienda['link_image'];

        // Calcular el alquiler anual estimado del inmueble
        $alquiler_anual = $rent_prediction * 12;

        // Según Idealista
        $IBI = (int)($rent_prediction * 0.36);
        $seguros = (int)($rent_prediction * 0.384);
        $mantenimiento = (int)($rent_prediction * 0.54);
        $comunidad = (int)($rent_prediction * 1.2);

        // Calcular los gastos operativos anuales y mensuales
        $gastos_operativos_anuales = $IBI + $seguros + $comunidad + $mantenimiento;
        $gastos_operativos_mensuales = $gastos_operativos_anuales / 12;

        // Estimar valor catastral para calcular precio del registro de la vivienda
        $tipo_impositivo = 0.00639; //en granada
        $valor_catastral = $IBI / $tipo_impositivo;

        // Aplicar el porcentaje según el precio de venta
        $ITPoIVA = int($sale_price < 150000) ? $sale_price * 0.072 : $sale_price * 0.082;
        $notaria = 0.35 * 900;
        $registro = (int)calcular_gastos_registro($valor_catastral);
        $reforma = 0;
        $porcentaje_agencia= 0.03;
        $agencia_inmobiliaria = $sale_price *0.03;

        $gastos_compra = $ITPoIVA + $notaria + $registro + $reforma + $agencia_inmobiliaria;

        // Calcular el coste total del inmueble (precio de venta + reformas + impuestos + gastos de compra)
        $coste_total = $sale_price + $gastos_compra;

        // Calcular mensualidad hipoteca
        $porcentaje_hipoteca=0.6;
        $hipoteca = $sale_price * $porcentaje_hipoteca;
        $inversion_inicial =(int) ($coste_total - $hipoteca);

        

        $TAN = 0.034;
        $interes_mensual = $TAN / 12;
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
        
    } else {
        echo "No se encontró la vivienda.";
        exit;
    }
} else {
    echo "ID de vivienda no especificado.";
    exit;
}
?>