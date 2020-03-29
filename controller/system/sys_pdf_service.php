<?php

class sys_pdf_service {
    function __construct($resource) {
        $this->utils = new utils();
        $this->frame_view = new frame_view();
        $this->frame_model = new frame_model();
        $this->resdata = $this->frame_model->getResourceByPath(get_class());
    }

    function main() {
        $this->frame_view->main([
            'menu' => get_class(),
            'js' => [$this->js()],
            'concatPlugins' => false,
            'cboClient' => false,
            'body' => [
                'icon' => $this->resdata->icono,
                'title' => $this->resdata->texto,
                'html' => $this->html([
                    'modalTitle' => 'Trabajador'
                ])
            ]
        ]);
    }

    function html($data) {
        ob_start(); ?>
<div class="card">				
	<div class="card-body">
		<div  id="areaTrab">
            <textarea id="htmldata" style="width: 100%; min-height: 500px;"></textarea>
            <button id="print" class="btn btn-primary">Imprimir</button>
		</div>			
	</div>				
</div>
        <?php return ob_get_clean();
    }

    function js() {
        ob_start(); ?>
<script>
    var path = 'sys_pdf_service';
$(document).off('click', '#print').on('click', '#print', function(){
	submit_post_via_hidden_form(
		`${path}/print`,
		{
			html: $("#htmldata").val()
		}
	);
});

function submit_post_via_hidden_form(url, params) {
    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
        action: url
    }).appendTo(document.body);

    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            $('<input type="hidden" />').attr({
                name: i,
                value: params[i]
            }).appendTo(f);
        }
    }

    f.submit();

    f.remove();
}
</script>
        <?php return ob_get_clean();
    }

    function print() {
        if (isset($_POST["html"])) {
            error_reporting(0);
            // echo $html;
            $html = $_POST["html"];
            // exit;
            //Limpiar warnings
            ob_clean();
            //Cabeceras pdf
            header('Content-type: application/pdf');
            header('Content-Disposition: inline;');
            header('Content-Transfer-Encoding: binary');
            // header('Content-Length: ' . filesize($file));
            header('Accept-Ranges: bytes');
            echo utils::post('http://34.236.202.115', [
                'HTMLtoPDF' => base64_encode($html)
            ]);
            exit;
        }
    }
}