<?php

class users {
    function __construct($resource) {
        $this->model = new users_model($resource);
        $this->view = new users_view();

        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', '/css/users.css'],
            'js' => ['datatables', 'datatables-select', '/js/users.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'subtitle' => 'Listado de usuarios',
                'html' => $this->view->html([
                    'modalTitle' => 'Usuarios'
                ])
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