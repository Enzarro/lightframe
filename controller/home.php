<?php

class home {
    function __construct() {
        utils::load([
            views.'home'
        ]);
        $this->view = new home_view();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'css' => ['datatables'],
            'js' => ['datatables'],
            'concatPlugins' => true,
            'body' => [
                'title' => 'Dashboard',
                'subtitle' => 'Inicio del sistema',
                'html' => $this->view->html()
            ]
        ]);
    }

    function get() {
        echo "aqui esta la wea de get";
    }
}