function actualizarEstatusPorHora() {
    const ahora = new Date();
    const año = ahora.getFullYear();
    const mes = ahora.getMonth();
    const dia = ahora.getDate();

    const horaActual = ahora.getHours() + ahora.getMinutes() / 60;
    const inicio = 9;     // 9:00am
    const fin = 19.5;     // 19:30 -> 19.5 horas

    if (horaActual < inicio || horaActual > fin) return;

    document.querySelectorAll(".entrega-info").forEach(card => {
        const horaEl = card.querySelector(".hora");
        const fechaEl = card.querySelector(".fecha");
        const estatusEl = card.querySelector(".estatus");

        if (!horaEl || !fechaEl || !estatusEl) return;

        let horaTexto = horaEl.textContent.trim().slice(0, 5);
        const fechaTexto = fechaEl.textContent.trim();

        const [year, month, day] = fechaTexto.split("-").map(Number);
        if (year !== año || month - 1 !== mes || day !== dia) return;

        const texto = estatusEl.textContent.trim().toLowerCase();
        const cita = new Date(`${fechaTexto}T${horaTexto}`);

        const diffMin = (cita - ahora) / 60000;

        if (diffMin <= 30 && diffMin >= 0 && texto === "programada") {
            const nuevoEstatus = "EN ESPERA";
            estatusEl.textContent = nuevoEstatus;
            estatusEl.style.backgroundColor = "rgba(241, 196, 15, 0.15)";
            estatusEl.style.color = "#f1c40f";

            const img = estatusEl.querySelector("img");
            if (img) img.src = "icons/clock.svg";

            const id = card.dataset.id;
            fetch("../php/actualizar_estatus.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}&estatus=${encodeURIComponent(nuevoEstatus)}`
            })
            .then(res => res.text())
            .then(data => console.log("Respuesta servidor:", data))
            .catch(err => console.error("Error:", err));
        }
    });
}

// Ejecutar al cargar la página
actualizarEstatusPorHora();

// Ejecutar cada 30 minutos
setInterval(actualizarEstatusPorHora, 60000);