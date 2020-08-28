<?php

class sys_clients {
    function __construct() {
        $this->frame_view = new frame_view();
        $this->model = new sys_clients_model();
        $this->view = new sys_clients_view();
        $this->utils = new utils();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', '/js/system/'.get_class().'.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'title' => 'Proyectos',
                'subtitle' => 'Listado de Clientes',
                'html' => $this->view->html([
                    'modalTitle' => 'Proyectos'
                ])
            ]
        ]);
    }

    function get() {
        echo json_encode($this->model->get());
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

    function consolidate() {
        set_time_limit(60 * 10);
        //Consolidar cliente seleccionado
        echo json_encode($this->model->consolidate($_POST['id'], ['event' => 'sys_clients']));
    }

    function spdbo() {
        //Consolidar sp de cliente seleccionado
        $this->utils->executeSP('dbo');
        echo json_encode([
			'type' => 'success',
			'text' => 'Los SP del esquema han sido consolidados'
		]);
    }

    function spcli() {
        //Consolidar sp de cliente seleccionado
        $clients = $this->model->get();
        $result = [];
        foreach (array_keys($clients) as $key) {
            $clients[$key]["db_name"] = trim($clients[$key]["db_name"]);
            $clientData = (object)$clients[$key];
            $this->utils->executeSP($clientData->db_name);
            $result = $this->utils->execSPFailReport;
        }
        echo json_encode([
			'type' => 'success',
            'title' => 'Los SP de esquemas cli han sido consolidados',
            'html' => '<pre>'.json_encode($result, JSON_PRETTY_PRINT).'</pre>'
		]);
    }

    function sp() {
        //Traer datos del cliente
		$clientData = $this->model->get($_POST['id']);
        //Consolidar sp de cliente seleccionado
        $this->utils->executeSP($clientData->db_name);
        echo json_encode([
			'type' => 'success',
			'text' => 'Los SP del esquema han sido consolidados'
		]);
    }

    function consolidate_all() {
        set_time_limit(60 * 10);
        //Consolidar todo
        if (isset($_POST['resume']) && $_POST['resume']) {
            echo $this->view->resume($this->model->consolidate_all(true));
        } else {
            echo json_encode($this->model->consolidate_all());
        }
        
    }

    function cleansing() {
        set_time_limit(60 * 10);
        //Consolidar todo
        if (isset($_POST['resume']) && $_POST['resume']) {
            echo $this->view->cleansing_resume($this->model->cleansing(true));
        } else {
            echo json_encode($this->model->cleansing());
        }
    }

    function set() {
        echo json_encode($this->model->set($_POST));
        // echo json_encode((object)$_POST["fields"][0]);
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

    function test() {
        echo "hola mundo";
    }
}