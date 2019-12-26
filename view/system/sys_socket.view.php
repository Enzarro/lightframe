<?php

class sys_socket_view {
    function html() { 
        ob_start(); ?>

        <h2>Barra de carga</h2>
    
        <div class="card-body pt-0" id="progress-test">
          <p>Test <span class="float-right text-bold-600">89%</span></p>
          <div class="progress">
            <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%; -webkit-transition: unset; transition: unset;"></div>
          </div>
        </div>

        <form id="progresstest">
            <button class="btn btn-success" type="button" id="test">Probar barra</button>
            <input type="text" id="time" value="10">
            <input type="text" id="step" value="0.1">
        </form>

        <br><br><br>
        <h2>Notificaciones por sistema / masivas</h2>

        <form id="massivetest">
            <button class="btn btn-success" type="button" id="test">Probar notificación</button>
            <input type="text" id="message" value="Mensaje de prueba">
        </form>

        <br><br><br>
        <h2>Subida de archivo XLS</h2>
        <form id="uploadxls" target="_blank">
          <input name="sheet" type="file"></input>
          <button type="submit">Subir</button>
        </form>
        <pre id="upxlsres"></pre>
    
        <?php return ob_get_clean();
    }
}