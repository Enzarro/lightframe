<?php

class users {
    function __construct() {
        utils::load([
            views.get_class(),
            models.get_class()
        ]);
        $this->frame_view = new frame_view();
        $this->model = new users_model();
        $this->view = new users_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', 'icheck'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', 'icheck', '/js/users.js'],
            'concatPlugins' => false,
            'body' => [
                'title' => 'Usuarios',
                'subtitle' => 'Listado de usuarios',
                'html' => $this->view->html()
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
    }

    function resources() {
        echo json_encode($this->model->resources());
    }

    function form() {
        $data_resources = $this->model->load_resources();
        if (!isset($_POST["id"])) {   
            echo $this->view->form(null, $data_resources);
        } else {
            $data = $this->model->get($_POST["id"]);
            echo $this->view->form($data, $data_resources);
        }      
    }

    function get() {
        //Llamada al modelo, retorno de JSON con resultado
        echo json_encode($_COOKIE);
    }

    function set() {
        echo json_encode($this->model->set($_POST));
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }
}