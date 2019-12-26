<?php

class sys_tree {
    function __construct() {
        $this->view = new sys_tree_view();
        $this->model = new sys_tree_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select', '/js/sys_tree.js'],
            'concatPlugins' => true,
            'cboClient' => false,
            'body' => [
                'title' => 'Árbol de recursos',
                'subtitle' => 'Parametrización de estructura de menús',
                'html' => $this->view->html([
                    'modalTitle' => 'Árbol de Recursos'
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

    function set() {
        echo json_encode($this->model->set($_POST));
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

}