<?php

class sys_query_view {
    function __construct($resource) {
        $this->resource = $resource;
        $this->FormItem = new FormItem();
        
        $this->frame_model = new frame_model();
        $this->frame_view = new frame_view();
    }

    function html() {
        ob_start(); ?>
<div class="card">				
	<div class="card-body">
		<div  id="areaTrab">
            <textarea id="query" style="width: 100%; min-height: 500px;"></textarea>
            <button id="print" class="btn btn-primary">Ejecutar</button>
            <table id="result" class="table" style="width: 100%; position: relative;"></table>
		</div>			
	</div>				
</div>
        <?php return ob_get_clean();
    }
}