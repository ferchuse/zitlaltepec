// var printService = new WebSocketPrinter();


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
	
	$("#modal_ponchar")
	
	$("#modal_ponchar").on('shown.bs.modal', function(){
		$("#boleto").focus();
		// alert('The modal is fully shown.');
	});
	
	
	$('#btn_ponchar').click(function(event){
		
		$("#modal_ponchar").modal("show");
	});
	
	$("#btn_seguridad, #btn_mutualidad, #btn_fianza, #btn_tag").click(cobrarCargo);
	
	
	$('#efectivo_pagado').on('keyup', calcularExcedente);
	$('#devolucion').on('keyup', calcularExcedente);
	$('#boletos_tijera').on('keyup', calcularEfectivo);
	
	
	$('#fecha_tarjetas').on('change', buscarFecha);
	
	$('#btn_generar_tarjeta').on('click', function(){
		
	$("#form_edicion")[0].reset();
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
	
	
	
	
	
	
	$('#form_abono').on('submit',guardarRecaudacion );
	
	
	$('#form_edicion').on('submit', function guardarTarjeta(event){
		event.preventDefault();
		
		
		
		
		let form = $("#form_edicion");
		let boton = $(this).find(":submit");
		let icono = boton.find('.fas');
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

function guardarRecaudacion(event){
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
	
	
	var form = $("#form_abono");
	var boton = form.find(":submit");
	var icono = boton.find('.fas');
	var datos = form.serialize();
	datos += "&"+ $("#form_boletos").serialize()
	
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
			
			
			imprimirAbono(respuesta.folio);
			
			
		}
		else{
			alertify.success('Guardado');
		}
		
		
		
		}).always(function(){
		boton.prop('disabled',false);
		icono.toggleClass('fa-save fa-spinner fa-pulse fa-fw');
	});
}

function imprimirAbono(folio){
	console.log("imprimirAbono()");
	
	
	
	// boton.prop("disabled", true); 
	// icono.toggleClass("fa-print fa-spinner fa-spin");
	
	$.ajax({
		url: "imprimir_abono.php",
		data:{
			folio : folio
		}
		}).done(function (respuesta){
		
		$.ajax({
			url: "http://localhost/impresiongenerallogo.php",
			method: "GET",
			data:{
				"textoimp" : atob(respuesta) 
			}
		});
		
		setTimeout(function(){
			
			window.location.href = "../inicio.php";
		}, 1000)
		
		printService.submit({
			'type': 'LABEL',
			'raw_content': respuesta
		});
		
		
		
		}).always(function(){
		
		// boton.prop("disabled", false);
		// icono.toggleClass("fa-print fa-spinner fa-spin");
		
	});
}





function cobrarCargo(){
	var boton = $(this);
	
	if($("#tarjeta").val() == ""){
		alert("No ha elegido tarjeta");
		$("#tarjeta").focus();
		return false;
	}
	if($("#recaudacion").val() == ""){
		alert("Elija recaudación");
		return false;
	}
	
	boton.prop("disabled", true)
	
	
	var id_cargo = $(this).data("id_cargo");
	var nombre_cargo = $(this).data("nombre_cargo");
	var monto = $(this).prev().val();
	
	
	
	return $.ajax({
		url: 'consultas/guardar_cargo.php',
		method: 'post',
		data: {
			"tarjeta": $("#tarjeta").val(),
			"monto": monto,
			"recaudacion": $("#recaudacion").val(),
			"cargo": id_cargo
		}
		}).done(function(respuesta){
		
		imprimirCargo(respuesta.folio, nombre_cargo);
		
		alertify.success("Guardado correctamente")
		}).always(function(){
		
		boton.hide();
		
		
	});
	
}




function buscarTarjeta(tarjeta){
	
	$("#tarjeta").addClass("cargando"); 
	return $.ajax({
		url: 'consultas/buscar_tarjeta.php',
		dataType: 'JSON',
		data: {
			"tarjeta": tarjeta
		}
		}).done(function(respuesta){
		
		if(respuesta.existe == "NO"){
			
			alert("Tarjeta no encontrada");
			return false;
		}
		
		if(respuesta.tarjeta.estatus_tarjetas == "C"){
			
			alert("Tarjeta Cancelada");
			return false;
		}	
		
		if(respuesta.tarjeta.estatus_tarjetas == "P"){
			
			alert("Tarjeta Recaudada");
			return false;
		}
		
		$("#form_abono")[0].reset();
		$("#tarjeta").val(tarjeta)
		
		$('#guias').html(respuesta.tabla_guias);
		$('#importe_con_guia').val(respuesta.importe_con_guia);
		$('#vale_dinero').val(respuesta.vale_dinero);
		$('#fecha_viaje').val(respuesta.tarjeta.fecha_viaje);
		$('#nombre_operador').val(respuesta.tarjeta.nombre_operador);
		$('#no_eco').val(respuesta.tarjeta.no_eco);
		$('#utilidad').val(respuesta.tarjeta.utilidad);
		$('#utilidad_monitoreo').val(respuesta.tarjeta.utilidad);
		// $('#mutualidad').val(respuesta.tarjeta.mutualidad);
		// $('#seguridad').val(respuesta.tarjeta.seguridad);
		$('#fianza').val(respuesta.tarjeta.fianza);
		$('#tag').val(respuesta.tarjeta.tag);
		
		calcularEfectivo();
		
		}).always(function(){
		
		$("#tarjeta").removeClass("cargando"); 
		
	});
	
}


function calcularEfectivo(){
	console.log("calcularEfectivo()");
	
	
	let utilidad = Number($("#utilidad").val());
	let vale_dinero = Number($("#vale_dinero").val());
	let importe_con_guia = Number($("#importe_con_guia").val());
	let importe_sin_guia = Number($("#importe_sin_guia").val());
	let boletos_tijera = Number($("#boletos_tijera").val());
	let fianza = Number($("#fianza").val());
	let mutualidad = Number($("#mutualidad").val());
	let seguridad = Number($("#seguridad").val());
	let tag = Number($("#tag").val());
	
	
	let efectivo_entregar = utilidad + fianza + mutualidad + seguridad + tag - vale_dinero - importe_con_guia - importe_sin_guia - boletos_tijera;
	
	// let efectivo_recaudado = utilidad + fianza + mutualidad + seguridad + tag - vale_dinero - importe_con_guia - importe_sin_guia - boletos_tijera;
	
	
	if(efectivo_entregar < 0 ){
		$("#devolucion").val(Math.abs(efectivo_entregar))
		$("#efectivo_entregar").val(0)
		
	}
	else{
		$("#devolucion").val(0)
		$("#efectivo_entregar").val(efectivo_entregar.toFixed(2));
	}
	
	$("#efectivo_recaudado").val(efectivo_entregar)
	
	
	
	
}

function calcularExcedente(){
	console.log("calcularExcedente()")
	
	let efectivo_entregar = Number($("#efectivo_entregar").val());
	let efectivo_pagado = Number($("#efectivo_pagado").val());
	let devolucion = Number($("#devolucion").val());
	let deuda_operador = efectivo_entregar - efectivo_pagado ;
	
	let utilidad_monitoreo =  Number($("#utilidad_monitoreo").val());
	
	
	// utilidad = utilidad_monitoreo - devolucion;
	
	// $("#utilidad").val(utilidad.toFixed(2) );
	
	$("#deuda_operador").val(deuda_operador.toFixed(2));
	
	
	// if(deuda_operador > 0){
	
	// $("#deuda_operador").val(deuda_operador.toFixed(2));
	// $("#devolucion").val(0);
	// $("#utilidad").val(utilidad_monitoreo);
	// }
	// else{
	
	// $("#deuda_operador").val(0);
	// $("#devolucion").val(Math.abs(deuda_operador.toFixed(2)));
	// $("#utilidad").val(utilidad.toFixed(2) );
	
	// }
	
	
	// $("#abono").val(abono.toFixed(2));
	
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


function imprimirCargo(folio, tabla){
	console.log("imprimirCargo()", folio , tabla)
	
	// 
	$.ajax({
		url: "imprimir_cargo.php",
		data:{
			"folio" : folio,
			"tabla" : tabla
		}
		}).done(function (respuesta){
		
		$.ajax({
			url: "http://localhost/impresiongenerallogo.php",
			method: "GET",
			data:{
				"textoimp" : atob(respuesta)
			}
		});
		
		// printService.submit({
		// 'type': 'LABEL',
		// 'raw_content': respuesta
		// });
		
		
		}).always(function(){
		
		
	});
}





