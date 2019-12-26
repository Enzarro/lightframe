<?php

class cont_plan_individual{
    function __construct($resource) {
        $this->view = new cont_plan_individual_view();
        $this->model = new cont_plan_individual_model($resource);
        // $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }


    function main(){
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select','select-picker'],
            'js' => ['datatables', 'datatables-select','sweetalert','select-picker','/js/cont/'.get_class().'.js'],
            'concatPlugins' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html(['title'=>'Plan Individual'])
            ]
        ]);        
    }

    function list(){
        if($_POST["type"] == "accounts"){
            echo json_encode($this->model->listcuentas());
        } else if($_POST["type"] == "subaccounts"){
            echo json_encode($this->model->listsubcuentas($_POST["codigo"]));
        }
           
    }

    function set(){
        $set = [];
        switch($_POST["fn"]){
            case "account-parent":
                $set = $this->model->set($_POST);
                break;
            case "update-plan":
                $set = $this->model->set_plan($_POST);
                break;
            default:
                $set = [];

        }
        
        echo json_encode($set);
        
    }

    function get(){
        echo $this->view->get($_POST);
    }

    function export(){
        if(isset($_POST)){
            $fn = $_POST["fn"];
            switch ($fn){
                case 'export_individual':
                    echo json_encode($this->model->export_plan());
                    break;
                case 'masivo_individual':
                    echo json_encode($this->model->export_masivo());
                    break;
                default:
                    echo json_encode([]);
            }
        }
    }

}

?>