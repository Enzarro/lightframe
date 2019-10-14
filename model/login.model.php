<?php

class login_model {
    function __construct() {
        global $config;
        $this->db = new database($config->database);
        $this->utils = new utils();
    }

    function get($data) {
        //Lógin desde base de datos
        $res = $this->db->queryToArray("SELECT * FROM usuarios WHERE ".$this->utils->arrayToQuery('and', $data));
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
        return $this->db->queryToSingleVal("INSERT INTO tokens ".$this->utils->arrayToQuery('insert', $data)." ON CONFLICT (usuario_id) DO UPDATE SET ".$this->utils->arrayToQuery('update', $updateParams)." RETURNING token");
    }

    function getTokenData($string) {
        global $config;
        if ($string == sha1($config->superuser->username.$config->superuser->password)) {
            return 'admin';
        } else {
            $res = $this->db->queryToArray(
                "SELECT usuarios.*
                FROM tokens 
                INNER JOIN usuarios ON usuarios.usuario_id = tokens.usuario_id
                WHERE token = '{$string}'");
            return (object)$res[0];
        }
    }
}