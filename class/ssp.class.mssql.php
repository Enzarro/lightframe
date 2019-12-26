<?php

class SSP {

	/**
	 * Parse column configurations
	 * 
	 * @param bool  $config   Defines which array should return
	 * @param array $columns  The columns to be parsed
	 */
	static function column_parse($config, $columns) {

	}


	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output ( $columns, $data )
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();

			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
				$column = $columns[$j];
				if (isset( $column['alias'] )){
					if ($column['alias']!= '' ){
						$c = $column['alias'];
					}else{
						$c = $column['db'];
					}	
				}else{
					$c = $column['db'];
				}
				
				//Numeric array
				$columnKey = $column['dt'];
				//Associative Array
				if (isset($column['alias']) && $column['alias']) {
					$columnKey = $column['alias'];
				} else if (isset($column['db']) && $column['db']) {
					$columnKey = $column['db'];
				} else {
					$columnKey = null;
				}

				if ($columnKey) {
					// Is there a formatter?
					if ( isset( $column['formatter'] ) ) {
						$row[ $columnKey ] =  $column['formatter']( $data[$i][ $c ], $data[$i] );
					}
					else {
						$row[ $columnKey ] =  $data[$i][ $c ];
					}
				}
				
			}

			$out[] = $row;
		}

		return $out;
	}


	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit ( $request, $columns )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = " OFFSET ".$request['start']." ROWS FETCH NEXT ".$request['length']." ROWS ONLY";
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order ( $request, $columns )
	{
		$order = '';

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = SSP::pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = $column['db'].' '.$dir;
				}
			}

			$order = 'ORDER BY '.implode(', ', $orderBy);
		}

		return $order;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns, $filtroAdd )
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = SSP::pluck( $columns, 'data' );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];
			$str = pg_escape_string($str);

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					$globalSearch[] = " {$column['db']} LIKE '%{$str}%'";
				}
			}
		}

		// Individual column filtering
		for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];
			// $str = pg_escape_string($str);

			if ( $requestColumn['searchable'] == 'true' && $str != '' ) {
				$columnSearch[] = " {$column['db']} LIKE '%{$str}%'";
			}
		}

		// Combine the filters into a single string
		$where = '';
		$join = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		//Agrega filtro general personalizado
		if ($filtroAdd !== NULL ){
			//Add join support
			if (is_array($filtroAdd)) {
				$join = $filtroAdd[0];
				$filtroAdd = $filtroAdd[1];
			} else {

			}
			if ( $where !== '' ) {
				$where = $filtroAdd.' AND '.$where;
			} else {
				$where = $filtroAdd;
			}						
		}
		
		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}
		
		if ($join !== '') {
			$where = $join.' '.$where;
		}

		return $where;
	}


	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array  $request Data sent to server by DataTables
	 *  @param  array  $pg_details SQL connection details - see sql_connect()
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array  $columns Column information array
	 *  @return array  Server-side processing response array
	 */
	static function simple ( $request, $pg_details, $table, $primaryKey, $columns, $filtroAdd=NULL ) {
		global $_DB;
		//Return DataTables Configuration
		foreach($columns as $key => &$val){
			//Associative Array
			if (!isset($val['db']) && isset($val['db_mssql'])) {
				$val['db'] = $val['db_mssql'];
			}

			if (isset($val['alias']) && $val['alias']) {
				$val['data'] = $val['alias'];
			} else if (isset($val['db']) && $val['db']) {
				$val['data'] = $val['db'];
			} else if (!isset($val['data'])) {
				$val['data'] = null;
			}
			
			if (isset($request["config"]) && $request["config"]) {
				if (isset($val['db'])) unset($val['db']);
				if (isset($val['alias'])) unset($val['alias']);
				if (isset($val['formatter'])) unset($val['formatter']);
				if (isset($val['dt'])) {
					$val['targets'] = $val['dt'];
					unset($val['dt']);
				}
			} else {
				if ($key != "dt" && $key != "db" && $key != "data" && $key != "alias" && $key != "formatter") unset($val[$key]);
			}
		}

		if (isset($request["config"]) && $request["config"]) {
			return $columns;
		}
		

		foreach ($columns as $key => $item) {
			if(!isset($item['db'])) {
				unset($columns[$key]);
			}
		}
		// $db = SSP::pg_connect( $pg_details );
		
		// Build the SQL query string from the request
		$limit = SSP::limit( $request, $columns );
		$order = SSP::order( $request, $columns );
		$where = SSP::filter( $request, $columns, $filtroAdd );
		$select = "SELECT ".implode(", ", SSP::pluckas($columns)).", count(*) OVER() AS full_count FROM $table $where $order $limit";
		//Debug
		
		//echo "Consulta para la tabla: $select<br>";
    	// $result = pg_query( $db, $select ) or SSP::fatal("Error al ejecutar la consulta.\n". pg_last_error()."\n $select");
		// $data = pg_fetch_all($result);
		$time_start = microtime(true);
		$data = $_DB->queryToArray($select);
		
		
		//Debug
		// Main query to actually get the data
		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";		
		// die();
		$recordsFiltered = $data?$data[0]['full_count']:0;
 		
		if ($filtroAdd !== NULL){
			if (is_array($filtroAdd)) {
				$join = $filtroAdd[0];
				$filtroAdd = $filtroAdd[1];
			} else {
				$join = "";
			}
			$where = " $join WHERE $filtroAdd ";
		}else{
			$where = "";
		}

		// $resTotalLength = pg_query( $db,"SELECT COUNT({$primaryKey}) cuenta FROM $table $where" );
		// $recordsTotalRow = pg_fetch_row($resTotalLength);

		// $recordsTotal = $recordsTotalRow[0];
		$recordsTotal = $_DB->queryToSingleVal("SELECT COUNT({$primaryKey}) cuenta FROM $table $where");
		// pg_free_result( $result );
		// pg_free_result( $resTotalLength );
		$execution_time = round((microtime(true) - $time_start) * 1000);
		
		
		return [
		 	"draw"            => intval( $request['draw'] ),
		 	"recordsTotal"    => intval( $recordsTotal ),
		 	"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => $recordsFiltered?SSP::data_output( $columns, $data ):[],
			"time" => $execution_time,
			"select" => preg_replace( "/\r|\n/", "", $select)
        ];
	}


	/**
	 * Connect to the database
	 *
	 * @param  array $pg_details SQL server connection details array, with the
	 *   properties:
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 * @return resource Database connection handle
	 */
	static function pg_connect( $pg_details )
	{
		$db =  pg_connect("
			host={$pg_details->host} 
			dbname={$pg_details->name} 
			user={$pg_details->user} 
			password={$pg_details->pass}") or SSP::fatal("Error en conexión a DB.\n". pg_last_error()) ;
		return $db;
	}


	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal ( $msg )
	{
		echo json_encode( array( 
			"error" => $msg
		) );

		exit(0);
	}



	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
	
	static function pluckas ( $a )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			if ( isset( $a[$i]['alias'] ) ) {
				if ($a[$i]['alias']!=''){
					$out[] = $a[$i]['db']." AS ".$a[$i]['alias'];
				}else{
					$out[] = $a[$i]['db'];
				}
			}else{
				$out[] = $a[$i]['db'];
			}
		}
		return $out;
	}	
}

