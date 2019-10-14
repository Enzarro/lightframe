<?php

class test1_model {
    function get($id) {
        global $config;
        $_DB = new database($config->database);

        //Grilla según ID
        return $_DB->queryToArray("SELECT * FROM test1 WHERE id = {$id}");
    }
}