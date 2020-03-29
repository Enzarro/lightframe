<?php

class sys_socket {
    function __construct() {
        $this->view = new sys_socket_view();
        $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->sp_model = new sys_sp_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select','sweetalert', '/js/system/'.get_class().'.js'],
            'concatPlugins' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html([
                    'modalTitle' => $this->resdata->texto
                ])
            ]
        ]);
    }

    function get() {
        $current = 0;
        $max = 100;
        
        $decimals = isset(explode('.', $_POST['step'])[1])?strlen(explode('.', $_POST['step'])[1]):0;

        $starttime = microtime(true);

        while ($current <= $max) {
            utils::emit('sys_socket:update-bar', [
                'current' => round($current, $decimals),
                'max' => $max,
                'time' => microtime(true) - $starttime
            ]);
            $current = $current + $_POST['step'];
            usleep(1000 * $_POST['time']);
        }
    }

    function testsp(){

        $this->utils->executeSP('dbo');

        // global $_DB;

        

        // $sql = file_get_contents(base."storedprocedures/dbo/WEB_Get_Count_Binnacle.StoredProcedure.sql");

        // $createpos = strpos($sql,'CREATE');


        // $sql = substr($sql,$createpos);

        // // $sql = preg_replace('/[\x00-\x1F\x80-\xFF]/', ' ', $sql);

        //  $_DB->query($sql);
        // // $this->sp_model->consolidateSP();
        // exit();
    }

    function message(){
        utils::emit('message masive', [
            'message' => $_POST["message"]
        ], true);
    }

    function list(){
        echo utils::emitUser();
    }

    function testpost() {
        echo json_encode($_POST);
    }

}