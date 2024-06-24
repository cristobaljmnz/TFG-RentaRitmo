document.addEventListener('DOMContentLoaded', (event) => {
    const viviendas = document.querySelectorAll('.vivienda');
    viviendas.forEach(vivienda => {
        const id = vivienda.getAttribute('data-id');
        const link = document.createElement('a');
        link.href = `detalle_vivienda.php?id=${id}`;
        link.className = 'vivienda-enlace';

        // no cambiar el formato de la carta
        link.style.textDecoration = 'none';
        link.style.color = 'inherit';

        // El enlace debe estar disponible desde todas partes de la carta
        link.style.display = 'block';  
        //así que hacemos que el link englobe a todo el "objeto" de la vivienda
        while (vivienda.firstChild) {
            link.appendChild(vivienda.firstChild);
        }

        // agregar el enlace a la div vivienda
        vivienda.appendChild(link);
    });
});
