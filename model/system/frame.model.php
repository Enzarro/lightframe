<?php

class frame_model {
    function __construct() {
        global $config;
        $this->db = new database($config->database);
        $this->utils = new utils();
    }

    function getResources($userData) {
        
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
            FROM recursos
            WHERE (eliminado != 1 OR eliminado IS NULL)");

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
                    recursos
                    LEFT JOIN permisos ON recursos.recurso_id = permisos.recurso_id AND usuario_id = {$userData->usuario_id}";
            $resUser = $this->db->queryToArray($query);

            // //Los que no tienen padres son los últimos hijos
            // $resLastChilds = array_filter($resAll, function($res) {
            //     return !$res['parent_id'];
            // });

            // //Obtener todas las funciones de los registros a los que el usuario tiene acceso
            // $resUserFunc = array_unique(array_column($resUser, 'funcion'));

            // //Agregar de forma recursiva todos los padres al arreglo de permisos del usuario
            // $resAllTree = $this->buildTree($resAll);
            // $resFinal = [];
            // foreach ($resAll as $resource) {

            //     if ($this->isChildActive($resAllTree, $search)) {
                    
            //     }

            //     foreach ($resUserFunc as $search) {
                    
                    
            //     }
                
            // }

            return $resUser;
        } else if ($userData == 'admin') {
            //Si es el usuario administrador, devolver todos los recursos
            return $resAll;
        }
    }

    function getResourceGrid($path) {
        return $this->db->queryToSingleVal(
            "SELECT
                grid_id
            FROM recursos
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
                    $this->isChildActive($child, $search);
                }
            }
        } else {
            return false;
        }
    }

    function isThereAnyChildActive($element) {
        if (!isset($element['activo']) || $element['activo'] == 1) {
            return true;
        }
        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                if ($child['activo'] == 1) {
                    return true;
                }
                if (isset($child['children'])) {
                    $this->isThereAnyChildActive($child);
                }
            }
        } else {
            return false;
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