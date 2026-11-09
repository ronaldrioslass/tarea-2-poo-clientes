<?php

class Cliente{
    private $conexion;

    private $id;
    private $nombre;
    private $apellido;
    private $cedula;
    private $correo;
    private $telefono;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }


    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }

    public function getNombre(){
        return $this->nombre;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }

    public function getApellido(){
        return $this->apellido;
    }
    public function setApellido($apellido){
        $this->apellido = $apellido;
    }

    public function getCedula(){
        return $this->cedula;
    }
    public function setCedula($cedula){
        $this->cedula = $cedula;
    }

    public function getCorreo(){
        return $this->correo;
    }
    public function setCorreo($correo){
        $this->correo = $correo;
    }

    public function getTelefono(){
        return $this->telefono;
    }
    public function setTelefono($telefono){
        $this->telefono = $telefono;
    }


    public function crearTabla(){
        $cadena = "create table if not exists clientes(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        cedula VARCHAR(10) NOT NULL,
        correo VARCHAR(150) NOT NULL,
        telefono VARCHAR(10) NOT NULL)";

        return $this->conexion->query($cadena);
    }

    public function guardar(){
        $cadena = "insert into clientes(nombre, apellido, cedula, correo, telefono) values(?, ?, ?, ?, ?)";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('sssss', $this->nombre, $this->apellido, $this->cedula, $this->correo, $this->telefono);
        $resultado = $ejecutar->execute();

        $this->id = $this->conexion->insert_id;
        return $resultado;
    }

    public function todos(){
        $cadena = "select * from clientes";
        $resultado = $this->conexion->query($cadena);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarPorId(){
        $cadena = "select * from clientes where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('i', $this->id);
        $ejecutar->execute();
        $fila = $ejecutar->get_result()->fetch_assoc();

        if ($fila === null) {
            return false;
        }

        $this->nombre = $fila["nombre"];
        $this->apellido = $fila["apellido"];
        $this->cedula = $fila["cedula"];
        $this->correo = $fila["correo"];
        $this->telefono = $fila["telefono"];
        return true;
    }

    public function actualizar(){
        $cadena = "update clientes set nombre = ?, apellido = ?, cedula = ?, correo = ?, telefono = ? where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('sssssi', $this->nombre, $this->apellido, $this->cedula, $this->correo, $this->telefono, $this->id);
        return $ejecutar->execute();
    }

    public function eliminar(){
        $cadena = "delete from clientes where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('i', $this->id);
        return $ejecutar->execute();
    }
}
