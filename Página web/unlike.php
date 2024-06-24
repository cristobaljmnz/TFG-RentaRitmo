<?php
session_start();
include("con_db.php");
$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['id_usuario'], $data['id_vivienda'])) {
        $id_usuario = $data['id_usuario'];
        $id_vivienda = $data['id_vivienda']; 
        // $link_recargar = $data['link_recargar'];

        $stmt = $conex->prepare("DELETE FROM mis_inversiones WHERE id_usuario = ? AND id_vivienda = ?");

        if ($stmt) {
            $stmt->bind_param("ss", $id_usuario, $id_vivienda);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                // header("Location: {$link_recargar}");
                // exit;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error al ejecutar la consulta preparada: ' . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error al preparar la consulta: ' . $conex->error;
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Datos faltantes en la solicitud';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Método de solicitud no permitido';
}

echo json_encode($response);
?>