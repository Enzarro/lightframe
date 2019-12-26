<?php


class cont_plan_general_model{

    var $table = 'dbo.cont_cuentas_generales';
    var $primaryKey = 'codigo_cuenta';
    
    function __construct($resource = null) {
		$this->resource = $resource;
		$this->utils = new utils();
    }

    function list(){
        $dtNum = 0;
        global $config;
		
        
        $columns = [
            [
                //DB
				'dt' => $dtNum++,
				'db' => $this->primaryKey,
				//DT
				'title' => 'Código Cuenta',
                'searchable' => true,
                "orderable" => true
            ],
			[
				//DB
				'dt' => $dtNum++,
				'db' => 'descripcion',
				//DT
                'title' => 'Descripción'
            ],
            [
				//DB
                'dt' => $dtNum++,
                'db' => "''",
				'alias' => 'actions',
				//DT
                'title' => 'Acciones',
                'formatter' => function( $d, $row ) {
					ob_start(); ?>
						<div class="btn-group btn-group" role="group" style="width: auto;">
							<button class="btn btn-success  main-edit" title="Editar registro" type="button"><i class="fas fa-edit"></i></button>
						</div>
					<?php return ob_get_clean();
				}
            ],
            [
				//DB
                'dt' => $dtNum++,                
				//DT
                'title' => '<span class="fas fa-trash text-center" aria-hidden="true"></span>',
                "data" => null,
                "orderable" => false,
                "defaultContent" => "",
				"className" => 'select-checkbox',
				"searchable" => false
            ]
        ];         

        
        $filtro = "parent = '0' AND (NOT eliminado = '1' OR eliminado IS NULL)";

        if (!isset($_POST["config"])) {
			if (!isset($this->resource['permisos_user_obj']) || !in_array('read', $this->resource['permisos_user_obj'])) {
				return [
					"draw" => intval( $_POST['draw'] ),
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"swal" => [
						"type" => "error",
						"text" => "No tiene permisos para ver en este módulo."
					],
					"data" => []
				];
			}
		}


		return SSP::simple( $_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
    }


    function get($id){
        global $_DB;

        $get_data = $_DB->queryToArray("SELECT  codigo_cuenta, descripcion	FROM {$this->table} WHERE {$this->primaryKey} = '{$id}' ");

        return $get_data[0];
    }

    function getChild($id){
        global $_DB;

        $get_data = $_DB->queryToArray("SELECT  codigo_cuenta, descripcion	FROM {$this->table} WHERE parent = '{$id}' ");

        return $get_data;
    }

    function set($data,$type){
        global $_DB;

        if(isset($type) && !empty($type)){
            if (!in_array('update', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para actualizar en este módulo.'
				];
			}            
            $valid = count($_DB->queryToArray("SELECT  * FROM {$this->table} WHERE {$this->primaryKey} = '{$data["codigo_cuenta"]}' AND (NOT eliminado = '1' OR eliminado IS NULL) "));
            $valobj = count($_DB->queryToArray("SELECT  * FROM {$this->table} WHERE {$this->primaryKey} = '{$data["codigo_cuenta"]}' AND descripcion='{$data["descripcion"]}' AND (NOT eliminado = '1' OR eliminado IS NULL) "));
            
            if($valid>0){
                $_DB->query("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} = '{$data["codigo_cuenta"]}' ");
                return [
                    'type' => 'success',
                    'title' => 'Cambios guardados',
                    'text' => 'Los cambios fueron guardados con éxito'
                ];
            } else if(($valid>0 && $valobj>0) || ($valid>0 || $valobj>0)){
                return [
                    'type' => 'warning',
                    'title' => 'Registro Existente',
                    'text' => 'Ya existe un registro con esa información.'
                ];
            }
        } else {

            if (!in_array('create', $this->resource['permisos_user_obj'])) {
				return [
					'type' => 'error',
					'text' => 'No tiene permisos para crear en este módulo.'
				];
			}


            $_DB->query("INSERT INTO {$this->table} ".$this->utils->arrayToQuery('insert',$data));

            //Success
            return [
                'type' => 'success',
                'title' => 'Cambios guardados',
                'text' => 'Los cambios fueron guardados con éxito'
            ];
        }
    }

    function setChild($data){
        global $_DB;
        $cont = 0;

        if (!in_array('update', $this->resource['permisos_user_obj'])) {
            return [
                'type' => 'error',
                'text' => 'No tiene permisos para actualizar en este módulo.'
            ];
        }

        if (!in_array('create', $this->resource['permisos_user_obj'])) {
            return [
                'type' => 'error',
                'text' => 'No tiene permisos para crear en este módulo.'
            ];
        }


        foreach($data as $d){
            $sb["codigo_cuenta"] = $d["codigo_cuenta"];
            $sb["descripcion"] = $d["descripcion"];
            $sb["eliminado"] = 0;
            $sb["parent"] = $d["parent"];
            $valid = count($_DB->queryToArray("SELECT  * FROM {$this->table} WHERE {$this->primaryKey} = '{$sb["codigo_cuenta"]}' AND (NOT eliminado = '1' OR eliminado IS NULL) "));
            $valobj = count($_DB->queryToArray("SELECT  * FROM {$this->table} WHERE {$this->primaryKey} = '{$sb["codigo_cuenta"]}' AND descripcion='{$sb["descripcion"]}' AND (NOT eliminado = '1' OR eliminado IS NULL)"));

            if(isset($d["type"]) && !empty($d["type"])){
                if($valid>0){
                    $_DB->query("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $sb)." WHERE {$this->primaryKey} = '{$sb["codigo_cuenta"]}' ");
                } else if(($valid>0 && $valobj>0) || ($valid>0 || $valobj>0)){
                    $cont++;
                }
            } else {
                $_DB->query("INSERT INTO {$this->table} ".$this->utils->arrayToQuery('insert',$sb));
            }
        }

        if($cont>0){
            return [
               'type' => 'warning',
               'title' => 'Registro Existente',
               'text' => 'Ya existe un registro con esa información.'
            ];
        } else {
            return [];
        }
    }


    function delete($id){
        global $_DB;

        if (!in_array('delete', $this->resource['permisos_user_obj'])) {
			return [
				'type' => 'error',
				'text' => 'No tiene permisos para eliminar en este módulo.'
			];
		}

        $data = ['eliminado' => 1];

        $_DB->query("UPDATE {$this->table} SET ".$this->utils->arrayToQuery('update', $data)." WHERE {$this->primaryKey} IN ".$this->utils->arrayToQuery('in', $id)."");

        return [
            'type' => 'success',
            'title' => 'Cambios guardados',
            'text' => 'Cuenta Eliminada'
        ];

    }


}

?>