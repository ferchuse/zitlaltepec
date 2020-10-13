var printService = new WebSocketPrinter();


$(document).ready(function(){
	
	$('#tarjeta').on('keyup',function(event){
		event.preventDefault();
		
		var tarjeta = $(this).val();
		if(tarjeta == ''){
			alertify.error("Ingrese una tarjeta");
			return false;
		}
		console.log("Buscar tarjeta", event.which )
		if(event.which == 13){
			
			buscarTarjeta(tarjeta);
		}
	});
	
	$('#fecha_tarjetas').on('change', buscarFecha);
	$('#imprimir_tarjeta').on('click', imprimirTicket);
	$('#imprimir_abonos').on('click', imprimirTicket);
	$('#btn_generar_tarjeta').on('click', function(){
		
		$("#form_edicion")[0].reset();
		// $("#modal_edicion").modal("show");
		$('#modal_edicion').modal({ backdrop: 'static'}).modal('show').on('shown.bs.modal', function () {
			$('#form_edicion input:eq(0)').trigger("focus");
		});
	});
	
	
	$('#efectivo').on('keyup', sumarImportes);
	
	$('form').on('keydown',function( event){
		
		if(event.which == 13){
			event.preventDefault();
			console.log("Enter, no enviar");
			return false;
		}
	});
	
	$('.nuevo').on('click',function(){
		$('#form_edicion')[0].reset();
		$('.modal-title').text('Nueva Tarjeta');
		$('#modal_edicion').modal('show');
	});
	
	
	
	// $('#num_eco').on('keyup',buscarUnidad );
	// $('#num_eco').on('blur',buscarUnidad );
	
	function buscarUnidad(event){
		event.preventDefault();
		
		var num_eco = $(this).val();
		if(num_eco == ''){
			alertify.error("Ingrese un Num Eco");
			return false;
		}
		
		
		console.log("Buscar code", event.keycode )
		console.log("Buscar code", event.code )
		console.log("Buscar which", event.which )
		console.log("Buscar location", event.location )
		if(event.which == 13 || event.which == 0){
			$("#num_eco").addClass("cargando");
			$.ajax({
				url: 'control/buscar_unidad.php',
				method: 'GET',
				dataType: 'JSON',
				data: {num_eco: num_eco}
				}).done(function(respuesta){
				console.log("buscarUnidad", respuesta) 
				if(respuesta.num_rows == 0){
					alertify.error("No encontrado")
				}
				else{
					$.each(respuesta.filas, function(name, value){
						$("#form_edicion #"+name).val(value);
						
						if(name == 'cuenta_derroteros'){
							console.log("cuenta", name)
							$("#saldo_tarjetas").val(value);
						}
					})
					
					
				}
				}).always(function(){
				
				$("#num_eco").removeClass("cargando");
			});
			
		};
	}
	
	
	
	// $('#form_abono').on('keypress',function(event){
	
	// if(event.which == 13){
	// event.preventDefault();
	// console.log("Enter form");
	// return false;
	// }
	// });
	
	//==========GUARDAR NUEVO ABONO============
	$('#form_abono').on('submit', function guardarAbono(event){
		event.preventDefault();
		console.log("saldo_tarjetas", $("#saldo_tarjetas").val())
		console.log("abono_unidad", $("#abono_unidad").val())
		
		if(Number($("#abono_unidad").val()) < Number($("#saldo_tarjetas").val())){
			alertify.error("El abono es menor al total de la tarjeta");
			return false;
		}
		if($("#imprimir_mutualidad").prop("hidden")){
			alertify.error("Falta Cobrar Mutualidad");
			return false;
		}
		
		
		let form = $("#form_abono");
		let boton = $("#boton_guardar_abono");
		let icono = boton.find('.fa');
		let datos = form.serialize();
		
		boton.prop('disabled',true);
		icono.toggleClass('fa-save fa-spinner fa-pulse ');
		
		$.ajax({  
			url: 'consultas/guardar_recaudacion.php',
			method: 'POST', 
			dataType: 'JSON',
			data: datos
			}).done(function(respuesta){ 
			if(respuesta.estatus == 'success'){
				alertify.success('Abono Generado correctamente');
				
				$("#id_abonos_unidades").val(respuesta.insert_id);
				$("#imprimir_abonos").prop("hidden", false);
				$("#imprimir_abonos").data("id_registro", respuesta.insert_id);
				$("#imprimir_abonos").data("url", "imprimir_abono_unidades.php");
				
				$("#imprimir_abonos").click();
				
				$("#respuesta_tarjeta").html("");
				$("#form_abono")[0].reset();
			}
			else{
				alertify.error('Ocurrio un error');
			}
			}).always(function(){
			boton.prop('disabled',false);
			icono.toggleClass('fa-save fa-spinner fa-pulse fa-fw');
		});
	})
	
	
	//==========GUARDAR NUEVA TARJETA============
	$('#form_edicion').on('submit', function guardarTarjeta(event){
		event.preventDefault();
		
		
		
		
		let form = $("#form_edicion");
		let boton = $(this).find(":submit");
		let icono = boton.find('.fa');
		let datos = form.serializeArray();
		let fecha_creacion = new Date().toString('yyyy-MM-dd HH:mm:ss')
		
		datos.push({
			name: "id_usuarios",
			value: $("#id_usuarios").val()
		});
		
		datos.push({
			name: "fecha_creacion",
			value: fecha_creacion
		});
		
		
		boton.prop('disabled',true);
		icono.toggleClass('fa-save fa-spinner fa-pulse ');
		
		$.ajax({
			url: '../../funciones/guardar.php',
			method: 'POST',
			dataType: 'JSON',
			data:{
				tabla: 'tarjetas',
				datos: datos
			}
			}).done(function(respuesta){
			if(respuesta.estatus == 'success'){
				
				alertify.success('Tarjeta Generada correctamente');
				$("#modal_edicion").modal("hide")
				$("#tarjeta").val(respuesta.insert_id);
				
				buscarTarjeta(respuesta.insert_id).done(function(){
					setTimeout(function(){
						$("#imprimir_tarjeta").click();
					}, 500);
					
				});
			}
			else{
				alertify.error('Ocurrio un error');
			}
			}).always(function(){
			boton.prop('disabled',false);
			icono.toggleClass('fa-save fa-spinner fa-pulse fa-fw');
		});
	})
	
	
});



function buscarTarjeta(tarjeta){
	
	$("#tarjeta").addClass("cargando"); 
	return $.ajax({
		url: 'consultas/buscar_tarjeta.php',
		dataType: 'JSON',
		data: {
			"tarjeta": tarjeta
		}
		}).done(function(respuesta){
		
		if(respuesta.tarjeta.estatus_tarjetas == "C"){
			
			alert("Tarjeta Cancelada");
			return false;
		}	
		
		if(respuesta.tarjeta.estatus_tarjetas == "P"){
			
			alert("Tarjeta Recaudada");
		return false;
		}
		
		$('#fecha_viaje').val(respuesta.tarjeta.fecha_viaje);
		$('#nombre_operador').val(respuesta.tarjeta.nombre_operador);
		$('#no_eco').val(respuesta.tarjeta.no_eco);
		$('#utilidad').val(respuesta.tarjeta.utilidad);
		$('#mutualidad').val(respuesta.tarjeta.mutualidad);
		$('#seguridad').val(respuesta.tarjeta.seguridad);
		
		
		}).always(function(){
		
		$("#tarjeta").removeClass("cargando"); 
		
	});
	
}


function guardarMutualidad(){
	if($("#id_recaudaciones").val() == ''){
		
		alertify.error("Seleccione Recaudacion"); 
		return false;
	}
	
	$boton = $(this);
	$boton.prop("disabled", true); 
	$("#loader_mutualidad").prop("hidden", false);
	
	var fecha_mutualidad = new Date().toString("yyyy-MM-dd hh:mm:ss");
	console.log("fecha_mutualidad", fecha_mutualidad);
	
	$.ajax({
		url: '../../funciones/fila_insert.php',
		method: 'POST',
		dataType: 'JSON',
		data: {
			tabla: 'mutualidad',
			valores: [
				{
					name: "fecha_mutualidad",
					value: fecha_mutualidad
				},	
				{
					name: "id_recaudaciones",
					value: $("#id_recaudaciones").val()
				},
				{
					name: "id_empresas",
					value: $("#id_empresas").val()
				},
				{
					name: "monto_mutualidad",
					value: $("#monto_mutualidad").val()
				},
				{
					name: "id_unidades",
					value: $("#form_abono #id_unidades").val()
				},
				{
					name: "id_usuarios",
					value: $("#id_usuarios").val()
				},
				{
					name: "id_conductores",
					value: $("#id_conductores").val()
				},
				{
					name: "id_administrador",
					value: $("#session_id_administrador").val()
				},
				{
					name: "tarjeta",
					value: $("#tarjeta").val()
				}
			]
		}
		}).done(function(respuesta){
		if(respuesta.estatus == 'success'){
			alertify.success("Guardado Correctamente");
		}
		
		// console.log(respuesta);
		$boton.prop("disabled", false);
		$boton.fadeOut(300); 
		$("#monto_mutualidad").prop("disabled", true); 
		$("#imprimir_mutualidad").prop("hidden", false);
		$("#imprimir_mutualidad").data("id_registro", respuesta.nuevo_id);
		
		
		$("#imprimir_mutualidad").data("url", "imprimir_mutualidad.php");
		
		
		$("#loader_mutualidad").prop("hidden", true);
		$("#imprimir_mutualidad").click();
		
		//actualiza tarjeta mutualidad cobrada = 1
		actualizaMutualidad();
		
		
	});
}

function actualizaMutualidad(){
	console.log("actualizaMutualidad")
	$.ajax({
		url: '../../funciones/fila_update.php',
		method: 'POST',
		dataType: 'JSON',
		data: {
			tabla: 'tarjetas',
			valores: [
				{
					name: "mutualidad_cobrada",
					value: 1
				}],
				id_campo : "tarjeta",
				id_valor: $("#tarjeta").val()
		}
		
		}).done(function(respuesta){
		
		
	})
}


function contarFolios(){
	console.log("contarFolios");
	var cantidad_tijera = 0;
	var folio_inicial = $(this).closest(".row").find(".folio_inicial").val();
	var folio_final = $(this).closest(".row").find(".folio_final").val();
	
	console.log("folio_inicial", folio_inicial);
	console.log("folio_final", folio_final);
	
	
	$(this).closest(".row").find(".cantidad").val(folio_final - folio_inicial);
	
	$(".cantidad").each(function(index, element){
		cantidad_tijera+= Number($(element).val());
	})
	
	$("#cantidad_tijera").val(cantidad_tijera);
}


function sumarImportes(){
	console.log("sumarImportes()");
	total_boletos = 0;
	total_recaudado = 0;
	var efectivo = Number($("#efectivo").val());
	var saldo_tarjetas = Number($("#saldo_tarjetas").val());
	
	total_boletos+= Number($("#bol_termicos_importe").val());
	total_boletos+= Number($("#importe_tijera").val())
	var total_recaudado = total_boletos + efectivo;
	
	
	$("#total_boletos").val(total_boletos); 
	$("#total_recaudado").val(total_recaudado);
	if(total_recaudado > saldo_tarjetas ){
		
		var abono_unidad = saldo_tarjetas ;
	}
	else{
		var abono_unidad = total_recaudado;
		
	}
	
	
	$("#abono_unidad").val(abono_unidad);
	
	var devolucion = (Number($("#total_recaudado").val()) - Number($("#abono_unidad").val())) ;
	
	if(devolucion > 0){
		$("#devolucion").val(devolucion);
	}
	else{
		$("#devolucion").val(0);
	}
	
}


function recaudaTarjeta(tarjeta){
	console.log("recaudaTarjeta()")
	
	return $.ajax({
		url: '../../funciones/fila_update.php',
		data: {
			tarjeta: tarjeta
		}
		}).done(function(respuesta){
		$("#respuesta_tarjeta").html(respuesta);
		$("#loader_tarjeta").prop("hidden", true); 
		$("#generar_mutualidad").click(guardarMutualidad); 
		$('#imprimir_mutualidad').on('click', imprimirTicket);
		$('#imprimir_tarjeta').data('id_registro', tarjeta);
	});
}

function buscarFecha(){
	
	$("#fecha_tarjetas").addClass("cargando"); 
	return $.ajax({
		url: 'control/buscar_fecha.php',
		dataType: 'JSON',
		data: {
			fecha_tarjetas: $("#fecha_tarjetas").val(),
			num_eco:  $("#num_eco").val()
		}
		}).done(function(respuesta){
		if(respuesta.existe == 1){
			
			$("#fecha_tarjetas")[0].setCustomValidity("Ya existe tarjeta en esta fecha");
			
		}
		else{
			
			
			$("#fecha_tarjetas")[0].setCustomValidity("");
			
		}
		
		
		$("#fecha_tarjetas").removeClass("cargando"); 
		
	});
	
}


function imprimirTicket(event){
	console.log("imprimirTicket()");
	var id_registro = $(this).data("id_registro");
	var url = $(this).data("url");
	var boton = $(this); 
	var icono = boton.find("fas");
	if(!id_registro){
		
		alertify.error("Ingrese una tarjeta");
		return false;
	}
	$("#ticket").html("");
	$("#ticket").height(0);
	
	boton.prop("disabled", true); 
	icono.toggleClass("fa-print fa-spinner fa-spin");
	
	$.ajax({
		url: "impresion/"+ url,
		data:{
			id_registro : id_registro
		}
		}).done(function (respuesta){
		
		$.ajax({
			url: "http://localhost/imprimir_zitlalli.php",
			method: "POST",
			data:{
				"texto" : respuesta
			}
		});
		
		printService.submit({
			'type': 'LABEL',
			'raw_content': respuesta
		});
		
		
		}).always(function(){
		
		boton.prop("disabled", false);
		icono.toggleClass("fa-print fa-spinner fa-spin");
		
	});
}





