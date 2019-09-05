<?php

class mantenedor_examen_model {

    var $db;

    function __construct() {
        $this->db = new database();
    }

    function list() {
        $data = $this->db->query_to_array("SELECT * FROM usuarios");
        return $data;
    }

    function get($id) {
        $data = $this->db->query_to_array("SELECT * FROM usuarios WHERE usuario_id = {$id}");
        return $data;
    }

    function set($id, $data) {
        if ($id) {
            $data = $this->db->query("UPDATE usuarios");
        } else {
            $data = $this->db->query("INSERT INTO usuarios");
        }
        
        return $data;
    }

    function delete() {
        $this->db->query("DELETE");
    }
}