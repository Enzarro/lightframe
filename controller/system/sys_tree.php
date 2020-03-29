<?php

class sys_tree {
    function __construct() {
        $this->view = new sys_tree_view();
        $this->model = new sys_tree_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select', '/js/system/sys_tree.js'],
            'concatPlugins' => true,
            'cboClient' => false,
            'body' => [
                'title' => 'Árbol de recursos',
                'subtitle' => 'Parametrización de estructura de menús',
                'html' => $this->view->html([
                    'modalTitle' => 'Árbol de Recursos'
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
            $data = $this->model->get($_POST["id"]);
            echo $this->view->form($data);
        }
    }

    function set() {
        echo json_encode($this->model->set($_POST));
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }

    function export() {
        set_time_limit(60 * 10);

        //Obtener grillas
        $res_list = $this->model->get();
        $resources = [];
        foreach ($res_list as $res) {
            $resources[] = $this->model->get($res['recurso_id'], true);
        }
        header('Content-disposition: attachment; filename=sys_tree.json');
        header('Content-type: application/json');
        echo json_encode($resources, JSON_PRETTY_PRINT);
    }

    function import() {
        global $_DB;
        set_time_limit(60 * 10);

        $file_content = file_get_contents($_FILES['main-import']['tmp_name']);
        $data = json_decode($file_content, true);
        if ($data && count($data)) {
            $_DB->query("DELETE FROM sys_recursos");

            $count = 0;
            foreach ($data as $res) {
                $count++;
                $this->model->set($res, true);

                utils::emit('sys_tree:import:bar', [
					'bar_trees' => [
						'name' => $res['name'],
						'current' => $count,
						'max' => count($data)
					]
				]);
            }
            echo json_encode([
                'type' => 'success',
                'title' => 'Definiciones cargadas',
                'html' => 'Se han cargado <b>'.count($data).'</b> definiciones de rescursos.'
            ]);
        } else {
            echo json_encode([
                'type' => 'warning',
                'title' => 'Definiciones no cargadas',
                'html' => 'No hay registros en el archivo subido.'
            ]);
        }
        
    }

}