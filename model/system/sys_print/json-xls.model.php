<?php

class jsontoxls {
    /**
     * columns: [{title, data, targets, combo: [{}, ...]}, ...]
     * data: [{}, ...]
     */
    var $spreadsheet;

    function __construct() {
        $this->spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    }

    function generate($req) {
        extract($req);

        $widthMultiplier = 1.2;
        $colCount = 1;

        //Default data:
        if (!isset($data)) {
            return;
        }
        if (!$data) {
            $columnKeys = array_column($columns, 'data');
            $row = [];
            foreach ($columnKeys as $key) {
                $row[$key] = '';
            }
            $data[] = $row;
        }

        $firstRow = $data[0];
        $keys = array_keys($firstRow);

        if (!isset($name)) {
            $name = "NewFile";
        }
        if (isset($includetimestamp) && $includetimestamp == true) {
            $name = $name.' - '.date('YmdHi');
        }
        if (!isset($sheetname)) {
            $sheetname = "Data";
        }else{
            $sheetname=substr($sheetname, 0, 24);
        }
        if (!isset($columns)) {
            // Generar definición de columnas a partir de primera fila de la data
            // dependiendo si es array asociativo o de indice numerico
            $columns = [];
            if ($this->isAssoc($firstRow)) {
                foreach($keys as $key) {
                    $columns[] = [
                        'title' => $key,
                        'data' => $key,
                        'targets' => $this->letter($colCount++),
                        'width' => round(strlen($key) * $widthMultiplier, 2)
                        // 'combo' => $key
                    ];
                }
            } else {
                foreach($keys as $key) {
                    $columns[] = [
                        'title' => "column_{$key}",
                        'data' => $key,
                        'targets' => $this->letter($colCount++),
                        'width' => round(strlen("column_{$key}") * $widthMultiplier, 2)
                        // 'combo' => $key
                    ];
                }
            }
        } else {

            foreach($columns as $c){
                $columns_add[] = $c;
                if(isset($c["combo"]) && count($c["combo"]>0)){
                    $columns_add[] = array("title" => $c["title"].".ID", "data" => "", "width" => $c["width"], 
                        "combo" => array_merge(["search" => "VLOOKUP"],$c["combo"]));
                    $combos[] = array("title" => $c["title"], "data" => $c["combo"]);
                }
            }
            
            $columns = isset($columns_add) ? $columns_add : $columns;
                

            foreach(array_keys($columns) as $key) {
                $columns[$key]['targets'] = $this->letter($colCount++);
                if (!isset($columns[$key]['title'])) {
                    if ($this->isAssoc($firstRow)) {
                        $columns[$key]['title'] = $keys[$key];
                    } else {
                        $columns[$key]['title'] = "column_".$keys[$key];
                    }
                }
                if (!isset($columns[$key]['data'])) {
                    $columns[$key]['data'] = $keys[$key];
                }
                if (!isset($columns[$key]['width'])) {
                    $columns[$key]['width'] = round(strlen($columns[$key]['title']) * $widthMultiplier, 2);
                }
                if (!isset($columns[$key]['combo'])){
                    $columns[$key]['combo'] = [];
                }
                if (!isset($columns[$key]['required'])){
                    $columns[$key]['required'] = null;
                } else {
                    if((is_array($columns[$key]['required']) && count($columns[$key]['required']) == 0) || empty($columns[$key]['required'])){
                        $columns[$key]['required'] = "";
                    } 
                }
            }
        }

       

        // $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

        //Styles
        $style = array(
            'alignment' => array(
                'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            )
        );
        $titlestyle = array(
            'font' => array(
                'color' => array(
                    'rgb' => 'ffffff'
                )
            ),
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '000000'
                ]
            ]
        );
        $bgcolor = array(
            'fill' => array(
                'type' => PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'color' => array(
                    'rgb' => 'CECECE'
                )
            )
        );
        $bgcolor = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'CECECE'
                ]
            ]
        ];
        $bstyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                )
            )
        );

        //PAGINA 1
        $this->spreadsheet->setActiveSheetIndex(0);
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetname);

        // echo json_encode($columns);
        // exit;
        
        //Definición de cabeceras de columnas
        foreach (array_keys($columns) as $kcol) {
            //Data
            $sheet->setCellValue($columns[$kcol]['targets'].'1', $columns[$kcol]['title']);
            //Width
            $sheet->getColumnDimension($columns[$kcol]['targets'])->setWidth($columns[$kcol]['width']);
        }

        //Definición de estilo
        $sheet->getStyle('A1:'.$this->letter($colCount-1).'1')->applyFromArray($titlestyle);

        // echo json_encode($combos);
        // exit;

        // contador de combos
        if(isset($combos)){
            if(count($combos) > 0){
                $cont = 1;
                //PAGINA 2 si esta combos habilitados
                $this->spreadsheet->createSheet(1);
                $this->spreadsheet->setActiveSheetIndex(1);
                $sheet = $this->spreadsheet->getActiveSheet();
                $sheet->setTitle('Combos');                
                foreach($combos as $co){
                    $sheet->mergeCells(''.$this->letter($cont).'1:'.$this->letter($cont+1).'1');
                    $sheet->setCellValue(''.$this->letter($cont).'1', $co["title"]);
                    $i=2;
                    foreach($co["data"] as $c){
                        $sheet->setCellValue(''.$this->letter($cont).''.$i, (isset($c["subtext"])?$c["name"]." (".$c["subtext"].")":$c["name"]));
                        $sheet->setCellValue(''.$this->letter($cont+1).''.$i, $c["value"]);
                        $i++;
                    }                    
                    $cont+=2;
                }

                if (isset($protect) && $protect == true) {
                    //Bloquear hoja (Proteger)
                    $sheet->getProtection()->setPassword('Datview1208');
                    $sheet->getProtection()->setSheet(true);
                }

                //PAGINA 1
                $this->spreadsheet->setActiveSheetIndex(0);
                $sheet = $this->spreadsheet->getActiveSheet();

                if (isset($protect) && $protect == true) {
                    $sheet->getProtection()->setPassword('Datview1208');
                    $sheet->getProtection()->setSheet(true);
                    $sheet->getStyle('A2:'.$this->letter($colCount-1).(count($data)+1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
                }
                
            }

        }
       

        // $spreadsheet->getActiveSheet()->getStyle('B2')->applyFromArray( 
        //     array( 
        //         'font' => array( 
        //             'name' => 'Arial', 
        //             'bold' => true, 
        //             'italic' => false, 
        //             'underline' => Font::UNDERLINE_DOUBLE, 
        //             'strikethrough' => false, 
        //             'color' => array( 
        //                 'rgb' => '808080' 
        //             ) 
        //         ), 
        //         'borders' => array( 
        //             'bottom' => array( 
        //                 'borderStyle' => Border::BORDER_DASHDOT, 
        //                 'color' => array( 'rgb' => '808080' ) 
        //             ), 
        //             'top' => array( 
        //                 'borderStyle' => Border::BORDER_DASHDOT, 
        //                 'color' => array( 'rgb' => '808080' ) 
        //             ) 
        //         ), 
        //         'quotePrefix' => true 
        //     ) 
        // );

        //Set array's data
        $currentRow = 2;
        
        foreach ($data as $row) {
            $cont = 1;
            foreach (array_keys($columns) as $kcol) {
                
                if (isset($columns[$kcol]['type'])) {
                    //Format
                    if ($columns[$kcol]['type'] == 'date') {
                        //Date
                        $dateFormat = strlen(explode('-', $row[$columns[$kcol]['data']])[0]) == 4 ? 'yyyy-mm-dd' : 'dd-mm-yyyy';
                        $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($row[$columns[$kcol]['data']]));
                        $sheet->getStyle($columns[$kcol]['targets'].':'.$columns[$kcol]['targets'])->getNumberFormat()->setFormatCode($dateFormat);
                    }else if ($columns[$kcol]['type'] == 'contpositive'){
                        if($row[$columns[$kcol]['data']]!=""):
                            $sheet->getStyle($columns[$kcol]['targets'].':'.$columns[$kcol]['targets'])->getNumberFormat()->setFormatCode('#,##0');                      
                            $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, abs($row[$columns[$kcol]['data']]));
                        endif;
                    
                    }else if ($columns[$kcol]['type'] == 'contpositivedecimal'){
                        if($row[$columns[$kcol]['data']]!=""):
                            $sheet->getStyle($columns[$kcol]['targets'].':'.$columns[$kcol]['targets'])->getNumberFormat()->setFormatCode('#,##0.00;\(#,##0.00\)');                        
                            $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, abs($row[$columns[$kcol]['data']]));
                        endif;
                    
                    }else if ($columns[$kcol]['type'] == 'contparentheses'){
                        if($row[$columns[$kcol]['data']]!=""):
                            $sheet->getStyle($columns[$kcol]['targets'].':'.$columns[$kcol]['targets'])->getNumberFormat()->setFormatCode("#,##0;(#,##0)");
                            $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, $row[$columns[$kcol]['data']]);
                        endif;
                    }else if ($columns[$kcol]['type'] == 'contparenthesesdecimal'){
                        if($row[$columns[$kcol]['data']]!=""):
                            $sheet->getStyle($columns[$kcol]['targets'].':'.$columns[$kcol]['targets'])->getNumberFormat()->setFormatCode("#,##0;(#,##0);\(#,##0.00\)");
                            $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, $row[$columns[$kcol]['data']]);
                        endif;
                    }
                } else if(count($columns[$kcol]['combo']) > 0){
                    if (array_key_exists('search', $columns[$kcol]['combo'])) {
                        $colNumber = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columns[$kcol]['targets'])-1; 
                        $letter  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNumber);
                        $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, '=VLOOKUP('.$letter.$currentRow.',Combos!$'.$this->letter($cont).'$2:$'.$this->letter($cont+1).'$'.count($columns[$kcol]['combo']).',2,0)');
                        $cont+=2;
                    } else {
                        $vlookup = 'Combos!$'.$this->letter($cont).'$2:$'.$this->letter($cont).'$'.(count($columns[$kcol]['combo'])+1);
                        $validation = $sheet->getCell($columns[$kcol]['targets'].$currentRow)->getDataValidation();
                        $validation->setType(PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Seleccion invalida');
                        $validation->setError('Valor no esta en la lista');
                        $validation->setFormula1($vlookup);
                        $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, $this->findName($columns[$kcol]['combo'],$row[$columns[$kcol]['data']]));
                    }
                } else if(!is_null($columns[$kcol]['required'])){
                    $formula = '';
                    $elementos = '';
                    if(is_array($columns[$kcol]['required'])){
                        foreach($columns[$kcol]['required'] as $req){
                            $elementos .= ($req != "") ? $req." - " : $req; 
                            $formula .= (is_numeric($req)) ? $columns[$kcol]['targets'].$currentRow.'='.$req.',' : $columns[$kcol]['targets'].$currentRow.'="'.strtoupper($req).'",'.$columns[$kcol]['targets'].$currentRow.'="'.strtolower($req).'",';
                        }
                    } else {
                        $formula = (is_numeric($columns[$kcol]['required'])) ? $columns[$kcol]['targets'].$currentRow.'='.$columns[$kcol]['required'].',' : $columns[$kcol]['targets'].$currentRow.'="'.strtoupper($columns[$kcol]['required']).'",'.$columns[$kcol]['targets'].$currentRow.'="'.strtolower($columns[$kcol]['required']).'",';
                    }
                    $validation = $sheet->getCell($columns[$kcol]['targets'].$currentRow)->getDataValidation();
                    $validation->setType(PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_CUSTOM);
                    $validation->setErrorStyle(PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('error entrada');
                    $validation->setError('Para marcar debe colocar: '.substr($elementos,0,-2));
                    $validation->setFormula1('=OR('.substr($formula, 0, -1).')');
                    $sheet->setCellValue($columns[$kcol]['targets'].$currentRow,$row[$columns[$kcol]['data']]);
                    $sheet->getCell($columns[$kcol]['targets'].$currentRow)->getStyle()->applyFromArray($style);
                } else {
                    //No format - direct data
                    $sheet->setCellValue($columns[$kcol]['targets'].$currentRow, $row[$columns[$kcol]['data']]);
                }
                
            }
            $currentRow++;
        }

        
        


        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        ob_clean();
        // We'll be outputting an excel file
        header('Content-type: application/openxmlformats-officedocument.spreadsheetml.sheet');
        // It will be called file.xls
        header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');
        $writer->save('php://output');
        exit;
    }

    function isAssoc(array $arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    function letter($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->letter($num2) . $letter;
        } else {
            return $letter;
        }
    }

    function findName($array,$value){
        $name = '';
        foreach($array as $a){
            if($a["value"] === $value) {
                if (isset($a["subtext"])) {
                    $name = $a["name"]." (".$a["subtext"].")";
                } else {
                    $name = $a["name"];
                }
                
            }
        }
        return $name;
    }

}