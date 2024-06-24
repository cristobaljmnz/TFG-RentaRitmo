<?php
session_start();
include("con_db.php");
// Array para almacenar los datos a imprimir como JSON
$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // Verificar si los datos recibidos están completos
    if (isset($data['id_usuario'], $data['id_vivienda'], $data['sliders'])) {
        // Extraer los datos necesarios
        $id_usuario = $data['id_usuario'];
        $id_vivienda = $data['id_vivienda']; 
        $sliders = $data['sliders'];
        // $link_recargar = $data['link_recargar'];
        
        $stmt = $conex->prepare("INSERT INTO mis_inversiones (id_usuario, id_vivienda, ibi, seguros, mantenimiento, comunidad, itpoiva, notaria, registro, reforma, porcentaje_agencia, inversion_inicial, tae, cashflow_anual, cashflow_mensual, rentabilidad_bruta, rentabilidad_neta, roce, sale_price, num_anios, porcentaje_hipoteca, payback_period) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Verificar si la preparación de la consulta fue exitosa
        if ($stmt) {
            if ($sliders['payback_period'] == 'Nunca'){
                $sliders['payback_period'] = -1;
            }
            $stmt->bind_param("ssssssssssssssssssssss", $id_usuario, $id_vivienda, $sliders['ibi'], $sliders['seguros'], $sliders['mantenimiento'], $sliders['comunidad'], $sliders['itpoiva'], $sliders['notaria'], $sliders['registro'], $sliders['reforma'], $sliders['porcentaje_agencia'], $sliders['inversion_inicial'], $sliders['tae'], $sliders['cashflow_anual'], $sliders['cashflow_mensual'], $sliders['rentabilidad_bruta'], $sliders['rentabilidad_neta'], $sliders['roce'], $sliders['sale_price'], $sliders['num_anios'], $sliders['porcentaje_hipoteca'], $sliders['payback_period']);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                $response['status'] = 'success';
                // header("Location: {$link_recargar}");
                // exit;
            } else {
                // Si la consulta falla, enviar un mensaje de error
                $response['status'] = 'error';
                $response['message'] = 'Error al ejecutar la consulta preparada: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            // Si la preparación de la consulta falla, enviar un mensaje de error
            $response['status'] = 'error';
            $response['message'] = 'Error al preparar la consulta: ' . $conex->error;
        }
    } else {
        // Si falta algún dato necesario en la solicitud, enviar un mensaje de error
        $response['status'] = 'error';
        $response['message'] = 'Datos faltantes en la solicitud';
    }
} else {
    // Si la solicitud no es de tipo POST, retornar un mensaje de error
    $response['status'] = 'error';
    $response['message'] = 'Método de solicitud no permitido';
}

// Imprimir el arreglo completo como JSON
echo json_encode($response);
?>