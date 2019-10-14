<?php

class test2_view {
    function html() {
        ob_start(); ?>
        hola mundo
        <?php return ob_get_clean();
    }
}