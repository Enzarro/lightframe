<?php

class frame_model {
    function __construct() {
        global $_DB;
        $this->db = $_DB;
        $this->utils = new utils();
        $this->sys_clients_model = new sys_clients_model();
        $this->sys_tree_model = new sys_tree_model();
        $this->login_model = new login_model();
    }

    function setSearchPath($client) {
        global $config;
        if ($config->database->type == "pgsql") {
            $clientObj = $this->sys_clients_model->get($client);
            if ($clientObj) {
                $this->db->query("SET search_path TO public, {$clientObj->db_name}");
            }
        }
    }

    function setHistory($table, $id, $action) {
        //El usuario puede ser extraído desde token
        //El cliente desde post
        //La fecha es ahora
        if(isset($_COOKIE['token'])){
            $userData = $this->login_model->getTokenData($_COOKIE['token']);
        }else{
            $userData = "admin";
        }
        // echo json_encode($userData);
        // exit;
        $data = [
            'usuario_id' => $userData=='admin'?0:$userData->usuario_id,
            'client_id' => (isset($_POST['client'])) ? $_POST['client'] : null,
            'fecha_accion' => 'now()',
            'nombre_tabla' => $table,
            'registro_id' => $id,
            'accion_id' => $action
        ];

        $this->db->query("INSERT INTO sys_historial ".$this->utils->arrayToQuery('insert', $data));
    }

    function getHistory($table, $id, $action) {
        $userData = $this->login_model->getTokenData($_COOKIE['token']);
        $data = [
            'usuario_id' => $userData=='admin'?0:$userData->usuario_id,
            'client_id' => $_POST['client'],
            'nombre_tabla' => $table,
            'registro_id' => $id,
            'accion_id' => $action
        ];

        return $this->db->queryToArray("SELECT usuario");
    }

    function getResAccess($path) {
        $userObj = $this->login_model->getTokenData($_COOKIE['token']);
        $resource = array_filter($this->getResources($userObj), function($res) use ($path) {
            return $res['funcion'] == $path;
        })[0];
        return ($resource);
    }

    function getResources($userData) {
        //Validar que la tabla exista
        $exists = $this->db->queryToSingleVal("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '{$this->sys_tree_model->table}'");
        if (!$exists) {
            return [];
        }
        //Todos los recursos del sistema
        $resAll = $this->db->queryToArray(
            "SELECT
                recurso_id as id,
                parent_id,
                texto,
                icono,
                funcion,
                grid_id,
                permisos_obj
            FROM {$this->sys_tree_model->table}
            WHERE (eliminado != 1 OR eliminado IS NULL) ORDER BY orden");
        //Parsear json
        // echo utils::var_doom($resAll, true);
        // die;
        if ($resAll) {
            $resAll = array_map(function($res) {
                $res['permisos_obj'] = json_decode($res['permisos_obj'], true);
                return $res;
            }, $resAll);
        } else {
            $resAll = [];
        }
        
        if(is_object($userData) && !isset($userData->rol_id)) return [];

        //Si viene el objeto de usuario...
        if (is_object($userData)) {
            //Obtener los recursos a los que tiene permiso
            $query = 
                "SELECT
                    recursos.recurso_id AS id,
                    recursos.parent_id,
                    recursos.texto,
                    recursos.icono,
                    recursos.funcion,
                    recursos.grid_id,
                    recursos.permisos_obj,
                    CASE WHEN permisos.recurso_id IS NULL THEN 0 ELSE 1 END AS activo,
                    permisos.permisos_obj AS permisos_user_obj 
                FROM
                    {$this->sys_tree_model->table} AS recursos
                    LEFT JOIN sys_permisos AS permisos ON recursos.recurso_id = permisos.recurso_id AND permisos.rol_id = {$userData->rol_id}
                WHERE (eliminado != 1 OR eliminado IS NULL) ORDER BY recursos.orden";
            $resUser = $this->db->queryToArray($query);
            //Parsear json
            $resUser = array_map(function($res) {
                $res['permisos_obj'] = json_decode($res['permisos_obj'], true);
                $res['permisos_user_obj'] = json_decode($res['permisos_user_obj'], true);
                return $res;
            }, $resUser);

            return $resUser;
        } else if ($userData == 'admin') {
            //Si es el usuario administrador, devolver todos los recursos
            if ($resAll) {
                $resAll = array_map(function($res) {
                    $res['permisos_user_obj'] = $res['permisos_obj']?array_map(function($perm) {
                        return $perm['key'];
                    }, $res['permisos_obj']):null;
                    return $res;
                }, $resAll);
                return $resAll;
            } else {
                return [];
            }
        }
    }

    function getResourceByPath($path) {
        $resAll = $this->db->queryToArray(
            "SELECT
                texto,
                icono
            FROM {$this->sys_tree_model->table}
            WHERE (eliminado != 1 OR eliminado IS NULL) AND funcion = '{$path}'");
        if ($resAll) {
            return (object)$resAll[0];
        } else {
            return (object)[
                'texto' => '',
                'icono' => ''
            ];
        }
    }

    function getResourceGrid($path) {
        $exists = $this->db->queryToSingleVal("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '{$this->sys_tree_model->table}'");
        if (!$exists) {
            return null;
        }
        return $this->db->queryToSingleVal(
            "SELECT
                grid_id
            FROM {$this->sys_tree_model->table}
            WHERE funcion = '{$path}' AND (eliminado != 1 OR eliminado IS NULL)");
    }

    function isChildActive($element, $search) {
        if (!$search) {
            return false;
        }
        if ($element['funcion'] == $search) {
            return true;
        }
        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['funcion'] == $search) {
                    return true;
                }
                if (isset($child['children'])) {
                    return $this->isChildActive($child, $search);
                }
            }
        } else {
            return false;
        }
    }

    function isThereAnyChildActive($element) {
        if (!isset($element['activo']) || $element['activo'] == 1) {
            return true;
        } else if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($this->isThereAnyChildActive($child)) {
                    return true;
                }
            }
        }
    }

    function buildTree(array $elements, $parentId = null) {
        
        //Arreglo final
		$branch = [];

		foreach ($elements as $element) {
            //Considerar que parentId parte en nulo en la sobrecarga de la función
            //Los elementos sin padre (parent_id = null) son los elementos raíz
			if ($element['parent_id'] == $parentId) {
                //Se pasa el arreglo completo nuevamente a la función y se repite el proceso
                //pasando el id del elemento como parent id, buscando a los hijos
                $children = $this->buildTree($elements, $element['id']);
                //Si la función retorna hijos, poner hijos en el elemento padre
				if ($children) {
					$element['children'] = $children;
                }
                //Meter en el arreglo final sólo las coincidencias con parentId
				$branch[] = $element;
			}
		}

		return $branch;
    }
    
}