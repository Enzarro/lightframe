<?php

class sys_profile {
    function __construct() {
        $this->model = new sys_profile_model();
        $this->view = new sys_profile_view();
        $this->frame_view = new frame_view();
    }

    function main() {
        if($this->model->get()===false):
            header('Location: /home');
            return;
        endif;
        $this->frame_view->main([
            'menu' => get_class(),
            'js' => ['/js/system/sys_profile.js'],
            'concatPlugins' => true,
            'cboClient' => false,
            'body' => [
                'title' => 'Mi perfil',
                'subtitle' => 'Información del Perfil',
                'html' => $this->view->html($this->model->get())
            ]
        ]);
    }

    function setuser() {
        echo json_encode($this->model->setUser($_POST));
    }

    function setlogin() {
        echo json_encode($this->model->setLogin($_POST));
    }
}
?>