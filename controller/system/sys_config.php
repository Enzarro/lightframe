<?php

class sys_config {
    function __construct() {
        $this->view = new sys_config_view();
        $this->model = new sys_config_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            // 'css' => ['datatables'],
            'js' => [/*'datatables', */'/js/system/sys_config.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'title' => 'Configuraciones',
                'subtitle' => 'Parámetros del frame',
                'html' => $this->view->html([
                    'modalTitle' => 'Configuraciones'
                ])
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

    function initdb() {
        set_time_limit(60 * 10);
        
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
                $return['swal'] = $this->model->createTables();
                $this->model->createSP("dbo");
            }
        }
        echo json_encode($return);
    }

    function wipedb() {
        $data = $this->model->wipedb();
        // echo json_encode($data, JSON_PRETTY_PRINT);
        // return;
        echo $this->view->wipe_resume($data);
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