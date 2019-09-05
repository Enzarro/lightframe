<?php

class login {
    function __construct() {
        //include(models.'m.login.php');
        utils::load([
            views.'login'
        ]);
        //$this->model = new m_login();
        $this->view = new v_login();
    }

    function main() {
        $this->view->html();
    }

    function error() {
        $this->view->html([
            'error' => true
        ]);
    }

    function login() {
        global $config;
        $returns = [
            "swal" => [
                "type" => "",
                "title" => "",
                "text" => ""
            ]
        ];
        if (!$_POST["username"] || !$_POST["password"]) {
            $returns["swal"] = [
                "type" => "",
                "title" => "",
                "text" => ""
            ];
            echo json_encode($returns);
            return;
        }
        //Login superuser
        if ($config && $config->superuser && ($_POST["username"] == $config->superuser->username && $_POST["password"] == $config->superuser->password)) {
            session_start();
            $_SESSION['key'] = true;
            header('Location: /');
            return;
        }
        header('Location: /login/error');
        return;
    }

    function logout() {
        session_start();
        session_destroy();
        header('Location: /');
        return;
    }
}