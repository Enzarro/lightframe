<?php

class sys_crud {
    function __construct() {
        utils::load([
            views.'sys_crud',
            models.'sys_crud'
        ]);
        $this->frame_view = new frame_view();
        $this->view = new sys_crud_view();
        $this->model = new sys_crud_model();
    }

    function main() {
        $this->frame_view->main([
            'css' => ['datatables'],
            'js' => ['datatables', '/js/sys_crud.js'],
            'body' => [
                'title' => 'Mantenedores',
                'subtitle' => 'Listado de mantenedores',
                'html' => $this->view->html()
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
    }

    function form() {
        echo $this->view->form();
    }

    function get() {
        //Llamada al modelo, retorno de JSON con resultado
        echo "chapalapachala";
    }

    function set() {

    }
}