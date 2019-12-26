

<?php 

class cont_plan_general{

    function __construct($resource) {

        $this->model = new cont_plan_general_model($resource);
        $this->view = new cont_plan_general_view();
        

        // $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main(){
        $this->frame_view->main([
            'menu' => get_class(),
            'css' => ['datatables', 'datatables-select'],
            'js' => ['datatables', 'datatables-select','sweetalert', '/js/cont/'.get_class().'.js'],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->view->html(['title'=>'Plan General'])
            ]
        ]);
        
    }


    function list(){
        echo json_encode($this->model->list());
    }

    function form(){
        echo $this->view->form($_POST);
    }

    function set(){
        $data["codigo_cuenta"] = $_POST["codigo_cuenta"];
        $data["descripcion"] = $_POST["descripcion"];
        $data["eliminado"] = 0;
        $data["parent"] = 0; 
        $sub = $this->model->setChild($_POST["subcuentas"]);
        $cu = $this->model->set($data,$_POST["type"]);
        if(count($sub) > 0) {
           $rep = $sub;
        } else {
            $rep = $cu;
        }
        echo json_encode($rep);
    }

    function delete(){
        echo json_encode($this->model->delete($_POST["codigos"]));
    }

}

?>