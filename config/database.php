<?php

class Database{
    private $conexion;
    
    public function conectar(){
        $this->conexion = new mysqli("localhost",'root','root','sexto');
        if($this->conexion->connect_error){
            throw new RuntimeException("Error al conectar con el servidor");
        }
        $this->conexion->set_charset("utf8mb4");
        return $this->conexion;
    }
}