document.addEventListener("DOMContentLoaded", () => {
    const unlikeButton = document.getElementById("unlikeButton");
    const idUsuarioInput = document.getElementById("id_usuario");
    const idViviendaInput = document.getElementById("id_vivienda");
    const unlikeIcon = document.getElementById("unlikeIcon");

    unlikeButton.addEventListener("click", () => {
        const id_usuario = idUsuarioInput.value;
        const id_vivienda = idViviendaInput.value;

        console.log("Datos enviados al servidor:", {
            id_usuario,
            id_vivienda,
        });
       
        fetch("unlike.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_usuario,
                id_vivienda,
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === "success") {
                unlikeIcon.src = "../images/like_vacio.svg";
                window.location.reload(); // Recargar la página
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});