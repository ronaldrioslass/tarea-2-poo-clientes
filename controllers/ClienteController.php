<?php

class ClienteController{
    private $cliente;

    public function __construct($conexion) {
        $this->cliente = new Cliente($conexion);
    }

    public function creartabla(){
        $resultado = $this->cliente->crearTabla();
        echo json_encode([
            "tabla_creada" => $resultado
        ]);
    }

    public function todos(){
        $datos = $this->cliente->todos();
        echo json_encode($datos);
    }

    public function uno($id){
        $this->cliente->setId($id);

        if (!$this->cliente->buscarPorId()) {
            $this->clienteNoEncontrado();
            return;
        }

        echo json_encode([
            "id" => $this->cliente->getId(),
            "nombre" => $this->cliente->getNombre(),
            "apellido" => $this->cliente->getApellido(),
            "cedula" => $this->cliente->getCedula(),
            "correo" => $this->cliente->getCorreo(),
            "telefono" => $this->cliente->getTelefono()
        ]);
    }

    public function guardar($datos){
        $this->llenarCliente($datos);
        $resultado = $this->cliente->guardar();

        http_response_code(201);
        echo json_encode([
            "guardado" => $resultado,
            "id" => $this->cliente->getId()
        ]);
    }

    public function actualizar($id, $datos){
        $this->cliente->setId($id);

        if (!$this->cliente->buscarPorId()) {
            $this->clienteNoEncontrado();
            return;
        }

        $this->llenarCliente($datos);
        $resultado = $this->cliente->actualizar();
        echo json_encode([
            "actualizado" => $resultado
        ]);
    }

    public function eliminar($id){
        $this->cliente->setId($id);

        if (!$this->cliente->buscarPorId()) {
            $this->clienteNoEncontrado();
            return;
        }

        $resultado = $this->cliente->eliminar();
        echo json_encode([
            "eliminado" => $resultado
        ]);
    }

    private function llenarCliente($datos){
        $this->cliente->setNombre($datos["nombre"]);
        $this->cliente->setApellido($datos["apellido"]);
        $this->cliente->setCedula($datos["cedula"]);
        $this->cliente->setCorreo($datos["correo"]);
        $this->cliente->setTelefono($datos["telefono"]);
    }

    private function clienteNoEncontrado(){
        http_response_code(404);
        echo json_encode(["error" => "El cliente no existe"]);
    }
}
