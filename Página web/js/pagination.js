document.addEventListener("DOMContentLoaded", () => {
    const startBtn = document.querySelector("#startBtn");
    const endBtn = document.querySelector("#endBtn");
    const prevBtn = document.querySelector("#prev");
    const nextBtn = document.querySelector("#next");
    const paginationContainer = document.querySelector(".pagination");

    const totalPaginas = parseInt(paginationContainer.getAttribute('data-total-pages'));
    const currentURLParams = new URLSearchParams(window.location.search);
    let currentStep = parseInt(currentURLParams.get("pagina") || 1) - 1;

    const updateBtn = () => {
        startBtn.disabled = currentStep === 0;
        prevBtn.disabled = currentStep === 0;
        endBtn.disabled = currentStep === totalPaginas - 1;
        nextBtn.disabled = currentStep === totalPaginas - 1;
    };

    const goToPage = (page) => {
        currentStep = page;
        const newURLParams = new URLSearchParams(currentURLParams);
        newURLParams.set('pagina', page + 1); // Establecer la página actual en los parámetros de la URL

        // Obtener el valor seleccionado del orden
        const selectedSortBy = currentURLParams.get('sort_by');
        if (selectedSortBy) {
            newURLParams.set('sort_by', selectedSortBy);
        }

        window.location.href = `${window.location.pathname}?${newURLParams.toString()}`;
    };

    startBtn.addEventListener("click", () => {
        goToPage(0);
    });

    endBtn.addEventListener("click", () => {
        goToPage(totalPaginas - 1);
    });

    prevBtn.addEventListener("click", () => {
        if (currentStep > 0) {
            goToPage(currentStep - 1);
        }
    });

    nextBtn.addEventListener("click", () => {
        if (currentStep < totalPaginas - 1) {
            goToPage(currentStep + 1);
        }
    });

    // Obtener el elemento select del orden
    var sortBySelect = document.getElementById("sort_by");

    // Escuchar el evento de cambio en el select
    sortBySelect.addEventListener("change", function() {
        var selectedValue = sortBySelect.value;
        var urlParams = new URLSearchParams(window.location.search);
        urlParams.set('pagina_actual', 1);
        urlParams.set('sort_by', selectedValue);
        
        var newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    });

    // Función para establecer la opción seleccionada en un select
    function setSelectedOption(selectId, value) {
        const select = document.getElementById(selectId);
        for (let option of select.options) {
            if (option.value === value) {
                option.selected = true;
                break;
            }
        }
    }

    // Establecer las opciones seleccionadas
    setSelectedOption('rentabilidadfiltro', currentURLParams.get('filter_rentabilidad'));
    setSelectedOption('rangoinversionfiltro', currentURLParams.get('filter_rangoinversion'));
    setSelectedOption('rooms', currentURLParams.get('filter_rooms'));
    setSelectedOption('bathrooms', currentURLParams.get('filter_bathrooms'));
    setSelectedOption('has_lift', currentURLParams.get('filter_has_lift'));
    setSelectedOption('tipo_vivienda', currentURLParams.get('filter_tipo_vivienda'));
    setSelectedOption('estudiantes', currentURLParams.get('filter_estudiantes'));
    setSelectedOption('sort_by', currentURLParams.get('sort_by'));

    updateBtn();
});
