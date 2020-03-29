<?php

class sys_sp {
    function __construct() {
        $this->frame_view = new frame_view();
        $this->model = new sys_tables_model();
        $this->view = new sys_tables_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select', '/js/system/'.get_class().'.js','popover'],
            'concatPlugins' => true,
            'body' => [
                'title' => 'Procedimietosky almacenadosky',
                'html' => $this->view->html([
                    'modalTitle' => 'Tabla'
                ])
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
    }

    function get() {

    }

    function set() {

    }
}