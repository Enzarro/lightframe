<?php

class sys_tables_model {

	var $db;
	var $table = 'information_schema.tables';
	var $primaryKey = 'table_name';
	var $schema;

    function __construct() {
		global $config;
		if ($config->database->type == "pgsql") {
			$this->schema = 'public';
		} else if ($config->database->type == "mssql") {
			$this->schema = 'dbo';
		}
		
		$this->utils = new utils();
		$this->sys_clients_model = new sys_clients_model();
        if (file_exists(root."/grids.json")) {
            $this->grids = json_decode(file_get_contents(root."/grids.json"));
        }
        if (!isset($this->grids)) {
            $this->grids = [];
        }
    }

	function list() {
		global $config;

		if (isset($_POST['client']) && $_POST['client']) {
			$client = $this->sys_clients_model->get($_POST['client']);
        	$this->schema = $client->db_name;
		}

		$schema = $this->schema;

		$dtNum = 0;
		$columns = [
			[
				//DB
				'dt' => $dtNum++,
				'db' => $this->primaryKey,
				//DT
				'title' => 'Tabla'
			],
            [
				'dt' => $dtNum++,
				'db_pgsql' => "(SELECT jsonb_agg(fields) FROM (SELECT table_name, column_name, udt_name FROM information_schema.columns WHERE table_schema = '{$this->schema}' AND table_name = information_schema.tables.table_name) AS fields)",
				'db_mssql' => "(SELECT table_name, column_name, data_type AS udt_name FROM information_schema.columns WHERE table_schema = '{$this->schema}' AND table_name = information_schema.tables.table_name FOR JSON AUTO)",
				'alias' => 'fields',
				'title' => 'Campos',
				'orderable' => false,
				'searchable' => false,
				'formatter' => function ($data) {
					$data = json_decode($data);
					ob_start(); ?>
					
					<!--<button type="button" class="btn btn-icon btn-pure secondary" data-toggle="popover" data-content="<div class='popover' role='popover'><div class='arrow'></div><div class='popover-header'></div><div class='popover-body'></div></div>" data-original-title="Default Template Structure" data-trigger="hover" data-placement="top" aria-describedby="popover446548">
					<i class="fas fa-eye"></i>
					</button>-->
					
					<div style="overflow: auto; height: 60px;"><?php
					foreach($data as $row) {
						echo "<b>Nombre:</b> ".utf8_decode($row->column_name).", <b>Tipo:</b> $row->udt_name<br>";
					}
					?></div><?php return ob_get_clean();
				}
			],
			[
				'dt' => $dtNum++,
				'db' => "''",
				'alias' => 'count',
				'title' => 'Registros',
				'formatter' => function($data, $row) use ($schema) {
					global $_DB;
					return $_DB->queryToSingleVal("SELECT COUNT(*) FROM {$schema}.{$row['table_name']}");
				},
				'orderable' => false,
				'searchable' => false
			]
		];

		//Filtro: Contenido de cláusula WHERE, también puede contener JOIN
		$filtro = "table_schema = '$this->schema'";

		return SSP::simple( $_POST, $config->database, $this->table, $this->primaryKey, $columns, $filtro);
	}
	
	
}