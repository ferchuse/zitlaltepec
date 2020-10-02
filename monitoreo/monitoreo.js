$(document).ready(onLoad);

function onLoad(){
	
	
	$(".origen, .destino").keyup(sumarBoletos)
	$("input").keydown(cursorPress)
	$("input").focus(function(){
		$(this).select();
	});
}







function sumarFila(event){
	console.log("sumarBoletos");
	let table;
	let total_tarifa, total_vuelta, total_origen, total_destino = 0;
	
	
	
	$(".tarifa").each(function(index, elemnt){
		let tarifa = Number($(this).text());
			
		let fila = $(this).closest("tr");
		 table = fila.closest("table");
		let origen = Number(fila.find(".origen").val());
		let destino = Number(fila.find(".destino").val());
		
		console.log("tarifa", tarifa)
		console.log("origen", origen)
		console.log("destino", destino)
		
		let total_tarifa = (tarifa * origen) + (tarifa * destino);
		total_vuelta+= total_tarifa;
		total_origen+=   tarifa * origen;
		total_destino+=   tarifa * destino;
		
		fila.find(".total_tarifa").val(total_tarifa);
		
		
	})
	
	table.find(".total_origen").val(total_origen)
	table.find(".total_destino").val(total_destino)
	table.find(".total_vuelta").val(total_vuelta)
	
	
}

function sumarTotales


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


