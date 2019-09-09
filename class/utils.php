<?php

class utils {

    static function pluginLoader($plugins, $filetype = null) {
        $pluginlist = [
            'jquery' => [
                '/bower_components/jquery/dist/jquery.min.js'
            ],
            'adminlte' => [
                '/bower_components/bootstrap/dist/js/bootstrap.min.js',
                '/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',
                '/bower_components/fastclick/lib/fastclick.js',
                '/dist/js/adminlte.min.js',
                '/bower_components/bootstrap/dist/css/bootstrap.min.css',
                '/dist/css/AdminLTE.min.css',
                '/dist/css/skins/_all-skins.min.css'
                
            ],
            'frame' => [
                'public/js/frame.js'
            ],
            'icons' => [
                '/bower_components/font-awesome/css/font-awesome.min.css',
                '/bower_components/Ionicons/css/ionicons.min.css'
            ],
            'sweetalert' => [
                '/bower_components/sweetalert2/sweetalert2.all.min.js'
            ],
            'autonumeric' => [
                'core/js/autoNumeric/autoNumeric.min.js'
            ],
            'datatables' => [
                '/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css',
                '/bower_components/datatables.net/js/jquery.dataTables.min.js',
                '/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',
                // 'core/bootstrap_admin/vendor/datatables-responsive/dataTables.responsive.js',
                // 'core/bootstrap_admin/vendor/datatables-responsive/dataTables.responsive.css',
                // 'core/bootstrap_admin/vendor/datatables/css/dataTables.bootstrap.min.css'
            ],
            'datatables-select' => [
                "core/bootstrap_admin/vendor/datatables-plugins/dataTables.select.min.js",
                "core/bootstrap_admin/vendor/datatables-plugins/select.dataTables.min.css"
            ],
            'datepicker' => [
                'core/js/bootstrap-datepicker/moment.min.js',
                'core/js/bootstrap-datepicker/moment.locale.es.js',
                'core/bootstrap/dist/js/transition.js',
                'core/bootstrap/dist/js/collapse.js',
                'core/js/bootstrap-datepicker/bootstrap-datetimepicker.min.js',
                'core/js/bootstrap-datepicker/bootstrap-datetimepicker.min.css'
            ],
            'typeahead' => [
                'core/js/typeahead/bootstrap3-typeahead.js',

            ],
            'validator' => [
                'core/js/validator.min.js',
                'core/js/validator.min.css'
            ],
            'jasny' => [
                'core/js/upload/js/jasny-bootstrap.min.js',
                'core/js/upload/css/jasny-bootstrap.min.css'
            ],
            'autosuggest' => [
                'core/js/autocomplete_login/bsn.AutoSuggest_2.1.3.js',
                'core/js/autocomplete_login/autosuggest_inquisitor.css'
            ],
            'bootstrap-select' => [
                'core/js/bootstrap-select/bootstrap-select.min.js',
                'core/js/bootstrap-select/defaults-es_CL.min.js',
                'core/js/bootstrap-select/bootstrap-select.css'
            ],
            'socket' => [
                'nodejs/public/js/socket.io.js'
            ]
        ];

        ob_start();
        foreach ($plugins as $plugin) {
            if (in_array($plugin, array_keys($pluginlist))) {
                //El plugin solicitado está en el listado
                foreach($pluginlist[$plugin] as $kLink) {
                    //Cargar los archivos correspondientes al plugin
                    if (pathinfo($kLink, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                        ?><script src="<?php echo public_url.$kLink; ?>"></script><?php
                    }
                    if (pathinfo($kLink, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                        ?><link href="<?php echo public_url.$kLink; ?>" rel="stylesheet"><?php
                    }
                }
                
            } else if (strstr($plugin, '/')) {
                //El plugin solicitado no está en el listado, interpretar como ruta directa a archivo
                if (pathinfo($plugin, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                    ?><script src="<?php echo public_url.$plugin; ?>"></script><?php
                }
                if (pathinfo($plugin, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                    ?><link href="<?php echo public_url.$plugin; ?>" rel="stylesheet"><?php
                }
            }
        }
        return ob_get_clean();
    }

    static function load($path) {
        if (!is_array($path)) {
            $paths[] = $path;
        } else {
            $paths = $path;
        }
        foreach ($paths as $path) {
            //Load view
            if (self::startswith($path, views) && !self::endswith($path, '.view.php')) {
                require($path.'.view.php');
            } else
            //Load model
            if (self::startswith($path, models) && !self::endswith($path, '.model.php')) {
                require($path.'.model.php');
            } else {
                //Load anything
                require($path);
            }
        }
        
    }

    static function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    
    static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    static function post($url = '', $data = [], $json = false) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if (isset($_SESSION["ss_cliente"])) {
            self::ajaxTemp(array_replace_recursive([
                'url' => $url,
                'cliente_id' => $_SESSION["ss_cliente"]
            ], $data), $result);
        }
        

        if ($json) return json_decode($result, true);
        return $result;
    }

    static function get($url, $data = [], $json = false) {

        if (!$data) {
            $data = [];
        }

        //Get AJAX temp
        /*if (isset($_SESSION["ss_cliente"])) {
            $tempData = self::getAjaxTemp(array_replace_recursive([
                'url' => $url,
                'cliente_id' => $_SESSION["ss_cliente"]
            ], $data));
            //Return temp if exists
            if ($tempData) {
                return $json?json_decode($tempData, true):$tempData;
            }
        }*/

        //Query URL
        $result = file_get_contents($url);

        //Set AJAX temp
        /*if (isset($_SESSION["ss_cliente"])) {
            self::ajaxTemp(array_replace_recursive([
                'url' => $url,
                'cliente_id' => $_SESSION["ss_cliente"]
            ], $data), $result);
        }*/

        return $json?json_decode($result, true):$result;

    }

    static function ajaxTemp($query, $data = null) {
        global $_DB;
        $queryEncoded = "";
        $dataEncoded = "";
        if (is_array($query)) {
            $queryEncoded = json_encode($query);
        } else {
            $queryEncoded = $query;
            $query = json_decode($query, true);

        }
        if (is_array($data)) {
            $dataEncoded = json_encode($data);
        } else {
            $dataEncoded = $data;
            $data = json_decode($data, true);
        }
        //First, get:
        $jsonWhere = jsonWhere($query);
        $getQry = "SELECT COUNT(time) FROM ajax_temp WHERE {$jsonWhere} AND time >= now() - time '00:01:00'";
        $res = $_DB->query($getQry);
        $reg = $_DB->to_object($res)->count;

        if ($reg) {
            if (!$data) {
                return true;
            }
            return false;
        } else {
            if (!$data) {
                return false;
            }
            $_DB->query("DELETE FROM ajax_temp WHERE {$jsonWhere}");
            $_DB->query("INSERT INTO ajax_temp (time, query, data) VALUES (now(), '{$queryEncoded}', '{$dataEncoded}')");
            return true;
        }
    }

    static function arrayToTable($params) {
        extract($params);
        //$table, $filter, $data, $insert = true, $delete = true
        if (!($table || $data)) {
            return false;
        }
        if (!isset($insert)) {
            $insert = true;
        }
        if (!isset($delete)) {
            $delete = true;
        }

        global $_DB;
        $columns = [];

        //Add filter colums to general data
        if (isset($filter)) {
            $data = array_map(function($row) use ($filter) {
                return array_replace_recursive($filter, $row);
            }, $data);
        }
        
        //Column definitions
        $qryCreate = "(";
        $firstElement = reset($data);
        if (!is_array($firstElement)) {
            $firstElement = $data;
        }
        $keys = array_keys($firstElement);
        $lastKey = end($keys);
        foreach($keys as $key) {
            if (is_int($firstElement[$key])) {
                $qryCreate .= "{$key} int";
                $columns[$key] = 'int4';
            } else {
                $qryCreate .= "{$key} varchar(255)";
                $columns[$key] = 'varchar';
            }
            if ($lastKey != $key) {
                $qryCreate .= ", ";
            }
        }
        $qryCreate .= ")";

        $createTable = true;
        //Check if temp table exists
        $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '{$table}'";
        $res = $_DB->query($query);
        $reg = $_DB->to_object($res)->count;

        if ($reg) {
            //Extract columns from DB
            $query = "SELECT column_name, udt_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '{$table}'";
            $res = $_DB->query($query);
            $dbColumns = [];
            while ($column = $_DB->to_object($res)) {
                $dbColumns[$column->column_name] = $column->udt_name;
            }
            //Compare columns
            $comparison = array_diff_assoc($columns, $dbColumns);
            if (!empty($comparison)) {
                $_DB->query("DROP TABLE {$table};");
            } else {
                $createTable = false;
            }

        }

        //Create table
        if ($createTable) {
            //Build "create table"...
            $_DB->query("CREATE TABLE {$table} {$qryCreate}");
        } 

        if ($delete) {
            //Erase possible data with filter
            $_DB->query("DELETE FROM {$table} WHERE ".$this->arrayToQuery('and', $filter));
        }
        
        if ($insert) {
            //Fill table with data
            $_DB->query("INSERT INTO {$table} ".$this->multipleArrayToInsert($data));
        }
        
    }

    static function getAjaxTemp($query) {
        global $_DB;
        $exists = self::ajaxTemp($query);
        if ($exists) {
            $jsonWhere = jsonWhere($query);
            $getQry = "SELECT data FROM ajax_temp WHERE {$jsonWhere}";
            $res = $_DB->query($getQry);
            $reg = stripslashes($_DB->to_object($res)->data);
        } else {
            return false;
        }
    }

    static function safeInsert($table, $data) {
        global $_DB;

        //Check if data exists
        $res = $_DB->query("SELECT COUNT(*) FROM {$table} WHERE ".$this->arrayToQuery('and', $data));
        $reg = $_DB->to_object($res)->count;
        if (!$reg) {
            if ($_DB->query("INSERT INTO {$table} ".$this->arrayToQuery('insert', $data))) {
                return true;
            }
        }
        return false;
    }

    /**
    * @param old_date date to format
    * @param type optional, order date
    *
    */
    static function formatDate($old_date, $type = null){        
        if(!$old_date){
            return "";
        }
        $date = new DateTime($old_date);
        if($type!=null){
            if($type == 1) {
                $type = "d-m-Y";
            } elseif ($type == 2) {
                $type = "Y-m-d";
            } else {
                $type = "d-Y-m";
            }
            $date = $date->format($type);
        } else {
            $date = $date->format("d-m-Y");
        }       
        return $date;
    }

    static function var_doom($var, $pre) {
        if ($pre) echo '<pre>';
        echo json_encode($var, JSON_PRETTY_PRINT);
        if ($pre) echo '</pre>';
    }

    function valuesArrayToSQLCompatibleString($array, $returnArray = false) {
        $keys = array_keys($array);
        $lastKey = end($keys);
        $finalString = '';
        $finalArray = [];
        foreach ($keys as $key) {
            //Vacío (null)
            if ($array[$key] == '' || $array[$key] === null) {
                $finalString .= 'null';
                $finalArray[] = 'null';
            }
            //Es numérico (sin comillas)
            elseif (is_int($array[$key]) || is_float($array[$key])) {
                $finalString .= "$array[$key]";
                $finalArray[] = "$array[$key]";
            } 
            //Es booleano (convertido a 1 o 0 sin comillas)
            elseif(is_bool($array[$key]) || $array[$key] == 'true' || $array[$key] == 'false') {
                if (is_bool($array[$key])) {
                    $finalString .= ($array[$key]?'1':'0');
                    $finalArray[] = ($array[$key]?'1':'0');
                } elseif ($array[$key] == 'true' || $array[$key] == 'false') {
                    $finalString .= ($array[$key]=='true'?'1':'0');
                    $finalArray[] = ($array[$key]=='true'?'1':'0');
                }
            }
            //Es string (por descarte, con comillas)
            else {
                if ($array[$key] == 'now()') {
                    $finalString .= "$array[$key]";
                    $finalArray[] = "$array[$key]";
                } else {
                    $finalString .= "'$array[$key]'";
                    $finalArray[] = "'$array[$key]'";
                }
            }
            //Coma separadora al final
            if ($key != $lastKey) {
                $finalString .= ",";
            }
        }
        if (!$returnArray) {
            return $finalString;
        } else {
            return $finalArray;
        }
        
    }
    
    //Convertir Array a String Query
    function arrayToQuery($action, $array, $test = false) {
        $jsonChars = ['"', '[', ']'];
        $sqlChars  = ["'", '(', ')'];
        $sQuery = "";
        if (count($array)) {
            $keys = str_replace(["'", '(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_keys($array))));
            //$values = str_replace(['(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_values($array))));
            if ($action == "update" || $action == "and") {
                $keys = explode(',', $keys);
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array), true);
                $fValues = array_combine($keys, $values);
                if ($test) {
                    echo json_encode($keys, JSON_PRETTY_PRINT);
                    echo "\n";
                    echo json_encode($values, JSON_PRETTY_PRINT);
                    exit;
                }
                if (count($fValues)) {
                    $i = 1;
                    foreach ($fValues as $key => $val) {
                        //BIT datatype syntax correction
                        if (strpos($val, "'b'") === 0) {
                            $val = substr($val, 1, -1);
                        }
                        $sQuery .= $key . " = " . $val;
                        if (count($fValues) > $i) {
                            if ($action == "update") {
                                $sQuery .= ", ".PHP_EOL;
                            } else if ($action == "and") {
                                $sQuery .= " AND ";
                            }
                            
                        }
                        $i++;
                    }
                }
            } elseif ($action == "insert") {
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array));
                $sQuery .= "(";
                $sQuery .= $keys;
                $sQuery .= ") VALUES (";
                $sQuery .= $values;
                $sQuery .= ")";
            } elseif ($action == "in") {
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array));
                $sQuery = "(" . $values . ")";
            }
            return $sQuery;
        } else {
            return false;
        }
    }
    
    function multipleArrayToWhere($array) {
        $keys = array_keys($array);
        $lastKey = end($keys);
        $query = "";
        foreach ($keys as $key) {
            $query .= "(".$this->arrayToQuery('and', $array[$key]).")";
            if ($key != $lastKey) {
                $query .= " OR ";
            }
        }
        return $query;
    }
    
    function multipleArrayToInsert($array) {
        $jsonChars = ['"', '[', ']'];
        $sqlChars  = ["'", '(', ')'];
        $sQuery = "";
        //Si el primer elemento del array tiene m�ltiples elementos -- array multidimensional
        if (count($array[0]) > 1) {
            //Extraer cabeceras desde el primer elemento
            $keys = str_replace(["'", '(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_keys($array[0]))));
    
            $array = array_map(function($item) {
                return array_values($item);
            }, $array);
    
            //$values = str_replace(['((', '))'], ['(', ')'], str_replace($jsonChars, $sqlChars, json_encode(array_values($array))));
    
            $sQuery .= "(";
            $sQuery .= $keys;
            $sQuery .= ") VALUES ";
            
            $last_key = array_keys($array);
            $last_key = end($last_key);
            foreach (array_keys($array) as $key) {
                $sQuery .= "(".$this->valuesArrayToSQLCompatibleString(array_values($array[$key])).")".($key!=$last_key?", ":"");
            }
        }
        return $sQuery;
    }

}