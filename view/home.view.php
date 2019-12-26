<?php

class home_view {
    function html() { 
        ob_start(); ?>
    
        <div class="jumbotron">
            <div class="container">
                <h1 class="display-3">Hola!</h1>
                <p>Navega a través del menú lateral.</p>
            </div>
        </div>
    
        <?php return ob_get_clean();
    }
}