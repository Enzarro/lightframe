<?php

class sys_clients {
    function __construct() {
        $this->frame_view = new frame_view();
        $this->model = new sys_clients_model();
        $this->view = new sys_clients_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', '/js/'.get_class().'.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'title' => 'Proyectos',
                'subtitle' => 'Listado de Clientes',
                'html' => $this->view->html([
                    'modalTitle' => 'Proyectos'
                ])
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
    }

    function form() {
        if (!isset($_POST["id"])) {
            echo $this->view->form();
        } else {
            $data = $this->model->get($_POST["id"]);
            echo $this->view->form($data);
        }
    }

    function consolidate() {
        //Consolidar tabla seleccionada
        echo json_encode($this->model->consolidate($_POST['id']));
    }

    function consolidate_all() {
        set_time_limit(60 * 10);
        //Consolidar todo
        if (isset($_POST['resume']) && $_POST['resume']) {
            echo $this->view->resume($this->model->consolidate_all(true));
        } else {
            echo json_encode($this->model->consolidate_all());
        }
        
    }

    function set() {
        echo json_encode($this->model->set($_POST));
        // echo json_encode((object)$_POST["fields"][0]);
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

    function test() {
        echo "hola mundo";
    }
}