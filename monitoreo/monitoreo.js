$(document).ready(onLoad);

function onLoad(){
	
	
	$("#form_monitoreo").submit(guardarMonitoreo);
	$("#tipo_unidad").change(calcularUtilidad);
	
	$("#diesel, #casetas, #despachadores, #incentivo, #fianza, #mutualidad, #fianza, #seguridad").keyup(calcularUtilidad)
	$("#row_vueltas").on("keyup", ".cant_origen, .cant_destino", sumarBoletos)
	$("#row_vueltas").on("change", ".cant_origen, .cant_destino", sumarBoletos)
	$("#row_vueltas").on("keydown", "input", cursorPress)
	
	// $("").keydown(cursorPress)
	$("input").focus(function(){
		$(this).select();
	});	
	
	$("#form_monitoreo").focus(function(){
		$(this).select();
	});
	
	$("#vueltas").change(renderVueltas)
	
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
	
	
	renderVueltas();
	
}




function renderVueltas(){
	console.log("renderVueltas()")
	str_vueltas="";
	
	
	
	for(num_vueltas = 1; num_vueltas <= Number($("#vueltas").val()); num_vueltas++ ){
		
		str_vueltas += `
		<div class="col-sm-4 ">
		<table class="table-bordered tabla_vuelta">
		<tr >
		<td colspan="4" class="text-center h4">
		VUELTA 	<span class="num_vuelta" >${num_vueltas}</span>
		</td >
		
		</tr>
		
		
		
		<tr >
		<td >
		TARIFA
		</td >
		<td >
		<select form="form_monitoreo" required class="origen" style="width: 90px">
		<option value="">Elige...</option>
		<option>APAXCO</option>
		<option>NUEVOS PASEOS</option>
		<option>SAUCES</option>
		<option>GUARDIA</option>
		<option>SAN BARTOLO</option>
		<option>PASEOS INTERIOR</option>
		<option>I.V.</option>
		</select>
		</td >
		<td >
		<select form="form_monitoreo" required class="destino" style="width: 90px">
		<option value="">Elige...</option>
		<option>APAXCO</option>
		<option>NUEVOS PASEOS</option>
		<option>SAUCES</option>
		<option>GUARDIA</option>
		<option>SAN BARTOLO</option>
		<option>PASEOS INTERIOR</option>
		<option>I.V.</option>
		</select>
		</td >
		<td >
		TOTAL
		</td >
		</tr>`;
		
		var tarifas = [5,10,12,15,20,25,30,35,38,40,42,43,44,45,48,50,57];
		
		
		
		tarifas.forEach(function(tarifa){
			
			str_vueltas += `
			<tr >
			<td class="tarifa">
			${tarifa}
			</td >
			<td class="w-25" >
			<input class="cant_origen" type="number"  value="">
			</td >
			<td >
			<input class="cant_destino" type="number" size="20">
			</td >
			<td >
			<input class="total_tarifa" readonly type="number" tabindex="-1">
			</td >
			</tr>`;
			
		});
		
		
		str_vueltas += `
		<tfoot>
		<tr >
		<td >
		TOTALES:
		</td >
		<td >
		<input class="total_origen" readonly type="number">
		</td >
		<td >
		<input class="total_destino" readonly type="number">
		</td >
		<td >
		<input class="total_vuelta" readonly type="number">
		</td >
		</tr>
		</tfoot>
		</table>
		</div>
		`;
		
	}
	
	
	$("#row_vueltas").html(str_vueltas);
}


function cargarMonitoreo(){
	
	
	
	
}



function guardarMonitoreo(event){
	event.preventDefault();
	
	var boton = $(this).find(":submit");
	var icono = boton.find(".fas");
	
	if($("#tarjeta").val() == ""){
		alert("Ingresa una tarjeta");
		
		return false;
		
		
	}
	
	
	boton.prop("disabled", true)
	icono.toggleClass("fa-save fa-spinner fa-spin");
	
	var monitoreo_vueltas =[];
	var monitoreo_boletos =[];
	
	
	$(".tabla_vuelta").each(function(i, tabla){
		console.log("for each tabla")
		
		var num_vuelta = $(this).find(".num_vuelta").text()
		
		monitoreo_vueltas.push({
			
			"num_vuelta": num_vuelta,
			"origen": $(this).find(".origen").val(),
			"destino": $(this).find(".destino").val(),
			"total_origen": $(this).find(".total_origen").val(),
			"total_destino": $(this).find(".total_destino").val(),
			"total_vuelta": $(this).find(".total_vuelta").val()
			
		})
		
		
		
		$(this).find(".tarifa").each(function(j, tarifa ){
			
			// console.log("for each tarifa")
			
			var fila = $(this).closest("tr");
			var tarifa = Number($(this).text());
			var cant_origen = Number(fila.find(".cant_origen").val());
			var cant_destino = Number(fila.find(".cant_destino").val());
			var total_tarifa = Number(fila.find(".total_tarifa").val());
			
			
			monitoreo_boletos.push({
				"num_vuelta": num_vuelta,
				"tarifa": tarifa,
				"cant_origen": cant_origen,
				"cant_destino": cant_destino,
				"total_tarifa": total_tarifa
				
			})
			// console.log("vueltas", vueltas);
		})
		
		
	})
	
	
	
	return $.ajax({
		url: 'consultas/guardar_monitoreo.php',
		dataType: 'JSON',
		method: 'POST',
		data: 
		{
			"tarjeta" :  $("#tarjeta").val(),
			"vueltas" :  $("#vueltas").val(),
			"ingreso_bruto" :  $("#ingreso_bruto").val(),
			"casetas" :  $("#casetas").val(),
			"diesel" :  $("#diesel").val(),
			"despachadores" :  $("#despachadores").val(),
			"comision" :  $("#comision").val(),
			"incentivo" :  $("#incentivo").val(),
			"mutualidad" :  $("#mutualidad").val(),
			"seguridad" :  $("#seguridad").val(),
			"fianza" :  $("#fianza").val(),
			"tag" :  $("#tag").val(),
			"utilidad" :  $("#utilidad").val(),
			"observaciones" :  $("#observaciones").val(),
			"monitoreo_vueltas": monitoreo_vueltas ,
			"monitoreo_boletos": monitoreo_boletos  
			
		}
		
		}).done(function(respuesta){
		
		alertify.success("Guardado Correctamente");
		window.location.href = "index.php";
		}).always(function(){
		boton.prop("disabled", false)
		icono.toggleClass("fa-save fa-spinner fa-spin");
		
		
		
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
	
	if($("#tipo_unidad").val() == "AUTOBUS" ){
		
		comision = 400;
	}
	else{
		
		
		comision = ingreso_bruto * .13;
	}
	
	$("#comision").val(comision.toFixed(0));
	
	let casetas = Number($("#casetas").val());
	let diesel = Number($("#diesel").val());
	let despachadores = Number($("#despachadores").val());
	let incentivo = Number($("#incentivo").val());
	let mutualidad = Number($("#mutualidad").val());
	let seguridad = Number($("#seguridad").val());
	let tag = Number($("#tag").val());
	
	
	
	let utilidad = ingreso_bruto -  casetas - diesel - despachadores - comision - incentivo - mutualidad - seguridad;
	
	console.log("utilidad" , utilidad);
	
	$("#utilidad").val(utilidad.toFixed(0));
	
}





function sumarBoletos(event){
	console.log("sumarBoletos");
	var tabla = $(this).closest("table");
	var total_tarifa = 0, total_vuelta = 0, total_origen = 0, total_destino = 0;
	
	
	
	// let tarifa = Number($(this).text());
	
	let fila = $(this).closest("tr");
	
	let origen = Number(fila.find(".cant_origen").val());
	let destino = Number(fila.find(".cant_destino").val());
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
	
	
	tabla.find(".cant_origen").each(function(index, elemnt){
		let fila = $(this).closest("tr");
		
		let origen = Number($(this).val());
		let destino = Number(fila.find(".cant_destino").val());
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


