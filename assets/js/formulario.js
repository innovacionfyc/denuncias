function mostrarArchivos(input, contenedorId) {
  const lista = document.getElementById(contenedorId);
  const loader = document.getElementById(
    contenedorId === 'fotosSeleccionadas' ? 'loaderFotos' : 'loaderAudios'
  );

  lista.innerHTML = '';
  loader.classList.remove('hidden');

  setTimeout(() => {
    for (const archivo of input.files) {
      const li = document.createElement('li');
      li.textContent = archivo.name;
      lista.appendChild(li);
    }

    loader.classList.add('hidden');
  }, 900); // simula tiempo de carga
}
