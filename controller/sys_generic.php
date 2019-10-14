<?php

class sys_generic {
    function __construct($uri) {
        utils::load([
            views.get_class(),
            models.get_class(),
            models.'sys_grid'
        ]);
        $this->uri = $uri;

        $this->frame_model = new frame_model();
        $this->sys_grid_model = new sys_grid_model();
        $this->frame_view = new frame_view();
        $this->model = new sys_generic_model();
        $this->view = new sys_generic_view();

        $this->object = $this->sys_grid_model->get($this->frame_model->getResourceGrid($uri));

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

        
    }

    function main() {
        $this->frame_view->main([
            'menu' => $this->uri,
            'css' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select'],
            'js' => ['datatables', 'datatables-select', 'datetimepicker', 'bootstrap-select', 'autonumeric', $this->jsData, '/js/'.get_class().'.js'],
            'concatPlugins' => true,
            'body' => [
                'title' => $this->object->name,
                'subtitle' => 'Grilla generada automáticamente',
                'html' => $this->view->html()
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list($this->object));
    }

    function form() {
        if (!isset($_POST["id"])) {
            echo $this->view->form($this->object);
        } else {
            $data = $this->model->get($_POST["id"], $this->object);
            echo $this->view->form($this->object, $data);
        }
    }

    function set() {
        echo json_encode($this->model->set($_POST, $this->object));
        // echo json_encode((object)$_POST["fields"][0]);
    }

    function delete() {
        echo json_encode($this->model->delete($_POST['list']));
    }
}