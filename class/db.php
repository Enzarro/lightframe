<?php

class database {

    function __construct($data = null) {
        if ($data) {
            $this->host = $data->host;
            $this->port = $data->port;
            $this->database = $data->name;
            $this->user = $data->user;
            $this->password = $data->pass;
            $this->db_connect();
        }
    }

    // Este método permite crear y abrir una conexi�n con PostgreSQL.
    function db_connect() {
        ob_start();
        if ($conn = pg_connect("host=$this->host port=$this->port dbname=$this->database user=$this->user password=$this->password")) {
            $this->conn = $conn;
            $this->query("SET CLIENT_ENCODING = 'UTF8';");
            return $this->conn;
        }
        else {
            $this->conn = ob_get_clean();
            return $this->conn;
        }
    }

    // Este método permite cerrar una conexión de PostgreSQL.
    function db_close() {
        if (pg_close($this->conn)) {
            return true;
        }
        else {
            return false;
        }
    }

    // Este método retorna el nombre de la base de datos, según la conexión abierta.
    function db_name($database, $conn) {
        if ($db = pg_DBname($this->conn)) {
            $this->database = $db;			
            return $db;
        }
        else {
            return false;
        }
    }
    
    // Este método permite ejecutar y abrir una consulta en SQL.
    function query($sql) {
        if ($res = pg_query($this->conn,$sql)) {
            return $res;
        }
        else {
            // echo "<b>No es posible ejecutar esta consulta:</b> <br>";
            echo pg_last_error($this->conn);
            return false;
        }
    }
    
    function query_to_array($sql) {
        if ($res = pg_query($this->conn, $sql)) {
            return $this->to_array_full($res);
        }
        else {
            // echo "<b>No es posible ejecutar esta consulta:</b> <br>";
            echo pg_last_error($this->conn);
            return false;
        }
    }

    function queryToArray($sql) {
        return $this->query_to_array($sql);
    }

    function queryToSingleVal($sql) {
        $res = $this->queryToArray($sql);
        if ($res) {
            return reset($res[0]);
        }
    }

    function pgexec($sql) {
        if ($res = pg_query($this->conn,$sql)) {
            pg_close($this->conn);
            return $res;
        }
        else {
            //echo "No es posible ejecutar esta consulta: <br>";
            //echo "$sql";				
            return false;
        }
    }

    // Este método retorna un arreglo con los datos de una consulta ejecutada y cargada.
    function to_array($res) {
        if ($array = pg_fetch_assoc($res)) {
        //if ($array = pg_fetch_array($res)) {
            return $array;
        }
        else {
            return false;
        }
    }
    
    function to_array_full($res) {
        $return = [];
        if ($this->num_rows($res)) {
            while ($array = pg_fetch_assoc($res)) {
                $return[] = $array;
            }
        }
        return $return;
    }
    
    // Este método retorna una fila de resultado como matriz (arreglo), de una consulta ejecutada y cargada.
    function to_row($res) {
        if ($array = pg_fetch_row($res)) {
            return $array;
        }
        else {
            return false;
        }
    }
    
    // Este método retorna el número de registros (filas) de una consulta ejecutada y cargada.
    function num_rows($res) {
        if ($num = pg_num_rows($res)) {
            return $num;
        }
        else {
            return false;
        }
    }

    // Este método retorna el número de campos de una consulta ejecutada y cargada.
    function num_fields($res) {
        if ($num = pg_num_fields($res)) {
            return $num;
        }
        else {
            return false;
        }
    }

    // Este método retorna el número de registros afectados por la ejecución de una consulta.
    function affected_rows($res) {
        if ($num = pg_affected_rows($res)) {
            return $num;
        }
        else {
            return false;
        }
    }
    
    // Este método Libera la memoria del resultado (consulta cargada).
    function free_result($res) {
        if (pg_free_result($res)) {
            return true;
        }
        else {
            return false;
        }
    }

    // Este método retorna una fila en forma de objeto, de una consulta ejecutada y cargada.
    function to_object($res) {
        if ($object = pg_fetch_object($res)) {
            return $object;
        }
        else {
            return false;
        }
    }

    // Este método retorna el nombre del campos de una consulta ejecutada y cargada.
    function field_type($result, $fila) {
        if ($type = pg_field_type($result, $fila)) {
            return $type;
        }
        else {
            return false;
        }
    }

    // Este método retorna el nombre del campos de una consulta ejecutada y cargada.
    function field_name($result, $fila) {
        if ($name = pg_field_name($result, $fila)) {
            return $name;
        }
        else {
            return false;
        }
    }

    function field_size($result, $fila) {
        if ($size = pg_field_size($result, $fila)) {
            return $size;
        }
        else {
            return false;
        }
    }

    function last_oid($result) {
        if ($oid = pg_last_oid($result)) {
            return $oid;
        }
        else {
            return false;
        }
    }
}