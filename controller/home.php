<?php

class home {
    function __construct() {
        $this->view = new home_view();
        $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables'],
            'js' => ['datatables'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                // 'subtitle' => 'Inicio del sistema',
                'html' => $this->view->html()
            ]
        ]);
    }
}