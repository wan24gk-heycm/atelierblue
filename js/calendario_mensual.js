// EN USO AGOSTO

(function () {
    const state = {
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),
    };

    let conteos = {
    };// fecha → total

    const grid = document.getElementById('grid')
    const monthLabel = document.getElementById('monthLabel');
    const btnPrev = document.getElementById('prev');
    const btnNext = document.getElementById('next');
    const btnToday = document.getElementById('today');

    btnPrev.addEventListener('click', () => changeMonth(-1));
    btnNext.addEventListener('click', () => changeMonth(1));
    btnToday.addEventListener('click', () => {
        const now = new Date();
        state.viewYear = now.getFullYear();
        state.viewMonth = now.getMonth();
        render();
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') changeMonth(-1);
        if (e.key === 'ArrowRight') changeMonth(1);
        if (e.key.toLowerCase() === 't') btnToday.click();
    });

    function changeMonth(delta) {
        let m = state.viewMonth + delta;
        let y = state.viewYear;
        if (m < 0) { m = 11; y--; }
        if (m > 11) { m = 0; y++; }
        state.viewMonth = m; state.viewYear = y;
        render();
    }

    function cargarConteos(year, month) {
        return fetch("php/conteos.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "year=" + year + "&month=" + (month + 1) // OJO: JS empieza en 0
        })
            .then(res => res.json())
            .then(data => {
                conteos = data; // Ejemplo recibido: { "2025-08-30": 2, "2025-08-31": 1 }
            })
            .catch(err => {
                console.error("Error cargando conteos:", err);
                conteos = {};
            });
    }

    function render() {
        // 1. Cargar conteos de BD antes de dibujar
        cargarConteos(state.viewYear, state.viewMonth).then(() => {
            // Etiqueta del mes (es-MX)
            const label = new Intl.DateTimeFormat('es-MX', { month: 'long', year: 'numeric' })
                .format(new Date(state.viewYear, state.viewMonth, 1));
            monthLabel.textContent = label;

            grid.innerHTML = '';

            const firstOfMonth = new Date(state.viewYear, state.viewMonth, 1);
            const lastOfMonth = new Date(state.viewYear, state.viewMonth + 1, 0);

            // En es-MX, semana inicia en lunes. En JS: getDay(): 0=Dom .. 6=Sáb
            // Queremos índice 0=Lun, 6=Dom
            const jsWeekday = firstOfMonth.getDay(); // 0..6
            const startIndex = (jsWeekday - 1 + 7) % 7; // 0..6 (Lun=0)

            // Días del mes anterior a mostrar como "relleno"
            const prevMonthDays = startIndex;
            const daysInPrevMonth = new Date(state.viewYear, state.viewMonth, 0).getDate();

            // Añadir días del mes anterior
            for (let i = prevMonthDays; i > 0; i--) {
                const d = daysInPrevMonth - i + 1;
                grid.appendChild(dayCell(d, true));
            }

            

            // Días del mes actual
            for (let d = 1; d <= lastOfMonth.getDate(); d++) {
                const cell = dayCell(d, false);

                // Marcar hoy
                const now = new Date();
                if (
                    d === now.getDate() &&
                    state.viewMonth === now.getMonth() &&
                    state.viewYear === now.getFullYear()
                ) {
                    cell.classList.add('today');
                    cell.id = "hoy-fecha"
                }

                // Mostrar contador de citas si existe
                const fecha = new Date(state.viewYear, state.viewMonth, d)
                    .toISOString().split("T")[0];
                if (conteos[fecha]) {
                    const badge = document.createElement('span');
                    badge.className = 'badge';
                    badge.textContent = ".";
                }

                grid.appendChild(cell);
            }
            
            // 3. Relleno con días del siguiente mes
const totalCellsSoFar = grid.children.length;
const toFill = (Math.ceil(totalCellsSoFar / 7) * 7) - totalCellsSoFar;
for (let d = 1; d <= toFill; d++) {
    grid.appendChild(dayCell(d, true));
}

        });
    }

    // function dayCell(dayNumber, isMuted) {
    //  const el = document.createElement('div');
    //  el.className = 'day' + (isMuted ? ' muted' : '');
    //  el.setAttribute('role', 'gridcell');
    //  const date = document.createElement('div');
    //  date.className = 'date';
    //  date.textContent = dayNumber;
    //  el.appendChild(date);
    //  return el;
    //  }

    function dayCell(dayNumber, isMuted) {
        const el = document.createElement('button');
        el.className = 'day' + (isMuted ? ' muted' : '');
        el.setAttribute('role', 'gridcell');

        const date = document.createElement('div');
        date.className = 'date';
        date.textContent = dayNumber;
        el.appendChild(date);

        let year = state.viewYear;
        let month = state.viewMonth;

        if (isMuted) {
            if (dayNumber > 20) {
                month = state.viewMonth - 1;
                if (month < 0) { month = 11; year--; }
            }
            else {
                month = state.viewMonth + 1;
                if (month > 11) { month = 0; year++; }
            }
        }

        const fullDate = new Date(year, month, dayNumber);
        const fechaStr = fullDate.toISOString().split("T")[0];
        el.dataset.date = fechaStr;

        // Si ya hay conteo cargado para este día, agregamos el badge
        if (conteos[fechaStr]) {
            const badge = document.createElement("span");
            badge.className = "badge";
            badge.textContent = ".";
            el.appendChild(badge);
            el.classList.add("con-citas"); // opcional para aplicar verde neon
        }

        // Click → cargar citas del día
        el.addEventListener("click", () => {
            document.querySelectorAll('.day').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');

            filtrarEntregas(fechaStr);

            fetch('php/entregas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'fecha=' + encodeURIComponent(fechaStr)
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('resultados').innerHTML = data;
                })
                .catch(error => console.error('Error al llamar entregas.php:', error));

            let hayResultados = false;
            const cont = document.querySelector(".total");
            let total = document.getElementById("total-citas");

            if (!hayResultados) {

                if (!total) {
                    total = document.createElement("div");
                    total.id = "total-citas"
                    total.className = "total";
                    cont.prepend(total);
                }
                if (!conteos[fechaStr]) {
                    total.textContent = "TOTAL DE CITAS: 0 DE 10"
                } else {
                    total.textContent = "TOTAL DE CITAS: " + conteos[fechaStr] + " DE 10"
                }
            } else if (msg) {
                total.remove();
            }

        });


        return el;
    }


    // Función para mostrar la fecha seleccionada en texto
    function filtrarEntregas(fecha) {
        let hayResultados = false;

        const contenedor = document.querySelector(".calendario");
        let msg = document.getElementById("result");

        if (!hayResultados) {

            if (!msg) {
                msg = document.createElement("div");
                msg.id = "result";
                msg.className = "cal-title";
                msg.style.marginTop = "1rem";
                contenedor.prepend(msg);
            }
            msg.textContent = fecha;
        } else if (msg) {
            msg.remove();
        }


    }

    function pintarEstatus() {
        document.querySelectorAll(".estatus").forEach(el => {
            const texto = el.textContent.trim().toLowerCase(); // normaliza a minúsculas
            const img = el.querySelector('img');

            if (texto === "en espera") {
                el.style.color = "#f1c40f";
                el.style.backgroundColor = "rgba(241, 196, 15, 0.15)";

                if (img) img.src = "icons/clock.svg"; // 👈 validar

            }
            else if (texto === "PROGRAMADA") {
                el.style.backgroundColor = "#7f8c8d"; // gris
                el.style.color = "#fff";
            }
            else if (texto === "entregada") {
                el.style.color = "#1e8449"; // Verde
                el.style.backgroundColor = "rgba(39, 174, 96, 0.15)";

                if (img) img.src = "icons/star.svg";
            }
            else if (texto === "no asiste") {
                el.style.color = "#841e1eff"; // Verde
                el.style.backgroundColor = "rgba(174, 39, 39, 0.15)";

                if (img) img.src = "icons/cross.svg";
            }
        });


    }

    // Observador que detecta cuando se agregan nodos al DOM
    const observer = new MutationObserver(() => {
        pintarEstatus();
    });

    // Empieza a observar el <body>
    observer.observe(document.body, { childList: true, subtree: true });

    // Llamada inicial por si ya hay algunos al cargar
    pintarEstatus();


    function actualizarEstatusPorHora() {
        const hoy = new Date();
        const año = hoy.getFullYear();
        const mes = hoy.getMonth();
        const dia = hoy.getDate();

        document.querySelectorAll(".entrega-info").forEach(card => {
            const horaEl = card.querySelector(".hora");
            const fechaEl = card.querySelector(".fecha");
            const estatusEl = card.querySelector(".estatus");

            if (horaEl && fechaEl && estatusEl) {
                // 👇 Aquí pon el log
                console.log(
                    "Analizando cita:",
                    horaEl.textContent.trim(),
                    fechaEl.textContent.trim(),
                    "estatus actual:",
                    estatusEl.textContent
                )
            };

            if (horaEl && fechaEl && estatusEl) {
                let horaTexto = horaEl.textContent.trim();   // ej: "14:30:00"
                const fechaTexto = fechaEl.textContent.trim(); // ej: "2025-08-31"
                const texto = estatusEl.textContent.trim().toLowerCase();

                // Asegurar que solo tome HH:MM
                horaTexto = horaTexto.slice(0, 5); // "14:30"

                // Crear objeto Date con fecha + hora
                const cita = new Date(`${fechaTexto}T${horaTexto}`);

                // Solo si es hoy
                if (
                    cita.getFullYear() === año &&
                    cita.getMonth() === mes &&
                    cita.getDate() === dia
                ) {
                    const ahora = new Date();
                    const diffMin = (cita - ahora) / 60000; // diferencia en minutos

                    if (diffMin <= 30 && diffMin >= 0 && texto === "programada") {
                        const nuevoEstatus = "EN ESPERA";

                        estatusEl.textContent = nuevoEstatus;
                        estatusEl.style.backgroundColor = "rgba(241, 196, 15, 0.15)";
                        estatusEl.style.color = "#f1c40f";
                        const img = estatusEl.querySelector("img");
                        if (img) img.src = "icons/clock.svg";

                        const id = card.dataset.id;
                        fetch("php/actualizar_estatus.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `id=${id}&estatus=${encodeURIComponent(nuevoEstatus)}`
                        })
                            .then(res => res.text())
                            .then(data => console.log("Respuesta servidor:", data))
                            .catch(err => console.error("Error:", err));

                    }
                    






                }
            }
        });
    }

    // Al cargar
    actualizarEstatusPorHora();

    // Revisar cada minuto
    setInterval(actualizarEstatusPorHora, 60000);








    render();
})();