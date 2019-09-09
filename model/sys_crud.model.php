<?php

class sys_crud_model {

    var $db;

    function __construct() {
        if (file_exists(root."/config.json")) {
            $this->cruds = json_decode(file_get_contents(root."/cruds.json"));
        }
        if (!$this->cruds) {
            $this->cruds = [];
        }
    }

    function list() {
        if (isset($_POST["config"]) && $_POST["config"]) {
            $targets = 0;
            return [[
                'targets' => $targets++,
                'title' => 'Nombre'
            ], [
                'targets' => $targets++,
                'title' => 'Campos'
            ], [
                'targets' => $targets++,
                'title' => 'Acciones',
                'data' => null,
                'searchable' => false,
                'orderable' => false,
                'width' => '100px',
                'defaultContent' => 
                    '<div class="btn-group btn-group" role="group" style="width: auto;">
                        <button id="edit" class="btn btn-success" title="Editar registro" type="button"><span aria-hidden="true" class="fa fa-pencil"></span></button>
                    </div>'
            ]];
        } else {
            return [
                'data' => array_map(function($crud) {
                    return [
                        0 => $crud->name,
                        1 => count($crud->fields)
                    ];
                }, $this->cruds)
            ];
        }
        
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