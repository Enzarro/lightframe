<?php

class sys_config {
    function __construct() {
        utils::load([
            views.'sys_config',
            models.'sys_config'
        ]);
        $this->view = new sys_config_view();
        $this->model = new sys_config_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'css' => ['datatables'],
            'js' => ['datatables', '/js/sys_config.js'],
            'body' => [
                'title' => 'Configuraciones',
                'subtitle' => 'Parámetros del frame',
                'html' => $this->view->html()
            ]
        ]);
    }

    function set() {
        $return = [];
        if (isset($_POST["user"])) {
            $data = [];
            if (is_array($_POST["user"])) {
                $data = (object)$_POST["user"];
            } else {
                $data = json_decode($_POST["user"]);
            }
            $return['swal'] = $this->model->setUser($data);
        }
        echo json_encode($return);
    }

}