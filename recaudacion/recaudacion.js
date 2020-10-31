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
	
	$('#btn_ponchar').click(function(event){
		
		$("#modal_ponchar").modal("show");
	});
	
	$("#btn_seguridad").click(cobrarCargo);
	$("#btn_mutualidad").click(cobrarCargo);
	$("#btn_fianza").click(cobrarCargo);
	
	// $('#boleto').on('keyup', buscarBoleto);
	$('#efectivo_pagado').on('keyup', calcularAbono);
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
	
	
	
	
	
	
	$('#form_abono').on('submit', function guardarRecaudacion(event){
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
				
				// $("#id_abonos_unidades").val(respuesta.insert_id);
				// $("#imprimir_abonos").prop("hidden", false);
				// $("#imprimir_abonos").data("id_registro", respuesta.insert_id);
				// $("#imprimir_abonos").data("url", "imprimir_abono_unidades.php");
				
				// $("#imprimir_abonos").click();
				
				// $("#respuesta_tarjeta").html("");
				// $("#form_abono")[0].reset();
				
				
			}
			else{
				alertify.success('Guardado');
			}
			window.location.href = "../inicio.php";
			}).always(function(){
			boton.prop('disabled',false);
			icono.toggleClass('fa-save fa-spinner fa-pulse fa-fw');
		});
	})
	
	
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
	var monto = $(this).data("monto");
	
	
	
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
// function cobrarSeguridad(){
// console.log("cobrarSeguridad()");
// var boton = $(this);
// boton.prop("disabled", true)

// if($("#tarjeta").val() == ""){
// alert("No ha elegido tarjeta");
// return false;
// }
// return $.ajax({
// url: 'consultas/guardar_cargo.php',
// method: 'post',
// data: {
// "tarjeta": $("#tarjeta").val(),
// "monto": $("#seguridad").val(),
// "cargo": 4
// }
// }).done(function(respuesta){

// imprimirCargo(respuesta.folio, "Seguridad");

// alertify.success("Seguridad Generada correctamente")
// }).always(function(){
// boton.hide();



// });

// }



// function cobrarFianza(){
// console.log("cobrarFianza()");
// var boton = $(this);


// if($("#tarjeta").val() == ""){
// alert("No ha elegido tarjeta");
// return false;
// }

// if($("#fianza").val() > 0){

// boton.prop("disabled", true)
// $.ajax({
// url: 'consultas/guardar_cargo.php',
// method: 'post',
// data: {
// "tarjeta": $("#tarjeta").val(),
// "recaudacion": $("#recaudacion").val(),
// "monto": $("#fianza").val(),
// "cargo": 6
// }
// }).done(function(respuesta){
// boton.hide();
// imprimirCargo(respuesta.folio, "Fianza");
// alertify.success("Fianza Generada correctamente")
// }).always(function(){

// });
// }
// else{
// alertify.error("La fianza debe ser mayor a 0")
// }
// }



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
		
		$('#fecha_viaje').val(respuesta.tarjeta.fecha_viaje);
		$('#nombre_operador').val(respuesta.tarjeta.nombre_operador);
		$('#no_eco').val(respuesta.tarjeta.no_eco);
		$('#utilidad').val(respuesta.tarjeta.utilidad);
		$('#mutualidad').val(respuesta.tarjeta.mutualidad);
		$('#seguridad').val(respuesta.tarjeta.seguridad);
		$('#fianza').val(respuesta.tarjeta.fianza);
		
		calcularEfectivo();
		
		}).always(function(){
		
		$("#tarjeta").removeClass("cargando"); 
		
	});
	
}


function calcularEfectivo(){
	// console.log("calcularUtilidad()");
	// let ingreso_bruto = Number($("#ingreso_bruto").val());
	
	// comision = ingreso_bruto * .13;
	
	// $("#comision").val(comision.toFixed(2));
	
	let utilidad = Number($("#utilidad").val());
	let vale_dinero = Number($("#vale_dinero").val());
	let importe_con_guia = Number($("#importe_con_guia").val());
	let importe_sin_guia = Number($("#importe_sin_guia").val());
	let fianza = Number($("#fianza").val());
	let mutualidad = Number($("#mutualidad").val());
	let seguridad = Number($("#seguridad").val());
	
	console.log("vale_dinero" , vale_dinero);
	console.log("importe_con_guia" , importe_con_guia);
	console.log("importe_sin_guia" , vale_dinero);
	console.log("fianza" , fianza);
	console.log("mutualidad" , mutualidad);
	console.log("seguridad" , seguridad);
	
	
	let efectivo_entregar = utilidad - vale_dinero - importe_con_guia - importe_sin_guia + fianza + mutualidad + seguridad;
	
	// console.log("utilidad" , utilidad);
	
	$("#efectivo_entregar").val(efectivo_entregar.toFixed(2));
	
}
function calcularAbono(){
	console.log("calcularAbono()")
	
	let efectivo_entregar = Number($("#efectivo_entregar").val());
	let efectivo_pagado = Number($("#efectivo_pagado").val());
	
	
	
	
	let abono = efectivo_entregar - efectivo_pagado ;
	
	
	$("#abono").val(abono.toFixed(2));
	
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





