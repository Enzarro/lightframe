<?php

class jsontograph{

    var $graph;

    function generate($req){
        // var_dump($req);
        // exit;

        extract($req);

        if (!isset($data)) {
            return;
        }

        //Ancho de imagen
        if(!isset($width)){
            $width = 350;
        } else {
            $width = (!$width) ? 350 : $width;
        }

        //Altura de imagen
        if(!isset($height)){
            $height = 250;
        } else {
            $height = (!$height) ? 250 : $height;
        }

        //Declara el tipo de grafico, sino no hay toma por defecto de barra
        if(isset($type)){
            if($type == "pie"){
                $graph = new Amenadiel\JpGraph\Graph\PieGraph($width, $height);
                $chart = 0;
            } else if($type == "line"){
                $graph = new Amenadiel\JpGraph\Graph\Graph($width, $height);
                $graph->SetScale("textlin");
                $chart = 2;
            } else {
                $graph = new Amenadiel\JpGraph\Graph\Graph($width, $height, 'auto');
                $graph->SetScale("textlin");
                $chart = 1;
            }
        } else {
            $graph = new Amenadiel\JpGraph\Graph\Graph($width, $height, 'auto');
            $graph->SetScale("textlin");
            $chart = 1;
        }
        
        //Margenes grafico
        if(isset($margin)){
            $graph->SetMargin($margin["left"],$margin["right"],$margin["top"],$margin["bottom"]);
        }

        //Titulo grafico
        if(isset($title)){
            $graph->title->Set($title);
        } 

        if(!$chart){
            $tfont = isset($font) ? $font : 11;
            $tfontlegend = isset($fontlegend) ? $fontlegend : 11;
            if(isset($yaxis)){
                $p = new Amenadiel\JpGraph\Plot\PiePlot(array_column($data, $yaxis["data"]));
                $p->ShowBorder();
                $p->SetColor('black');
                $p->SetSliceColors($yaxis["colors"]); 
                $p->value->SetFont(FF_ARIAL, FS_NORMAL, $tfont);   
            } else {
                $p = new Amenadiel\JpGraph\Plot\PiePlot($data);
                $p->ShowBorder();
                $p->SetColor('black');
                $p->value->SetFont(FF_ARIAL, FS_NORMAL, $tfont);    
                if (isset($legends)) $p->setLegends($legends); 
            }

            $graph->Add($p);
            
            if (isset($legends)) {
                $graph->legend->SetFont(FF_ARIAL, FS_NORMAL, $tfontlegend);
                $graph->legend->SetMarkAbsSize($tfontlegend);
            }

        } else {
            
            if($chart == 1){
                if(is_array($yaxis["data"])){
                    $values = array_values($yaxis["data"]);
                    $col = array_values($yaxis["colors"]);
                    $legend = (array_key_exists('legends', $yaxis)) ? array_values($yaxis["legends"]) : [];
                    $group = [];                    
                    $i = 0;

                    foreach($values as $d){
                        $p = new Amenadiel\JpGraph\Plot\BarPlot(array_column($data, $d));
                        $p->SetFillColor($col[$i]);
                        $p->SetColor($col[$i]);
                        if(isset($legend[$i])){
                            $p->SetLegend($legend[$i]);
                        }
                        array_push($group, $p);
                        $i++;
                    }    

                    $graph->Add($group);

                    if(count($legend) > 0){
                        $graph->legend->SetFrameWeight(1); 
                    }

                } else {
                    $p = new Amenadiel\JpGraph\Plot\BarPlot(array_column($data, $yaxis["data"]));
                    $p->SetFillColor($yaxis["colors"]);
                    $p->SetColor($yaxis["colors"]);
                    $graph->Add($p);
                }                
            } else {
                if(is_array($yaxis["data"])){
                    $values = array_values($yaxis["data"]);
                    $col = array_values($yaxis["colors"]);
                    $legend = (array_key_exists('legends', $yaxis)) ? array_values($yaxis["legends"]) : [];
                    $i = 0;

                    foreach($values as $d){
                        $p = new Amenadiel\JpGraph\Plot\LinePlot(array_column($data, $d));
                        $p->SetColor($col[$i]);
                        if(isset($legend[$i])){
                            $p->SetLegend($legend[$i]);
                        }
                        $graph->Add($p);
                        $i++;
                    }  

                    if(count($legend) > 0){
                        $graph->legend->SetFrameWeight(1); 
                    }      

                } else {
                    $p = new Amenadiel\JpGraph\Plot\LinePlot(array_column($data, $yaxis["data"]));
                    $p->SetColor($yaxis["colors"]);
                    $graph->Add($p);
                }
            }

            $graph->ygrid->SetFill(false);
            $graph->yaxis->HideLine(false);
            $graph->yaxis->HideTicks(false,false);
            $graph->xaxis->SetTickLabels(array_column($data, $xaxis["data"]));

            if(isset($xaxis["font"])){
                if($xaxis["font"] == 1){
                    $graph->xaxis->SetFont(FF_FONT1);
                } else if($xaxis["font"] == 2){
                    $graph->xaxis->SetFont(FF_FONT2);
                } else {
                    $graph->xaxis->SetFont(FF_FONT0);
                }
            }

            if(isset($yaxis["font"])){
                if($yaxis["font"] == 1){
                    $graph->yaxis->SetFont(FF_FONT1);
                } else if($yaxis["font"] == 2){
                    $graph->yaxis->SetFont(FF_FONT2);
                } else {
                    $graph->yaxis->SetFont(FF_FONT0);
                }
            }

            if(isset($xaxis["align"])){
                $graph->xaxis->SetLabelAlign($xaxis["align"]["horizontal"],$xaxis["align"]["vertical"]);
            }

            //Formatos eje y
            if(isset($yaxis["format"])){
                if($yaxis["format"] == "money"){
                    $graph->yaxis->SetLabelFormat('$%01.0f');
                }
            }                  

            //Angulo eje x
            if(isset($xaxis["angle"])){
                $graph->xaxis->SetLabelAngle($xaxis["angle"]);
            }

        }
        
        $img = $graph->Stroke(_IMG_HANDLER);
        ob_start();
        imagepng($img);
        $img_data = ob_get_contents();
        ob_end_clean();

        return "data:image/png;base64,".base64_encode($img_data);
    }
}

?>