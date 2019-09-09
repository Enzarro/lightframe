<?php

class sys_config_model {
    function setUser($data) {
        global $config;
        $config->superuser->username = $data->username;
        $config->superuser->password = $data->password;
        if (!file_put_contents(root."/config.json", json_encode($config, JSON_PRETTY_PRINT))) {
            //Error
            return [
                'type' => 'warning',
                'title' => 'Cambios no guardados',
                'text' => 'Hubo un problema al guardar el fichero'
            ];
        } else {
            //Success
            return [
                'type' => 'success',
                'title' => 'Cambios guardados',
                'text' => 'Los cambios fueron guardados con éxito'
            ];
        }
    }
}