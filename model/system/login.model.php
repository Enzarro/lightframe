<?php

class login_model {
    function __construct() {
        global $_DB;
        $this->db = $_DB;
        $this->utils = new utils();
        $this->users_model = new users_model();
    }

    function get($data) {
        //Lógin desde base de datos
        $res = $this->db->queryToArray("SELECT * FROM {$this->users_model->table} WHERE ".$this->utils->arrayToQuery('and', $data)." AND (eliminado != 1 OR eliminado IS NULL)");
        if ($res) {
            //Generar token
            return $this->genToken($res[0]['usuario_id']);
        }
        return $res;
    }

    function genToken($id) {
        $data = [
            'usuario_id' => $id,
            'token' => sha1($id.date('Y-m-d H:i:s')),
            'created_at' => 'now()'
        ];
        $updateParams = [
            'token' => 'excluded.token',
            'created_at' => 'excluded.created_at',
        ];
        if ($this->db->queryToSingleVal("SELECT COUNT(*) FROM sys_tokens WHERE usuario_id = {$id}")) {
            unset($data['usuario_id']);
            return $this->db->queryToSingleVal("UPDATE sys_tokens SET ".$this->utils->arrayToQuery([
                'action' => 'update',
                'array' => $data,
                'where' => "WHERE usuario_id = {$id}",
                'return' => 'token'
            ]));
        } else {
            return $this->db->queryToSingleVal("INSERT INTO sys_tokens ".$this->utils->arrayToQuery([
                'action' => 'insert', 
                'array' => $data,
                'return' => 'token'
            ]));
        }
    }

    function getTokenData($string) {
        global $config;
        if ($string == sha1($config->superuser->username.$config->superuser->password)) {
            return 'admin';
        } else {
            $res = $this->db->queryToArray(
                "SELECT usuarios.*
                FROM sys_tokens AS tokens
                INNER JOIN {$this->users_model->table} as usuarios ON usuarios.usuario_id = tokens.usuario_id
                WHERE token = '{$string}'");
            if (isset($res[0])) {
                return (object)$res[0];
            } else {
                return false;
            }
            
        }
    }
}