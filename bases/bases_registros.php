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
		
		<title>Salidas del Dia</title>
		
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
							<h4>Salidas del Dia </h4>
						</div>
						<div class="col-sm-12">
							<form id="form_filtros" >
								
								<table >
									<tr >
										<td class="text-left">
											<button type="submit" class="btn btn-info" >
												<i class="fas fa-search"></i> Buscar
											</button>
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
											<label for="">Checador: </label>
										</td >
										<td >
											<?= generar_select($link, "checadores", "id_checadores" , "nombre", true)?>
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Base: </label>
										</td >
										<td >
											<?= generar_select($link, "bases", "id_base" , "base", true)?>
										</td >
									</tr>
									
								</table>
								
								
							</form>
						</div>
						
					</div>
					
					
					<div class="row">
						
						<div class="col-sm-12 table-responsive" id="tabla_registros">
							
						</div>
						
						
					</div>
				</div>
				
			</div>
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		
		<script src="js/bases_registros.js"></script>
	
</body>

</html>
