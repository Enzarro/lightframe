<?php

class jsontopdf {
    /**
     * columns: [{title, data, targets, combo: [{}, ...]}, ...]
     * data: [{}, ...]
     */

    function generate($req) {
        extract($req);
        $firstRow = $data[0];
        $keys = array_keys($firstRow);
        if(!isset($data)){
            return;
        }
        (!isset($name))?$name = "NewFile":"";
        (isset($includetimestamp) && $includetimestamp==true)?$name = $name.date('YmdHi'):'';
        (!isset($css))?$css='generic':"";
        (isset($pagecount) && $pagecount==true)?$pagecount = 'Página {PAGENO} de {nbpg}':$pagecount='';
        (!isset($numbernegative))?$numbernegative=false:"";
        (!isset($blankspace))?$blankspace=true:"";
        if (!isset($columns)) {
            $columns = [];
            if ($this->isAssoc($firstRow)) {
                foreach($keys as $key) {
                    $columns[] = [
                        'title' => $key,
                        'data' => $key,
                        'width' => false,
                        'headfontsize' => 10,
                        'bodyfontsize' => 9,
                        'fontweight' => false,
                        'fnumeric' => false,
                        'fdecimal' => false,
                        'align' => 'left'
                    ];
                }
            } else {
                foreach($keys as $key) {
                    $columns[] = [
                        'title' => "column_{$key}",
                        'data' => $key,
                        'width' => false,
                        'headfontsize' => 10,
                        'bodyfontsize' => 9,
                        'fontweight' => false,
                        'fnumeric' => false,
                        'fdecimal' => false,
                        'align' => 'left'
                    ];
                }
            }
        } else {
            foreach(array_keys($columns) as $key) {
                if (!isset($columns[$key]['title'])) {
                    if ($this->isAssoc($firstRow)) {
                        $columns[$key]['title'] = $keys[$key];
                    } else {
                        $columns[$key]['title'] = "column_".$keys[$key];
                    }
                }
                (!isset($columns[$key]['data']))? $columns[$key]['data'] = $keys[$key]:"";
                (!isset($columns[$key]['width']))?$columns[$key]['width'] = false:"";
                (!isset($columns[$key]['headfontsize']))?$columns[$key]['headfontsize'] = 10:"";
                (!isset($columns[$key]['bodyfontsize']))?$columns[$key]['bodyfontsize'] = 9:"";
                (!isset($columns[$key]['fontweight']))?$columns[$key]['fontweight'] = false:"";
                (!isset($columns[$key]['fnumeric']))?$columns[$key]['fnumeric'] = false:"";
                (!isset($columns[$key]['fdecimal']))?$columns[$key]['fdecimal'] = false:"";
                (!isset($columns[$key]['align']))? $columns[$key]['align'] = 'left':"";
            }
        }

        ob_start(); ?>
            <htmlpageheader name="myHTMLHeader">
                <table class="pdf-table-header" style="width:100%;">
                    <tr>
                        <td width="33%" style="font-size:10px;"><?=isset($header1)?$header1:''?></td>
                        <td width="33%" style="text-align:center;"><h3><?=isset($title)?$title:''?></h3><small><?=isset($subtitle)?$subtitle:''?></small></td>
                        <td width="33%" style="font-size:10px; text-align: right;"><?=isset($header2)?$header2:''?></td>
                    </tr>
                </table>
            </htmlpageheader>
        <?php $header = ob_get_clean();

        ob_start(); ?>
            <table style="width:100%; font-family: verdana;">
                <thead>
                    <?php if(isset($trheader)){ echo $trheader; } ?>
                    <tr>
                        <?php foreach (array_keys($columns) as $kcol): 
                            ($columns[$kcol]['width']==true)?$width=" width: ".$columns[$kcol]['width']."%;":$width=null;
                        ?>
                        <td style='font-size:<?=$columns[$kcol]['headfontsize']?>px;font-weight:bold;<?=$width?>'><?=$columns[$kcol]['title']?></td>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach (array_keys($columns) as $kcol): 
                                ($columns[$kcol]['fontweight']==true)?$bold=" font-weight:bold; ":$bold="";
                                $tdbold='';
                                if(isset($row['configuracion']) ){
                                    //Configuraciones
                                    if (isset($row['configuracion']['negritas']) && in_array($columns[$kcol]['data'], $row['configuracion']['negritas'])) {
                                        //Negritas
                                        ($bold=="")?$tdbold=' font-weight:bold; ':$tdbold='';
                                    }
                                }
                                if (isset($row[$columns[$kcol]['data']])){
                                    ($columns[$kcol]['fnumeric']==true)?$data=$this->fNum($row[$columns[$kcol]['data']],$columns[$kcol]['fdecimal'],$numbernegative):$data=$row[$columns[$kcol]['data']];
                                }else{
                                    $data="";
                                }
                                ($blankspace==true)?$data=str_replace(' ','&nbsp;',$data):"";
                            ?>
                            <td style='font-size:<?=$columns[$kcol]['bodyfontsize']?>px; text-align:<?=$columns[$kcol]['align']?>;<?=$bold?>;<?=$tdbold?>'><?=$data?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php $body = ob_get_clean();

        ob_start(); ?>
            <table width="100%" class="pdf-table-extra">
            <tr>
                <td class="table-extra-td" style="border-top:1px solid #ccc;"><b><?=isset($asignature1)?$asignature1:''?></b></td>
                <td width="100%"></td>
                <td class="table-extra-td" style="border-top:1px solid #ccc;"><b><?=isset($asignature2)?$asignature2:''?></b></td>
            </tr>
            </table>
            <?php $asignature = ob_get_clean();

        ob_start(); ?>
            <htmlpagefooter name="myHTMLFooter">
                <div class="pdf-table-foot">
                    <table width="100%">
                        <tr>
                            <td width="33%"><?=isset($footer1)?$footer1:''?></td>
                            <td width="33%" style="text-align:center;"><?php echo $pagecount;?></td>
                            <td width="33%" style="text-align: right;"><?=isset($footer2)?$footer2:''?></td>
                        </tr>
                    </table>
                </div>
            </htmlpagefooter>
        <?php $footer = ob_get_clean();
        $html = '<body>'.$header.$body.$asignature.$footer.'</body>';
        // echo $html;exit;
        $mpdf = new \Mpdf\Mpdf(['format' => $pageType]);
        $mpdf->SetTitle($name);
        $mpdf->SetCompression(true);
        $mpdf->simpleTables = false;
        $mpdf->packTableData = true;
        $stylesheet = file_get_contents("css/".$css.".css");
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($html);
        $mpdf->WriteHTML(ob_get_clean());
        $mpdf->Output($name.'.pdf', 'I');
    }

    function isAssoc(array $arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    function fNum($value, $decimals = false,$numbernegative = false) {
        if($numbernegative==true){
            $parenthesisafter='(';
            $parenthesisbefore=')';
        }else{
            $parenthesisafter='';
            $parenthesisbefore='';
        }
        if ($value=='0') return '-';
        if (is_null($value)) return '';
        if (!is_numeric($value)) return $value;
        if (is_bool($decimals)) { 
            if ($decimals) {
                $decimals = 2;
            } else {
                $decimals = 0;
            }
        }
        if ($value < 0) return  $parenthesisafter.number_format(abs($value), $decimals, ',', '.').$parenthesisbefore;
        elseif ($value > 0) return number_format($value, $decimals, ',', '.');
        else return 0;
    }
}

