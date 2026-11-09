const API = "../index.php";

const formulario = document.getElementById("formCliente")
const campoId = document.getElementById("id")
const tablahtml = document.getElementById("tablaClientes")
const mensaje = document.getElementById("mensaje")
const tituloFormulario = document.getElementById("tituloFormulario")
const botonGuardar = document.getElementById("botonGuardar")
const botonCancelar = document.getElementById("botonCancelar")

// tipo puede ser "success" (verde) o "danger" (rojo)
function mostrarMensaje(texto, tipo) {
    mensaje.textContent = texto;
    mensaje.className = "alert alert-" + tipo;
}

// Llama a la API y devuelve la respuesta en JSON.
// Si la API responde con error, lanza un Error con el mensaje del servidor.
async function llamarAPI(parametros, opciones = {}) {
    const respuesta = await fetch(API + "?" + parametros, opciones);
    const resultado = await respuesta.json();
    if (!respuesta.ok) {
        throw new Error(resultado.error || "Ocurrió un error");
    }
    return resultado;
}

// ----- R: listar -----

async function todos() {
    try {
        const clientes = await llamarAPI("accion=todos");
        dibujarTabla(clientes);
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

function dibujarTabla(clientes) {
    tablahtml.innerHTML = "";

    if (clientes.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = 7;
        celda.className = "text-center text-muted";
        celda.textContent = "Todavía no hay clientes registrados.";
        fila.appendChild(celda);
        tablahtml.appendChild(fila);
        return;
    }

    clientes.forEach(cliente => {
        tablahtml.appendChild(crearFila(cliente));
    });
}

function crearFila(cliente) {
    const fila = document.createElement("tr");
    const valores = [cliente.id, cliente.nombre, cliente.apellido, cliente.cedula, cliente.correo, cliente.telefono];

    valores.forEach(valor => {
        const celda = document.createElement("td");
        celda.textContent = valor;
        fila.appendChild(celda);
    });

    const nombreCompleto = cliente.nombre + " " + cliente.apellido;
    const celdaAcciones = document.createElement("td");
    celdaAcciones.className = "text-nowrap";
    celdaAcciones.appendChild(
        crearBoton("Editar", "Editar a " + nombreCompleto, "btn btn-sm btn-warning me-2", () => editar(cliente.id))
    );
    celdaAcciones.appendChild(
        crearBoton("Eliminar", "Eliminar a " + nombreCompleto, "btn btn-sm btn-danger", () => eliminar(cliente))
    );
    fila.appendChild(celdaAcciones);

    return fila;
}

function crearBoton(texto, descripcion, clases, alHacerClic) {
    const boton = document.createElement("button");
    boton.type = "button";
    boton.textContent = texto;
    boton.className = clases;
    boton.setAttribute("aria-label", descripcion);
    boton.addEventListener("click", alHacerClic);
    return boton;
}

// ----- C y U: guardar o actualizar -----

formulario.addEventListener("submit", async function(event) {

    event.preventDefault();

    // Si el campo oculto tiene un id, estamos editando un cliente
    const editando = campoId.value !== "";
    const accion = editando ? "actualizar" : "guardar";
    const datos = new URLSearchParams(new FormData(formulario));

    try {
        await llamarAPI("accion=" + accion, {
            method: "POST",
            body: datos
        });

        mostrarMensaje(editando ? "Cliente actualizado correctamente" : "Cliente guardado correctamente", "success");
        salirModoEdicion();
        await todos();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
});

// Trae el cliente desde la API y pone sus datos en el formulario
async function editar(id) {
    try {
        const cliente = await llamarAPI("accion=uno&id=" + id);

        campoId.value = cliente.id;
        formulario.nombre.value = cliente.nombre;
        formulario.apellido.value = cliente.apellido;
        formulario.cedula.value = cliente.cedula;
        formulario.correo.value = cliente.correo;
        formulario.telefono.value = cliente.telefono;

        tituloFormulario.textContent = "Editar cliente #" + cliente.id;
        botonGuardar.textContent = "Actualizar";
        botonCancelar.hidden = false;
        formulario.nombre.focus();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

function salirModoEdicion() {
    formulario.reset();
    campoId.value = "";
    tituloFormulario.textContent = "Registrar cliente";
    botonGuardar.textContent = "Guardar";
    botonCancelar.hidden = true;
}

botonCancelar.addEventListener("click", salirModoEdicion);

// ----- D: eliminar -----

async function eliminar(cliente) {
    const confirmado = confirm("¿Seguro que deseas eliminar a " + cliente.nombre + " " + cliente.apellido + "?");
    if (!confirmado) {
        return;
    }

    const datos = new URLSearchParams();
    datos.append("id", cliente.id);

    try {
        await llamarAPI("accion=eliminar", {
            method: "POST",
            body: datos
        });

        mostrarMensaje("Cliente eliminado correctamente", "success");

        // Si justo estábamos editando a este cliente, limpiamos el formulario
        if (campoId.value == cliente.id) {
            salirModoEdicion();
        }
        await todos();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

// ----- Crear la tabla en la base de datos -----

async function crearTabla() {
    try {
        const resultado = await llamarAPI("accion=creartabla", {
            method: "POST"
        });
        if (!resultado.tabla_creada) {
            throw new Error("No se pudo crear la tabla");
        }
        mostrarMensaje("Tabla creada correctamente. Si ya existía, se conservaron los datos.", "success");
        await todos();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

document.getElementById("crearTabla").addEventListener("click", crearTabla);

todos();
