<?php

class sys_query {
    function __construct($resource) {
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
        $this->model = new sys_query_model();
        $this->view = new sys_query_view($resource);
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select','datetimepicker'],
            'js' => [
                'datatables', 'datatables-select', 'autonumeric', 
                '/app-assets/vendors/js/editors/tinymce/tinymce.min.js',
                '/js/system/'.get_class().'.js', 'moment','datetimepicker'
            ],
            'concatPlugins' => false,
            'cboClient' => true,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html()
            ]
        ]);
    }

    function query() {
        if (isset($_POST['query']) || !$_POST['query']) {
            echo json_encode($this->model->query($_POST['query']));
        } else {
            echo json_encode([
                'swal' => [
                    'type' => 'error',
                    'text' => 'No se ha recibido una query'
                ]
            ]);
        }
    }

}