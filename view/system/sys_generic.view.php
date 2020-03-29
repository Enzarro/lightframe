<?php

class sys_generic_view {

    function __construct($resource, $object) {
        $this->resource = $resource;
        $this->object = $object;
        $this->model = new sys_generic_model($resource, $object);
        $this->FormItem = new FormItem();
        
        $this->frame_model = new frame_model();
        $this->frame_view = new frame_view();
    }

    function html($data = null) {
        ob_start(); ?>

        <div class="card">					
            <div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">
                <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>				
                <button id="main-export" class="btn btn-success" title='descargar información'><span class="fa fa-download"></span></button>	
                <label for="main-import">
                    <span class="btn btn-primary btn-file" title='importar información'><span class="fa fa-upload"></span></span>
                </label>
            </div>					
            <div class="card-body">						
                <table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="generic"></table>					
            </div>				
        </div>

        <!-- <pre><?=json_encode($this->resource, JSON_PRETTY_PRINT)?></pre> -->

        <div class="modal animated fade" id="modal-default" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-bottom-blue-grey border-bottom-lighten-4">
                        <h4 class="modal-title"><?=$data['modalTitle']?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body "></div>
                    <div class="modal-footer  border-top-blue-grey border-top-lighten-4">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                        <button id="save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-12" style="min-height: 100% !important;">
            <form class="form form-horizontal" id="jsonxls" action="http://34.236.202.115/" target="_blank" method="post">
                <textarea name="JSONtoXLS" id="JSONtoXLS" style="display:none;"></textarea>             
            </form>
        </div> 

        <div class="col-md-12" style="min-height: 100% !important;">
            <textarea name="XLStoJSON" id="XLStoJSON" style="display:none;"></textarea>        
            <input type="file" id="main-import" name="main-import" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display:none;">    
        </div>       
        <?php return ob_get_clean();
    }

    function jsData ($data) {
        ob_start(); ?><script>
        <?php foreach ($data as $name => $value): ?>
        var <?=$name?> = <?php if (is_string($value)): ?>"<?php endif; ?><?=$value?><?php if (is_string($value)): ?>"<?php endif; ?>;
        <?php endforeach; ?>
        </script><?php return ob_get_clean();
    }

    function form($object, $data = null) {
        // $fields = $object->fields;

        $fields = array_filter($object->fields, function($field) {
            return !in_array('hiddenForm', $field->attr);
        });

        //Crear objetos de FormItem según parametrización
        $fields = array_map(function($field) use ($data) {
            //Arreglo base a retornar
            $finalField = [
                'label' => $field->name,
                'name' => $field->column,
                'type' => $field->type
            ];
            //Campos de tipo texto
            if (in_array($field->type, ['int', 'float', 'rut', 'dtpicker', 'datetime', 'date', 'time'])) {
                $finalField['type'] = 'text';
                
                //Campo de tipo autonumerico
                if (in_array('primary', $field->attr)) {
                    if (!isset($data)) {
                        $finalField['value'] = "Este valor se genera automáticamente";
                    }
                    $finalField['prop']['disabled'] = true;
                }
                //Campo de tipo rut
                if ($field->type == 'rut') {
                    $finalField['prop']['data-fitype="rut"'] = true;
                }
                //Campo de tipo entero
                if ($field->type == 'int' && !in_array('primary', $field->attr)) {
                    $finalField['prop']['data-fitype="anumeric"'] = true;
                    $finalField['prop']["data-fisettings='".json_encode([
                        // 'vMin' => '0',
                        'aSep' => '',
                        'aPad' => false,
                        'lZero' => 'deny',
                        'wEmpty' => 'zero',
                        'mDec' => '0'
                    ])."'"] = true;
                }
                //Campo de tipo float
                if ($field->type == 'float') {
                    $finalField['prop']['data-fitype="anumeric"'] = true;
                    $finalField['prop']["data-fisettings='".json_encode([
                        'mDec' => '10',
                        'aSep' => '',
                        'aDec' => ',',
                        'aPad' => false,
                        'lZero' => 'deny',
                        'wEmpty' => 'zero'
                    ])."'"] = true;
                }
            }
            if (in_array($field->type, ['select', 'bselect'])) {
                $finalField['type'] = 'select';
                if ($field->origin) {
                    $finalField['type-params'] = $this->model->getGridCbo($field->origin);
                }
                if ($field->type == 'bselect') {
                    $finalField['prop']['data-fitype="bselect"'] = true;
                    $finalField['prop']["data-fisettings='".json_encode([
                        "liveSearch" => true
                    ])."'"] = true;
                }
            }
            if (in_array('notnull', $field->attr)) {
                $finalField['prop']['required'] = true;
            }

            return $finalField;
        }, $fields);

        $form = [
            'formid' => 'form-generic',
            'roweven' => true,
            'common' => [
                'horizontal' => true,
                'size' => 'sm',
                'stack' => true
            ],
            'fields' => $fields
        ];
        // if ($data) {
        //     $data->fields = utils::dtBuildDataFromConfig($this->model->getCamposDTConfig(), $data->fields)['data'];
        // }
        ob_start(); ?>
        <?=$this->FormItem->buildArray($form, $data)?>
        <?php return ob_get_clean();
    }

    function resume($data = null) {
        if (!isset($data['type'])):
            $html = ob_start(); ?>
            <h4>Filas en conflicto </h4>
            <div style="overflow-x:auto;">
                <br>
                <table class="table table-striped table-bordered table-hover dataTable no-footer" style="font-size: x-small;">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($data[0]) as $key) : ?>
                            <td><?= $key ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $dat) : ?>
                        <tr>
                            <?php foreach ($dat as $val) : ?>
                            <td><?= $val ?></td>
                            <?php endforeach; ?>
                        <tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php return ob_get_clean();
        else:
            return $data;
        endif;
    } 
}
