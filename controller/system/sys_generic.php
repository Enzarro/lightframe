<?php

class sys_generic {
    function __construct($uri, $resource) {
        global $client;
        global $config;
        
        $this->frame_model = new frame_model();
        $this->frame_view = new frame_view();
        $this->sys_grid_model = new sys_grid_model();

        $this->uri = $uri;
        $this->resource = $resource;
        $this->object = $this->sys_grid_model->get($this->frame_model->getResourceGrid($this->uri));
        // if ($this->object->target_schema == 2) {
        //     $this->object->table = $client?"{$client->db_name}.{$this->object->table}":false;
        // } else if ($this->object->target_schema == 1) {
        //     if ($config->database->type == 'pgsql') {
        //         $this->object->table = "public.{$this->object->table}";
        //     } else if ($config->database->type == 'mssql') {
        //         $this->object->table = "dbo.{$this->object->table}";
        //     }
        // }

        $this->resdata = $this->frame_model->getResourceByPath($this->uri);

        $this->model = new sys_generic_model($resource, $this->object);
        $this->view = new sys_generic_view($resource, $this->object);

		$primaryKey = '';
		//Primary key
		foreach($this->object->fields as $field) {
			if (in_array('primary', $field->attr)) {
				$primaryKey = $field->column;
			}
        }
        $this->jsData = $this->view->jsData([
            'path' => $this->uri,
            'primary' => $primaryKey
        ]);

        $this->sys_print = new sys_print();

    }

    function main() {
        $this->frame_view->main([
            'menu' => $this->resource['funcion'],
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'daterangepicker', 'dropify'],
            'js' => ['datatables', 'datatables-select', 'moment', 'datetimepicker', 'daterangepicker', 'autonumeric', 'dropify', $this->jsData, '/js/system/'.get_class().'.js'],
            'concatPlugins' => false,
            'cboClient' => $this->object->target_schema == 2,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'subtitle' => 'Grilla generada automáticamente',
                'html' => $this->view->html([
                    'modalTitle' => $this->resdata->texto
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
            $data = $this->model->get($_POST["id"], $this->object);
            echo $this->view->form($data);
        }
    }

    function get() {
        echo json_encode($this->model->get());
    }

    function set() {
        echo json_encode($this->model->set($_POST, $_FILES));
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

    function getComboData(){
        echo json_encode($this->object = $this->model->getGridCbo($this->frame_model->getResourceGrid($this->uri)));
    }

    function export(){
        //Definición de columnas
        $excel = $this->model->export($this->resdata);
        //Datos
        if (isset($_POST["type"]) && $_POST["type"]=='export') {
            $excel["data"] = $this->model->getall();
        }
        echo json_encode($excel);
    }

    function import(){
        $file = base64_encode(file_get_contents($_FILES["main-import"]["tmp_name"],true));
		$newdata = utils::post('http://34.236.202.115', [
			'file' => $file,
			'XLStoJSON' => $_POST["XLStoJSON"]
        ], true);
		echo json_encode($this->view->resume($this->model->import($newdata)));
		exit;
    }
}