<?php

class sys_grid {
    function __construct() {
        utils::load([
            views.get_class(),
            models.get_class()
        ]);
        $this->frame_view = new frame_view();
        $this->model = new sys_grid_model();
        $this->view = new sys_grid_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', '/js/'.get_class().'.js'],
            'concatPlugins' => false,
            'body' => [
                'title' => 'Grillas',
                'subtitle' => 'Listado de grillas',
                'html' => $this->view->html()
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