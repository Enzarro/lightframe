<?php

class sys_grid_obj_model {

    var $id;
    var $name;
    var $table;
    var $target_schema;
    var $schema;
    var $fields;

    function __construct($data) {
        $this->utils = new utils;
        if (is_object($data)) $data = (array)$data;
        foreach (array_keys($data) as $key) {
            if (property_exists('sys_grid_obj_model', $key)) {
                $this->{$key} = $data[$key];
            }
        }
    }

    function set($data) {
        //Setear uno o mas registros
        $rows = $this->toMultiArray($data);
        $rows = $this->scrapDataByFields($this->toMultiArray($rows));
        $rows = $this->formatDataByFields($rows);
        //Registros a insertar (donde no esté seteada primary key)
        $inserted = $this->insert($rows);
        //Registros a updatear (donde esté seteada primary key)
        $updated = $this->update($rows);
        return compact('inserted', 'updated');
    }

    function get() {

    }

    function delete() {

    }

    function insert($rows) {
        if (!$rows) return;
        global $_DB;
        //Obtener llaves primarias
        $pks = $this->getPrimaryKeys();
        $pks = array_column($pks, 'column');
        //Ver si hay ids falsos, remplazar por null
        $rows = array_map(function($row) use ($pks) {
            foreach ($pks as $pk) {
                if ($row[$pk] == 0) {
                    $row[$pk] = null;
                }
            }
            return $row;
        }, $rows);
        //Filtrar filas donde en las primary keys haya data seteada
        $nopkrows = array_filter($rows, function($row) use ($pks) {
            $haspkdata = false;
            foreach ($pks as $pk) {
                if ($row[$pk] != null) {
                    $haspkdata = true;
                }
            }
            return !$haspkdata;
        });
        //Si hay filas con pks no seteadas, hacer insert masivo excluyendo columna id
        if ($nopkrows) {
            //Quitar columna pk
            $insertrows = array_map(function ($row) use ($pks) {
                return array_diff_key($row, array_flip($pks));
            }, $nopkrows);
            //Hacer insert masivo
            $query = "INSERT INTO {$this->schema}.{$this->table} ".$this->utils->multipleArrayToInsert($insertrows, $pks);
            //Retornar primary keys
            return $_DB->queryToArray($query);
        }
    }

    function update($rows) {
        if (!$rows) return;
        global $_DB;
        //Obtener llaves primarias
        $pks = $this->getPrimaryKeys();
        $pks = array_column($pks, 'column');
        //Obtener llaves no primarias
        $nopks = $this->getNonPrimaryKeys();
        $nopks = array_column($nopks, 'column');
        //Filtrar filas donde en las primary keys haya data seteada
        $pkrows = array_filter($rows, function($row) use ($pks) {
            $haspkdata = false;
            foreach ($pks as $pk) {
                if ($row[$pk] !== null || $row[$pk] !== 0) {
                    $haspkdata = true;
                }
            }
            return $haspkdata;
        });
        //Si hay filas con pks no seteadas, hacer insert masivo excluyendo columna id
        if ($pkrows) {
            //Hacer insert masivo
            $result = [];
            foreach ($pkrows as $row) {
                //Columnas a actualizar
                $updaterow = array_diff_key($row, array_flip($pks));
                //Columnas pk (where)
                $where = array_diff_key($row, array_flip($nopks));
                //Consulta
                $query = "UPDATE {$this->schema}.{$this->table} SET ".$this->utils->arrayToQuery([
                    'action' => 'update',
                    'array' => $updaterow,
                    'where' => $where,
                    'return' => $pks,
                ]);
                //Guardar resultado
                $data = $_DB->queryToArray($query);
                if ($data) {
                    $result[] = reset($data);
                }
                
            }
            
            //Retornar primary keys
            return $result;
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
    
    /**
     * Recibe un arreglo de objetos o arreglo de arreglos asociativos $data
     * Devuelve nuevo arreglo en base a columna "column" de arreglo $this->fields
     */
    function scrapDataByFields($data) {
        if (!$data) return;
        $newrow = $this->getEmptyRowObject();
        $newdata = [];
        foreach ($data as $row) {
            $new = $newrow;
            foreach (array_keys($row) as $col) {
                if (in_array($col, array_keys($newrow))) {
                    $new[$col] = $row[$col];
                }
            }
            $newdata[] = $new;
        }
        return $newdata;
    }

    function formatDataByFields($data) {
        if (!$data) return;
        $newrow = $this->getEmptyRowObject();
        $newdata = [];
        foreach ($data as $row) {
            $new = $newrow;
            foreach (array_keys($row) as $col) {
                $foundfield = array_filter($this->fields, function($field) use ($col) {
                    return $field->column == $col;
                });
                if ($foundfield) {
                    $foundfield = reset($foundfield);
                    switch($foundfield->type) {
                        case 'int':
                            $new[$col] = intval($row[$col]);
                        break;
                        case 'float':
                            $new[$col] = floatval($row[$col]);
                        break;
                        default:
                            $new[$col] = $row[$col];
                    }
                } else {
                    $new[$col] = $row[$col];
                }
                
            }
            $newdata[] = $new;
        }
        return $newdata;
    }

    function getEmptyRowObject() {
        $columns = array_column($this->fields, 'column');
        $nulldata = array_map(function() {
            return;
        }, $columns);
        $newrow = array_combine($columns, $nulldata);
        return $newrow;
    }

    function getPrimaryKeys() {
        $kfields = array_values(array_filter($this->fields, function($field) {
            return in_array('primary', $field->attr);
        }));
        return $kfields;
    }

    function getNonPrimaryKeys() {
        $kfields = array_values(array_filter($this->fields, function($field) {
            return !in_array('primary', $field->attr);
        }));
        return $kfields;
    }

    function getDTConfig() {
        $dtNum = 0;
        $dtcfg_canales = [];
        $sys_generic_model = new sys_generic_model();
        foreach($this->fields as $field) {
            //Campo base
            $newfield = [
                'targets' => $dtNum++,
                'title' => $field->name,
                'data' => $field->column,
                'editType' => $field->type,
            ];
            
            //Tipo select
            //A partir de listado de tablas, genera data para alimentar datatable sin ssp
            if (in_array($field->type, ['select', 'bselect'])) {
                if ($field->origin) {
                    $cbodata = $sys_generic_model->getGridCbo($field->origin);
                    $newfield['editData'] = array_map(function($row) {
                        return [
                            'id' => $row[0],
                            'text' => $row[1],
                        ];
                    }, $cbodata['table']);
                }
            }

            if ($field->type == 'check') {
                $newfield['editType'] = 'checkbox';
            }

            if (in_array($field->type, ['int', 'float'])) {
                $newfield['editType'] = 'anumeric';
            }

            //ID
            if ($newfield['data'] == 'id') {
                $newfield['visible'] = false;
                $newfield['editType'] = 'id';
            }

            //Fila vacía
            $dtcfg_canales['empty'][$field->column] = null;
            //Agregar columna
            $dtcfg_canales['config'][] = $newfield;
        }
        return $dtcfg_canales;
    }
}