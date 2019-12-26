$(document).ready(function(){
    var d = new Date();
    var month = d.getMonth();
    var day = d.getDate();
    var year = d.getFullYear();	
        $('#calendar_from_tb1').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 00, 00)
        });

        $('#calendar_to_tb1').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 23, 59),
            useCurrent: false
        });
        
        $("#calendar_from_tb1").on("dp.change", function (e) {
            $('#calendar_to_tb1').data("DateTimePicker").minDate(e.date);
        });
        
        $("#calendar_to_tb1").on("dp.change", function (e) {
            $('#calendar_from_tb1').data("DateTimePicker").maxDate(e.date);
        });
    
    /**/
    
        $('#calendar_from_tb2').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 00, 00)
        });

        $('#calendar_to_tb2').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 23, 59),
            useCurrent: false
        });
        
        $("#calendar_from_tb2").on("dp.change", function (e) {
            $('#calendar_to_tb2').data("DateTimePicker").minDate(e.date);
        });
        
        $("#calendar_to_tb2").on("dp.change", function (e) {
            $('#calendar_from_tb2').data("DateTimePicker").maxDate(e.date);
        });
    
    /**/
    
        $('#calendar_from_tb3').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 00, 00)
        });

        $('#calendar_to_tb3').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD',
            //viewMode: 'years',
            defaultDate: new Date(year, month, day, 23, 59),
            useCurrent: false
        });
        
        $("#calendar_from_tb3").on("dp.change", function (e) {
            $('#calendar_to_tb3').data("DateTimePicker").minDate(e.date);
        });
        
        $("#calendar_to_tb3").on("dp.change", function (e) {
            $('#calendar_from_tb3').data("DateTimePicker").maxDate(e.date);
        });

        // $("#usuario_search").selectpicker({
        // 	liveSearch: true
        // });
});	


function buscar_filtro(valor){	
    if(valor==1){			
        var usuarioS = $("#usuario_search").val();
        var usuarioName = $( "#usuario_search option:selected" ).text();
        if(!validation_form_v2("usuario_search")) return;			
        if(!validation_form_v2("calendar_from_tb1")) return;		
        if(!validation_form_v2("calendar_to_tb1")) return;				
        $("#area_filtro_cuerpo_tb1").hide();				
        $("#area_filtro_cuerpo_tb1").before(loading_layer);				
        $("#xls_area_tb1").html("");
        $.post(pathSite+apiPath,{												
            desdeUsuario: $("#calendar_from_tb1").val(),
            hastaUsuario: $("#calendar_to_tb1").val(),
            fn: 'usuario',
            btn: 'buscarUsuario',
            idU: usuarioS,
            nameUser: usuarioName					
            },
        function(data){					
            $("#loading_div").remove();					
            $("#area_filtro_cuerpo_tb1").show();					
            $("#xls_area_tb1").html(data);					
            }
        );	
    }	
    if(valor==2){
        if(!validation_form_v2("calendar_from_tb2")) return;		
        if(!validation_form_v2("calendar_to_tb2")) return;				
        $("#area_filtro_cuerpo_tb2").hide();				
        $("#area_filtro_cuerpo_tb2").before(loading_layer);				
        $("#xls_area_tb2").html("");	
        $.post(pathSite+apiPath,{												
            desde: $("#calendar_from_tb2").val(),
            hasta: $("#calendar_to_tb2").val(),
            fn: 'consolidado',
            btn: 'buscarConsolidado'						
            },
        function(data){					
            $("#loading_div").remove();					
            $("#area_filtro_cuerpo_tb2").show();					
            $("#xls_area_tb2").html(data);					
            }
        );	
    }
    if(valor==3){
        if(!validation_form_v2("calendar_from_tb3")) return;		
        if(!validation_form_v2("calendar_to_tb3")) return;			
        $("#area_filtro_cuerpo_tb3").hide();			
        $("#area_filtro_cuerpo_tb3").before(loading_layer);				
        $("#xls_area_tb3").html("");
        $.post(pathSite+apiPath,{												
            desdeCliente: $("#calendar_from_tb3").val(),
            hastaCliente: $("#calendar_to_tb3").val(),
            fn: 'clienteComprobante',
            btn: 'buscarClienteComprobante'						
            },
        function(data){					
            $("#loading_div").remove();					
            $("#area_filtro_cuerpo_tb3").show();					
            $("#xls_area_tb3").html(data);					
            }
        );
    }
}

$("#xls_1").click(function() {
    $("#xlsform1").submit();
});

$("#xls_2").click(function() {
    $("#xlsform2").submit();
});

$("#xls_3").click(function() {
    $("#xlsform3").submit();
});

    function buscarFiltroOLongJhonson(valor){
        $("#loading_div").remove();

            switch(valor){
                
                case 1:
                
                    if(!validation_form_v2("usuario_search")) return;
                
                    if(!validation_form_v2("calendar_from_tb1")) return;
            
                    if(!validation_form_v2("calendar_to_tb1")) return;
                    
                    $("#area_filtro_cuerpo_tb1").hide();
                    
                    $("#area_filtro_cuerpo_tb1").before(loading_layer);
                    
                    $("#xls_area_tb1").html("");
                
                    $.post(pathSite + "api/ajax/show_form.php",{
                    
                        archivo: cryp("usuarios_control","en")
                            
                        ,clase: cryp("usuarios_control","en")
                            
                        ,metodo: cryp("informe","en")
                            
                        ,accion: cryp(String(""),"en")
                        
                        ,ind: cryp(String(""),"en")
                        
                        ,id_user: cryp(String($("#usuario_search").val()),"en")
                        
                        ,id_name: cryp(String($("#usuario_search").val()),"en")
                        
                        ,fechas: cryp($("#calendar_from_tb1").val() + "#" + $("#calendar_to_tb1").val(),"en")
                            
                    },function(data){
                        
                        $("#loading_div").remove();
                        
                        $("#area_filtro_cuerpo_tb1").show();
                        
                        $("#xls_area_tb1").html(data.html);
                        
                    }
                    , "json");
                    
                break;
                
                case 2:
                
                    if(!validation_form_v2("calendar_from_tb2")) return;
            
                    if(!validation_form_v2("calendar_to_tb2")) return;
                    
                    $("#area_filtro_cuerpo_tb2").hide();
                    
                    $("#area_filtro_cuerpo_tb2").before(loading_layer);
                    
                    $("#xls_area_tb2").html("");
            
                    $.post(pathSite + "api/ajax/show_form.php",{
                    
                        archivo: cryp("usuarios_control","en")
                            
                        ,clase: cryp("usuarios_control","en")
                            
                        ,metodo: cryp("informe_con","en")
                            
                        ,accion: cryp(String(""),"en")
                        
                        ,ind: cryp(String(""),"en")
                        
                        ,fechas: cryp($("#calendar_from_tb2").val() + "#" + $("#calendar_to_tb2").val(),"en")
                            
                    },function(data){
                        
                        $("#loading_div").remove();
                        
                        $("#area_filtro_cuerpo_tb2").show();
                        
                        $("#xls_area_tb2").html(data.html);
                        
                    }
                    , "json");
                
                break;
                
                case 3:
                
                    if(!validation_form_v2("calendar_from_tb3")) return;
            
                    if(!validation_form_v2("calendar_to_tb3")) return;
                    
                    $("#area_filtro_cuerpo_tb3").hide();
                    
                    $("#area_filtro_cuerpo_tb3").before(loading_layer);
                    
                    $("#xls_area_tb3").html("");
                
                    $.post(pathSite + "api/ajax/show_form.php",{
                    
                        archivo: cryp("usuarios_control","en")
                            
                        ,clase: cryp("usuarios_control","en")
                            
                        ,metodo: cryp("informe_cliente_com","en")
                            
                        ,accion: cryp(String(""),"en")
                        
                        ,ind: cryp(String(""),"en")
                        
                        ,fechas: cryp($("#calendar_from_tb3").val() + "#" + $("#calendar_to_tb3").val(),"en")
                            
                    },function(data){
                        
                        $("#loading_div").remove();
                        
                        $("#area_filtro_cuerpo_tb3").show();
                        
                        $("#xls_area_tb3").html(data.html);
                        
                    }
                    , "json");
                
                break;
                
            }
        
            return;
            
    }
    // $("#loading_div").remove();
    // switch(valor){			
    // 	case 1:
        
            // if(!validation_form_v2("usuario_search")) return;			
            // if(!validation_form_v2("calendar_from_tb1")) return;		
            // if(!validation_form_v2("calendar_to_tb1")) return;				
            // $("#area_filtro_cuerpo_tb1").hide();				
            // $("#area_filtro_cuerpo_tb1").before(loading_layer);				
            // $("#xls_area_tb1").html("");			
    // 		$.post(pathSite + "api/ajax/show_form.php",{				
    // 			archivo: cryp("usuarios_control","en")					
    // 			,clase: cryp("usuarios_control","en")					
    // 			,metodo: cryp("informe","en")						
    // 			,accion: cryp(String(""),"en")					
    // 			,ind: cryp(String(""),"en")					
    // 			,id_user: cryp(String($("#usuario_id").val()),"en")					
    // 			,id_name: cryp(String($("#usuario_search").val()),"en")					
    // 			,fechas: cryp($("#calendar_from_tb1").val() + "#" + $("#calendar_to_tb1").val(),"en")						
    // 		},function(data){					
    // 			$("#loading_div").remove();					
    // 			$("#area_filtro_cuerpo_tb1").show();					
    // 			$("#xls_area_tb1").html(data.html);					
    // 		}
    // 		, "json");
            
    // 	break;
        
    // 	case 2:
        
            // if(!validation_form_v2("calendar_from_tb2")) return;		
            // if(!validation_form_v2("calendar_to_tb2")) return;				
            // $("#area_filtro_cuerpo_tb2").hide();				
            // $("#area_filtro_cuerpo_tb2").before(loading_layer);				
            // $("#xls_area_tb2").html("");
    
    // 		$.post(pathSite+apiPath,{												
    // 			fechas: $("#calendar_from_tb2").val() + "#" + $("#calendar_to_tb2").val(),
    // 			fn: 'consolidado',
    // 			combo: 'buscarConsolidado'						
    // 		},function(data){					
                // $("#loading_div").remove();					
                // $("#area_filtro_cuerpo_tb2").show();					
                // $("#xls_area_tb2").html(data.html);					
    // 		}
    // 		, "json");			
    // 	break;
        
    // 	case 3:

            // if(!validation_form_v2("calendar_from_tb3")) return;		
            // if(!validation_form_v2("calendar_to_tb3")) return;			
            // $("#area_filtro_cuerpo_tb3").hide();			
            // $("#area_filtro_cuerpo_tb3").before(loading_layer);				
            // $("#xls_area_tb3").html("");			
    // 		$.post(pathSite + "api/ajax/show_form.php",{				
    // 			archivo: cryp("usuarios_control","en")						
    // 			,clase: cryp("usuarios_control","en")						
    // 			,metodo: cryp("informe_cliente_com","en")						
    // 			,accion: cryp(String(""),"en")					
    // 			,ind: cryp(String(""),"en")					
    // 			,fechas: cryp($("#calendar_from_tb3").val() + "#" + $("#calendar_to_tb3").val(),"en")						
    // 		},function(data){					
    // 			$("#loading_div").remove();					
    // 			$("#area_filtro_cuerpo_tb3").show();					
    // 			$("#xls_area_tb3").html(data.html);					
    // 		}
    // 		, "json");			
    // 	break;			
    // }	
    // return;

    // $(document).ready(function() {
        
    // 	//Datetime Pickers
        
            // var d = new Date();
            // var month = d.getMonth();
            // var day = d.getDate();
            // var year = d.getFullYear();
            
            // 	$('#calendar_from_tb1').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 00, 00)
            // 	});

            // 	$('#calendar_to_tb1').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 23, 59),
            // 		useCurrent: false
            // 	});
                
            // 	$("#calendar_from_tb1").on("dp.change", function (e) {
            // 		$('#calendar_to_tb1').data("DateTimePicker").minDate(e.date);
            // 	});
                
            // 	$("#calendar_to_tb1").on("dp.change", function (e) {
            // 		$('#calendar_from_tb1').data("DateTimePicker").maxDate(e.date);
            // 	});
            
            // /**/
            
            // 	$('#calendar_from_tb2').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 00, 00)
            // 	});

            // 	$('#calendar_to_tb2').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 23, 59),
            // 		useCurrent: false
            // 	});
                
            // 	$("#calendar_from_tb2").on("dp.change", function (e) {
            // 		$('#calendar_to_tb2').data("DateTimePicker").minDate(e.date);
            // 	});
                
            // 	$("#calendar_to_tb2").on("dp.change", function (e) {
            // 		$('#calendar_from_tb2').data("DateTimePicker").maxDate(e.date);
            // 	});
            
            // /**/
            
            // 	$('#calendar_from_tb3').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 00, 00)
            // 	});

            // 	$('#calendar_to_tb3').datetimepicker({
            // 		locale: 'es',
            // 		format: 'DD-MM-YYYY',
            // 		//viewMode: 'years',
            // 		defaultDate: new Date(year, month, day, 23, 59),
            // 		useCurrent: false
            // 	});
                
            // 	$("#calendar_from_tb3").on("dp.change", function (e) {
            // 		$('#calendar_to_tb3').data("DateTimePicker").minDate(e.date);
            // 	});
                
            // 	$("#calendar_to_tb3").on("dp.change", function (e) {
            // 		$('#calendar_from_tb3').data("DateTimePicker").maxDate(e.date);
            // 	});
                
    // 	/**/
    // 		var options = {
    // 			script: pathSite + 'api/ajax/files.autocomplete.usuario_control.php?autocomplete_json=true&',
    // 			varname:'input',
    // 			json:true,
    // 			shownoresults:false,
    // 			maxresults:6,
    // 			callback: function (obj) { 
                
    // 				$("#usuario_id").val(obj.id); 

    // 			}
    // 		};
            
    // 		var as_json = new bsn.AutoSuggest('usuario_search', options);	
        
    // });
    
    
    // function buscar_filtro(valor){
        
    // 	$("#loading_div").remove();

    // 	switch(valor){
            
    // 		case 1:
            
    // 			if(!validation_form_v2("usuario_search")) return;
            
    // 			if(!validation_form_v2("calendar_from_tb1")) return;
        
    // 			if(!validation_form_v2("calendar_to_tb1")) return;
                
    // 			$("#area_filtro_cuerpo_tb1").hide();
                
    // 			$("#area_filtro_cuerpo_tb1").before(loading_layer);
                
    // 			$("#xls_area_tb1").html("");
            
    // 			$.post(pathSite + "api/ajax/show_form.php",{
                
    // 				archivo: cryp("usuarios_control","en")
                        
    // 				,clase: cryp("usuarios_control","en")
                        
    // 				,metodo: cryp("informe","en")
                        
    // 				,accion: cryp(String(""),"en")
                    
    // 				,ind: cryp(String(""),"en")
                    
    // 				,id_user: cryp(String($("#usuario_id").val()),"en")
                    
    // 				,id_name: cryp(String($("#usuario_search").val()),"en")
                    
    // 				,fechas: cryp($("#calendar_from_tb1").val() + "#" + $("#calendar_to_tb1").val(),"en")
                        
    // 			},function(data){
                    
    // 				$("#loading_div").remove();
                    
    // 				$("#area_filtro_cuerpo_tb1").show();
                    
    // 				$("#xls_area_tb1").html(data.html);
                    
    // 			}
    // 			, "json");
                
    // 		break;
            
    // 		case 2:
            
    // 			if(!validation_form_v2("calendar_from_tb2")) return;
        
    // 			if(!validation_form_v2("calendar_to_tb2")) return;
                
    // 			$("#area_filtro_cuerpo_tb2").hide();
                
    // 			$("#area_filtro_cuerpo_tb2").before(loading_layer);
                
    // 			$("#xls_area_tb2").html("");
        
    // 			$.post(pathSite + "api/ajax/show_form.php",{
                
    // 				archivo: cryp("usuarios_control","en")
                        
    // 				,clase: cryp("usuarios_control","en")
                        
    // 				,metodo: cryp("informe_con","en")
                        
    // 				,accion: cryp(String(""),"en")
                    
    // 				,ind: cryp(String(""),"en")
                    
    // 				,fechas: cryp($("#calendar_from_tb2").val() + "#" + $("#calendar_to_tb2").val(),"en")
                        
    // 			},function(data){
                    
    // 				$("#loading_div").remove();
                    
    // 				$("#area_filtro_cuerpo_tb2").show();
                    
    // 				$("#xls_area_tb2").html(data.html);
                    
    // 			}
    // 			, "json");
            
    // 		break;
            
    // 		case 3:
            
    // 			if(!validation_form_v2("calendar_from_tb3")) return;
        
    // 			if(!validation_form_v2("calendar_to_tb3")) return;
                
    // 			$("#area_filtro_cuerpo_tb3").hide();
                
    // 			$("#area_filtro_cuerpo_tb3").before(loading_layer);
                
    // 			$("#xls_area_tb3").html("");
            
    // 			$.post(pathSite + "api/ajax/show_form.php",{
                
    // 				archivo: cryp("usuarios_control","en")
                        
    // 				,clase: cryp("usuarios_control","en")
                        
    // 				,metodo: cryp("informe_cliente_com","en")
                        
    // 				,accion: cryp(String(""),"en")
                    
    // 				,ind: cryp(String(""),"en")
                    
    // 				,fechas: cryp($("#calendar_from_tb3").val() + "#" + $("#calendar_to_tb3").val(),"en")
                        
    // 			},function(data){
                    
    // 				$("#loading_div").remove();
                    
    // 				$("#area_filtro_cuerpo_tb3").show();
                    
    // 				$("#xls_area_tb3").html(data.html);
                    
    // 			}
    // 			, "json");
            
    // 		break;
            
    // 	}
    
    // 	return;
        

    // }
    
    // function export_doc(valor){
    
    // 	switch(valor){
            
    // 		case 1:
            
    // 			$("#documento_xls_area").attr({"src" : pathSite + "api/export_xls/files.usuarios_control.php?swe=" + cryp(String(valor),"en")});
            
    // 		break;
            
    // 		case 2:
            
    // 			$("#documento_xls_area").attr({"src" : pathSite + "api/export_xls/files.usuarios_control.php?swe=" + cryp(String(valor),"en")});
            
    // 		break;
            
    // 		case 3:
            
    // 			$("#documento_xls_area").attr({"src" : pathSite + "api/export_xls/files.usuarios_control.php?swe=" + cryp(String(valor),"en")});
            
    // 		break;
            
    // 	}
    
        
    // }
    
    