<?php

class test2 {

    function __construct() {
        utils::load([
            views.get_class(),
            models.get_class()
        ]);
        $this->frame_view = new frame_view();
        $this->model = new test2_model();
        $this->view = new test2_view();
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

}