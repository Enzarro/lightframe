<?php

class sys_tree {
    function __construct() {
        utils::load([
            views.'sys_tree',
            models.'sys_tree'
        ]);
        $this->view = new sys_tree_view();
        $this->model = new sys_tree_model();
        $this->frame_view = new frame_view();
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables'],
            'js' => ['datatables', '/js/sys_tree.js'],
            'body' => [
                'title' => 'Árbol de recursos',
                'subtitle' => 'Parametrización de estructura de menús',
                'html' => $this->view->html()
            ]
        ]);
    }

    function list() {
        echo json_encode($this->model->list());
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
            $data = [];
            if (is_array($_POST["db"])) {
                $data = (object)$_POST["db"];
            } else {
                $data = json_decode($_POST["db"]);
            }
            $return['swal'] = $this->model->setDB($data);
        }
        echo json_encode($return);
    }

    function testdb() {
        $return = [];
        if (isset($_POST["db"])) {
            $data = [];
            if (is_array($_POST["db"])) {
                $data = (object)$_POST["db"];
            } else {
                $data = json_decode($_POST["db"]);
            }
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