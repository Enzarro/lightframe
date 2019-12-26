<?php

class sys_socket {
    function __construct() {
        $this->view = new sys_socket_view();
        $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'daterange'],
            'js' => ['datatables', 'datatables-select', 'daterange','sweetalert', '/js/'.get_class().'.js'],
            'concatPlugins' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html([
                    'modalTitle' => $this->resdata->texto
                ])
            ]
        ]);
    }

    function get() {
        $current = 0;
        $max = 100;
        
        $decimals = isset(explode('.', $_POST['step'])[1])?strlen(explode('.', $_POST['step'])[1]):0;

        while ($current <= $max) {
            utils::emit('update-bar', [
                'current' => round($current, $decimals),
                'max' => $max
            ]);
            $current = $current + $_POST['step'];
            usleep(1000 * $_POST['time']);
        }
    }
}