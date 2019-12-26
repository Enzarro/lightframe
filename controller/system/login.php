<?php

class login {
    function __construct() {
        $this->model = new login_model();
        $this->view = new login_view();
    }

    function main() {
        $this->view->html();
    }

    function error($id = 1) {
        $this->view->html([
            'error' => $id
        ]);
    }

    function login() {
        
        global $config;
        //Si usuario o password no son enviados, enviar a página de error
        if (!$_POST["username"] || !$_POST["password"]) {
            header('Location: /login/error');
            return;
        }
        if ($config && $config->superuser && ($_POST["username"] == $config->superuser->username && $_POST["password"] == $config->superuser->password)) {
            //Login admin frame
            setcookie('token', sha1($config->superuser->username.$config->superuser->password), strtotime( '+30 days' ), "/");
            header('Location: /');
            exit;
        } else {
            //Login usuario DB
            error_log(sha1($_POST["password"]));
            $userDbRes = $this->model->get([
                'username' => $_POST["username"],
                'password' => sha1($_POST["password"])
            ]);
            if ($userDbRes) {
                //Login user db
                setcookie('token', $userDbRes, strtotime( '+30 days' ), "/");
                header('Location: /');
                exit;
            }
        }
        header('Location: /login/error');
        exit;
    }

    function logout() {
        unset($_COOKIE['token']);
        setcookie('token', null, -1, '/');
        header('Location: /');
        return;
    }
}