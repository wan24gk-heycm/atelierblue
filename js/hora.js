document.getElementById('fecha').addEventListener('change', function() {
    const fecha = this.value;

    fetch('php/horas.php?fecha=' + encodeURIComponent(fecha))
        .then(response => response.json())
        .then(data => {
            const selectHora = document.getElementById('hora');
            selectHora.innerHTML = '<option value="">Seleccione una hora</option>';

            if (data.length > 0 && !data.error) {
                data.forEach(hora => {
                    let option = document.createElement('option');
                    option.value = hora;
                    option.textContent = hora;
                    selectHora.appendChild(option);
                });
            } else {
                let option = document.createElement('option');
                option.value = "";
                option.textContent = "No hay horas disponibles";
                selectHora.appendChild(option);
            }
        })
        .catch(error => console.error('Error al obtener horas:', error));
});



window.onload = () => {
  fetch("php/unidades.php") // la ruta correcta a tu archivo
    .then(response => response.json())
    .then(data => {
      const lista = document.getElementById("lista-unidades");
      lista.innerHTML = '<option value="">Seleccione Unidad</option>'; // Limpia y agrega opción por defecto

      if (data.length > 0) {
        data.forEach(item => {
          const option = document.createElement("option");
          option.value = item.nombre;
          option.textContent = item.nombre;
          lista.appendChild(option);
        });
      } else {
        lista.innerHTML = '<option value="">No se encontraron registros</option>';
      }
    })
    .catch(err => {
      console.error(err);
      document.getElementById("lista-unidades").innerHTML =
        '<option value="">Error al cargar datos</option>';
    });
};

