<?php

class rrhh_trabajadores_view {
    function __construct($resource) {
        $this->resource = $resource;
        $this->model = new rrhh_trabajadores_model($resource);
    }
	function html($data) {
        ob_start(); ?>

<div class="card">					
	<div class="card-header border-bottom-blue-grey border-bottom-lighten-4 box-shadow-0 border-bottom-2 ">

		<div class="form-row">
			<div class="col-auto">
				<button id="main-new" class="btn btn-primary"><span class="fa fa-plus"></span> Nuevo</button>
			</div>
			<div class="col-auto">
				<button id="main-delete" class="btn btn-danger"><span class="fa fa-trash"></span> Eliminar</button>
			</div>
			<div class="col-auto">
				<div class="input-group" style="display: inline-flex;">
					<div class="input-group-prepend">
						<label class="input-group-text" for="fechatributaria"><span class="glyphicon glyphicon-calendar"></span> Fecha tributaria</label>
					</div>
					<div class="input-group-append">
						<input type="button" id="fechatributaria" class="btn btn-outline-secondary" style="width: 80px;">
					</div>
				</div>
			</div>
			<div class="col-auto">
				<button class="btn btn-outline-secondary" id="element-worker-viewdisabled" title="Visualizar trabajadores deshabilitados" type="button" value="false"><span aria-hidden="true" class="fa fa-eye"></span></button>
			</div>
		</div>

	</div>					
	<div class="card-body">
		<pre id="monthdata" style="display: none;"><b>UF:</b> 28.000</pre>
		<table width="100%" class="table table-striped table-bordered table-hover" cellspacing="0" id="dataTables-ccg"></table>				
	</div>				
</div>

<div class="modal animated fade" id="modal-default" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header border-bottom-blue-grey border-bottom-lighten-4">
				<h4 class="modal-title"><?=$data['modalTitle']?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="modal-body "></div>
			<div class="modal-footer  border-top-blue-grey border-top-lighten-4">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
				<button id="save" type="button" class="btn btn-primary"><span class="fa fa-save"></span> Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- DISABLE DATE -->
<form role="form" novalidate="true" id="disableform" style="display: none;">
	<div class="row elementcontainer" style="">
		<div class="form-group col-xs-12" style="">	
			<label for="cuenta" class="control-label col-sm-3">Fecha deshabilitación</label>
			<div class="col-sm-9">		
				<input type="text" class="form-control" id="disable-date">
				<span class="glyphicon form-control-feedback" aria-hidden="true" style="padding-right: 20px;"></span>
				<div class="help-block with-errors"></div>	
			</div>			
		</div>
	</div>
</form>

<div id="ez-Modal" class="modal fade" role="dialog" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-lg"  style="width: 95%">
	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close closemodal">×</button>
		<h4 id="ez-Modal-Title" class="modal-title">Vista previa</h4>
	  </div>
	  <div class="modal-body">
		<div id="ez-Modal-Body">
		</div>
	  </div>
	  <div class="modal-footer">
	  
	  </div>
	</div>
  </div>
</div>
        
        <?php return ob_get_clean();
	}
	
	//MODULE: Form Trabajador
	function FormTrabajador($id, $date) {
        $this->model = new rrhh_trabajadores_model($this->resource);
		$year = explode('-',$date)[0];
		$month = explode('-',$date)[1];
		$months = [null, "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $objT = new rrhh_trabajador;
        
        $trabajadorCargas = [];
        $trabajadorCargasCols = $this->model->fnGetCargasFamiliaresDTConfig();
        $trabajadorCargasEmptyRow = $this->model->fnGetCargasFamiliaresDTEmptyRow();

        $trabajadorCCs = [];
        $trabajadorCCsCols = $this->model->fnGetCCsDTConfig();
        $trabajadorCCsEmptyRow = $this->model->fnGetCCsDTEmptyRow();

        $trabajadorBonos = [];
        $trabajadorBonosCols = $this->model->fnGetBonosDTConfig();
        $trabajadorBonosEmptyRow = $this->model->fnGetBonosDTEmptyRow();

        //Traer datos desde la BD y armar opciones de combos
        $rawBody = null;
        $BodyList = null;
		if ($id) {
			//Carga de datos según ID
			$objT->setHead($this->model->getTrabajadorHead($id));
			$rawBody = $this->model->getTrabajadorBody($id, $date);
			$objT->setBody($rawBody);

			//$data = $this->model->FormTrabajadorData($id, $date);
			//Carga de HTML bonos
			$fiBonosFields = self::GenerateBonosFields($id, $date.'-01');
			$BodyList = $this->model->LogList($id);
			//Botón DateLog
			
			$trabajadorCCs = $this->model->getCC([
                'trabajador_id' => $id,
                'fecha_log' => $date.'-01'
            ]);

            $trabajadorBonos = $this->model->getBonos([
                'trabajador_id' => $id,
                'fecha_log' => $date.'-01'
            ]);
            
            $trabajadorCargas = $this->model->getCargas([
                'trabajador_id' => $id
            ]);

			$yearLog = explode('-',$objT->getLastBody('fecha_log'))[0];
			$monthLog = explode('-',$objT->getLastBody('fecha_log'))[1];
			if ($date.'-01' != $objT->getLastBody('fecha_log')):
			?>
			<h5>
				<span class="glyphicons glyphicons-question-sign"></span>
				Datos <b>laborales</b> y <b>de pago</b> cargados desde <b><?php echo $months[intval($monthLog)]; ?></b> del <b><?php echo $yearLog; ?></b>
			</h5>
			<?php
			endif;
        }
        
        $commonProps = [
            'horizontal' => true,
            'size' => 'sm',
            'stack' => true
        ];

		//Campos Datos Personales
		$fiNombre = new FormItem([
            'label' => "Nombre(s)",
            'name' => "nombre",
            'value' => $objT->getHead('nombre'),
            'type' => 'text',
            'prop' => ['required' => true]
        ], $commonProps);

        $fiAPaterno = new FormItem([
            'label' => "Apellido Paterno",
            'name' => "apaterno",
            'value' => $objT->getHead('apellido_paterno'),
            'type' => 'text',
            'prop' => ['required' => true]
        ], $commonProps);

        $fiAMaterno = new FormItem([
            'label' => "Apellido Materno",
            'name' => "amaterno",
            'value' => $objT->getHead('apellido_materno'),
            'type' => 'text',
            'prop' => ['required' => true]
        ], $commonProps);

        $fiFNacimiento = new FormItem([
            'label' => "Fecha de nacimiento",
            'name' => "datenacimiento",
            'value' => $objT->getHead('fecha_nacimiento'),
            'type' => 'text',
            'prop' => ['required' => true],
            ''
        ], $commonProps);

		$fiFNacimiento = new FormItem;
		$fiFNacimiento->setBasic("Fecha de nacimiento", "datenacimiento", $objT->getHead('fecha_nacimiento'));
		$fiFNacimiento->setType("text");
		$fiFNacimiento->horizontal = true;
		$fiFNacimiento->size = "sm";
		$fiFNacimiento->stack = true;
		$fiFNacimiento->setAddon('r', '<span class="glyphicon glyphicon-calendar"></span>');

		$fiTelefono = new FormItem;
		$fiTelefono->setBasic("Teléfono", "phone", $objT->getHead('fono'));
		$fiTelefono->setType("text");
		$fiTelefono->horizontal = true;
		$fiTelefono->size = "sm";
		$fiTelefono->stack = true;
		$fiTelefono->setAddon('l', '+56');
		$fiTelefono->setAddon('r', '<span class="glyphicon glyphicon-earphone"></span>');

		$fiDireccion = new FormItem;
		$fiDireccion->setBasic("Dirección", "direccion", $objT->getHead('direccion'));
		$fiDireccion->setType("text");
		$fiDireccion->horizontal = true;
		$fiDireccion->size = "sm";
		$fiDireccion->stack = true;
		$fiDireccion->setAddon('r', '<span class="glyphicon glyphicon-home"></span>');

		$fiPaises = $this->model->cboPaises();
		$fiPaises->size = "sm";
		$fiPaises->stack = true;
		$fiPaises->value = $objT->getHead('nacionalidad_id');

		$fiRegiones = $this->model->cboRegiones();
		$fiRegiones->size = "sm";
		$fiRegiones->stack = true;
		$fiRegiones->value = $objT->getHead('region_id');

		$fiProvincias = $this->model->cboProvincias($objT->getHead('region_id'));
		$fiProvincias->size = "sm";
		$fiProvincias->stack = true;
		$fiProvincias->value = $objT->getHead('provincia_id');

		$fiComunas = $this->model->cboComunas($objT->getHead('provincia_id'));
		$fiComunas->size = "sm";
		$fiComunas->stack = true;
		$fiComunas->value = $objT->getHead('comuna_id');

		$fiNivelEstudios = $this->model->cboNivelEstudios();
		$fiNivelEstudios->size = "sm";
		$fiNivelEstudios->stack = true;
		$fiNivelEstudios->value = $objT->getHead('nivelestudios_id');

		$fiEmail = new FormItem;
		$fiEmail->setBasic("E-Mail", "email", $objT->getHead('email'));
		$fiEmail->setType("text");
		$fiEmail->horizontal = true;
		$fiEmail->size = "sm";
		$fiEmail->stack = true;
		$fiEmail->setAddon('r', '<span class="glyphicon glyphicon-envelope"></span>');

		$fiEstadoCivil = $this->model->cboEstadoCivil();
		$fiEstadoCivil->size = "sm";
		$fiEstadoCivil->stack = true;
		$fiEstadoCivil->value = $objT->getHead('estadocivil');

		$fiSexo = $this->model->cboSexo();
		$fiSexo->size = "sm";
		$fiSexo->stack = true;
		$fiSexo->value = $objT->getHead('sexo');

		//Campos datos laborales
		$fiFechaIngreso = new FormItem;
		$fiFechaIngreso->setBasic("Fecha de ingreso", "dateingreso", $objT->getLastBody("dateingreso"));
		$fiFechaIngreso->setType("text");
		$fiFechaIngreso->horizontal = true;
		$fiFechaIngreso->size = "sm";
		$fiFechaIngreso->stack = true;
		$fiFechaIngreso->setAddon('r', '<span class="glyphicon glyphicon-calendar"></span>');

		$fiTipoContrato = $this->model->cboTipoContrato();
		$fiTipoContrato->size = "sm";
		$fiTipoContrato->stack = true;
		$fiTipoContrato->value = $objT->getLastBody("contrato_id");

		$fiAFPs = $this->model->cboAFPs();
		$fiAFPs->size = "sm";
		$fiAFPs->stack = true;
		$fiAFPs->value = $objT->getLastBody("afp_id");

		$fiIsapres = $this->model->cboIsapres();
		$fiIsapres->size = "sm";
		$fiIsapres->stack = true;
		$fiIsapres->value = $objT->getLastBody("isapre_id");

		$fiCC = $this->model->cboCC();
		$fiCC->size = "sm";
		$fiCC->stack = true;
		$fiCC->value = $objT->getLastBody("centro_costo_id");

		$fiCargos = $this->model->cboCargos();
		$fiCargos->size = "sm";
		$fiCargos->stack = true;
		$fiCargos->value = $objT->getLastBody("cargo");

		$fiSueldoBase = new FormItem;
		$fiSueldoBase->setBasic("Sueldo base", "sueldobase", $objT->getLastBody("sueldo_base"));
		$fiSueldoBase->setType("text");
		$fiSueldoBase->horizontal = true;
		$fiSueldoBase->size = "sm";
		$fiSueldoBase->stack = true;
		$fiSueldoBase->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiQuincena = new FormItem;
		$fiQuincena->setBasic("Quincena", "sueldoquincena", $objT->getLastBody("sueldo_quincena"));
		$fiQuincena->setType("text");
		$fiQuincena->horizontal = true;
		$fiQuincena->size = "sm";
		$fiQuincena->stack = true;
		$fiQuincena->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiGratificacion = new FormItem;
		$fiGratificacion->setBasic("Gratificación legal", "gratificacionlegal", $objT->getLastBody("gratificacion_legal")); // CAUTION
		$fiGratificacion->setType("text");
		$fiGratificacion->horizontal = true;
		$fiGratificacion->size = "sm";
		$fiGratificacion->stack = true;
		$fiGratificacion->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');
		ob_start(); ?>
		<select id="gratificacion-mode" name="gratificacion-mode" class="btn btn-default" style="padding-top: 7px; padding-bottom: 7px; width: 112px;" title="Modalidad de gratificación">
			<option title="25% Imp." value="1"<?php if($objT->getLastBody("sueldo_quincena") == 1): ?> selected<?php endif; ?>>25% Sueldo Imponible Tope Gratificación</option>
			<option title="Max.Grat" value="2"<?php if($objT->getLastBody("sueldo_quincena") == 2): ?> selected<?php endif; ?>>Máxima gratificación</option>
			<option title="A Elecc." value="3"<?php if($objT->getLastBody("sueldo_quincena") == 3): ?> selected<?php endif; ?>>Monto a elección</option>
		</select>
		<?php $gratBtns = ob_get_clean();
		$fiGratificacion->setAddon('r', $gratBtns, 'btn');

		$fiTopeHorasLunVie = new FormItem;
		$fiTopeHorasLunVie->setBasic("Tope horas Lun-Vie", "horaslunvie", $objT->getLastBody("horas_lun_vie"));
		$fiTopeHorasLunVie->setType("text");
		$fiTopeHorasLunVie->horizontal = true;
		$fiTopeHorasLunVie->size = "sm";
		$fiTopeHorasLunVie->stack = true;
		$fiTopeHorasLunVie->setAddon('r', '<span class="glyphicon glyphicon-time"></span>');

		$fiTopeHorasSabDom = new FormItem;
		$fiTopeHorasSabDom->setBasic("Tope horas Sab-Dom", "horassabdom", $objT->getLastBody("horas_sab_dom"));
		$fiTopeHorasSabDom->setType("text");
		$fiTopeHorasSabDom->horizontal = true;
		$fiTopeHorasSabDom->size = "sm";
		$fiTopeHorasSabDom->stack = true;
		$fiTopeHorasSabDom->setAddon('r', '<span class="glyphicon glyphicon-time"></span>');

		$fiBAsistencia = new FormItem;
		$fiBAsistencia->setBasic("Asistencia", "bonoasistencia", $objT->getLastBody("bono_asistencia"));
		$fiBAsistencia->setType("text");
		$fiBAsistencia->horizontal = true;
		$fiBAsistencia->size = "sm";
		$fiBAsistencia->stack = true;
		$fiBAsistencia->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiBMovilizacion = new FormItem;
		$fiBMovilizacion->setBasic("Movilización", "bonomovilizacion", $objT->getLastBody("bono_movilizacion"));
		$fiBMovilizacion->setType("text");
		$fiBMovilizacion->horizontal = true;
		$fiBMovilizacion->size = "sm";
		$fiBMovilizacion->stack = true;
		$fiBMovilizacion->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiBColacion = new FormItem;
		$fiBColacion->setBasic("Colación", "bonocolacion", $objT->getLastBody("bono_colacion"));
		$fiBColacion->setType("text");
		$fiBColacion->horizontal = true;
		$fiBColacion->size = "sm";
		$fiBColacion->stack = true;
		$fiBColacion->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiAdIsapre = new FormItem;
		$fiAdIsapre->setBasic("Adicional Isapre", "isapreadicional", $objT->getLastBody("isapre_adicional"));
		$fiAdIsapre->setType("text");
		$fiAdIsapre->horizontal = true;
		$fiAdIsapre->size = "sm";
		$fiAdIsapre->stack = true;
		$fiAdIsapre->setAddon('l', 'UF');

		$fiAPVUF = new FormItem;
		$fiAPVUF->setBasic("APV", "apvuf", $objT->getLastBody("apv_uf"));
		$fiAPVUF->setType("text");
		$fiAPVUF->horizontal = true;
		$fiAPVUF->size = "sm";
		$fiAPVUF->stack = true;
		$fiAPVUF->setAddon('l', 'UF');

		$fiAPVPercent = new FormItem;
		$fiAPVPercent->setBasic("APV", "apvporcentaje", $objT->getLastBody("apv_porcentaje"));
		$fiAPVPercent->setType("text");
		$fiAPVPercent->horizontal = true;
		$fiAPVPercent->size = "sm";
		$fiAPVPercent->stack = true;
		$fiAPVPercent->setAddon('l', '%');

		$fiAPVPactado = new FormItem;
		$fiAPVPactado->setBasic("APV", "apvpactado", $objT->getLastBody("apv_pactado"));
		$fiAPVPactado->setType("text");
		$fiAPVPactado->horizontal = true;
		$fiAPVPactado->size = "sm";
		$fiAPVPactado->stack = true;
		$fiAPVPactado->setAddon('l', '<span class="glyphicon glyphicon-usd"></span>');

		$fiCCAF = new FormItem;
		$fiCCAF->setBasic("CCAF", "ccaf", $objT->getLastBody("ccaf"));
		$fiCCAF->setType("text");
		$fiCCAF->horizontal = true;
		$fiCCAF->size = "sm";
		$fiCCAF->stack = true;
		$fiCCAF->setAddon('l', '%');



		//Campos datos de pago
		$fiFormaPago = $this->model->cboFormaPago();
		$fiFormaPago->size = "sm";
		$fiFormaPago->stack = true;
		$fiFormaPago->value = $objT->getLastBody("formapago");

		$fiFPEfectivo = $this->model->cboFPEfectivo();
		$fiFPEfectivo->size = "sm";
		$fiFPEfectivo->stack = true;
		$fiFPEfectivo->value = $objT->getLastBody("fp-efectivo-pago");

		$fiFPValeVista = $this->model->cboFPValeVista();
		$fiFPValeVista->size = "sm";
		$fiFPValeVista->stack = true;
		$fiFPValeVista->value = $objT->getLastBody("fp-valevista-entrega");

		$fiVVBancos = $this->model->cboBancos();
		$fiVVBancos->size = "sm";
		$fiVVBancos->stack = true;
		$fiVVBancos->name = "fp-valevista-banco";
		$fiVVBancos->value = $objT->getLastBody("fp-valevista-banco");

		$fiTipoCuenta = $this->model->cboTipoCuenta();
		$fiTipoCuenta->size = "sm";
		$fiTipoCuenta->stack = true;
		$fiTipoCuenta->value = $objT->getLastBody("fp-deposito-tipo");

		$fiBancos = $this->model->cboBancos();
		$fiBancos->size = "sm";
		$fiBancos->stack = true;
		$fiBancos->value = $objT->getLastBody("fp-deposito-banco");

		$fiFPNCuenta = new FormItem;
		$fiFPNCuenta->setBasic("Número de cuenta", "fp-deposito-ncuenta", $objT->getLastBody("fp-deposito-ncuenta"));
		$fiFPNCuenta->setType("text");
		$fiFPNCuenta->horizontal = true;
		$fiFPNCuenta->size = "sm";
		$fiFPNCuenta->stack = true;
		
		

        ob_start();
        
		echo '<pre style="display: none">'.json_encode($rawBody, JSON_PRETTY_PRINT).'</pre>';
		echo '<pre style="display: none">'.json_encode($objT->getLastBody(), JSON_PRETTY_PRINT).'</pre>';
        ?>
        <form id="trabajador_new" class="form-horizontal">
	
            <!-- <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#dpersonales">Datos personales</a></li>
                <li><a data-toggle="tab" href="#dlaborales">Datos laborales</a></li>
                <li><a data-toggle="tab" href="#dpago">Datos de pago</a></li>
                <li><a data-toggle="tab" href="#dcargas">Cargas familiares</a></li>
                <li><a data-toggle="tab" href="#dhistorial">Fichas mensuales</a></li>
            </ul> -->

            <ul class="nav nav-tabs nav-underline nav-justified">
                <li class="nav-item">
                    <a class="nav-link active" id="user_tab-tab1" data-toggle="tab" href="#dpersonales" aria-controls="dpersonales" aria-expanded="true">
                        <i class="fas fa-user"></i> Datos personales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resources_tab-tab2" data-toggle="tab" href="#dlaborales" aria-controls="dlaborales">
                        <i class="fa fa-user-tie"></i> Datos laborales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resources_tab-tab2" data-toggle="tab" href="#dpago" aria-controls="dpago">
                        <i class="fa fa-money-check-alt"></i> Datos de pago
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resources_tab-tab3" data-toggle="tab" href="#dcargas" aria-controls="dcargas">
                    <i class="fa fa-users"></i> Cargas familiares
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resources_tab-tab3" data-toggle="tab" href="#dhistorial" aria-controls="dhistorial">
                    <i class="fa fa-calendar-alt"></i> Fichas mensuales
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="dpersonales" class="tab-pane active" role="tabpanel" aria-labelledby="user_tab-tab1" aria-expanded="false" style="padding-top:20px;">
                    <h3>Datos personales</h3>
                    
                    <div class="row">
                        <?php echo $fiNombre->build(); ?>
                        <?php echo $fiAPaterno->build(); ?>
                    </div>

                    <div class="row">
                        <?php echo $fiAMaterno->build(); ?>
                        
                        <div class="form-group required col-xs-12 col-md-6 form-group-sm elementcontainer" style="margin-bottom: 0px;">
                            <label for="rut" class="col-sm-3 control-label">RUT</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="rut-s" placeholder="RUT trabajador"<?php if($id): ?> value="<?php echo explode('-',$objT->getHead('rut'))[0]; ?>"<?php endif; ?>>
                                    <input type="hidden" id="rut" name="rut"<?php if($objT->getHead('rut')): ?> value="<?php echo $objT->getHead('rut'); ?>"<?php endif; ?>>
                                    <span class="input-group-addon" ><strong>-</strong></span>
                                    <span class="input-group-addon" id="rut-v"><?php if($id): echo explode('-',$objT->getHead('rut'))[1]; else: ?>0<?php endif; ?></span>
                                </div>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="row">
                        <?php echo $fiFNacimiento->build(); ?>
                        <?php echo $fiTelefono->build(); ?>
                    </div>
                    
                    <div class="row">
                        <?php echo $fiDireccion->build(); ?>
                        <?php echo $fiRegiones->build(); ?>
                    </div>
                    
                    <div class="row">
                        <!-- Aquí está el problem -->
                        <?php echo $fiProvincias->build(); ?>
                        
                        <?php echo $fiComunas->build(); ?>
                        
                    </div>
                    
                    <div class="row">
                        <?php echo $fiNivelEstudios->build(); ?>
                        <?php echo $fiEmail->build(); ?>
                    </div>
                    
                    <div class="row">
                        <?php echo $fiEstadoCivil->build(); ?>
                        <?php echo $fiSexo->build(); ?>
                    </div>
                    
                    <div class="row">
                        <?php echo $fiPaises->build(); ?>
                        
                        <div class="form-group required col-xs-12 col-md-6 form-group-sm elementcontainer" style="margin-bottom: 0px; display: none;">
                            <label for="ciudad_origen" class="col-sm-3 control-label">Ciudad de origen</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ciudad_origen" name="ciudad_origen" placeholder="Ej: Tampa, Florida">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-home"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="dlaborales" class="tab-pane fade in" style="padding-top:20px;">
                    <h3>Contrato</h3>
                    
                    <input type="hidden" name="datelog" value="<?php echo $date; ?>">
                    
                    <div class="row">
                        <?php echo $fiTipoContrato->build(); ?>

                        <?php echo $fiCargos->build(); ?>

                        

                    </div>
                    <div class="row">

                        <div class="form-group col-xs-12 col-md-6 form-group-sm elementcontainer" style="display: none;">
                            <label for="contrato-fin" class="col-sm-3 control-label">Fecha fin contrato</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="contrato-fin" name="contrato-fin">
                                    <span class="input-group-addon"><span class="fa fa-hourglass-end fa-fw"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-xs-12 col-md-6 form-group-sm elementcontainer" style="display: none;">
                            <label for="contrato-hito" class="col-sm-3 control-label">Hito</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="contrato-hito" name="contrato-hito">
                                    <span class="input-group-addon"><span class="fa fa-hourglass-end fa-fw"></span></span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <!-- <?php echo $fiCC->build(); ?> -->
                        
                    </div>

                    <h3>Centros de costos</h3>
                    <div id="centroscostos">
                        <textarea name="centroscostos" id="data" style="display: none;"><?php echo json_encode($trabajadorCCs, JSON_PRETTY_PRINT); ?></textarea>
                        <pre id="config" style="display: none;"><?=json_encode($trabajadorCCsCols)?></pre>
                        <pre id="emptyrow" style="display: none;"><?=json_encode($trabajadorCCsEmptyRow)?></pre>
                        <a class="btn btn-primary" id="agregar"><span class="glyphicon glyphicon-plus"></span> Agregar</a>
                        <!-- <br>
                        <br> -->
                        <table class="table table-bordered table-condensed" style="width: 100%"></table>
                    </div>

                    <h3>Remuneración</h3>

                    <div class="row">
                        <?php echo $fiSueldoBase->build(); ?>
                        <?php echo $fiQuincena->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiGratificacion->build(); ?>
                        <?php echo $fiBAsistencia->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiBMovilizacion->build(); ?>
                        <?php echo $fiBColacion->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiTopeHorasLunVie->build(); ?>
                        <?php echo $fiTopeHorasSabDom->build(); ?>
                    </div>
                    
                    <h3>Imposiciones</h3>
                    
                    <div class="row">
                        <?php echo $fiAFPs->build(); ?>
                        <?php echo $fiIsapres->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiAdIsapre->build(); ?>
                        <?php echo $fiAPVUF->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiAPVPercent->build(); ?>
                        <?php echo $fiAPVPactado->build(); ?>
                    </div>
                    <div class="row">
                        <?php echo $fiCCAF->build(); ?>
                    </div>

                    <h3>Adicionales mes</h3>
                    <div id="bonos">
                        <textarea name="bonos" id="data" style="display: none;"><?php echo json_encode($trabajadorBonos, JSON_PRETTY_PRINT); ?></textarea>
                        <pre id="config" style="display: none;"><?=json_encode($trabajadorBonosCols)?></pre>
                        <pre id="emptyrow" style="display: none;"><?=json_encode($trabajadorBonosEmptyRow)?></pre>
                        <a class="btn btn-primary" id="agregar"><span class="glyphicon glyphicon-plus"></span> Agregar</a>
                        <!-- <br>
                        <br> -->
                        <table class="table table-bordered table-condensed" style="width: 100%"></table>
                    </div>

                </div>
                
                <div id="dpago" class="tab-pane fade in" style="padding-top:20px;">
                    <h3>Datos de pago</h3>
                    
                    <div class="row">
                        <?php echo $fiFormaPago->build(); ?>
                        
                        
                        <div class="fp" id="fp-1" style="display: none;"> <!-- 1. Efectivo -->
                            <?php echo $fiFPEfectivo->build(); ?>
                        </div>
                    </div>

                    
                    <!-- 2. Cheque -->
                    
                    <div class="row">
                        <div class="fp" id="fp-3" style="display: none;"> <!-- 3. Vale Vista -->
                            <?php echo $fiFPValeVista->build(); ?>
                            <?php echo $fiVVBancos->build(); ?>
                        </div>
                        
                        
                        <div class="fp" id="fp-4" style="display: none;"> <!-- 4. Depósito -->
                            <?php echo $fiTipoCuenta->build(); ?>
                            <?php echo $fiBancos->build(); ?>
                            <?php echo $fiFPNCuenta->build(); ?>
                        </div>
                    </div>
                </div>

                <div id="dcargas" class="tab-pane fade in" style="padding-top:20px;">
                    <h3>Cargas familiares</h3>
                    <div id="cargas">
                        <textarea name="cargas" id="data" style="display: none;"><?php echo json_encode($trabajadorCargas, JSON_PRETTY_PRINT); ?></textarea>
                        <pre id="config" style="display: none;"><?=json_encode($trabajadorCargasCols)?></pre>
                        <pre id="emptyrow" style="display: none;"><?=json_encode($trabajadorCargasEmptyRow)?></pre>
                        <a class="btn btn-primary" id="agregar"><span class="glyphicon glyphicon-plus"></span> Agregar</a>
                        <!-- <br>
                        <br> -->
                        <table class="table table-bordered table-xs" style="width: 100%"></table>
                    </div>
                </div>
                
                <div id="dhistorial" class="tab-pane fade in" style="padding-top:20px;">
                    <h3>Fichas mensuales del trabajador</h3>

                    <pre style="display: none;"><?php echo json_encode($BodyList, JSON_PRETTY_PRINT); ?></pre>
                    
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr> 
                                <th>Fecha</th> 
                                <th>Sueldo base</th> 
                                <th>Centro de costos</th> 
                                <th>Cargo</th>
                                <th>AFP</th> 
                                <th>Isapre</th>
                                <th></th> 
                            </tr>
                        </thead>
                        <tbody id="changelog">
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <form id="cargo_form" class="form-horizontal" style="display: none;">
            <div class="form-group">
                <div class="col-sm-12">
                    <a class="btn btn-primary" id="cargo-back"><span class="glyphicon glyphicon-menu-left"></span> Atrás</a>
                </div>
            </div>

            <h3>Mantenedor - Cargo</h3>
            
            <div class="form-group">
                <label for="cargo-select-name" class="col-sm-3 control-label">Listado de cargos</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <select class="form-control" id="cargo-select-name" name="cargo-select-name"> <!-- combo -->
                            @CARGOSOPTIONS@
                        </select>
                        <span class="input-group-btn">
                            <button id="cargo-action-update" class="btn btn-default" type="button" data-toggle="tooltip" title="Guardar cambios en cargo seleccionado"><span class="glyphicon glyphicon-floppy-disk"></span></button>
                            <button id="cargo-action-delete" class="btn btn-default" type="button" data-toggle="tooltip" title="Eliminar cargo seleccionado"><span class="glyphicon glyphicon-trash"></span></button>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="cargo-name" class="col-sm-3 control-label">Nombre del cargo</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="text" class="form-control" id="cargo-name" name="cargo-name">
                        <span class="input-group-btn">
                            <button id="cargo-action-add" class="btn btn-default" type="button" data-toggle="tooltip" title="Agregar nuevo cargo al listado"><span class="glyphicon glyphicon-plus"></span></button>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="#cargo-description" class="col-sm-3 control-label">Descripción del cargo</label>
                <div class="col-sm-9">
                    <div class="btn-toolbar" data-role="editor-toolbar" data-target="#cargo-description" style="padding-bottom: 5px;">
                    <div class="btn-group">
                        <a class="btn btn-default" data-edit="bold" title="" data-original-title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold" aria-hidden="true"></i></a>
                        <a class="btn btn-default" data-edit="italic" title="" data-original-title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic" aria-hidden="true"></i></a>
                        <a class="btn btn-default" data-edit="strikethrough" title="" data-original-title="Strikethrough"><i class="fa fa-strikethrough" aria-hidden="true"></i></a>
                        <a class="btn btn-default" data-edit="underline" title="" data-original-title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline" aria-hidden="true"></i></a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-default" data-edit="insertunorderedlist" title="" data-original-title="Bullet list"><i class="fa fa-list-ul" aria-hidden="true"></i></a>
                        <a class="btn btn-default" data-edit="insertorderedlist" title="" data-original-title="Number list"><i class="fa fa-list-ol" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="" style="display: none;">
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body" id="cargo-description" contenteditable="true" style="overflow:scroll; min-height:100px; max-height:300px;"></div>
                    </div>
                </div>
            </div>
        </form>
        
        <?php return ob_get_clean();
	}

	function DateManagerForm($id) {
		$events = $this->model->getTrabajadorEvents($id);

		$fi = new FormItem;
		$fi->setType("select", [
			"table" => [
				[1, 'Ingreso'],
				[2, 'Término'],
				[3, 'Finiquito']
			],
			"includeNone" => false
		]);

		ob_start();
		?>
		<table id="events-listdiv" class="table table-bordered table-hypercondensed">
			<thead>
				<tr>
					<td>
						Tipo
					</td>
					<td>
						Fecha
					</td>
					<td></td>
				</tr>
			</thead>
			<tbody>
		<?php
		$first = true;
		foreach ($events as $event) {
			$fi->value = $event["accion"];
			?>
			<tr>
				<td>
					<select class="form-control event-type"<?php if($first): ?> disabled<?php endif; ?>>
						<?php echo $fi->selectOptionsBuilder(); ?>
					</select>
				</td>
				<td>
					<input type="text" class="form-control event-date" value="<?php echo $event["fecha_accion"]; ?>">
				</td>
				<td>
					<div class="btn-group btn-group-justified" role="group" style="width: auto;">
						<div class="btn-group btn-group" role="group">
							<button id="event-remove" class="btn btn-danger" title="Eliminar evento" type="button"<?php if($first): ?> disabled<?php endif; ?>><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></button>
						</div>
					</div>
				</td>
			</tr>
			<?php
			$first = false;
		}
		$fi->value = null;
		?>
			</tbody>
		</table>

		<table id="evento-template" style="display: none;">
			<tr>
				<td>
					<select class="form-control event-type">
						<?php echo $fi->selectOptionsBuilder(); ?>
					</select>
				</td>
				<td>
					<input type="text" class="form-control event-date">
				</td>
				<td>
					<div class="btn-group btn-group-justified" role="group" style="width: auto;">
						<div class="btn-group btn-group" role="group">
							<button id="event-remove" class="btn btn-danger" title="Eliminar evento" type="button"><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></button>
						</div>
					</div>
				</td>
			</tr>
		</table>

		<button id="event-add" type="button" class="btn btn-primary btn-xs btn-block">Agregar evento</button>
		<?php

		return ob_get_clean();
	}
	
	function GenerateBonosFields($id, $fechalog) {
		global $_DB;

		$fi = new FormItem;
		$fi->setType("select", [
			"table" => [
				[1, 'Haber imponible'],
				[2, 'Haber no imponible'],
				[3, 'Descuento']
			],
			"includeNone" => false
		]);

		$gen = "";
		$dbBonos = $_DB->query("SELECT bono_nombre, bono_valor, bono_tipo from rrhh_trabajadores_bonos WHERE trabajador_id = '".$id."' AND fecha_log = '".$fechalog."';");
		if($_DB->num_rows($dbBonos) != false) {
			ob_start();
			while($dbBono = $_DB->to_object($dbBonos)) {
				$fi->value = $dbBono->bono_tipo;
				?>
				<tr>
					<td>
						<input type="text" class="form-control input-sm bono-name" placeholder="Nombre bono" value="<?php echo $dbBono->bono_nombre ?>">
					</td>
					<td>
						<input type="text" class="form-control input-sm bono-amount" placeholder="Monto bono" value="<?php echo number_format($dbBono->bono_valor, 0, ',', '') ?>">
					</td>
					<td>
						<select class="form-control input-sm bono-type">
							<?php echo $fi->selectOptionsBuilder(); ?>
						</select>
					</td>
					<td>
						<div class="btn-group btn-group-justified" role="group" style="width: auto;">
							<div class="btn-group btn-group-sm" role="group">
								<button id="bono-remove" class="btn btn-danger" title="Eliminar asiento" type="button"><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></button>
							</div>
						</div>
					</td>
				</tr>
				<?php
			}
			$gen = ob_get_clean();
		}
		return $gen;
	}
}