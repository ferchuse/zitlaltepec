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
											<label for="">Usuario: </label>
										</td >
										<td >
											<?= generar_select($link, "usuarios", "cve" , "nombre")?>
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
			
			function listarRegistros(){
			console.log("listarRegistros()");
			
			let form = $("#form_filtro");
			let boton = form.find(":submit");
			let icono = boton.find('.fa');
			
			boton.prop('disabled',true);
			icono.toggleClass('fa-search fa-spinner fa-pulse ');
			
			return $.ajax({
			url: 'consultas/lista_recaudacion.php',
			data: $("#form_filtro").serialize()
			}).done(function(respuesta){
			
			$("#tabla_registros").html(respuesta)
			
			// $(".imprimir").click(function(){
			// imprimirTicket($(this).data("id_registro"))
			// });
			
			// $(".cancelar").click(confirmaCancelacion);
			
			// $("#check_all").change(checkAll);
			
			// $(".seleccionar").change(contarSeleccionados)
			
			}).always(function(){  
			
			boton.prop('disabled',false);
			icono.toggleClass('fa-search fa-spinner fa-pulse fa-fw');
			
		});
	}
	
	
</script>


</body>

</html>
