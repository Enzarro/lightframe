<?php

class mantenedor_examen {
    function __construct() {
        utils::load([
            views.'mantenedor_examen'
        ]);
        $this->frame_view = new frame_view();
        $this->view = new mantenedor_examen_view();
    }

    function main() {
        $this->frame_view->main([
            'css' => ['datatables'],
            'js' => ['datatables', '/js/mantenedor_examen.js'],
            'body' => [
                'title' => 'Mantenedor examen',
                'subtitle' => 'Mantenedor prueba frame',
                'html' => $this->view->html()
            ]
        ]);
    }

    function get() {
        //Llamada al modelo, retorno de JSON con resultado
        echo "chapalapachala";
    }
}