<?php

class cont_documentacion_clientes {
    function __construct() {
        $this->frame_view = new frame_view();
        $this->model = new cont_documentacion_clientes_model();
		$this->view = new cont_documentacion_clientes_view();
    }

    function main() {
		$this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', ],
            'js' => ['datatables', 'datatables-select', '/js/cont/cont_documentacion_clientes.js'],
            'concatPlugins' => false,
            'body' => [
				'title' => 'Documentacion Clientes',
				'icon' => 'fa fa-folder-open',
                'subtitle' => 'Listado de Documentacion de Clientes',
                'html' => $this->view->html([
                    'modalTitle' => 'Documentacion Clientes'
                ])
            ]
        ]);
	}
	
	function dtdata(){
		if (isset($_POST["config"])) {
			echo json_encode($this->model->fnDTData(null, $_POST["config"]));
			return;
		}
		if (isset($_POST["identifier"])) {
			echo json_encode($this->model->fnDTData($_POST["identifier"]));
		} else {
			echo json_encode($this->model->fnDTData(null));
		}
		return;
	}

	function form(){
		if (isset($_POST["id"])) {
			echo utf8_decode($this->view->throwForm($this->model->fnGetAllData($_POST["id"])));
		} else {
			echo utf8_decode($this->view->throwForm());
		}
		return;
	}

	function new(){
		if ($_POST["data"]) {
			echo json_encode($this->model->fnNew(json_decode($_POST["data"], true)));
			return;
		}
	}

	function update(){
		if ($_POST["id"] && $_POST["data"]) {
			echo json_encode($this->model->fnEdit($_POST["id"], json_decode($_POST["data"], true)));
			return;
		}
	}
			
	function delete(){
		if ($_POST["list"]) {
			echo json_encode($this->model->fnDelete($_POST["list"]));
			return;
		}
	}
	
	function dt_files_data(){
		if ($_POST["id"]) {
			echo json_encode(['data' => $this->model->fnGetFilesFromFolder($_POST["id"])]);
		}
	}

	function dt_files_form(){
		echo utf8_decode($this->view->uploadForm(isset($_POST["id"])?$_POST["id"]:null,isset($_POST["client"])?$_POST["client"]:null));
	}

	function file_upload(){
		if ($_POST["nombre"] && $_POST["activo"]) {
			if (isset($_POST["carpeta"])) {
				echo json_encode($this->model->fnNewFile($_POST["carpeta"], $_POST["nombre"], $_POST["descripcion"], isset($_FILES["userfile"])?$_FILES["userfile"]:null, $_POST["activo"]));
			}elseif (isset($_POST["id"])) {
				echo json_encode($this->model->fnEditFile($_POST["id"], $_POST["nombre"], $_POST["descripcion"], isset($_FILES["userfile"])?$_FILES["userfile"]:null, $_POST["activo"]));
			}
		}
	}

	function file_delete(){
		if ($_POST["list"]) {
			echo json_encode($this->model->fnDeleteFile($_POST["list"]));
			return;
		}
	}
}