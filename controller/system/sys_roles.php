<?php

class sys_roles {
    function __construct($resource) {
        $this->model = new sys_roles_model($resource);
        $this->view = new sys_roles_view();

        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select', '/css/users.css'],
            'js' => ['datatables', 'datatables-select', 'autonumeric', '/js/system/sys_roles.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'subtitle' => 'Listado de roles',
                'html' => $this->view->html([
                    'modalTitle' => 'Roles'
                ])
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
    }

    function resources() {
        echo json_encode($this->model->resources());
    }

    function form() {
        $data = null;
        $resources = $this->model->load_resources();
        $roles = $this->model->get();
        if (isset($_POST["id"])) {
            $id = $_POST["id"];
            $data = $this->model->get($id);
            $roles = array_filter($roles, function($rol) use ($id) {
                return $rol['id'] != $id;
            });
        }
        echo $this->view->form(compact(['data', 'resources', 'roles']));
    }

    function get() {
        //Llamada al modelo, retorno de JSON con resultado
        echo json_encode($_COOKIE);
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
        $roles = $this->model->get();
        $resources = [];
        foreach ($roles as $res) {
            $rol = $this->model->get($res['id'], true);
            $rol->permission = array_map(function($perm) {
                $data = json_decode($perm['permisos_obj']);
                $perm['permisos_obj'] = $data?$data:null;
                return $perm;
            }, $rol->permission);
            $resources[] = $rol;
        }
        header('Content-disposition: attachment; filename=sys_roles.json');
        header('Content-type: application/json');
        echo json_encode($resources, JSON_PRETTY_PRINT);
    }

    function import() {
        global $_DB;
        set_time_limit(60 * 10);

        $file_content = file_get_contents($_FILES['main-import']['tmp_name']);
        $data = json_decode($file_content, true);
        if ($data && count($data)) {

            

            $this->model->setJSON($data, true);

            echo json_encode([
                'type' => 'success',
                'title' => 'Definiciones cargadas',
                'html' => 'Se han cargado <b>'.count($data).'</b> definiciones de roles.'
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