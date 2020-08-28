<?php

class sys_print {

    function jsontoxls() {
        $data = json_decode($_POST["JSONtoXLS"], true);
        $objXLS = new jsontoxls();
        $objXLS->generate($data);
        exit;
    }

    function xlstojson() {
        $data = json_decode($_POST["XLStoJSON"], true);
        $objXLS = new xlstojson();
        // $objXLS->generate($data,base64_decode($_POST["file"]));
        $objXLS->generate($data,base64_decode($_POST["main-import"]["tmp_name"]));

        exit;
    }

    function jsontopdf() {
        $data = json_decode($_POST["JSONtoPDF"], true);
        $objPDF = new jsontopdf();
        $objPDF->generate($data);
        exit;
    }

    function htmltopdf() {
        $objPDF = new htmltopdf();
        ob_start();
        $objPDF->generate(base64_decode($_POST["HTMLtoPDF"]));
        echo ob_get_clean();
        exit;
    }

    function jsontograph() {
        $objGraph = new jsontograph();
        $data = json_decode($_POST["JSONtoGraph"]);
        if(is_array($data)){
            $return = [];
            foreach(array_keys($data) as $key) {            
                $return[$key] = $objGraph->generate(json_decode(json_encode($data[$key]), true));
                // $return[$key] = json_decode(json_encode($data[$key]), true);
            }
            echo json_encode($return);
            exit;
        } else {
            echo $objGraph->generate(json_decode(json_encode($data), true));
            exit;
        }   
    }
}