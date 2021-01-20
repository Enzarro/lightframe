<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class utils {
    function __construct() {
        global $_DB;
        global $config;
        $this->db = $_DB;
        $this->config = $config;
        if (file_exists(base."/pluginlist.json")) {
            $this->pluginlist = json_decode(file_get_contents(base."/pluginlist.json"), true);
        }
    }

    var $pluginlist = [];

    var $excludeConcat = [
        'icons'
    ];

    function pluginLoader($plugins, $filetype = null, $concat = false) {
        
        ob_start();
        
        foreach ($plugins as $plugin) {
            if (self::startsWith(ltrim($plugin), '<s') || self::startsWith(ltrim($plugin), '<l')) {
                echo $plugin;
            } else if (in_array($plugin, array_keys($this->pluginlist))) {
                //El plugin solicitado está en el listado
                if ($concat && !in_array($plugin, $this->excludeConcat)) {
                    foreach($this->pluginlist[$plugin] as $kLink) {
                        //Cargar los archivos correspondientes al plugin
                        if (file_exists(root.$kLink)) {
                            if (pathinfo($kLink, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                                ?><script data-name="<?=$kLink?>"><?php echo PHP_EOL.file_get_contents(root.$kLink).PHP_EOL; ?></script><?php
                            }
                            if (pathinfo($kLink, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                                ?><style data-name="<?=$kLink."?".filemtime(root.$kLink)?>"><?php echo PHP_EOL.file_get_contents(root.$kLink).PHP_EOL; ?></style><?php
                            }
                        } else {
                            ?><script data-name="<?=$kLink?>">console.error("File <?=$kLink?> does not exist")</script><?php
                        }
                        
                    }
                } else {
                    foreach($this->pluginlist[$plugin] as $kLink) {
                        $public = public_url;
                        if (file_exists(root.$kLink)) {
                            //Cargar los archivos correspondientes al plugin
                            if (pathinfo($kLink, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                                ?><script src="<?php echo /*public_url.*/$kLink."?".filemtime(root.$kLink); ?>"></script><?php echo PHP_EOL;
                            }
                            if (pathinfo($kLink, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                                ?><link href="<?php echo /*public_url.*/$kLink."?".filemtime(root.$kLink); ?>" rel="stylesheet"><?php echo PHP_EOL;
                            }
                        } else {
                            ?><script data-name="<?=$kLink?>">console.error("File <?=$kLink?> does not exist")</script><?php
                        }
                    }
                }
                
            } else if (strstr($plugin, '/')) {
                if ($this->startsWith($plugin, 'http')) {
                    //El plugin solicitado está en otro servidor
                    if (pathinfo($plugin, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                        ?><script src="<?php echo $plugin; ?>"></script><?php
                    }
                    if (pathinfo($plugin, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                        ?><link href="<?php echo $plugin; ?>" rel="stylesheet"><?php
                    }
                } elseif ($concat && !in_array($plugin, $this->excludeConcat)) {
                    if (file_exists(root.$plugin)) {
                        //El plugin solicitado no está en el listado, interpretar como ruta directa a archivo
                        if (pathinfo($plugin, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                            ?><script data-name="<?=$plugin?>"><?php echo file_get_contents(root.$plugin); ?></script><?php
                        }
                        if (pathinfo($plugin, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                            ?><style data-name="<?=$plugin?>"><?php echo file_get_contents(root.$plugin); ?></style><?php
                        }
                    } else {
                        ?><script data-name="<?=$plugin?>">console.error("File <?=$plugin?> does not exist")</script><?php
                    }
                } else {
                    if (file_exists(root.$plugin)) {
                        //El plugin solicitado no está en el listado, interpretar como ruta directa a archivo
                        if (pathinfo($plugin, PATHINFO_EXTENSION) == 'js' && $filetype != 'css') {
                            ?><script src="<?php echo /*public_url.*/$plugin."?".filemtime(root.$plugin); ?>"></script><?php
                        }
                        if (pathinfo($plugin, PATHINFO_EXTENSION) == 'css' && $filetype != 'js') {
                            ?><link href="<?php echo /*public_url.*/$plugin; ?>" rel="stylesheet"><?php
                        }
                    } else {
                        ?><script data-name="<?=$plugin?>">console.error("File <?=$plugin?> does not exist")</script><?php
                    }
                }
            }
        }
        
        return ob_get_clean();
    }

    static function autoLoad() {
        //Clases
        // self::load_files(classes);
        // $classes = scandir(classes);
        // $classes = array_values(array_filter($classes, function($file) {
        //     return self::endsWith($file, '.php') && $file != 'lightframe.php';
        // }));
        // $classes = array_map(function($file) {
        //     return classes.$file;
        // }, $classes);
        // self::load($classes);

        self::load([
            classes.'formitembuilder.php'
        ]);

        //Controladores
        self::load_files(controllers);
        // $controllers = scandir(controllers);
        // $controllers = array_values(array_filter($controllers, function($file) {
        //     return self::endsWith($file, '.php');
        // }));
        // $controllers = array_map(function($file) {
        //     return controllers.$file;
        // }, $controllers);
        // self::load($controllers);

        //Modelos
        self::load_files(models);
        // $models = scandir(models);
        // $models = array_values(array_filter($models, function($file) {
        //     return self::endsWith($file, '.model.php');
        // }));
        // $models = array_map(function($file) {
        //     return models.$file;
        // }, $models);
        // self::load($models);

        //Vistas
        self::load_files(views);
        // $views = scandir(views);

        // echo json_encode($views);
        // exit;

        // $views = array_values(array_filter($views, function($file) {
        //     return self::endsWith($file, '.view.php');
        // }));
        // $views = array_map(function($file) {
        //     return views.$file;
        // }, $views);
        // self::load($views);
    }

    static function load_files($target) {
        if(is_dir($target)) {
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            foreach( $files as $file ) {
                self::load_files( $file );
            }
        } elseif(is_file($target)) {
            self::load($target);
        }
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
                require_once($path.'.view.php');
            } else
            //Load model
            if (self::startswith($path, models) && !self::endswith($path, '.model.php')) {
                require_once($path.'.model.php');
            } else {
                //Load anything
                require_once($path);
            }
        }
        
    }

    static function dtBuildDataFromConfig($columns, $data) {
        return [
            'data' => array_map(function($row) use ($columns) { 
                $return = [];
                foreach ($columns as $column) {
                    if ($column['data'] && property_exists($row, $column['data'])) {
                        if (isset($column['format'])) {
                            $return[$column['data']] = $column['format']($row->{$column['data']});
                        // } else if (is_array($row->{$column['data']})) {
                        //     $return[$column['data']] = count($row->{$column['data']});
                        } else {
                            $return[$column['data']] = $row->{$column['data']};
                        }
                    } else {
                        $return[$column['data']] = null;
                    }
                }
                return $return;
            }, $data)
        ];
    }

    static function getNewIncrementalID($column, $data) {
        $newID = 0;
        foreach ($data as $row) {
            //Convertir arreglo a objeto (si es que)
            if (is_array($row)) $row = (object)$row;
            //Pisar newid si el id de la fila es más alto
            if ($row->{$column} > $newID) {
                $newID = $row->{$column};
            }
        }
        //Sumar 1 al id más alto
        $newID++;
        //Retornar ID nuevo
        return $newID;
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

    static function emit($name, $values, $emitall = false) {
        global $config;
        self::post($config->socket->local."/emit", [
            "name" => $name,
            "values" => json_encode($values),
            "token" => (isset($_COOKIE["token"])) ? $_COOKIE["token"] : 0,
            "system" =>  $config->socket->system,
            "emitall" => $emitall
        ]);
        // self::curl_post_async($config->socket->local."/emit", [
        //     "name" => $name,
        //     "values" => json_encode($values)
        // ]);
    }

    static function emitUser() {
        global $config;
        return self::post($config->socket->local."/send_user_connect", [
            "system" =>  $config->socket->system
        ]);
    }

    static function post($url = '', $data = [], $json = false) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ];
        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if (isset($_SESSION["ss_cliente"])) {
            self::ajaxTemp(array_replace_recursive([
                'url' => $url,
                'cliente_id' => $_SESSION["ss_cliente"]
            ], $data), $result);
        }
        

        if ($json) return json_decode($result, true);
        return $result;
    }

    static function curl_post_async($url, $params)
    {
        foreach ($params as $key => &$val) {
        if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $parts=parse_url($url);

        $fp = fsockopen($parts['host'],
            isset($parts['port'])?$parts['port']:80,
            $errno, $errstr, 30);

        // pete_assert(($fp!=0), "Couldn't open a socket to ".$url." (".$errstr.")");

        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out.= $post_string;

        fwrite($fp, $out);
        fclose($fp);
    }

    static function get($url, $data = [], $json = false) {
        if (!$data) {
            $data = [];
        }
        //Query URL
        $url = $url . "?" . http_build_query($data);

        $result = file_get_contents($url);
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

    function arrayToTable($params) {
        global $_DB;
        global $config;
		extract($params);
        //$table, $key, $filter, $data, $insert = true, $delete = true, $duplicate = true
        if (!isset($schema)) {
            if ($config->database->type == "pgsql") {
                $schema = 'public';
            } else if ($config->database->type == "mssql") {
                $schema = 'dbo';
            }
		}
		if (!isset($table)) {
			return false;
		}
		if (!isset($insert)) {
			$insert = true;
		}
		if (!isset($delete)) {
			$delete = true;
        }
        if (!isset($duplicate)) {
			$duplicate = true;
        }
        if (!isset($preserve)) {
			$preserve = true;
		}

		
		$columns = [];

		//Add filter colums to general data
		if (isset($filter)) {
			$data = array_map(function($row) use ($filter) {
				return array_replace_recursive($filter, $row);
			}, $data);
		}
		
		//Column definitions
        $qryCreate = "(";
        if ($columnDefs) {
            $keys = array_keys($columnDefs);
            $lastKey = end($keys);
            foreach($keys as $ckey) {
                if ($this->isMultiArray($columnDefs[$ckey])) {
                    //Setear campos a partir de tipo de campo cuando no haya definicion de tipo de columna (old style)

                    //Tipos de datos
                    if (in_array('int', $columnDefs[$ckey])) {
                        if (in_array('autonum', $columnDefs[$ckey])) {
                            if ($config->database->type == "pgsql") {
                                $qryCreate .= "{$ckey} SERIAL";
                            } else if ($config->database->type == "mssql") {
                                $qryCreate .= "{$ckey} INT IDENTITY(1,1)";
                            }
                        } else {
                            $qryCreate .= "{$ckey} INT";
                        }
                        if ($config->database->type == "pgsql") {
                            $columns[$ckey] = 'int4';
                        } else if ($config->database->type == "mssql") {
                            $columns[$ckey] = 'int';
                        }
                    } else if (in_array('varchar', $columnDefs[$ckey])) {
                        $qryCreate .= "{$ckey} VARCHAR(255)";
                        $columns[$ckey] = 'varchar';
                    } else if (in_array('varcharmax', $columnDefs[$ckey])) {
                        if ($config->database->type == "pgsql") {
                            $qryCreate .= "{$ckey} VARCHAR";
                            $columns[$ckey] = 'varchar';
                        } else if ($config->database->type == "mssql") {
                            $qryCreate .= "{$ckey} NVARCHAR(MAX)";
                            $columns[$ckey] = 'nvarchar';
                        }
                    } else if (in_array('float', $columnDefs[$ckey])) {
                        $qryCreate .= "{$ckey} FLOAT";
                        if ($config->database->type == "pgsql") {
                            $columns[$ckey] = 'float4';
                        } else if ($config->database->type == "mssql") {
                            $columns[$ckey] = 'float';
                        }
                    } else if (in_array('timestamp', $columnDefs[$ckey])) {
                        if ($config->database->type == "pgsql") {
                            $qryCreate .= "{$ckey} TIMESTAMP";
                            $columns[$ckey] = 'timestamp';
                        } else if ($config->database->type == "mssql") {
                            $qryCreate .= "{$ckey} DATETIME";
                            $columns[$ckey] = 'datetime';
                        }
                    } else if (in_array('date', $columnDefs[$ckey])) {
                        $qryCreate .= "{$ckey} DATE";
                        $columns[$ckey] = 'date';
                    } else if (in_array('time', $columnDefs[$ckey])) {
                        $qryCreate .= "{$ckey} TIME";
                        $columns[$ckey] = 'time';
                    } else if (in_array('json', $columnDefs[$ckey])) {
                        if ($config->database->type == "pgsql") {
                            $qryCreate .= "{$ckey} JSON";
                            $columns[$ckey] = 'json';
                        } else if ($config->database->type == "mssql") {
                            $qryCreate .= "{$ckey} NVARCHAR(MAX)";
                            $columns[$ckey] = 'nvarchar';
                        }
                    }

                    //Características
                    if (in_array('primary', $columnDefs[$ckey])) {
                        $qryCreate .= " PRIMARY KEY";
                    }
                    if (in_array('notnull', $columnDefs[$ckey])) {
                        $qryCreate .= " NOT NULL";
                    }
                    
                } else {
                    if (isset($columnDefs[$ckey]['length']) && $columnDefs[$ckey]['length'] == 'max' && $config->database->type == "pgsql") {
                        $columnDefs[$ckey]['length'] = null;
                    }
                    //Características
                    if (isset($columnDefs[$ckey]['attr'])) {
                        if ($columnDefs[$ckey]['type'] == 'int' && in_array('autonum', $columnDefs[$ckey]['attr'])) {
                            if ($config->database->type == "pgsql") {
                                $qryCreate .= "{$ckey} SERIAL";
                            } else if ($config->database->type == "mssql") {
                                $qryCreate .= "{$ckey} INT IDENTITY(1,1)";
                            }
                        } else {
                            $qryCreate .= ("{$ckey} {$columnDefs[$ckey]['type']}" . (isset($columnDefs[$ckey]['length'])?"({$columnDefs[$ckey]['length']})":""));
                        }
                        $columns[$ckey] = $columnDefs[$ckey]['type'];
                        if (in_array('primary', $columnDefs[$ckey]['attr'])) {
                            $qryCreate .= " PRIMARY KEY";
                        }
                        if (in_array('notnull', $columnDefs[$ckey]['attr'])) {
                            $qryCreate .= " NOT NULL";
                        }
                    } else {
                        $qryCreate .= ("{$ckey} {$columnDefs[$ckey]['type']}" . (isset($columnDefs[$ckey]['length'])?"({$columnDefs[$ckey]['length']})":""));
                        $columns[$ckey] = $columnDefs[$ckey]['type'];
                    }
                }
                
                //Coma final
                if ($lastKey != $ckey) {
                    $qryCreate .= ", ";
                }
            }
        } else {
            $firstElement = reset($data);
            if (!is_array($firstElement)) {
                $firstElement = $data;
            }
            $keys = array_keys($firstElement);
            $lastKey = end($keys);
            foreach($keys as $ckey) {
                if (is_int($firstElement[$ckey])) {
                    $qryCreate .= "{$ckey} int";
                    if ($config->database->type == "pgsql") {
                        $columns[$ckey] = 'int4';
                    } else if ($config->database->type == "mssql") {
                        $columns[$ckey] = 'int';
                    }
                } else {
                    $qryCreate .= "{$ckey} varchar(255)";
                    $columns[$ckey] = 'varchar';
                }
                if ($lastKey != $ckey) {
                    $qryCreate .= ", ";
                }
            }
        }
        $qryCreate .= ")";

		$createTable = true;
        //Check if temp table exists
        $diff = $this->compareTableDefs($schema, $table, $columns);
        return $diff;

        if (!empty($diff['comparisonAdd']) || !empty($diff['comparisonSub'])) {
            if ($preserve) {
                //Traer toda la data desde la tabla que se va a droppear
                $tmpData = $_DB->queryToArray("SELECT * FROM {$schema}.{$table}");
                //Guardar data en CSV
                $this->saveTempCSV($tmpData, $table, $schema);
                if ($tmpData) {
                    $columnKeys = array_keys($columnDefs);
                    //Old data in new columns / delete old columns data
                    $tmpData = array_map(function($row) use ($columnKeys) {
                        $newrow = [];
                        foreach (array_keys($row) as $ckey) {
                            if (in_array($ckey, $columnKeys)) {
                                $newrow[$ckey] = $row[$ckey];
                            }
                        }
                        return $newrow;
                    }, $tmpData);
                    //Venía data por defecto
                    if (isset($data)) {
                        if ($key) {
                            $oldKeyVals = array_column($tmpData, $key);
                            foreach ($data as $row) {
                                if (!in_array($row[$key], $oldKeyVals)) {
                                    $tmpData[] = $row;
                                }
                            }
                        }
                    }
                    $data = $tmpData;
                }
            }
            $_DB->query("DROP TABLE {$schema}.{$table};");
            error_log("Dropping table {$schema}.{$table}, comparison: ".json_encode($comparisonAdd).json_encode($comparisonSub), 0);
            error_log("DB Columns: ".json_encode($dbColumns));
            error_log("New Columns: ".json_encode($columns));
        }

		//Create table
		if ($createTable) {
			//Build "create table"...
            $_DB->query("CREATE TABLE {$schema}.{$table} {$qryCreate}");
            error_log("Creating table {$schema}.{$table}", 0);
            error_log("CREATE TABLE {$schema}.{$table} {$qryCreate}");
		}

		if ($delete) {
            //Erase possible data with filter
            if ($filter) {
                $_DB->query("DELETE FROM {$schema}.{$table} WHERE ".$this->arrayToQuery('and', $filter));
                error_log("Deleting filtered data", 0);
            } else {
                $_DB->query("DELETE FROM {$schema}.{$table}");
                error_log("Deleting data", 0);
            }
		}
		
		if ($insert) {
            //Check if data is duplicated
            if (!$duplicate && isset($key)) {
                $keystoinsert = array_column($data, $key);
                $_DB->query("DELETE FROM {$schema}.{$table} WHERE {$key} IN ".$this->arrayToQuery('in', $keystoinsert));
                error_log("Deleting duplicated data: ".$this->arrayToQuery('in', $keystoinsert), 0);
            }

            //Fill table with data
            if (isset($data) && $data) {
                if ($config->database->type == "mssql") {
                    $_DB->query("SET IDENTITY_INSERT {$schema}.{$table} ON");
                }
                $_DB->query("INSERT INTO {$schema}.{$table} ".$this->multipleArrayToInsert($data));
                if ($config->database->type == "mssql") {
                    $_DB->query("SET IDENTITY_INSERT {$schema}.{$table} OFF");
                }
                error_log("Inserting data: ".$this->multipleArrayToInsert($data), 0);
            }
		}
		
    }

    function compareTableDefs($schema, $table, $columns) {
        global $config;
        global $_DB;

        $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$schema}' AND table_name = '{$table}'";
        $reg = $_DB->queryToSingleVal($query);

		if ($reg) {
            //Extract columns from DB
            if ($config->database->type == "pgsql") {
                $query = "SELECT column_name, udt_name FROM information_schema.columns WHERE table_schema = '{$schema}' AND table_name = '{$table}'";
            } else if ($config->database->type == "mssql") {
                $query = "SELECT column_name, data_type as udt_name FROM information_schema.columns WHERE table_schema = '{$schema}' AND table_name = '{$table}'";
            }
			
			$res = $_DB->queryToArray($query);
			$dbColumns = [];
			foreach ($res as $column) {
                $column = (object)$column;
                if ($column && property_exists($column, 'column_name')) {
                    $dbColumns[$column->column_name] = $column->udt_name;
                } else {
                    ob_start();
                    echo $query;
                    var_dump($column);
                    error_log(ob_get_clean());
                    die;
                }
			}
			//Compare columns
            $comparisonAdd = array_diff_assoc($columns, $dbColumns);
            $comparisonSub = array_diff_assoc($dbColumns, $columns);
			if (!empty($comparisonAdd) || !empty($comparisonSub)) {
                return compact(['comparisonAdd', 'comparisonSub']);
			} else {
                return;
				$createTable = false;
			}

		}
    }
    
    function saveTempCSV($array, $table, $schema) {
        $dir = base."tempcsv/{$schema}/";
        //Crear carpeta
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        //Abrir archivo
        $fp = fopen("{$dir}{$table}-".date("YmdHis").".csv", 'w');
        //Escribir nombres de columnas
        fputcsv($fp, array_keys($array[0]));
        //Escribir data
        foreach ($array as $row) {
            fputcsv($fp, array_values($row));
        }
        //Cerrar
        fclose($fp);
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

    var $execSPFailReport = [];
    function executeSP($schema, $target = null) {
        if ($target == null) {
            if ($schema == 'dbo') {
                //dbo
                $this->executeSP($schema, base."storedprocedures/dbo/");
            } else {
                //cli
                $this->executeSP($schema, base."storedprocedures/cli/");
            }
        } else {        
            if(file_exists($target)){   
                if(is_dir($target)) {
                    $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
                    foreach( $files as $file ) {
                        $this->executeSP($schema, $file);
                    }
                } else if(is_file($target) && pathinfo($target)['extension'] == 'sql') {
                    //self::load($target); 
                    //ejecutar consulta

                    //leer archivo $target
                    $sql = file_get_contents($target);

                    // $fp = file($target, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    // $sql = '';
                    // foreach ($fp as $line) {
                    //     if ($line != '' && strpos($line, '--') === false) {
                    //         $sql .= $line;
                    //     }
                    // }
                    
                    $createpos = strpos($sql,'CREATE');

                    //cli
                    $sql = substr($sql,$createpos);
                    if ($schema != 'dbo') {

                        $sql = str_replace('__client', $schema, $sql);
                        
                    }
                    $result = $this->db->query($sql);
                    if ($result == false) {
                        $this->execSPFailReport[$schema][] = $target;
                    }
                }
            }
        }
        
    }

    static function safeInsert($table, $data) {
        /*global $_DB;

        //Check if data exists
        $res = $_DB->query("SELECT COUNT(*) FROM {$table} WHERE ".$this->arrayToQuery('and', $data));
        $reg = $_DB->to_object($res)->count;
        if (!$reg) {
            if ($_DB->query("INSERT INTO {$table} ".$this->arrayToQuery('insert', $data))) {
                return true;
            }
        }*/
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

    static function var_doom($var, $pre = true) {
        if ($pre) echo '<pre>';
        echo json_encode($var, JSON_PRETTY_PRINT);
        if ($pre) echo '</pre>';
    }

    function valuesArrayToSQLCompatibleString($array, $returnArray = false) {
        global $config;
        $keys = array_keys($array);
        $lastKey = end($keys);
        $finalString = '';
        $finalArray = [];
        foreach ($keys as $key) {
            //Vacío (null)
            if ($array[$key] === '' || $array[$key] === null || strtolower($array[$key]) === 'null') {
                $finalString .= 'null';
                $finalArray[] = 'null';
            }
            //Es numérico (sin comillas)
            elseif (!is_string($array[$key]) && (is_int($array[$key]) || is_float($array[$key]))) {
                $array[$key] = str_replace(',', '.', "$array[$key]");
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
                if ($array[$key] == 'now()' || $array[$key] == 'getdate()' || $array[$key] == 'DEFAULT' || utils::startsWith($array[$key], 'excluded.')) {
                    //Strings especiales / funciones de BD
                    if ($array[$key] == 'now()' && $config->database->type == 'mssql') {
                        $array[$key] = 'getdate()';
                    }
                    if ($array[$key] == 'getdate()' && $config->database->type == 'pgsql') {
                        $array[$key] = 'now()';
                    }
                    $finalString .= "$array[$key]";
                    $finalArray[] = "$array[$key]";
                } else {
                    //String común
                    if (strpos($array[$key], "'") !== false) {
                        //String contiene comillas simples/duplicar
                        $array[$key] = str_replace("'", "''", $array[$key]);
                    }
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
    /**
     * $action: puede ser un string o un arreglo. Si es un arreglo, todos los parámetros deberían venir dentro del arreglo, incluyendo "action".
     * La acción puede ser "insert", "update", "in", "and".
     * 
     * $array: Es el arreglo de datos que será convertido a la acción determinada. De no ser enviado como segundo parámetro, debe ir dentro del arreglo.
     * $return: Las columnas que devolverá la consulta generada de las filas afectadas.
     * $where: El string correspondiente a la cláusula where.
     */
    function arrayToQuery($action, $array = null) {
        global $config;
        if (!is_null($array) && !is_array($array)) $array = [$array];
        if (is_array($action)) {
            extract($action);
            if (!isset($where)) {
                $where = null;
            } else if (is_array($where)) {
                if ($where) {
                    if ($this->isMultiArray($where)) {
                        $where = "WHERE ".$this->multipleArrayToWhere($where);
                    } else {
                        $where = "WHERE ".$this->arrayToQuery('and', $where);
                    }
                } else {
                    $where = null;
                }
                    
            }
        }
        $jsonChars = ['"', '[', ']'];
        $sqlChars  = ["'", '(', ')'];
        $sQuery = "";
        if (count($array)) {
            
            $keys = str_replace(["'", '(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_keys($array))));
            
            //$values = str_replace(['(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_values($array))));
            if (in_array($action, ['insert', 'update'])) {
                if (isset($return) && $return) {
                    $return = $this->returnToQuery($return);
                } else {
                    $return = "";
                }
            }
            if ($action == "update" || $action == "and") {
                $keys = explode(',', $keys);
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array), true);
                $fValues = array_combine($keys, $values);
                
                if (count($fValues)) {
                    $i = 1;
                    
                    foreach ($fValues as $key => $val) {
                        //BIT datatype syntax correction
                        if (strpos($val, "'b'") === 0) {
                            $val = substr($val, 1, -1);
                        }
                        $sQuery .= $key . " = " . $val;
                        //Glue
                        if (count($fValues) > $i) {
                            if ($action == "update") {
                                $sQuery .= ", ";
                            } else if ($action == "and") {
                                $sQuery .= " AND ";
                            }
                        }
                        $i++;
                    }

                    if ($config->database->type == "mssql") {
                        if (isset($return) && isset($where)) {
                            $sQuery = "{$sQuery} {$return} {$where}";
                        } else if (isset($return)) {
                            $sQuery = "{$sQuery} {$return}";
                        } else if (isset($where)) {
                            $sQuery = "{$sQuery} {$where}";
                        }
                    } else if ($config->database->type == "pgsql") {
                        if (isset($return) && isset($where)) {
                            $sQuery = "{$sQuery} {$where} {$return}";
                        } else if (isset($return)) {
                            $sQuery = "{$sQuery} {$return}";
                        } else if (isset($where)) {
                            $sQuery = "{$sQuery} {$where}";
                        }
                    }
                    
                }
            } elseif ($action == "insert") {
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array));
                $sQuery .= "(";
                $sQuery .= $keys;
                if ($config->database->type == "mssql" && $return) {
                    $sQuery .= ") {$return} VALUES (";
                } else {
                    $sQuery .= ") VALUES (";
                }
                $sQuery .= $values;
                if ($config->database->type == "pgsql" && $return) {
                    $sQuery .= ") {$return}";
                } else {
                    $sQuery .= ")";
                }
            } elseif ($action == "in") {
                $values = $this->valuesArrayToSQLCompatibleString(array_values($array));
                $sQuery = "(" . $values . ")";
            }
            return $sQuery;
        } else {
            return false;
        }
    }

    function toMultiArray($data) {
        if (!$data) return;
        $keys_first = array_keys($data);
        if (is_int($keys_first[0])) {
            //Es multi, devolver
            return $data;
        } else {
            //No es multi, envolver y devolver
            return [$data];
        }
    }

    function isMultiArray($data) {
        if (!$data) return;
        $keys_first = array_keys($data);
        if (is_int($keys_first[0])) {
            //Es multi, devolver
            return true;
        } else {
            //No es multi, envolver y devolver
            return false;
        }
    }

    function returnToQuery($return, $action = "inserted") {
        global $config;
        if (is_array($return)) {
            $return = array_map(function($col) use ($config,$action) {
                if ($config->database->type == "mssql") {
                    return "{$action}.{$col}";
                } else {
                    return $col;
                }
            }, $return);
            $return = implode(', ', $return);
        } else {
            if ($config->database->type == "mssql") {
                $return = "{$action}.{$return}";
            }
        }
        if ($config->database->type == "mssql") {
            $return = "OUTPUT {$return}";
        } else if ($config->database->type == "pgsql") {
            $return = "RETURNING {$return}";
        }
        return $return;
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
    
    function multipleArrayToInsert($array, $return = []) {
        global $config;
        $jsonChars = ['"', '[', ']'];
        $sqlChars  = ["'", '(', ')'];
        $sQuery = "";
        //Si el primer elemento del array tiene m�ltiples elementos -- array multidimensional
        if (isset($array[0]) && count($array[0]) >= 1) {
            //Extraer cabeceras desde el primer elemento
            $keys = str_replace(["'", '(', ')'], "", str_replace($jsonChars, $sqlChars, json_encode(array_keys($array[0]))));
    
            $array = array_map(function($item) {
                return array_values($item);
            }, $array);
    
            //$values = str_replace(['((', '))'], ['(', ')'], str_replace($jsonChars, $sqlChars, json_encode(array_values($array))));

            if (isset($return) && $return) {
                $return = $this->returnToQuery($return);
            } else {
                $return = "";
            }
    
            $sQuery .= "(";
            $sQuery .= $keys;
            if ($config->database->type == "mssql" && $return) {
                $sQuery .= ") {$return} VALUES ";
            } else {
                $sQuery .= ") VALUES ";
            }
            
            $last_key = array_keys($array);
            $last_key = end($last_key);
            foreach (array_keys($array) as $key) {
                $sQuery .= "(".$this->valuesArrayToSQLCompatibleString(array_values($array[$key])).")".($key!=$last_key?", ":"");
            }

            if ($config->database->type == "pgsql" && $return) {
                $sQuery .= " {$return}";
            }
        }
        return $sQuery;
    }

    function is_true($val, $return_null=false){
        $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
        return ( $boolval===null && !$return_null ? false : $boolval );
    }

    // fileUploadMaxSize / uploadFile por revisar
    function fileUploadMaxSize($returnByte=0){
		$upload_max_filesize = ini_get('upload_max_filesize');
        $upload_post_max_size = ini_get('post_max_size');
        // echo $upload_max_filesize.'--'.$upload_post_max_size;
		if(($upload_max_filesize * 1)<($upload_post_max_size * 1)){
			$upload_max_filesize_bytes = $upload_max_filesize;
		}else{
			$upload_max_filesize_bytes = $upload_post_max_size;
		}
			if($returnByte==1){
				$upload_max_filesize_bytes = trim($upload_max_filesize_bytes);
				$ultimo = strtolower($upload_max_filesize_bytes[strlen($upload_max_filesize_bytes)-1]);
				switch($ultimo) {
					case 'g':
						$upload_max_filesize_bytes *= 1024;
					case 'm':
						$upload_max_filesize_bytes *= 1024;
					case 'k':
						$upload_max_filesize_bytes *= 1024;
				}
			}
		return $upload_max_filesize_bytes;
	}

	function uploadFile($fileUpload,$fileTemp){
        $tamanoArchivo = $_FILES[$fileTemp]['size'];
        // if ($tamanoArchivo > $this->fileUploadMaxSize(1)){
        //     return false;
        // }else{
            if(move_uploaded_file($_FILES[$fileTemp]['tmp_name'],$fileUpload)){
               return true;
            }else{
               return false;
            }
        // }
    }

    function imageToBase64($imgfile){

        $ext = pathinfo($imgfile);

        $img = file_get_contents(root.$imgfile);

        $imgb64 = 'data:image/'.$ext["extension"].';base64, '.base64_encode($img); 

        return $imgb64;
    }

    function limpiarCaracteresEspeciales($s) {
        $s = str_replace(['á','à','â','ãª'],"a",$s);
        $s = str_replace(['Á','À','Â','Ã'],"A",$s);
        $s = str_replace(['é','è','ê'],"e",$s);
        $s = str_replace(['É','È','Ê'],"E",$s);
        $s = str_replace(['í','ì','î'],"i",$s);
        $s = str_replace(['Í','Ì','Î'],"I",$s);
        $s = str_replace(['ó','ò','ô','õº'],"o",$s);
        $s = str_replace(['Ó','Ò','Ô','Õ'],"O",$s);
        $s = str_replace(['ú','ù','û'],"u",$s);
        $s = str_replace(['Ú','Ù','Û'],"U",$s);
        $s = str_replace(" ","",$s);
        $s = str_replace("ñ","n",$s);
        $s = str_replace("Ñ","N",$s);
        //para ampliar los caracteres a reemplazar agregar lineas de este tipo:
        //$s = str_replace("caracter-que-queremos-cambiar","caracter-por-el-cual-lo-vamos-a-cambiar",$s);
        return $s;
    }

    function cleanRut($rut){
        // '/^0+|[^0-9kK]+/g'
        $rut = (gettype($rut) === 'string') ? preg_replace("/^0+|[^0-9kK]+/",'',$rut) : '';
        return  $rut;
    }

    function validateRut($rut){        
        
        if(gettype($rut) !== 'string'){
            return false;
        }

        if(!preg_match("/^0*(\d{1,3}(\.?\d{3})*)-?([\dkK])$/",$rut)){
            return false;
        }
        

        $rut = $this->cleanRut($rut);

        $t = (int)substr($rut, 0, -1);
        $m = 0;
        $s = 1;

        while ($t > 0) {
            $s = ($s + ($t % 10) * (9 - $m++ % 6)) % 11;
            $t = floor($t/10);
        }

        $v = ($s > 0) ? ''.($s - 1) : 'K';

	    return strtolower($v) === strtolower(substr($rut, -1));
    }

    function formatRut($rut){
        $rut = $this->cleanRut($rut);

        $length = strlen($rut);

        $result = substr($rut,$length - 4, 3)."-".$rut[$length - 1];

        for($i = 4; $i < $length; $i+= 3){
            $result = substr($rut, -3 - $i, -$i).".".$result;
        }

        return $result;
    }

    function createDirectory($pathname){
        if(!file_exists($pathname)){
            return mkdir($pathname, 0777);
        } else {
            return true;
        }
    }

    //funcion para crear miniaturas de imagenes
    function resizeImage($name_file, $src_file, $dest_file, $new_size = 200, $resize_aspect = "wd", $quality = "80") {

        global $config;

        $this->createDirectory(root.$config->global->s3path.$dest_file);

        $dest_file = root.$dest_file.$name_file;

        $imginfo = getimagesize($src_file);

        if ($imginfo == null) {

            return false;
        }

        // GD2 can only handle JPG, PNG & GIF images
        if (!$imginfo[2] > 0 && $imginfo[2] <= 3) {
            return false;
        }

        // height/width
        $srcWidth = $imginfo[0];
        $srcHeight = $imginfo[1];
        
        if($srcWidth > 200){
            if ($resize_aspect == 'ht') {
                $ratio = $srcHeight / $new_size;
            } elseif ($resize_aspect == 'wd') {
                $ratio = $srcWidth / $new_size;
            } elseif ($resize_aspect == 'sq') {
                $ratio = min($srcWidth, $srcHeight) / $new_size;
            } else {
                $ratio = max($srcWidth, $srcHeight) / $new_size;
            }
        } else {
            $ratio = 1.0;
        }
        

        /**
         * Initialize variables
         */
        $clipX = 0;
        $clipY = 0;

        $ratio = max($ratio, 1.0);
        if ($resize_aspect == 'sq') {
            $destWidth = (int) (min($srcWidth, $srcHeight) / $ratio);
            $destHeight = (int) (min($srcWidth, $srcHeight) / $ratio);
            if ($srcHeight > $srcWidth) {
                $clipX = 0;
                $clipY = ((int) ($srcHeight - $srcWidth) / 2);
                $srcHeight = $srcWidth;
            } elseif ($srcWidth > $srcHeight) {
                $clipX = ((int) ($srcWidth - $srcHeight) / 2);
                $clipY = 0;
                $srcWidth = $srcHeight;
            }
        } else {
            $destWidth = (int) ($srcWidth / $ratio);
            $destHeight = (int) ($srcHeight / $ratio);
        }

        if (!function_exists('imagecreatefromjpeg')) {
            echo 'PHP running on your server does not support the GD image library, check with your webhost if ImageMagick is installed';
            exit;
        }
        if (!function_exists('imagecreatetruecolor')) {
            echo 'PHP running on your server does not support GD version 2.x, please switch to GD version 1.x on the admin page';
            exit;
        }

        if ($imginfo[2] == 1)
            $src_img = imagecreatefromgif($src_file);
        elseif ($imginfo[2] == 2)
            $src_img = imagecreatefromjpeg($src_file);
        else
            $src_img = imagecreatefrompng($src_file);
        if (!$src_img) {
            return false;
        }
        if ($imginfo[2] == 1) {
            $dst_img = imagecreate($destWidth, $destHeight);
        } else {
            $dst_img = imagecreatetruecolor($destWidth, $destHeight);
        }

        imagecopyresampled($dst_img, $src_img, 0, 0, $clipX, $clipY, (int) $destWidth, (int) $destHeight, $srcWidth, $srcHeight);
        imagejpeg($dst_img, $dest_file, $quality);
        imagedestroy($src_img);
        imagedestroy($dst_img);

        // We check that the image is valid
        $imginfo = getimagesize($dest_file);
        if ($imginfo == null) {
            @unlink($dest_file);
            return false;
        } else {
            return true;
        }
    }

    function sendEmail($data){
        $mail = new PHPMailer(TRUE);

        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth = true;
        $mail->Host = "email-smtp.us-east-1.amazonaws.com";
        $mail->Username = "AKIAQL7KNHBJEJFQAO3A";
        $mail->Password = "BPM957r2l0Va95w51En6KCuv0ZJQuWGHQPERo/MqVg/y";
        $mail->Port = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;


        $mail->setFrom("noreply@buildingclerk.com", 'Building Clerk');

        //Destinatarios : puede ser uno o un arreglo
        if(is_array($data["email"])){
            foreach ($data["email"] as $email){
                $mail->AddAddress($email);
            }            
        } else {
            $mail->AddAddress($data["email"]);
        }
             

        $mail->isHTML(true);
        $mail->Subject = $data["asunto"];
        $mail->Body = $data["bodyEmail"];

        // attachment = variable que permite adjuntar archivo en un correo
        /* attachment debe ser un ARRAY con los parametros: 
            content = contenido del archivo, sin base64
            filename = nombre que llevara el archivo adjunto
            ext = extension de archivo
        */
        if(isset($data["attachment"]) && is_array($data["attachment"])){
            if(isset($data["attachment"][0])){
                foreach($data["attachment"] as $file){
                    $mail->AddStringAttachment($file["content"], $file["filename"].'.'.$file["ext"]);
                }
            } else {
                $file = $data["attachment"];
                $mail->AddStringAttachment($file["content"], $file["filename"].'.'.$file["ext"]);
            }
        }


        if (!$mail->send()) {
            return 'Mailer Error: '. $mail->ErrorInfo;
        } else {
           return 'Message sent!';
        }

    }

    static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function memory_usage() {
        $mem_usage = memory_get_usage(true);
        if ($mem_usage < 1024) {
            $mem_usage .= ' b';
        } elseif ($mem_usage < 1048576) {
            $mem_usage = round($mem_usage/1024,2) . ' kb';
        } else {
            $mem_usage = round($mem_usage/1048576,2) . ' mb';
        }
        return $mem_usage;
    }

}