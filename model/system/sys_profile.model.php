<?php

class sys_profile_model {

    var $usuarios_plus = 'dbo.sys_usuarios_plus';
    var $usuarios = 'dbo.sys_usuarios';

    function __construct() {
        $this->grid_model = new sys_grid_model();
        $this->login_model = new login_model();
		$this->utils = new utils();
    }

    function get(){
		global $_DB;
        $dataLogin = $this->login_model->getTokenData($_COOKIE['token']);
        if(!isset($dataLogin->usuario_id) || $dataLogin->usuario_id=='' || $dataLogin->usuario_id=='admin'):
            return false;
        endif;
        $dataUser = $_DB->queryToArray("SELECT nombre, apellido, telefono, correo, rut FROM {$this->usuarios_plus} WHERE sys_usuarios_id = {$dataLogin->usuario_id}");
        return [
            'loginData' => $dataLogin,
            'userData' => (object)$dataUser[0]
        ];
    }

    function setUser($data) {
        global $_DB;
        extract($data);
        $dataLogin = $this->login_model->getTokenData($_COOKIE['token']);
        $object = [
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'telefono' => $user['telefono'],
            'correo' => $user['correo']
        ];
        $object_id = $_DB->queryToSingleVal("UPDATE {$this->usuarios_plus} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $object, 'where' => " WHERE sys_usuarios_id = {$dataLogin->usuario_id}", 'return' => 'sys_usuarios_id']));
        if($object_id == 0 || $object_id==null):
            return [
                'type' => 'error',
                'title' => 'Ocurrio un inconveniente',
                'html' => 'No se pudo actualizar la información, volver a intentar mas tarde'
            ];
        endif;
        return [
            'type' => 'success',
            'title' => 'Enhorabuena!',
            'html' => 'Información actualizada correctamente'
        ];
    }

    function setLogin($data) {
        global $_DB;
        extract($data);       
        $dataLogin = $this->login_model->getTokenData($_COOKIE['token']);
        if($user["password"]!==$user["rep_password"]){
            return [
                'type' => 'warning',
                'title' => 'Ocurrio un inconveniente',
                'html' => "Las contraseñas no coinciden"
            ];
        } 
        $valUser = $_DB->queryToSingleVal("SELECT count(u.usuario_id) as cantidad  FROM {$this->usuarios} u
        INNER JOIN {$this->usuarios_plus} up ON u.usuario_id=up.sys_usuarios_id 
        WHERE u.username = '{$user["username"]}' AND  u.usuario_id NOT IN ({$dataLogin->usuario_id}) AND (u.eliminado IS NULL OR u.eliminado=0) AND (u.estado=1)");
        if($valUser>0){
            return [
                'type' => 'warning',
                'title' => 'Ocurrio un inconveniente',
                'html' => "Este nombre de usuario ya se encuentra ingresado en la plataforma"
            ];
        } 
        $object = [
            'username' => $user['username'],
            'password' => sha1($user["password"]),
        ];
        $object_id = $_DB->queryToSingleVal("UPDATE {$this->usuarios} SET ".$this->utils->arrayToQuery(['action' => 'update', 'array' => $object, 'where' => " WHERE usuario_id = {$dataLogin->usuario_id}", 'return' => 'usuario_id']));
        if($object_id == 0 || $object_id==null):
            return [
                'type' => 'error',
                'title' => 'Ocurrio un inconveniente',
                'html' => 'No se pudo actualizar la información, volver a intentar mas tarde'
            ];
        endif;
        return [
            'type' => 'success',
            'title' => 'Enhorabuena!',
            'html' => 'Información actualizada correctamente'
        ];
    }
}
?>