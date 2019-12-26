<?php

class rrhh_trabajadores {
    function __construct($resource) {
        $this->model = new rrhh_trabajadores_model($resource);
		// $this->r_model = new rrhh_remun_model();
        $this->view = new rrhh_trabajadores_view($resource);

        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', 'tempus', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'tempus', 'bootstrap-select', 'bootstrap-wysiwyg', 'autonumeric', '/js/rrhh/'.get_class().'.js'],
            'concatPlugins' => false,
            'cboClient' => true,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html([
                    'modalTitle' => 'Trabajador'
                ])
            ]
        ]);
    }

    function list() {
        if (isset($_POST["config"])) {
            echo json_encode($this->model->fnDTData(null, null, $_POST["config"]));
            return;
        }
        if (isset($_POST["identifier"])) {
            echo json_encode($this->model->fnDTData($_POST["identifier"]));
        } else {
            $res = $this->model->fnDTData(null, $_POST["viewdisabled"], null, $_POST["date"]);
            ob_clean();
            echo json_encode($res);
        }
    }

    function form() {
        if (isset($_POST["identifier"])) {
            echo utf8_decode($this->view->FormTrabajador($_POST["identifier"], $_POST["date"]));
        } else {
            echo utf8_decode($this->view->FormTrabajador(null, $_POST["date"]));
        }
    }

    function set() {
        echo json_encode($this->model->InsertTrabajador(json_decode($_POST["form"], true)));
        // echo json_encode($this->model->UpdateTrabajador($_POST["identifier"], json_decode($_POST["form"], true)));
    }

    function delete() {
        echo json_encode($this->model->fnDelete($_POST["list"]));
    }
}