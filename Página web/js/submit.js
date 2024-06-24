document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.querySelector("form");
    const submitButton = registerForm.querySelector("input[type='submit']");
    const requiredInputs = registerForm.querySelectorAll("input[required]:not([type='submit'])");

    function checkAllInputs() {
        let allInputsFilled = true;
        requiredInputs.forEach(function(input) {
            if (!input.value) {
                allInputsFilled = false;
            }
        });
        return allInputsFilled;
    }

    function toggleSubmitButtonColor() {
        if (checkAllInputs()) {
            submitButton.style.backgroundColor = "#FF9F01"; // Cambia el color del botón a naranja si todos los campos obligatorios están completos
        } else {
            submitButton.style.backgroundColor = "#666666"; // Restaura el color original del botón si algún campo obligatorio está vacío
        }
    }

    requiredInputs.forEach(function(input) {
        input.addEventListener("input", toggleSubmitButtonColor); // Escucha el evento de entrada en cada campo obligatorio
    });
});