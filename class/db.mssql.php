<?php
// Crear una clase para trabajar con MS-SQL Server.
class database {

    function __construct($data = null) {
        if ($data) {
            $this->host = $data->host;
            $this->port = $data->port;
            $this->database = $data->name;
            $this->user = $data->user;
            $this->password = $data->pass;
            $this->schema = 'dbo';
            $this->db_connect();
        }
    }

    // Este m�todo permite crear y abrir una conexion con MS_SQL Server.
    function db_connect() {
        $arrcon = [
            'Database' => $this->database,
            'UID' => $this->user,
            'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
            'ReturnDatesAsStrings' => true
        ];
        $hostcon = "{$this->host}";
        $conn = sqlsrv_connect($hostcon, $arrcon);
        if ($conn) {
            $this->conn = $conn;
            return $conn;
        } else {
            $this->conn = json_encode(sqlsrv_errors(), JSON_PRETTY_PRINT);
            return false;
        }
    }

    // Este metodo permite cerrar una conexion de MS-SQL Server.
    function db_close($conn) {
        if (sqlsrv_close ($conn)) {
            return true;
        }
        else {
            return false;
        }
    }

    // Este metodo retorna el nombre de la base de datos, segun la conexion abierta.
    function db_name($database) {
        if ($db = mssql_select_db($database,$this->conn)) {
            $this->database = $database;			
            return $db;
        }
        else {
            return false;
        }
    }

    // Este metodo permite ejecutar y abrir una consulta en SQL.
    function query($sql) {
        if (is_array($sql)) {
            return false;
            //action
            //from
            //data
            //where
            //return
        }
        // $this->conn = $this->db_connect() or die ("No fue posible connectar con la base de dados -> "."$this->database");
        $res = sqlsrv_query($this->conn, $sql, array(), array('Scrollable' => 'buffered'));
        if ($res) {
            // $this->db_close($this->conn);
            return $res;
        } else {
            utils::emit('sql-error', [
                'error' => sqlsrv_errors()[0]['message'],
                'query' => $sql
            ]);
            error_log(sqlsrv_errors()[0]['message']);
            error_log($sql);
            // $this->db_close($this->conn);
            return false;
        }
    }

    function query_to_array($sql) {
        if (is_string($this->conn) || gettype($this->conn) != "resource") return false;
        ob_start();
        if ($res = sqlsrv_query($this->conn, $sql)) {
            return $this->to_array($res);
        }
        else {
            utils::emit('sql-error', [
                'error' => sqlsrv_errors()[0]['message'],
                'query' => $sql
            ]);
            error_log(sqlsrv_errors()[0]['message']);
            error_log($sql);
            // echo pg_last_error($this->conn);
            return false;
        }
    }

    function queryToArray($sql) {
        return $this->query_to_array($sql);
    }

    function queryToSingleVal($sql) {
        if (is_string($this->conn)) return false;
        $res = $this->queryToArray($sql);
        if ($res) {
            return reset($res[0]);
        }
    }

    // Este metodo retorna un arreglo con los datos de una consulta ejecutada y cargada.
    function to_array($res) {
        $array = [];
        while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $array[] = $row;
        }
        if ($array) {
            return $array;
        }
        else {
            return [];
        }
    }
    
    // Este metodo retorna una fila de resultado como matriz (arreglo), de una consulta ejecutada y cargada.
    function to_row($res) {
        if ($array = mssql_fetch_row($res)) {
            return $array;
        }
        else {
            return false;
        }
    }
            
    // Este metodo retorna el numero de registros (filas) de una consulta ejecutada y cargada.
    function num_rows($res) {
        if ($res && $num = sqlsrv_num_rows($res)) {
            return $num;
        }
        else {
            return false;
        }
    }

    // Este metodo retorna el numero de campos de una consulta ejecutada y cargada.
    function num_fields($res) {
        if ($num = mssql_num_fields($res)) {
            return $num;
        }
        else {
            return false;
        }
    }

    // Este metodo retorna el numero de registros afectados por la ejecucion de una consulta.
    function affected_rows($res) {
        if ($num = mssql_rows_affected($res)) {
            return $num;
        }
        else {
            return false;
        }
    }

    // Este metodo Libera la memoria del resultado (consulta cargada).
    function free_result($res) {
        if (mssql_free_result($res)) {
            return true;
        }
        else {
            return false;
        }
    }
    
    // Este metodo retorna una fila en forma de objeto, de una consulta ejecutada y cargada.
    function to_object($res) {
        if ($res) {
            $object = sqlsrv_fetch_object($res);
            if ($object) {
                return (object)$object;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }				
}