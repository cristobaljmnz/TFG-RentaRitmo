document.addEventListener("DOMContentLoaded", () => {
    const likeButton = document.getElementById("likeButton");
    const idUsuarioInput = document.getElementById("id_usuario");
    const idViviendaInput = document.getElementById("id_vivienda");
    const likeIcon = document.getElementById("likeIcon");

    function transformarValor(value, esPorcentaje = false, dividir =false) {
        let nuevoValor = value.replace('€', '').trim();
        if (esPorcentaje) {
            nuevoValor = nuevoValor.replace('%', '').replace(',', '.').trim();
            nuevoValor = (parseFloat(nuevoValor)).toString();
        } else {
            nuevoValor = nuevoValor.replace(',', '.');
        }
        if (dividir){
            nuevoValor = nuevoValor.replace('%', '').replace(',', '.').trim();
            nuevoValor = (parseFloat(nuevoValor) /100 ).toString();
        }
        return nuevoValor;
    }
    function transformarAnios(valor){
        if (valor != 'Nunca'){
            let nuevoValue = valor.replace('años', '').replace(',', '.').trim();
            return nuevoValue;
        }
        else{
            return valor.toString();
        }
    }

    likeButton.addEventListener("click", () => {
        const sliders = {
            comunidad: transformarValor(document.getElementById("comunidad").value),
            ibi: transformarValor(document.getElementById("ibi").value),
            mantenimiento: transformarValor(document.getElementById("mantenimiento").value),
            seguros: transformarValor(document.getElementById("seguros").value),
            porcentaje_agencia: transformarValor(document.getElementById("porcentaje_agencia").value, true),
            itpoiva: transformarValor(document.getElementById("itpoiva").value),
            notaria: transformarValor(document.getElementById("notaria").value),
            reforma: transformarValor(document.getElementById("reforma").value),
            registro: transformarValor(document.getElementById("registro").value),
            sale_price: transformarValor(document.getElementById("sale_price").value),
            tae: transformarValor(document.getElementById("tae").value, true),
            porcentaje_hipoteca: transformarValor(document.getElementById("porcentaje_hipoteca").value, true),
            inversion_inicial: transformarValor(document.getElementById("inversion_inicial").value),
            num_anios: document.getElementById("num_anios").value,
            rentabilidad_bruta: transformarValor(document.getElementById("rentabilidadBrutaValue").textContent, false,true),
            rentabilidad_neta: transformarValor(document.getElementById("rentabilidadNetaValue").textContent, false,true),
            roce: transformarValor(document.getElementById("ROCEValue").textContent,false, true),
            cashflow_anual: transformarValor(document.getElementById("cashflowAnualValue").textContent),
            cashflow_mensual: transformarValor(document.getElementById("cashflowMensualValue").textContent),
            payback_period: transformarAnios(document.getElementById("paybackPeriodValue").textContent)
        };

        const id_usuario = idUsuarioInput.value;
        const id_vivienda = idViviendaInput.value;

        console.log("Datos enviados al servidor:", {
            id_usuario,
            id_vivienda,
            sliders: sliders
        });

        fetch("like.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_usuario,
                id_vivienda,
                sliders: sliders
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === "success") {
                likeIcon.src = "../images/like_relleno.svg";
                window.location.reload(); // Recargar la página
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});