// Logica del panel de gestion de envios: carga, filtro y actualizacion de estado
const cuerpoTabla = document.getElementById('cuerpoTablaEnvios');
const filtroEstado = document.getElementById('filtroEstado');
const mensajeEstado = document.getElementById('mensajeEstado');

const ESTADOS = ['pendiente', 'en_transito', 'entregado', 'devuelto'];

async function cargarEnvios() {
    const estado = filtroEstado.value;
    const url = estado ? `php/listar_envios.php?estado=${encodeURIComponent(estado)}` : 'php/listar_envios.php';

    try {
        const respuesta = await fetch(url);
        const cuerpo = await respuesta.json();

        if (!cuerpo.exito) {
            mostrarMensaje(cuerpo.mensaje || 'No se pudieron cargar los envios', true);
            return;
        }

        renderizarTabla(cuerpo.datos);
    } catch (error) {
        mostrarMensaje('Error de conexion al cargar los envios', true);
    }
}

function renderizarTabla(envios) {
    // Se reconstruye la tabla usando createElement en vez de innerHTML,
    // para evitar riesgos de inyeccion con datos provenientes del servidor.
    cuerpoTabla.textContent = '';

    envios.forEach((envio) => {
        const fila = document.createElement('tr');

        fila.appendChild(crearCelda(envio.id_envio));
        fila.appendChild(crearCelda(envio.id_pedido));
        fila.appendChild(crearCelda(envio.transportista));
        fila.appendChild(crearCelda(envio.numero_seguimiento));
        fila.appendChild(crearCelda(envio.direccion_destino));

        const celdaEstado = document.createElement('td');
        const spanEstado = document.createElement('span');
        spanEstado.className = `estado-${envio.estado}`;
        spanEstado.textContent = formatearEstado(envio.estado);
        celdaEstado.appendChild(spanEstado);
        fila.appendChild(celdaEstado);

        fila.appendChild(crearCeldaSelector(envio));

        cuerpoTabla.appendChild(fila);
    });
}

function crearCelda(valor) {
    const celda = document.createElement('td');
    celda.textContent = valor;
    return celda;
}

function crearCeldaSelector(envio) {
    const celda = document.createElement('td');
    const selector = document.createElement('select');
    selector.className = 'selector-estado';

    ESTADOS.forEach((estado) => {
        const opcion = document.createElement('option');
        opcion.value = estado;
        opcion.textContent = formatearEstado(estado);
        if (estado === envio.estado) {
            opcion.selected = true;
        }
        selector.appendChild(opcion);
    });

    selector.addEventListener('change', () => actualizarEstadoEnvio(envio.id_envio, selector.value));
    celda.appendChild(selector);
    return celda;
}

function formatearEstado(estado) {
    const nombres = {
        pendiente: 'Pendiente',
        en_transito: 'En transito',
        entregado: 'Entregado',
        devuelto: 'Devuelto',
    };
    return nombres[estado] || estado;
}

async function actualizarEstadoEnvio(idEnvio, nuevoEstado) {
    try {
        const respuesta = await fetch('php/actualizar_estado_envio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idEnvio, estado: nuevoEstado }),
        });
        const cuerpo = await respuesta.json();

        if (!cuerpo.exito) {
            mostrarMensaje(cuerpo.mensaje || 'No se pudo actualizar el envio', true);
            return;
        }

        mostrarMensaje(`Envio ${idEnvio} actualizado a "${formatearEstado(nuevoEstado)}"`, false);
        cargarEnvios();
    } catch (error) {
        mostrarMensaje('Error de conexion al actualizar el envio', true);
    }
}

function mostrarMensaje(texto, esError) {
    mensajeEstado.textContent = texto;
    mensajeEstado.style.color = esError ? '#a81c2c' : '#1c8a4b';
}

filtroEstado.addEventListener('change', cargarEnvios);
document.addEventListener('DOMContentLoaded', cargarEnvios);
