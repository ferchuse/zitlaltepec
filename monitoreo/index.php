<?php
	// include("../login/login_check.php");
	// $link_activo = "guias";
?>
<!DOCTYPE html>
<html lang="en">
	
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<title>Monitoreo</title>
		
		<?php include_once("../styles.php");?>
		
		
		
		<style>
			input[readonly]{
			width : 60px;
			background-color: #e9ecef !important;
			}
			.origen, .destino{
			width : 60px;
			
			}
		</style>
		
	</head>
	
	<body>
		
		
		<?php include_once("../menu.php");?>
		
		
		<div class="container-fluid">
			
			<div class="row">
				<div class="col-sm-10">
					<form id="form_recaudacion" >
						
						<table >
							<tr >
								<td class="text-left">
									<label for="">Tarjeta: :</label>
								</td >
								<td >
									<input type="number" name="tarjeta" id="tarjeta" value="">
								</td >
							</tr>
							<tr >
								<td >
									<label for="">Fecha de Viaje: :</label>
								</td >
								<td >
									<input readonly type="date" name="fecha_viaje" id="fecha_viaje" value="">
								</td >
							</tr>
							<tr >
								<td >
									<label for="">Unidad: :</label>
								</td >
								<td >
									<input readonly type="number" name="unidad" id="unidad" value="">
								</td >
							</tr>
							<tr >
								<td >
									<label for="">Operador: :</label>
								</td >
								<td >
									<input readonly type="text" name="operador" id="operador" value="">
								</td >
							</tr>
							
						</table>
						
						
						
						
						
						
						
					</form>
					
					<hr>
					
					<table class="table-bordered">
						<tr >
							<td colspan="4" class="text-center h4">
								VUELTA 1
							</td >
							
						</tr>
						
						
						
						<tr >
							<td >
								TARIFA
							</td >
							<td >
								<select>
									<option>APAXCO</option>
								</select>
							</td >
							<td >
								<select>
									<option>APAXCO</option>
								</select>
							</td >
							<td >
								TOTAL
							</td >
						</tr>
						
						
						
						<?php 
							
							$tarifas = [5,10,12,15,20,25,30,35,38,40,42,43,44,45,48,50,57];
							
							foreach($tarifas AS $tarifa){?>
							<tr >
								<td class="tarifa">
									<?= $tarifa ?>
								</td >
								<td class="w-25" >
									<input class="origen" type="number"  >
								</td >
								<td >
									<input class="destino" type="number" size="20">
								</td >
								<td >
									<input class="total_tarifa" readonly type="number" tabindex="-1">
								</td >
							</tr>
							<?PHP 
							}
							
						?>
						
						<tfoot>
							<tr >
								<td >
									TOTALES:
								</td >
								<td >
									<input class="total" readonly type="number">
								</td >
								<td >
									<input class="total" readonly type="number">
								</td >
								<td >
									<input class="total" readonly type="number">
								</td >
							</tr>
						</tfoot>
					</table>
				</div>
				
			</div>
			
			
			
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		<script src="monitoreo.js?v=<?= date("Ymdi")?>"></script>
		
		
	</body>
	
</html>
