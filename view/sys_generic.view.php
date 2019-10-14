<?php

class sys_generic_view {

    function __construct() {
        utils::load([
            classes.'formitembuilder.php'
        ]);
        $this->model = new sys_generic_model();
        $this->FormItem = new FormItem();
    }

    function html() {
        ob_start(); ?>
    
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                    <!-- <h3 class="box-title"></h3> -->
                        <button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
                        <button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
                    </div>
                    <div class="box-body">
                        <table id="generic" class="table table-bordered table-striped"></table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-default" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Grilla</h4>
                </div>
                <div class="modal-body">
                    <p>One fine body…</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button id="save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
                </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
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

        //Quitar los (el) campo de llave primaria
        $fields = array_filter($object->fields, function($field) {
            return !in_array('primary', $field->attr);
        });

        //Crear objetos de FormItem según parametrización
        $fields = array_map(function($field) {
            //Arreglo base a retornar
            $finalField = [
                'label' => $field->name,
                'name' => $field->column,
                'type' => $field->type
            ];
            //Campos de tipo texto
            if (in_array($field->type, ['int', 'float', 'rut'])) {
                $finalField['type'] = 'text';
                //Campo de tipo entero
                if ($field->type == 'int') {
                    $finalField['prop']['data-fitype="anumeric"'] = true;
                    $finalField['prop']["data-fisettings='".json_encode([
                        'vMin' => '0',
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
                    $finalField['type-params'] = [
                        'table' => $this->model->getGridCbo($field->origin)
                    ];
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
}