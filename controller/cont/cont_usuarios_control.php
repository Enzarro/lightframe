<?php

class cont_usuarios_control {
    function __construct() {
        $this->model = new cont_usuarios_control_model();
        $this->view = new cont_usuarios_control_view();

        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select', '/js/usuarios_control.js'],
            'concatPlugins' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'subtitle' => 'Listado de usuarios',
                'html' => $this->view->html([
                    'modalTitle' => 'Usuarios'
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
        $data_resources = $this->model->load_resources();
        if (!isset($_POST["id"])) {   
            echo $this->view->form(null, $data_resources);
        } else {
            $data = $this->model->get($_POST["id"]);
            echo $this->view->form($data, $data_resources);
        }      
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

    function consolidado(){
        if($_POST["btn"] == "buscarConsolidado" && isset($_POST["desde"]) && isset($_POST["hasta"])){
            $nDias = $cUsuariosControlModel->getDiasHabiles($_POST["desde"], $_POST["hasta"]);
            $resu = $cUsuariosControlModel->ConsolidadoData($_POST["desde"], $_POST["hasta"]);
            echo utf8_decode($iUsuarioControlView->informe_con($resu, $nDias));
            return;
        }
    }

    function clienteComprobante(){
        if($_POST["btn"] == "buscarClienteComprobante" && isset($_POST["desdeCliente"]) && isset($_POST["hastaCliente"])){
            $nDias = $cUsuariosControlModel->getDiasHabiles($_POST["desdeCliente"], $_POST["hastaCliente"]);
            $resu = $cUsuariosControlModel->ClienteComprobanteData($_POST["desdeCliente"], $_POST["hastaCliente"]);
            echo utf8_decode($iUsuarioControlView->informe_cliente_com($resu, $nDias));
            return;
        }
    }

    // function clienteComprobante(){
        
    //   /*  if($_POST["fn"] == "consolidado" && $_POST["btn"] == "buscarConsolidado" && isset($_POST["desde"]) && isset($_POST["hasta"])){
    //         $nDias = $cUsuariosControlModel->getDiasHabiles($_POST["desde"], $_POST["hasta"]);
    //         $resu = $cUsuariosControlModel->ConsolidadoData($_POST["desde"], $_POST["hasta"]);
    //         echo utf8_decode($iUsuarioControlView->informe_con($resu, $nDias));
    //         return;
    //     }*/
    //     if($_POST["fn"] == "clienteComprobante" && $_POST["btn"] == "buscarClienteComprobante" && isset($_POST["desdeCliente"]) && isset($_POST["hastaCliente"])){
    //         $nDias = $cUsuariosControlModel->getDiasHabiles($_POST["desdeCliente"], $_POST["hastaCliente"]);
    //         $resu = $cUsuariosControlModel->ClienteComprobanteData($_POST["desdeCliente"], $_POST["hastaCliente"]);
    //         echo utf8_decode($iUsuarioControlView->informe_cliente_com($resu, $nDias));
    //         return;
    //     }       
    //     if($_POST["fn"] == "usuario" && $_POST["btn"] == "buscarUsuario" && isset($_POST["idU"]) && isset($_POST["nameUser"]) && isset($_POST["desdeUsuario"]) && isset($_POST["hastaUsuario"])){
    //         $nDias = $cUsuariosControlModel->getDiasHabiles($_POST["desdeUsuario"], $_POST["hastaUsuario"]);
    //         $resu = $cUsuariosControlModel->resumenUsuario($_POST["idU"], $_POST["desdeUsuario"], $_POST["hastaUsuario"]);
    //         echo utf8_decode($iUsuarioControlView->informe($_POST["nameUser"], $resu));
    //         return;
    //     }   
    // }
}