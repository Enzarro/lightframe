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
        if (!$_POST["username"] || !$_POST["password"] || !$_POST["dispositivo"]) {
            if ($_POST["dispositivo"] != 1) {
                echo json_encode([
                    "type" => "error",
                    "text" => "Usuario o contraseña inválidos"
                ]);
                die();
            } else {
                header('Location: /login/error');
                return;
            }
        }
        if ($config && $config->superuser && ($_POST["username"] == $config->superuser->username && $_POST["password"] == $config->superuser->password)) {
            $adminToken = sha1($config->superuser->username.$config->superuser->password);
            //Login admin frame
            if ($_POST["dispositivo"] != 1) {
                echo json_encode([
                    'token' => $adminToken
                ]);
                die();
            } else {
                setcookie('token', $adminToken, strtotime( '+30 days' ), "/");
                header('Location: /');
                exit;
            }
        } else {
            //Login usuario DB
            $userDbRes = $this->model->get([
                'username' => $_POST["username"],
                'password' => sha1($_POST["password"])
            ]);
            if ($userDbRes) {
                if ($_POST["dispositivo"] != 1) {
                    echo json_encode([
                        'token' => $userDbRes,
                        'data' => $this->model->getTokenData($userDbRes)
                    ]);
                    die();
                } else {
                    //Login user db
                    setcookie('token', $userDbRes, strtotime( '+30 days' ), "/");
                    header('Location: /');
                    exit;
                }
            }
        }
        if ($_POST["dispositivo"] != 1) {
            echo json_encode([
                "type" => "error",
                "text" => "Usuario o contraseña inválidos"
            ]);
            die();
        } else {
            header('Location: /login/error');
            exit;
        }
    }

    function logout() {
        if ((isset($_POST['dispositivo']) && $_POST['dispositivo'] != 1) && $_COOKIE['token']) {
            $this->model->eraseToken($_COOKIE['token']);
            echo json_encode([
                'type' => 'success',
                'text' => 'Sesión cerrada'
            ]);
            exit;
        }
        unset($_COOKIE['token']);
        setcookie('token', null, -1, '/');
        header('Location: /');
        return;
    }
}