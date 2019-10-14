<?php

class test2_model {
    function get($id) {
        global $config;
        $_DB = new database($config->database);

        //Grilla según ID
        return $_DB->queryToArray("SELECT * FROM test2 WHERE id = {$id}");
    }
}