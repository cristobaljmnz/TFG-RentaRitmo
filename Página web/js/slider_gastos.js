document.addEventListener("DOMContentLoaded", () => {
    const sliders = {
        comunidad: document.getElementById("comunidad"),
        ibi: document.getElementById("ibi"),
        mantenimiento: document.getElementById("mantenimiento"),
        seguros: document.getElementById("seguros"),
        porcentaje_agencia: document.getElementById("porcentaje_agencia"),
        itpoiva: document.getElementById("itpoiva"),
        notaria: document.getElementById("notaria"),
        reforma: document.getElementById("reforma"),
        registro: document.getElementById("registro"),
        sale_price: document.getElementById("sale_price"),
        tae: document.getElementById("tae"),
        porcentaje_hipoteca: document.getElementById("porcentaje_hipoteca"),
        inversion_inicial: document.getElementById("inversion_inicial"),
        num_anios: document.getElementById("num_anios")
    };

    const spanValues = {
        comunidad: document.getElementById("comunidadValue"),
        ibi: document.getElementById("ibiValue"),
        mantenimiento: document.getElementById("mantenimientoValue"),
        seguros: document.getElementById("segurosValue"),
        porcentaje_agencia: document.getElementById("porcentajeAgenciaValue"),
        itpoiva: document.getElementById("itpoivaValue"),
        notaria: document.getElementById("notariaValue"),
        reforma: document.getElementById("reformaValue"),
        registro: document.getElementById("registroValue"),
        sale_price: document.getElementById("salePriceValue"),
        tae: document.getElementById("taeValue"),
        porcentaje_hipoteca: document.getElementById("porcentajeHipotecaValue"),
        inversion_inicial: document.getElementById("inversionInicialValue"),
        num_anios: document.getElementById("numAniosValue")
    };

    function convertirPorcentajeAFloat(valorPorcentaje) {
        // Eliminar el símbolo de porcentaje (%) y cualquier carácter de separación de miles
        const valorSinPorcentaje = valorPorcentaje.replace('%', '').replace(',', '.');
        // Convertir el valor a tipo float y dividir por 100 para obtener el valor decimal
        return parseFloat(valorSinPorcentaje);
    }
    
    function convertirAPrecio(valorPrecio) {
        const valor = (valorPrecio).toLocaleString('es-ES', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        return valor + '€';
    }

    function convertirAAnios(valor) {
        if (valor < 0) {
            return 'Nunca';
        }
        else{
            const valor1 = (valor).toLocaleString('es-ES', {minimumFractionDigits: 0, maximumFractionDigits: 1});
            return valor1 + ' años';
        }
    }

    function convertirAPorcentaje(valor) {
        // Multiplicar por 100 y formatear como porcentaje
        const valorPorcentaje = (valor * 100).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        return valorPorcentaje + '%';
    }
    

    function updateSpan(slider, span, unit = "") {
        // Parsear el valor del slider a número y multiplicarlo por 100 si la unidad es "%"
        const parsedValue = parseFloat(slider.value);
        const formattedValue = unit === "%" ? (parsedValue * 100).toLocaleString('es-ES', {minimumFractionDigits: 0, maximumFractionDigits: 2}) : parsedValue;
        
        // Actualizar el contenido del span
        span.textContent = formattedValue + unit;
    }

    function recalcular() {
        const sale_price = parseInt(sliders.sale_price.value);

        const rent_prediction = parseFloat(document.getElementById('rentPrediction').getAttribute('data-value'));

        const comunidad = parseFloat(sliders.comunidad.value);
        const ibi = parseFloat(sliders.ibi.value);
        const mantenimiento = parseFloat(sliders.mantenimiento.value);
        const seguros = parseFloat(sliders.seguros.value);

        const tae = convertirPorcentajeAFloat(sliders.tae.value) ;
        const porcentaje_hipoteca = convertirPorcentajeAFloat(sliders.porcentaje_hipoteca.value) ;
        const num_anios = parseFloat(sliders.num_anios.value);

        const new_development = parseFloat(document.getElementById('new_development').getAttribute('value'));
        // Función para calcular ITPoIVA
        function calcular_ITPoIVA(sale_price, new_development) {
            if (new_development === 1) {
                return sale_price * 0.1;
            } else {
                return (sale_price < 150000) ? sale_price * 0.072 : sale_price * 0.082;
            }
        }

        // Calcular ITPoIVA inicial
        const itpoiva = parseInt(calcular_ITPoIVA(sale_price, new_development));
        // document.getElementById("itpoivaValue").textContent =itpoiva;
        const notaria = parseFloat(sliders.notaria.value);
        const registro = parseFloat(sliders.registro.value);
        const reforma = parseFloat(sliders.reforma.value);
        const porcentaje_agencia = convertirPorcentajeAFloat(sliders.porcentaje_agencia.value) ;
        const agencia_inmobiliaria = sale_price * porcentaje_agencia;

        const gastos_compra = itpoiva + notaria + registro + reforma + agencia_inmobiliaria;
        //document.getElementById("auxValue").textContent= gastos_compra;
        const coste_total = sale_price + gastos_compra ;

        const hipoteca = sale_price * porcentaje_hipoteca;
        const inversion_inicial = parseInt(coste_total - hipoteca);
        // document.getElementById("inversionInicialValue").textContent =inversion_inicial;

        sliders.itpoiva.value = itpoiva;
        spanValues.itpoiva.textContent = itpoiva;
        
        sliders.inversion_inicial.max = coste_total;
        sliders.inversion_inicial.value = inversion_inicial;
        spanValues.inversion_inicial.textContent = inversion_inicial;

        const interes_mensual = tae / 12;
        const num_cuotas = 12 * num_anios;

        const cuota_hip_mensual = (hipoteca * interes_mensual * Math.pow(1 + interes_mensual, num_cuotas)) / (Math.pow(1 + interes_mensual, num_cuotas) - 1);
        const cuota_hip_anual = cuota_hip_mensual * 12;

        const alquiler_anual = rent_prediction * 12;
        const gastos_operativos_anuales = ibi + seguros + comunidad + mantenimiento;
        const gastos_operativos_mensuales = gastos_operativos_anuales / 12;

        const cashflow_anual = alquiler_anual - cuota_hip_anual - gastos_operativos_anuales;
        const cashflow_mensual = rent_prediction - cuota_hip_mensual - gastos_operativos_mensuales;

        const rentabilidad_bruta = alquiler_anual / coste_total;
        const rentabilidad_neta = (alquiler_anual - gastos_operativos_anuales - (cuota_hip_anual - hipoteca / num_anios)) / coste_total;
        const ROCE = (cashflow_anual + (hipoteca / num_anios)) / inversion_inicial;
        const payback_period = inversion_inicial / cashflow_anual;

        document.getElementById("rentabilidadBrutaValue").textContent =convertirAPorcentaje(rentabilidad_bruta);
        document.getElementById("rentabilidadNetaValue").textContent = convertirAPorcentaje(rentabilidad_neta);
        document.getElementById("ROCEValue").textContent = convertirAPorcentaje(ROCE);
        document.getElementById("cashflowAnualValue").textContent = convertirAPrecio(cashflow_anual);
        document.getElementById("cashflowMensualValue").textContent = convertirAPrecio(cashflow_mensual);
        document.getElementById("paybackPeriodValue").textContent = convertirAAnios(payback_period);

        // Imprimir todos los valores
        console.log("Sale price:", sale_price);
        console.log("Rent prediction:", rent_prediction);
        console.log("Comunidad:", comunidad);
        console.log("IBI:", ibi);
        console.log("Mantenimiento:", mantenimiento);
        console.log("Seguros:", seguros);
        console.log("TAE:", tae);
        console.log("Porcentaje hipoteca:", porcentaje_hipoteca);
        console.log("Número de años:", num_anios);
        console.log("New development:", new_development);
        console.log("ITP o IVA:", itpoiva);
        console.log("Notaria:", notaria);
        console.log("Registro:", registro);
        console.log("Reforma:", reforma);
        console.log("Porcentaje agencia:", porcentaje_agencia);
        console.log("Agencia inmobiliaria:", agencia_inmobiliaria);
        console.log("Gastos de compra:", gastos_compra);
        console.log("Coste total:", coste_total);
        console.log("Hipoteca:", hipoteca);
        console.log("Inversión inicial:", inversion_inicial);
        console.log("Interés mensual:", interes_mensual);
        console.log("Número de cuotas:", num_cuotas);
        console.log("Cuota hipoteca mensual:", cuota_hip_mensual);
        console.log("Cuota hipoteca anual:", cuota_hip_anual);
        console.log("Alquiler anual:", alquiler_anual);
        console.log("Gastos operativos anuales:", gastos_operativos_anuales);
        console.log("Gastos operativos mensuales:", gastos_operativos_mensuales);
        console.log("Cashflow anual:", cashflow_anual);
        console.log("Cashflow mensual:", cashflow_mensual);
        console.log("Periodo de Recuperación:", payback_period);
        console.log("Rentabilidad bruta:", rentabilidad_bruta);
        console.log("Rentabilidad neta:", rentabilidad_neta);
        console.log("ROCE:", ROCE);
    }

    for (const [key, slider] of Object.entries(sliders)) {
        slider.addEventListener("input", () => {
            let unit = "€"; 
    
            if (key.includes("porcentaje")) {
                unit = "%";
            }else if (key === "tae") {
                unit = "%"; 
            } else if (key === "num_anios") {
                unit = ""; // Sin unidad
            }
    
            updateSpan(slider, spanValues[key], unit);
            recalcular();
        });
    }
    

    recalcular();
    
});


(function(){
    const titlegastos = [...document.querySelectorAll('.gastos__title')];
    console.log(titlegastos)

    titlegastos.forEach(question =>{
        question.addEventListener('click', ()=>{
            let height = 0;
            let answer = question.nextElementSibling;
            let addPadding = question.parentElement.parentElement;

            addPadding.classList.toggle('gastos__padding--add');
            question.children[0].classList.toggle('gastos__arrow--rotate');

            if(answer.clientHeight === 0){
                height = answer.scrollHeight;
            }

            answer.style.height = `${height}px`;
        });
    });
})();
