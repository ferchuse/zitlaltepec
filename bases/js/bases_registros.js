// var printService = new WebSocketPrinter();


$(document).ready(onLoad)


function onLoad(){
	
	
	listarRegistros();
	
	$('#form_filtros').submit( function(event){
		event.preventDefault();
		
		listarRegistros();
	});
}





function listarRegistros() {
	console.log("listarRegistros");
	
	
	let boton = $("#form_filtros").find(":submit");
	let icono = boton.find('.fas');
	
	boton.prop('disabled',true);
	icono.toggleClass('fa-search fa-spinner fa-pulse ');
	
	$.ajax({
		url: 'consultas/lista_bases_registros.php',
		method: 'GET',
		data: $("#form_filtros").serialize()
		}).done(function(respuesta){
		
		$('#lista_registros').html(respuesta);
		
		
		
		}).always(function(){  
		
		boton.prop('disabled',false);
		icono.toggleClass('fa-search fa-spinner fa-pulse fa-fw');
		
	});;
}
