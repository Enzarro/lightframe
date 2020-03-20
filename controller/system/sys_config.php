<?php

class sys_config {
    function __construct() {
        utils::load([
            views.'sys_config',
            models.'sys_config'
        ]);
        $this->view = new sys_config_view();
        $this->model = new sys_config_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            // 'css' => ['datatables'],
            'js' => [/*'datatables', */'/js/sys_config.js'],
            'concatPlugins' => true,
            'body' => [
                'title' => 'Configuraciones',
                'subtitle' => 'Parámetros del frame',
                'html' => $this->view->html()
            ]
        ]);
    }

    //User
    function setuser() {
        $return = [];
        if (isset($_POST["user"])) {
            $data = [];
            if (is_array($_POST["user"])) {
                $data = (object)$_POST["user"];
            } else {
                $data = json_decode($_POST["user"]);
            }
            $return['swal'] = $this->model->setUser($data);
        }
        echo json_encode($return);
    }

    //DB
    function setdb() {
        $return = [];
        if (isset($_POST["db"])) {
            //Setear variable desde post
            $data = [];
            if (is_array($_POST["db"])) {
                $data = (object)$_POST["db"];
            } else {
                $data = json_decode($_POST["db"]);
            }
            //Probar conexión
            $return['swal'] = $this->model->testDB($data);
            if ($return['swal']['type'] == 'success') {
                //Guardar configuración
                $return['swal'] = $this->model->setDB($data);
                //Crear tablas
                $this->model->createTables();
            }
            
        }
        echo json_encode($return);
    }

    function testdb() {
        $return = [];
        if (isset($_POST["db"])) {
            //Setear variable desde post
            $data = [];
            if (is_array($_POST["db"])) {
                $data = (object)$_POST["db"];
            } else {
                $data = json_decode($_POST["db"]);
            }
            //Probar conexión
            $return['swal'] = $this->model->testDB($data);
        }
        echo json_encode($return);
    }

    //DB
    function setlogin() {
        $return = [];
        if (isset($_POST["login"])) {
            $data = [];
            if (is_array($_POST["login"])) {
                $data = (object)$_POST["login"];
            } else {
                $data = json_decode($_POST["login"]);
            }
            $return['swal'] = $this->model->setLogin($data);
        }
        echo json_encode($return);
    }

    function testlogin() {
        if (isset($_POST["login"])) {
            $data = [];
            if (is_array($_POST["login"])) {
                $data = (object)$_POST["login"];
            } else {
                $data = json_decode($_POST["login"]);
            }
            $return['swal'] = $this->model->testLogin($data);
        }
    }

}