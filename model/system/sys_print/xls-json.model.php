<?php

class xlstojson {
    /**
     * columns: [{title, data, targets, combo: [{}, ...]}, ...]
     * data: [{}, ...]
     */
    var $spreadsheet;

    function __construct() {
        $this->spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    }

    function generate($req,$data) {

		extract($req);
	
		try {
			$inputFileName = tempnam(sys_get_temp_dir(), 'excel_'.date('u').rand(1, 1000));
			$handle = fopen($inputFileName, "w");
			fwrite($handle, $data);
			$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
			fclose($handle);
			unlink($inputFileName);
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();
	
		$arrData = [];
		for ($row = 1; $row <= $highestRow; $row++){ 
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
			$arrData[] = $rowData[0];
		}
		
        foreach($columns as $col){
			if(isset($col["combo"]))$col["title"]=$col["title"].'.ID';
			$columnList[$col["title"]]= $col["data"];
        }
        
		$columnPos = [];
		foreach (array_shift($arrData) as $adKey => $adCols) {
			if (isset($columnList[$adCols])) {
				$columnPos[$adKey] = $columnList[$adCols];
			}
		}
		$finalDataSet = [];
		foreach ($arrData as $adRow) {
			$finalDataRow = [];
			foreach ($adRow as $adRowKey => $adRowVal) {
				if (isset($columnPos[$adRowKey]) && $adRowVal !== '#N/A' && $adRowVal !== null) {
					$data = [];
					if(is_float($adRowVal)){
						$adRowVal = str_replace(',','.',$adRowVal);
					}
					$finalDataRow[$columnPos[$adRowKey]] = $adRowVal;
				}else if(isset($columnPos[$adRowKey])){
					$finalDataRow[$columnPos[$adRowKey]] = null;
				}
			}
			$finalDataSet[] = $finalDataRow;
		}
		$finalDataSet = array_filter($finalDataSet,'implode');
		echo json_encode($finalDataSet);
    }
}