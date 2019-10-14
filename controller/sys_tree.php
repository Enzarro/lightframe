<?php

class sys_tree {
    function __construct() {
        utils::load([
            views.'sys_tree',
            models.'sys_tree'
        ]);
        $this->view = new sys_tree_view();
        $this->model = new sys_tree_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', '/js/sys_tree.js'],
            'body' => [
                'title' => 'Árbol de recursos',
                'subtitle' => 'Parametrización de estructura de menús',
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

    function set() {
        echo json_encode($this->model->set($_POST));
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

}