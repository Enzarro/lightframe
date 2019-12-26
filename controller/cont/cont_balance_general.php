<?php
class cont_balance_general {
    private  $decimal;
	
	function __construct(){
        $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->model = new cont_balance_general_model();
        $this->view = new cont_balance_general_view(isset($_POST["decimal"])?$_POST["decimal"]:null);
	}
	
	function main(){
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables','datetimepicker'],
            'js' => ['datatables','datetimepicker','jquery-base64', '/js/c_balance_general.js'],
            'concatPlugins' => false,
            'body' => [
				'title' => 'General',
				'icon' => 'fa fa-file',
                'subtitle' => 'Balance General',
                'html' => $this->view->html([
                    'modalTitle' => 'General'
                ])
            ]
        ]);
    }

    function load_init_cbo(){
        $res = $this->model->loadBalanceType();
        echo utf8_decode($this->view->showCbo($res));
    }

    function buscar(){
        if(isset($_POST["type"]) && isset($_POST["from"]) && isset($_POST["to"])){
            $res = $this->model->loadTable($_POST["type"], $_POST["from"], $_POST["to"]);
            echo utf8_decode($this->view->showTable($res, $_POST["from"], $_POST["to"]));
        }
    }

    function big_book_modal(){
        if( isset($_POST["from"]) && isset($_POST["to"]) && isset($_POST["account"])){
            $res = $this->model->loadTableByAccount($_POST["from"], $_POST["to"], $_POST["account"]);
            echo utf8_decode($this->view->showTableByAccount($res));
        }
    }

    function comprobant_detail(){
        if(isset($_POST["id_comprobant"])){
            $res = $this->model->loadTableByComprobant($_POST["id_comprobant"]);
            echo utf8_decode($this->view->showTableByComprobant($res));
        }
    }

    function print_pdf(){
        if (isset($_POST["pdfKey"]) && isset($_POST["pdfFile"])) {
            $CurlConnect = curl_init();
            curl_setopt($CurlConnect, CURLOPT_URL, pathServerPdf);
            curl_setopt($CurlConnect, CURLOPT_POST,   1);
            curl_setopt($CurlConnect, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($CurlConnect, CURLOPT_POSTFIELDS, http_build_query($_POST));
            //curl_setopt($CurlConnect, CURLOPT_USERPWD, $login.':'.$password);
            $Result = curl_exec($CurlConnect);
    
            header('Cache-Control: public'); 
            header('Content-type: application/pdf');
            //header('Content-Disposition: attachment; filename="new.pdf"');
            header('Content-Disposition: inline; filename="'.$this->utils->cryp($_POST["pdfFile"],"de").'.pdf"');
            header('Content-Length: '.strlen($Result));
            echo $Result;
        }
    }
}