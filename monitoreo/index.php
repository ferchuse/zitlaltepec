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
			.vueltas input[readonly]{
			width : 60px;
			background-color: #e9ecef !important;
			}
			
			input[readonly]{
			
			background-color: #e9ecef !important;
			}
			.origen, .destino{
			width : 60px;
			
			}
			
			
		</style>
		
	</head>
	
	<body>
		
		
		<?php //include_once("../menu.php");?>
		
		
		<div class="container-fluid">
			<?php //include_once("../main.php");?>
		
			<div class="row">
				<div class="col-sm-10">
					
					<table >
						<tr >
							<td class="text-left">
								<label for="">Tarjeta: :</label>
							</td >
							<td >
								<input type="number" name="tarjeta" id="tarjeta" value="" > 
							</td >
						</tr>
						<tfoot id="respuesta_tarjeta">
							
							
						</tfoot>
					</table>
					
					<hr>
					<div class="row vueltas">
						<?php 
							for($vuelta = 1; $vuelta <= 3; $vuelta++){?>
							<div class="col-sm-4">
								<table class="table-bordered">
									<tr >
										<td colspan="4" class="text-center h4">
											VUELTA 	<?php echo $vuelta;?>
										</td >
										
									</tr>
									
									
									
									<tr >
										<td >
											TARIFA
										</td >
										<td >
											<select style="width: 90px">
												<option>APAXCO</option>
												<option>NUEVOS PASEOS</option>
												<option>SAUCES</option>
												<option>GUARDIA</option>
												<option>SAN BARTOLO</option>
												<option>I.V.</option>
											</select>
										</td >
										<td >
											<select  style="width: 90px">
												<option>APAXCO</option>
												<option>NUEVOS PASEOS</option>
												<option>SAUCES</option>
												<option>GUARDIA</option>
												<option>SAN BARTOLO</option>
												<option>I.V.</option>
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
							<?PHP 
							}
							
						?>
					</div>
				</div>
			</div>
			
			<hr>
			<form id="form_monitoreo">
				<div class="row">
					<div class="col-sm-6">
						<table class="table-bordered">
							<tr >
								<td >
									Ingreso Bruto
								</td >
								<td >
									<input readonly type="number" id="ingreso_bruto" name="ingreso_bruto">
								</td >
							</tr>
							<tr >
								<td >
									Casetas
								</td >
								<td >
									<input required type="number" id="casetas" name="casetas">
								</td >
							</tr>
							<tr >
								<td >
									Diesel
								</td >
								<td >
									<input type="number" id="diesel" name="diesel">
								</td >
							</tr>
							<tr >
								<td >
									Despachadores
								</td >
								<td >
									<input type="number" id="despachadores" name="despachadores">
								</td >
							</tr>
							<tr >
								<td >
									Comisión
								</td >
								<td >
									<input readonly type="number" id="comision" name="comision">
								</td >
							</tr>
							<tr >
								<td >
									Incentivo
								</td >
								<td >
									<input type="number" id="incentivo" name="incentivo">
								</td >
							</tr>
							<tr >
								<td >
									Mutualidad
								</td >
								<td >
									<input readonly type="number" id="mutualidad" name="mutualidad" value="20">
									<button id="btn_mutualidad" class="btn btn-secondary btn-sm" type="button" >
										Cobrar
									</button >
								</td >
							</tr>
							
							<tr >
								<td >
									Seguridad
								</td >
								<td >
									<input  readonly type="number" id="seguridad" name="seguridad" value="20">
									<button id="btn_seguridad" class="btn btn-secondary btn-sm" type="button">
										Cobrar
									</button >
								</td >
							</tr>
							<tr >
								<td >
									Fianza
								</td >
								<td >
									<input type="number" id="fianza" name="fianza">
								</td >
							</tr>
							<tr >
								<td >
									Utilidad
								</td >
								<td >
									<input readonly type="number" id="utilidad" name="utilidad">
								</td >
							</tr>
							<tr >
								<td >
									Observaciones
								</td >
								<td >
									<input type="text" id="observaciones"  name="observaciones" size="60">
								</td >
							</tr>
							
							
							<tfoot>
								
								<tr >
									<td >
										
									</td >
									<td >
										<button type="submit" class="btn btn-success">
											<i class="fas fa-save"></i> Guardar
										</button >
									</td >
								</tr>
								
							</tfoot>
						</table>
						
					</div>
				</div>
			</form>
			
			
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		<script src="monitoreo.js?v=<?= date("Ymdis")?>"></script>
		
		
	</body>
	
	</html>
