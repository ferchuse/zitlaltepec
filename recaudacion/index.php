<?php
	include("../funciones/generar_select.php");
	// include('../conexi.php');
	// $link = Conectarse();
?>
<!DOCTYPE html>
<html lang="en">
	
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<title>Recaudación</title>
		
		<?php include_once("../styles.php");?>
		
		<style>
			input[readonly]{
			
			background-color: #e9ecef
			}
			
			.cargando{
			background: url('../img/cargando.gif') no-repeat right center;
			}
			
		</style>
		
		
	</head>
	
	<body>
		
		
		<?php //include_once("../menu.php");?>
		
		
		<?php include("../navbar.php")?>
		<div id="wrapper" class="d-print-none">
			<?php include_once("../menu.php");?>
			
			<div id="content-wrapper">	
				<div class="container-fluid">
					
					<div class="row">
						
						<div class="col-sm-12">
							<h4>Recaudación </h4>
						</div>
						<div class="col-sm-12">
							<form id="form_filtro" >
								
								<table >
									<tr >
										<td class="text-left">
											<button type="submit" class="btn btn-info" >
												<i class="fas fa-search"></i> Buscar
											</button>
										</td >
										<td >
											<a href="nueva_recaudacion.php" class="btn btn-success" >
												<i class="fas fa-file"></i> Nuevo
											</a>
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Fecha Inicial: </label>
										</td >
										<td >
											<input type="date" name="fecha_inicial" id="fecha_inicial"  value="<?= date("Y-m-d")?>">
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Fecha Final: </label>
										</td >
										<td >
											<input type="date" name="fecha_final" id="fecha_final" value="<?= date("Y-m-d")?>">
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Unidad: </label>
										</td >
										<td >
											<input type="text" name="num_eco" id="num_eco" >
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Usuario: </label>
										</td >
										<td >
											<?= generar_select($link, "usuarios", "cve" , "nombre", true, false, false, 0, 0, "usuarios_cve","usuarios_cve")?>
										</td >
									</tr>
									
								</table>
								
								
							</form>
						</div>
						
					</div>
					
					
					<div class="row">
						
						<div class="col-sm-12" id="tabla_registros">
							
						</div>
						
						
					</div>
				</div>
				
			</div>
		</div>
		
		
		
		<?php include_once("modal_ponchar.php");?>
		<?php include_once("../scripts.php");?>
		
		<script src="../plugins/pos_print/websocket-printer.js" > </script>
		<script >
			
			$(document).ready(function(){
			
			listarRegistros();
			
			$('#cve').select2();
			
			$('#form_filtro').on('submit', function filtrar(event){
			event.preventDefault();
			
			listarRegistros();
			
			
			
			});
			});
			
			var printService = new WebSocketPrinter();
			
			function imprimirTicket(folio){
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
			"textoimp" : atob(respuesta) + atob(respuesta)
			}
			});
			
			
			
			printService.submit({
			'type': 'LABEL',
			'raw_content': respuesta
			});
			
			
			
			}).always(function(){
			
			// boton.prop("disabled", false);
			// icono.toggleClass("fa-print fa-spinner fa-spin");
			
			});
			}
			
			function listarRegistros(){
			console.log("listarRegistros()");
			
			let form = $("#form_filtro");
			let boton = form.find(":submit");
			let icono = boton.find('.fas');
			
			boton.prop('disabled',true);
			icono.toggleClass('fa-search fa-spinner fa-pulse ');
			
			return $.ajax({
			url: 'consultas/lista_recaudacion.php',
			data: $("#form_filtro").serialize()
			}).done(function(respuesta){
			
			$("#tabla_registros").html(respuesta)
			
			$(".imprimir").click(function(){
			imprimirTicket($(this).data("id_registro"))
			});
			
			$(".cancelar").click(confirmaCancelacion);
			
			// $("#check_all").change(checkAll);
			
			// $(".seleccionar").change(contarSeleccionados)
			
			}).always(function(){  
			
			boton.prop('disabled',false);
			icono.toggleClass('fa-search fa-spinner fa-pulse fa-fw');
			
			});
		}
		
		
		function confirmaCancelacion(event){
			console.log("confirmaCancelacion()");
			let boton = $(this);
			let icono = boton.find(".fas");
			var id_registro = $(this).data("id_registro");
			
			
			alertify.confirm()
			.setting({
				'reverseButtons': true,
				'labels' :{ok:"SI", cancel:'NO'},
				'title': "Cancelar" ,
				'onok':cancelarRegistro
			}).show();
			
			
			
			
			function cancelarRegistro(evt, motivo){
				if(motivo == ''){
					console.log("Escribe un motivo");
					alertify.error("Escribe un motivo");
					return false;
					
				}
				
				boton.prop("disabled", true);
				icono.toggleClass("fa-times fa-spinner fa-spin");
				
				
				return $.ajax({
					url: "control/cancelar_abono.php",
					dataType:"JSON",
					data:{
						id_registro : id_registro,
						nombre_usuarios : $("#sesion_nombre_usuarios").text(),
						motivo : motivo
					}
					}).done(function (respuesta){
					if(respuesta.result == "success"){
						alertify.success("Cancelado");
						listarRegistros();
					}
					else{
						alertify.error(respuesta.result);
						
					}
					
					}).always(function(){
					boton.prop("disabled", false);
					icono.toggleClass("fa-times fa-spinner fa-spin");
					
				});
			}
		}
		
		
		
	</script>
	
	
</body>

</html>
