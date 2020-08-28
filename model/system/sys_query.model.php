<?php

class sys_query_model {
    function query($query) {
        global $_DB;
        //Ejecutar la query y obtener la data
        $data = $_DB->queryToArray($query);
        $columns = [];
        //Declarar columnas de datatables en relación a primera fila de data
        if ($data && count($data)) {
            $frow = reset($data);
            $colkeys = array_keys($frow);
            $dtNum = 0;
            foreach ($colkeys as $col) {
                $columns[] = [
                    'targets' => $dtNum++,
                    'title' => $col,
                    'data' => $col
                ];
            }
        }
        if (!$columns) return [
            "swal" => [
                "type" => "warning",
                "text" => "La consulta no retornó datos"
            ]
        ];
        return compact('data', 'columns');
    }

    function getCamposDTConfig($columns) {
        $dtNum = 0;
        $fecha = new DateTime();
        return [
            [
				'targets' => $dtNum++,
				'title' => "ID",
                'data' => 'id',
                'visible' => false,
                'searchable' => false,
                'editType' => 'id'
            ],
			[
				'targets' => $dtNum++,
				'title' => "Copropietario",
                'data' => 'id_usuario',
                'editType' => 'bselect',
                'editConfig' => [
                    'liveSearch' => true,
                ],
                'editData' => $copropietarios,
                'data-subtext' => "rut",
            ],
            [
				'targets' => $dtNum++,
				'title' => "Perfil",
				'data' => 'perfil_id',
                'editType' => 'select',
                'editData' => $this->getPerfiles(),
            ],
            [
				'targets' => $dtNum++,
				'title' => "Entrada",
				'data' => 'fecha_entrada',
                'editType' => 'drpicker',
                'editCallback' => 'callbackEntradaSalida',
                'editConfig' => [
                    'locale' => [   
                        'format' => 'YYYY-MM-DD',
                        'customRangeLabel' => "Selecciona una fecha",
                        'daysOfWeek' => ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                        'monthNames' => ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                        'firstDay' => 1,
                    ],
                    'singleDatePicker' => true,
                    'autoUpdateInput' => false,
                    // 'minDate' => $fecha->format("Y-m-01"),
                    'maxDate' => $fecha->format("Y-m-d"),
                    // 'startDate' => $fecha->format("Y-m-d"),
                ]
            ],
            [
				'targets' => $dtNum++,
				'title' => "Salida",
                'data' => 'fecha_salida',
                'editType' => 'drpicker',
                'editCallback' => 'callbackEntradaSalida',
                'editConfig' => [
                    'locale' => [
                        'format' => 'YYYY-MM-DD',
                        'customRangeLabel' => "Selecciona una fecha",
                        'daysOfWeek' => ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                        'monthNames' => ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                        'firstDay' => 1
                    ],
                    'singleDatePicker' => true,
                    'autoUpdateInput' => false,
                    // 'minDate' => $fecha->format("Y-m-01"),
                    'maxDate' => $fecha->format("Y-m-d"),
                    // 'startDate' => $fecha->format("Y-m-d"),
                ]
            ],
			[
				'targets' => $dtNum++,
                'title' => "Acciones",
                'width' => "50px",
				'name' => 'actions',
				'data' => null,
                'defaultContent' => '',
				'editConfig' => [
                    'deleteExisting' => true,
                    'editExisting' => true,
                    'editCallback' => 'callbackRow'
				]
			]
		];
    }
}