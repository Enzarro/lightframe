<?php

class cont_maestros_clientes{
    function __construct() {
        $this->view = new cont_maestros_clientes_view();
        $this->model = new cont_maestros_clientes_model();
        // $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main(){
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select','sweetalert', '/js/'.get_class().'.js'],
            'concatPlugins' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html()
            ]
        ]);
        
    }
}

?>