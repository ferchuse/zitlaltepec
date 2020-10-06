$(document).ready(onLoad);

function onLoad(){
	
	
	$("#diesel, #casetas, #despachadores, #incentivo, #fianza").keyup(calcularUtilidad)
	$(".origen, .destino").keyup(sumarBoletos)
	$(".origen, .destino").change(sumarBoletos)
	$("input").keydown(cursorPress)
	$("input").focus(function(){
		$(this).select();
	});	
	
	$("#form_monitoreo").focus(function(){
		$(this).select();
	});
	
	
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
	
}



function buscarTarjeta(tarjeta){
	
	$("#tarjeta").addClass("cargando"); 
	return $.ajax({
		url: 'consultas/buscar_tarjeta_json.php',
		data: {
			"tarjeta": tarjeta
		}
		}).done(function(respuesta){
		$("#respuesta_tarjeta").html(respuesta);
		
		
		}).always(function(){
		
		$("#tarjeta").removeClass("cargando"); 
		
	});
	
}



function calcularUtilidad(){
	console.log("calcularUtilidad()");
	let ingreso_bruto = Number($("#ingreso_bruto").val());
	
	comision = ingreso_bruto * .13;
	
	$("#comision").val(comision.toFixed(2));
	
	let casetas = Number($("#casetas").val());
	let diesel = Number($("#diesel").val());
	let despachadores = Number($("#despachadores").val());
	let incentivo = Number($("#incentivo").val());
	let mutualidad = Number($("#mutualidad").val());
	let seguridad = Number($("#seguridad").val());
	
	console.log("comision" , comision);
	console.log("casetas" , casetas);
	console.log("diesel" , diesel);
	console.log("despachadores" , despachadores);
	console.log("incentivo" , incentivo);
	console.log("mutualidad" , mutualidad);
	console.log("seguridad" , seguridad);
	
	
	let utilidad = ingreso_bruto - casetas - diesel - despachadores - comision - incentivo - mutualidad - seguridad;
	
	console.log("utilidad" , utilidad);
	
	$("#utilidad").val(utilidad.toFixed(2));
	
}





function sumarBoletos(event){
	console.log("sumarBoletos");
	var tabla = $(this).closest("table");
	var total_tarifa = 0, total_vuelta = 0, total_origen = 0, total_destino = 0;
	
	
	
	// let tarifa = Number($(this).text());
	
	let fila = $(this).closest("tr");
	
	let origen = Number(fila.find(".origen").val());
	let destino = Number(fila.find(".destino").val());
	let tarifa = Number(fila.find(".tarifa").text());
	
	console.log("tarifa", tarifa)
	console.log("origen", origen)
	console.log("destino", destino)
	console.log("total_tarifa", total_tarifa)
	
	total_tarifa = (tarifa * origen) + (tarifa * destino);
	total_vuelta+= total_tarifa;
	total_origen+=    origen;
	total_destino+=   destino;
	
	fila.find(".total_tarifa").val(total_tarifa);
	
	
	
	console.log("total_origen", total_origen)
	console.log("total_destino", total_destino)
	console.log("total_vuelta", total_vuelta)
	
	// console.log("total_origen", table.find(".total_origen"))
	sumarTotales(tabla);
	
}




function sumarTotales(tabla){
	console.log("sumarTotales()", tabla);
	
	var total_tarifa = 0, total_vuelta = 0, total_origen = 0, total_destino = 0 , ingreso_bruto = 0;
	
	
	tabla.find(".origen").each(function(index, elemnt){
		let fila = $(this).closest("tr");
		
		let origen = Number($(this).val());
		let destino = Number(fila.find(".destino").val());
		total_tarifa = Number(fila.find(".total_tarifa").val());
		
		
		// console.log("tarifa", tarifa)
		// console.log("origen", origen)
		// console.log("destino", destino)
		
		
		total_vuelta+= total_tarifa;
		total_origen+=    origen;
		total_destino+=   destino;
		
		
	})
	
	
	tabla.find(".total_origen").val(total_origen);
	tabla.find(".total_destino").val(total_destino)
	tabla.find(".total_vuelta").val(total_vuelta)
	
	
	$(".total_vuelta").each(function(index, elemnt){
		
		let total_vuelta = Number($(this).val());
		ingreso_bruto+= total_vuelta;
		console.log("ingreso_bruto", ingreso_bruto);
		console.log("total_vuelta", total_vuelta);
	});
	
	
	
	$("#ingreso_bruto").val(ingreso_bruto);
	
	// calculaComision();
	
	
	calcularUtilidad();
}

// function calculaComision(){

// let ingreso_bruto = Number($("#ingreso_bruto"));

// let comision = ingreso_bruto * .13;

// $("#comision").val(ingreso_bruto)
// }



function cursorPress(event){
	
	console.log(event.key);
	
	let column_index = $(this).closest("tr").find("input").index(this);
	
	console.log(column_index);
	
	if(event.key == "ArrowDown"){
		event.preventDefault();
		
		$(this).closest("tr").next().find("input").eq(column_index).focus();
	}
	
	
	if(event.key == "ArrowUp"){
		event.preventDefault();
		
		$(this).closest("tr").prev().find("input").eq(column_index).focus();
	}
	
	if(event.key == "ArrowLeft"){
		event.preventDefault();
		
		$(this).closest("tr").find("input").eq(column_index - 1).focus();
	}
	if(event.key == "ArrowRight"){
		event.preventDefault();
		
		$(this).closest("tr").find("input").eq(column_index + 1).focus();
	}
}


