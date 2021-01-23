<?php

class sys_grid {
    function __construct() {
        $this->frame_view = new frame_view();
        $this->model = new sys_grid_model();
        $this->view = new sys_grid_view();
        $this->clients_model = new sys_clients_model();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['jsoneditor', 'datatables', 'datatables-select', 'datatables-rowreorder'],
            'js' => ['jsoneditor', 'datatables', 'datatables-select', 'datatables-rowreorder', '/js/system/'.get_class().'.js','popover'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'title' => 'Definición de tablas',
                'html' => $this->view->html([
                    'modalTitle' => 'Tabla'
                ])
            ]
        ]);
    }

    function list() {
        return $this->model->list();
    }

    function form() {
        if (!isset($_POST["id"])) {
            return $this->view->form();
        } else {
            $data = $this->model->get($_POST["id"]);
            return $this->view->form($data);
        }
    }

    function consolidate() {
        set_time_limit(60 * 10);
        
        $grid = $this->model->get($_POST['id']);

        if ($grid->target_schema == 2) {
            //Traer listado de clientes
            $clients = $this->clients_model->get();

            $lastres = null;
            foreach (array_keys($clients) as $key) {
                $clientData = (object)$clients[$key];
                $lastres = $this->model->consolidate($grid->id, $clientData->db_name);
            }
            return $lastres;
        } else {
            //Consolidar tabla seleccionada
            return $this->model->consolidate($grid->id);
        }
    }

    function set() {
        return $this->model->set($_POST);
        // echo json_encode((object)$_POST["fields"][0]);
    }

    function delete() {
        return $this->model->delete($_POST['list']);
    }

    function export() {
        set_time_limit(60 * 10);

        //Obtener grillas
        $grid_list = $this->model->get();
        $grids = [];
        foreach ($grid_list as $grid) {
            $grids[] = $this->model->get($grid['grid_id'], true);
        }
        header('Content-disposition: attachment; filename=sys_grid.json');
        header('Content-type: application/json');
        echo json_encode($grids, JSON_PRETTY_PRINT);
        die;
    }

    function import() {
        global $_DB;
        set_time_limit(60 * 10);

        $file_content = file_get_contents($_FILES['main-import']['tmp_name']);
        $data = json_decode($file_content, true);
        if ($data && count($data)) {
            $_DB->query("DELETE FROM sys_grids");
            $_DB->query("DELETE FROM sys_grids_fields");
            $_DB->query("DELETE FROM sys_fields_attrs");

            $count = 0;
            foreach ($data as $grid) {
                $count++;
                $this->model->set($grid, true);

                utils::emit('sys_grid:import:bar', [
					'bar_grids' => [
						'name' => $grid['name'],
						'current' => $count,
						'max' => count($data)
					]
				]);
            }
            return [
                'type' => 'success',
                'title' => 'Definiciones cargadas',
                'html' => 'Se han cargado <b>'.count($data).'</b> definiciones de tablas.'
            ];
        } else {
            return [
                'type' => 'warning',
                'title' => 'Definiciones no cargadas',
                'html' => 'No hay registros en el archivo subido.'
            ];
        }
        
    }
}