<?php
class cont_documentacion_clientes_model{

    private $uploadPath = 'uploader/';

    function __construct() {
		global $client;
		$this->utils = new utils();
        $this->sys_clients_model = new sys_clients_model();
		$this->squema = $client?"{$client->db_name}":false;
    }

    function fnDTData($object, $returnconfig = false) {
        global $_DB;
        global $config;
        global $actionbuttons;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $table = "{$client->db_name}.cont_categorias";
        $actionbuttons = 
        '<div class="btn-group btn-group-justified" role="group" style="width: auto;">
            <div class="btn-group btn-group-sm" role="group">
                <button id="ccg-edit" class="btn btn-success" title="Abrir registro" type="button" btnPermis="btn_editar"><span aria-hidden="true" class="fa fa-folder-open"></span> Administrar...</button>
            </div>
        </div>';
        $primaryKey = 'id';
        $columns = [
            [
                //DB
                'dt' => 0,
                'db' => 'id',
                'formatter' => function( $d, $row ) {
                    return intval($d);
                },
                //DT
                'title' => 'ID',
                'visible' => false,
                'searchable' => false,
                'formitem' => array(
                    
                )
            ],
            [
                //DB
                'dt' => 1,
                'db' => 'nombre',
                //DT
                'title' => 'Nombre'
            ],
            [
                //DB
                'dt' => 2,
                'db' => "(SELECT COUNT(*) FROM {$client->db_name}.cont_archivos WHERE cont_archivos.fk_categoria_id = {$client->db_name}.cont_categorias.id AND (NOT cont_archivos.eliminado = '1' OR cont_archivos.eliminado IS NULL))",
                // 'db' => 'activo',
                'alias' => 'archivos',
                //DT
                'title' => 'Archivos'
            ],
            [
                //DB
                'dt' => 3,
                'db' => 'activo',
                'formatter' => function( $d, $row ) {
                    return $this->activoIcon($d);
                },
                //DT
                'title' => 'Activo',
                'responsivePriority' => 4
            ],
            [
                //DT
                'dt' => 4,
                'title' => 'Acciones',
                "responsivePriority" => 2,
                "orderable" => false,
                "width" => "120px",
                "data" => null,
                "defaultContent" => $actionbuttons,
                "searchable" => false
            ],
            [
                //DT
                'dt' => 5,
                "title" => '<span class="fa fa-trash" aria-hidden="true"></span>',
                "responsivePriority" => 1,
                "width" => "16px",
                "data" => null,
                "defaultContent" => "",
                "orderable" => false,
                "className" => 'select-checkbox',
                "searchable" => false
            ],
        ];
        
        if (!isset($_POST["config"])) {
            if (!$_POST["client"]) {
                return [
                    "draw"            => intval( $_POST['draw'] ),
                    "recordsTotal"    => 0,
                    "recordsFiltered" => 0,
                    "data"            => []
               ];
            } 
		}
        $filtro = ["", "(NOT eliminado = '1' OR eliminado IS NULL) "];
        return SSP::simple($_POST, $config->database, $table, $primaryKey, $columns, $filtro);
    }
    
    function activoIcon($value) {
        if ($value == 1) {
            return '<span aria-hidden="true" class="fa fa-check"></span>';
        } else {
            return '<span aria-hidden="true" class="fa fa-remove"></span>';
        }
    }
    
    function fnDelete($list) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $list = json_decode($list);
        $errors = false;
        $return_res = array();
        foreach($list as $id) {
            $sql = "UPDATE {$client->db_name}.cont_categorias SET
                        eliminado = 1
                    WHERE id = {$id}";
            if ($_DB->query($sql) == false) {
                $errors = true;
            }
        }
        
        if ($errors) {
            $return_res["type"] = "error";
            $return_res["text"] = "Hubieron problemas al realizar la eliminación.";
        } else {
            $return_res["type"] = "success";
            $return_res["text"] = "Se eliminaron los registros seleccionados correctamente.";
        }
        return $return_res;
    }

    function fnGetAllData($id) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $sql = "SELECT
                    id
                    ,nombre
                    ,activo
                FROM {$client->db_name}.cont_categorias WHERE id = {$id}";
        $res = $_DB->query($sql);
        $reg = $_DB->to_object($res);
        return $reg;
    }

    function fnNew($data) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $return_res = array();
        
        //Parse form data
        $formArray = Array();
        $formArray['activo'] = 0;
        foreach($data as $input) {
            if ($input["name"] == 'activo') {
                $formArray['activo'] = 1;
                continue;
            }
            $formArray[$input["name"]] = ("'".strtoupper($input["value"])."'");
        }
        
        //Check if duplicate
        $sql = "SELECT count(id) FROM {$client->db_name}.cont_categorias WHERE nombre = ".$formArray["nombre"]."   AND (NOT eliminado = '1' OR eliminado IS NULL)";
        $res = $_DB->queryToSingleVal($sql);
        if($res == 0 || $res == null) {
            //Insert
            $sql =	"INSERT INTO {$client->db_name}.cont_categorias (
                    nombre
                    ,activo
                    ,eliminado
                ) OUTPUT INSERTED.id VALUES (
                    ".$formArray["nombre"]."
                    ,".$formArray["activo"]."
                    ,0
                ) ";
            //Insert result
            $res = $_DB->query($sql);
            $return_res = array();
            if($res != false) {
                //Success
                $return_res["type"] = "success";
                $return_res["text"] = "Registro ingresado correctamente.";
            } else {
                //Error
                $res = ob_get_contents();
                ob_clean();
                $return_res["type"] = "warning";
                $return_res["text"] = "<pre>".$res."</pre>";
            }
        } else {
            //Exists
            $return_res["type"] = "warning";
            $return_res["text"] = "Ya existe una carpeta con el mismo nombre.";
        }
        return $return_res;
    }
    
    function fnGetFilesFromFolder($id) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $files = [];
        
        $sql = "SELECT 
                    id, nombre, [file], descripcion, activo
                FROM {$client->db_name}.cont_archivos 
                WHERE fk_categoria_id = {$id} AND (NOT eliminado = '1' OR eliminado IS NULL);";
        $res = $_DB->queryToArray($sql);
        if (count($res)) {
            foreach ($res as $reg) {
                $ext = strtolower(pathinfo($reg["file"], PATHINFO_EXTENSION));
                //Fontawesome Icons
                $button = [
                    'head' => '<span class="',
                    'icon' => '',
                    'foot' => '" aria-hidden="true"></span>'
                ];
                if ($ext == 'pdf') {
                    $button['icon'] = 'fa fa-file-pdf';
                } elseif (in_array($ext, ['doc', 'docx'])) {
                    $button['icon'] = 'fa fa-file-word';
                } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                    $button['icon'] = 'fa fa-file-excel';
                } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                    $button['icon'] = 'fa fa-file-image';
                } else {
                    $button['icon'] = 'fa fa-file';
                }
                
                $files[] = [
                    'id' => $reg["id"],
                    'nombre' => "<div style='overflow: auto; height: 30px;'>".$reg["nombre"]."</div>",
                    'file' => $client->db_name.'/'.$reg["file"],
                    'ext' => implode($button).' '.$ext,
                    'descripcion' => "<div style='overflow: auto; height: 30px; font-size: smaller;'>".$reg["descripcion"]."</div>",
                    'activo' => $this->activoIcon($reg["activo"])
                ];
            }
        }
        
        return $files;
    }

    function fnNewFile($carpeta, $nombre, $descripcion, $archivo, $activo) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $return_res = [];
        //Validation
        $nombre = strtoupper($nombre);
        $activo = $activo=='true'?1:0;
        
        //Check if duplicate
        $sql = "SELECT count(id) FROM {$client->db_name}.cont_archivos WHERE nombre = '{$nombre}' AND fk_categoria_id = {$carpeta} AND (NOT eliminado = '1' OR eliminado IS NULL);";
        $res = $_DB->queryToSingleVal($sql);
        if($res == 0 || $res == null) {
            //Subir
            $uploadFile = "";
            $resFiles = false;
            if($archivo['name'] != ""){
                $uploadFile = date("dmYHis") . "." .strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                if (is_dir($this->uploadPath.$client->db_name)===false){  mkdir($this->uploadPath.$client->db_name, 0777);}
                $resFiles = $this->utils->uploadFile($this->uploadPath.$client->db_name.'/'.$uploadFile, "userfile");
            }
            if($resFiles){
                $sql = "INSERT INTO {$client->db_name}.cont_archivos (
                    fk_categoria_id
                    ,nombre
                    ,[file]
                    ,descripcion
                    ,activo
                    ,eliminado
                ) VALUES (
                    {$carpeta}
                    ,'{$nombre}'
                    ,'{$uploadFile}'
                    ,'{$descripcion}'
                    ,{$activo}
                    ,0
                )";
                $_DB->query($sql);
            }        
            if ($res !== false && $resFiles !== false) {
                //Success
                $return_res["type"] = "success";
                $return_res["text"] = "Archivo ingresado correctamente.";
                return $return_res;
            } else {
                //Error
                $res = ob_get_contents();
                ob_clean();
                $return_res["type"] = "warning";
                if($resFiles){
                    $return_res["text"] = "<pre>".$res."</pre>";
                } else {
                    $return_res["text"] = "No se pudo subir el archivo";
                }
                
                return $return_res;
            }
        } else {
            //Existe
            $return_res["type"] = "warning";
            $return_res["text"] = "Ya existe un archivo con el mismo nombre en esta carpeta.";
            return $return_res;
        }
    }
    
    function fnDeleteFile($list) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $list = json_decode($list);
        
        $errors = false;
        $return_res = array();
        foreach($list as $id) {
            $sql = "UPDATE {$client->db_name}.cont_archivos SET
                        eliminado = 1
                    WHERE id = {$id}";
            if ($_DB->query($sql) == false) {
                $errors = true;
            }
        }
        
        if ($errors) {
            $return_res["type"] = "error";
            $return_res["text"] = "Hubieron problemas al realizar la eliminación.";
        } else {
            $return_res["type"] = "success";
            $return_res["text"] = "Se eliminaron los registros seleccionados correctamente.";
        }
        return $return_res;
    }
    
    function fnGetAllFileData($cliente,$id) {
        global $_DB;
        $client = $this->sys_clients_model->get($cliente);
        $sql = "SELECT
                    id, nombre, [file], descripcion, activo 
                FROM {$client->db_name}.cont_archivos 
                WHERE id = {$id}";
        $res = $_DB->query($sql);
        $reg = $_DB->to_object($res);
        return $reg;
    }

    function fnEditFile($id, $nombre, $descripcion, $archivo, $activo) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $return_res = [];
        
        //Validation
        $nombre = strtoupper($nombre);
        $activo = $activo=='true'?1:0;
        
        $sql = "SELECT fk_categoria_id, [file] FROM {$client->db_name}.cont_archivos WHERE id = {$id};";
        $res = $_DB->queryToArray($sql);
        if (count($res) == 0 ||count($res) == null) {
            //No existe
            $return_res["type"] = "warning";
            $return_res["text"] = "El archivo no existe.";
            return $return_res;
        } else {
            $reg=(object)$res[0];
            $uploadFile = $reg->file;
            $carpeta = $reg->fk_categoria_id;
        }
        
        //Check if duplicate
        $sql = "SELECT count(id) FROM {$client->db_name}.cont_archivos WHERE nombre = '{$nombre}' AND fk_categoria_id = {$carpeta} AND (NOT eliminado = '1' OR eliminado IS NULL) AND NOT id = {$id};";
        $res = $_DB->queryToSingleVal($sql);
        if($res == 0 || $res == null) {
            //Subir
            if(is_array ($archivo)){
                if($archivo['name'] != "") {
                    @unlink($this->uploadPath.$client->db_name.'/'.$_POST["old_file"]);
                    $uploadFile = date("dmYHis") . "." .strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                    $this->utils->uploadFile($this->uploadPath.$client->db_name.'/'.$uploadFile, "userfile");
                }
            }
            $sql = "UPDATE {$client->db_name}.cont_archivos SET
                        nombre = '{$nombre}',
                        [file] = '{$uploadFile}',
                        descripcion = '{$descripcion}',
                        activo = {$activo},
                        eliminado = 0
                    WHERE
                        id = {$id}";
            $_DB->query($sql);
            if ($res !== false) {
                //Success
                $return_res["type"] = "success";
                $return_res["text"] = "Archivo ingresado correctamente.";
                return $return_res;
            } else {
                //Error
                $res = ob_get_contents();
                ob_clean();
                $return_res["type"] = "warning";
                $return_res["text"] = "<pre>".$res."</pre>";
                return $return_res;
            }
        } else {
            //Existe
            $return_res["type"] = "warning";
            $return_res["text"] = "Ya existe un archivo con el mismo nombre en esta carpeta.";
            return $return_res;
        }
    }

    function fnEdit($id, $data) {
        global $_DB;
        $client = $this->sys_clients_model->get($_POST["client"]);
        $return_res = array();
        
        //Parse form data
        $formArray = Array();
        $formArray['activo'] = 0;
        foreach($data as $input) {
            if ($input["name"] == 'activo') {
                $formArray['activo'] = 1;
                continue;
            }
            $formArray[$input["name"]] = ("'".strtoupper($input["value"])."'");
        }
        
        //Check if duplicate
        $sql = "SELECT count(id) FROM {$client->db_name}.cont_categorias WHERE nombre = {$formArray["nombre"]} AND NOT id = '{$id}' AND (NOT eliminado = '1' OR eliminado IS NULL);";
        $res = $_DB->queryToSingleVal($sql);
        if ($res == 0 || $res == null) {
            //Update
            $sql = "UPDATE {$client->db_name}.cont_categorias SET
                        nombre = ".$formArray["nombre"]."
                        ,activo = ".$formArray["activo"]."
                    WHERE id=".$id;
            //Update result
            $res = $_DB->query($sql);
            if($res != false) {
                //Success
                $return_res["type"] = "success";
                $return_res["text"] = "Registro actualizado correctamente.";
            } else {
                //Error
                $res = ob_get_contents();
                ob_clean();
                $return_res["type"] = "warning";
                $return_res["text"] = "<small>".$res."</small>";
            }
        } else {
            //Exists
            $return_res["type"] = "warning";
            $return_res["text"] = "Ya existe un registro con el mismo nombre.";
        }
        return $return_res;
    }
}
?>