<?php

class home_view {
    function html() { 
        ob_start(); ?>
    
        <div class="row">
            <div class="col-xs-12">
            

            <div class="jumbotron">
                <div class="container">
                    <h1 class="display-3">Hola!</h1>
                    <p>Navega a través del menú lateral.</p>
                </div>
            </div>

            <!-- /.col -->
        </div>
        <!-- /.row -->
    
        <?php return ob_get_clean();
    }
}