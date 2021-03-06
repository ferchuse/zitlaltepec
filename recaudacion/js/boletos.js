
$(document).ready( onLoad);


//boletos

/*
	
	1038609010
	
	90100100151994
	
	
	90100100058967
	90100100058963
	90100100058964
	90100100058965
	90100100058966
	90100100058967
*/




function onLoad(){
	
	$("#boleto").keypress(function(event){
		
		if(event.keyCode==13){
			agregarBoletoSencillo();
		}
	})
	
	$("#tablaboletossencillos").on("click", ".btn_borrar", borrarBoleto);
	$("#form_boletos").on("submit", function(event){
		event.preventDefault();
	});
}



function poncharsencillos(){
	$("#dialogboletossencillos").dialog("open");
}

function borrarBoleto(){
	console.log("borrarBoleto()")
	boton = $(this)
	boton.prop("disabled", true)
	
	folio = $(this).data("folio")
	taquilla = $(this).data("taquilla")
	
	$.ajax({
		url: "consultas/reactivar_boleto.php",
		type: "GET",
		// async: false,
		dataType: "json",
		data: {
			"folio": folio,
			"taquilla": taquilla
		}
	})
	.done(function(data) {
		
		boton.closest("tr").remove();
		
		sumarBoletos();
		
		calcularEfectivo();
	});
	
	// sumarBoletos();
}


function agregarBoletoSencillo(){
	console.log("agregarBoletoSencillo")
	$.ajax({
		url: "consultas/buscar_boleto.php",
		type: "POST",
		// async: false,
		dataType: "json",
		data: {
			boleto: $("#boleto").val()
		}
	})
	.done(function(data) {
		if(data.error == 1){
			alert(data.mensaje);
		}
		else{
			
			$("#tablaboletossencillos").append(data.html);
		}
		$("#boleto").val("")
		
		sumarBoletos();
		
		calcularEfectivo();
	});
	
	
}


function sumarBoletos(){
	var total_boletos  = 0 ;
	var cant_boletos = 0 ;
	$("#tablaboletossencillos tbody tr").each(function(index, item){
		total_boletos += Number($(this).find(".monto").text());
		cant_boletos ++ ;
		
	});
	
	$("#importe_sin_guia").val(total_boletos);
	$("#cant_boletos").val(cant_boletos);
	
}

// function limpiarboletosencillo(){
// document.getElementById(\'capturadosencillo\').value = 0;
// document.getElementById(\'boletosencillo\').value = "";
// document.getElementById(\'boletosencillo\').focus();
// }


// function validarboletossencillo(){
// boletos = [];
// $(".aboletos").each(function(){
// campo = $(this);
// boleto = {};
// boleto.taquilla = campo.attr("taquilla");
// boleto.folio = campo.attr("folio");
// boleto.monto = campo.attr("monto");
// boletos.push(boleto);
// });
// document.forma.boletossencillos.value = JSON.stringify(boletos);
// regresar = true;
// $.ajax({
// url: "ponchado_sencillos.php",
// type: "POST",
// async: false,
// dataType: "json",
// data: {
// boletos: document.getElementById("boletossencillos").value,
// ajax: 4
// },
// success: function(data) {
// if(data.error == 1){
// alert(data.mensaje);
// regresar = false;
// $("#panel").hide();
// }
// }
// });
// return regresar;
// }

// function quitar_boletosencillo(aref){
// aref.parents("tr:first").remove();
// calcularboletosencillo();
// document.getElementById(\'boletosencillo\').focus();
// }

// function calcularboletosencillo(){
// cantidad=0;
// monto = 0;
// $(".aboletos").each(function(){
// monto += $(this).attr("monto")/1;
// cantidad++;
// });
// document.forma.cant_sencillos.value=cantidad.toFixed(0);
// document.forma.monto_sencillos.value=monto.toFixed(2);
// }


// function generaCargo(cvecargo){
// if((document.forma.tarjeta.value/1)>0 && document.forma.recaudacion.value != "0"){
// atcr("recaudacion_operador.php","_blank",12,cvecargo);
// }
// else{
// if((document.forma.tarjeta.value/1)<=0)
// alert("Necesita cargar primero la tarjeta");
// else
// alert("Necesita seleccionar la recaudacion");
// }
// }

// function calcular(){
// var total = 0;
// total = (document.forma.monto_boletos_tijera.value/1)+(document.forma.monto_boletos.value/1)+(document.forma.monto_boletos_abordo.value/1)+(document.forma.monto_vale_dinero.value/1)+(document.forma.monto_taqmovil.value/1)+(document.forma.monto_abonomovil.value/1)+(document.forma.diesel_manual.value/1)+(document.forma.monto_sencillos.value/1);
// document.forma.total_boletos.value=total.toFixed(2);
// total = (document.forma.diesel.value/1)+(document.forma.comision.value/1)+(document.forma.lavada.value/1)+(document.forma.vale_comida.value/1)+(document.forma.bono_productividad.value/1)+(document.forma.casetas.value/1)+(document.forma.despachadores.value/1)+(document.forma.excedente.value/1) + (document.forma.monto_vale_diesel.value/1);
// document.forma.total_gasto.value=total.toFixed(2);
// total = (document.forma.total_boletos.value/1)+(document.forma.monto_efectivo.value/1)-(document.forma.total_gasto.value/1);
// document.forma.monto.value=total.toFixed(2);
// }

// function traeViaje(){
// objeto1=crearObjeto();
// if (objeto1.readyState != 0) {
// alert("Error: El Navegador no soporta AJAX");
// } else {
// objeto1.open("POST","recaudacion_monitoreo.php",true);
// objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
// objeto1.send("ajax=2&plaza="+document.forma.plaza.value+"&tarjeta="+document.forma.folio.value);
// objeto1.onreadystatechange = function(){
// if (objeto1.readyState==4){
// var opciones2=objeto1.responseText.split("|");
// if(opciones2[0]=="0"){
// if(opciones2[1]=="0")
// alert("El folio no existe");
// else if(opciones2[1]=="1"){
// alert("El folio ya fue cobrado");
// }
// else if(opciones2[1]=="2"){
// alert("No se ha pagado la mutualidad de la tarjeta");
// }
// else if(opciones2[1]=="3"){
// alert("La tarjeta esta cancelada");
// }
// else if(opciones2[1]=="4"){
// alert("La tarjeta debe de ser del mismo dia");
// }
// $(".tarjetas").each(function(){
// this.value="";
// });
// $("#tdguias").html("");
// $("#tdvales").html("");
// }
// else{
// document.forma.tarjeta.value=opciones2[0];
// document.forma.fecha_viaje.value=opciones2[1];
// document.forma.empresa.value=opciones2[2];
// document.forma.nomempresa.value=opciones2[3];
// document.forma.unidad.value=opciones2[4];
// document.forma.no_eco.value=opciones2[5];
// document.forma.operador.value=opciones2[6];
// document.forma.nomoperador.value=opciones2[7];
// document.forma.derrotero.value=opciones2[8];
// document.forma.nomderrotero.value=opciones2[9];
// $("#tdguias").html(opciones2[13]);
// document.forma.cant_boletos.value=opciones2[14];
// document.forma.monto_boletos.value=opciones2[15];
// $("#tdvales").html(opciones2[16]);
// document.forma.monto_vale_dinero.value=opciones2[17];
// document.forma.totaldeuda.value=opciones2[18];
// document.forma.cant_taqmovil.value=opciones2[19];
// document.forma.monto_taqmovil.value=opciones2[20];
// document.forma.cant_abonomovil.value=opciones2[21];
// document.forma.monto_abonomovil.value=opciones2[22];
// document.forma.litros_vale_diesel.value=opciones2[23];
// document.forma.monto_vale_diesel.value=opciones2[24];
// }
// calcular();
// }
// }
// }
// }

