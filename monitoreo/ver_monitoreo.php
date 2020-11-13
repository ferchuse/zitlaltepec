<?php
	// include("../login/login_check.php");
	// $link_activo = "guias";
	
	
	require_once('../conexi.php');
	$link = Conectarse();
	
	
	$consulta_monitoreo = "SELECT *, 
	usuarios.nombre as usuarios_nombre,
	operadores.nombre as operadores_nombre
	FROM monitoreo
	LEFT JOIN tarjetas_unidad ON tarjetas_unidad.cve = monitoreo.tarjeta
	LEFT JOIN operadores ON tarjetas_unidad.operador = operadores.cve
	LEFT JOIN empresas ON empresas.cve = tarjetas_unidad.empresa
	LEFT JOIN unidades ON unidades.cve = tarjetas_unidad.unidad
	LEFT JOIN usuarios ON usuarios.cve = monitoreo.usuario
	WHERE id_monitoreo = '{$_GET["id_monitoreo"]}'
	";
	
	
	$result_monitoreo = mysqli_query($link,$consulta_monitoreo);
	
	if($result_monitoreo){
		
		while($fila = mysqli_fetch_assoc($result_monitoreo)){
			
			$lista_monitoreo = $fila ;
		}
	}
	else{
		die("error en $consulta_monitoreo". mysqli_error($link) );
		
	}
	
	
	
	$consulta_vueltas = "SELECT * FROM monitoreo_vueltas
	WHERE id_monitoreo = '{$_GET["id_monitoreo"]}'
	ORDER BY num_vuelta
	";
	
	$result_vueltas = mysqli_query($link,$consulta_vueltas);
	
	if($result_vueltas){
		
		
		while($fila_vueltas = mysqli_fetch_assoc($result_vueltas)){
			
			$lista_boletos = [];
			
			$consulta_boletos = "SELECT * FROM monitoreo_boletos
			WHERE 
			num_vuelta = '{$fila_vueltas["num_vuelta"]}'
			AND id_monitoreo = '{$_GET["id_monitoreo"]}'
			";
			
			$result_boletos = mysqli_query($link,$consulta_boletos);
			
			if($result_boletos){
				
				while($fila_boletos= mysqli_fetch_assoc($result_boletos)){
					
					$lista_boletos[] = $fila_boletos;
				}
				
				$fila_vueltas["boletos"] = $lista_boletos; 
				
				$lista_vueltas[] = $fila_vueltas ;
				
			}
			else{
				die("error en $consulta_boletos". mysqli_error($link) );
				
			}
			
		}
	}
	else{
		die("error en $consulta_vueltas". mysqli_error($link) );
		
	}
	
	
	// echo "<pre>";
	// print_r($lista_monitoreo);
	
	// echo "</pre>";
	
	// echo "<pre>";
	// print_r($lista_vueltas);
	
	// echo "</pre>";
	
	
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
			.tabla_vuelta input[readonly]{
			width : 60px;
			background-color: #e9ecef !important;
			}
			
			input[readonly]{
			
			background-color: #e9ecef !important;
			}
			.cant_origen, .cant_destino{
			width : 60px;
			
			}
			/* Chrome, Safari, Edge, Opera */
			input::-webkit-outer-spin-button,
			input::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
			}
			
			/* Firefox */
			input[type=number] {
			-moz-appearance: textfield;
			}
			
		</style>
		
	</head>
	
	<body>
		
		<?php include("../navbar.php")?>
		<div id="wrapper" class="d-print-none">
			<?php include_once("../menu.php");?>
			
			<div id="content-wrapper">	
				<div class="container-fluid">
					<form id="form_monitoreo">
						<div class="row">
							<div class="col-sm-12">
								
									<h4>Ver Monitoreo </h4>
								
								
									<a href="index.php" class="btn btn-success" >
										<i class="fas fa-arrow-left"></i> Regresar
									</a>
									
									<table >
										<tr >
											<td class="text-left">
												<label for="">Folio:</label>
											</td >
											<td >
												<input readonly type="number" name="id_monitoreo" id="id_monitoreo" value="<?php echo $lista_monitoreo["id_monitoreo"]?>" > 
											</td >
										</tr>
										<tr >
											<td class="text-left">
												<label for="">Fecha:</label>
											</td >
											<td >
												<input readonly type="text"  value="<?php echo $lista_monitoreo["fecha_monitoreo"]?>" > 
											</td >
										</tr>
										<tr >
											<td class="text-left">
												<label for="">Usuario:</label>
											</td >
											<td >
												<input readonly type="text" value="<?php echo $lista_monitoreo["usuarios_nombre"]?>" > 
											</td >
										</tr>
										<tr >
											<td class="text-left">
												<label for="">Tarjeta:</label>
											</td >
											<td >
												<input readonly type="number" name="tarjeta" id="tarjeta" value="<?php echo $lista_monitoreo["tarjeta"]?>" > 
											</td >
											
									</tr>
									<tr >
										<td >
											<label for="">Fecha de Viaje: </label>
										</td >
										<td >
											<input  readonly type="date" name="fecha_viaje" id="fecha_viaje" value="<?= $lista_monitoreo["fecha_viaje"]?>">
										</td >
									</tr>
									<tr >
										<td >
											<label for="">Unidad: </label>
										</td >
										<td >
											<input readonly type="number" name="unidad" id="unidad" value="<?= $lista_monitoreo["no_eco"]?>">
										</td >
									</tr>
									<tr >
										<td >
											<label for="">Operador: </label>
										</td >
										<td >
											<input readonly type="text" name="operador" id="operador" value="<?= $lista_monitoreo["operadores_nombre"]?>">
										</td >
									</tr>
									
									
									<tr >
										
										<td class="text-left">
											<label for="">Vueltas:</label>
										</td >
										<td >
											<input readonly type="number" name="vueltas" form="form_monitoreo" id="vueltas"  value="<?php echo $lista_monitoreo["vueltas"]?>"> 
										</td >
									</tr>
								</table>
								
								<hr>
								<div class="row vueltas" id="row_vueltas">
									
									<?php
										
										foreach($lista_vueltas as $vuelta){
										?>
										
										<div class="col-sm-4 ">
											<table class="table-bordered tabla_vuelta">
												<tr >
													<td colspan="4" class="text-center h4">
														VUELTA 	<span class="num_vuelta" ><?= $vuelta["num_vuelta"]?></span>
													</td >
													
												</tr>
												
												<tr >
													<td >
														TARIFA
													</td >
													<td >
														<select required class="origen" style="width: 90px">
															<option value=""><?= $vuelta["origen"]?></option>
															
														</select>
													</td >
													<td >
														<select required class="destino" style="width: 90px">
															<option value=""><?= $vuelta["destino"]?></option>
															
														</select>
													</td >
													<td >
														TOTAL
													</td >
												</tr>
												
												
												
												
												
												<?php
													$tarifas = [5,10,12,15,20,25,30,35,38,40,42,43,44,45,48,50,57];
													
													foreach($vuelta["boletos"] as $boleto){
													?>
													
													<tr >
														<td class="tarifa">
															<?= $boleto["tarifa"]?>
														</td >
														<td class="w-25" >
															<input readonly class="cant_origen" type="number"  value="<?= $boleto["cant_origen"]?>">
														</td >
														<td >
															<input readonly class="cant_destino" type="number" size="20" value="<?= $boleto["cant_destino"]?>">
														</td >
														<td >
															<input readonly class="total_tarifa"  type="number" tabindex="-1" value="<?= $boleto["total_tarifa"]?>">
														</td >
													</tr>
													
													<?php
													}
												?>
												
												
												
												
												<tfoot>
													<tr >
														<td >
															TOTALES:
														</td >
														<td >
															<input class="total_origen" readonly type="number" value="<?= $vuelta["total_origen"]?>">
														</td >
														<td >
															<input class="total_destino" readonly type="number" value="<?= $vuelta["total_destino"]?>">
														</td >
														<td >
															<input class="total_vuelta" readonly type="number"  value="<?= $vuelta["total_vuelta"]?>">
														</td >
													</tr>
												</tfoot>
											</table>
										</div>
										<?php
										}
									?>
									
									
									
								</div>
							</div>
						</div>
						
						<hr>
						
						<div class="row">
							<div class="col-sm-6">
								<table class="table-bordered">
									<tr >
										<td >
											Ingreso Bruto
										</td >
										<td >
											<input readonly type="number" id="ingreso_bruto" name="ingreso_bruto" value="<?= $lista_monitoreo["ingreso_bruto"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Casetas
										</td >
										<td >
											<input readonly required type="number" id="casetas" name="casetas"  value="<?= $lista_monitoreo["casetas"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Diesel
										</td >
										<td >
											<input  readonly type="number" id="diesel" name="diesel"  value="<?= $lista_monitoreo["diesel"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Despachadores
										</td >
										<td >
											<input readonly type="number" id="despachadores" name="despachadores"  value="<?= $lista_monitoreo["despachadores"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Comisión
										</td >
										<td >
											<input readonly type="number" id="comision" name="comision" step="any"  value="<?= $lista_monitoreo["comision"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Incentivo
										</td >
										<td >
											<input readonly type="number" id="incentivo" name="incentivo"  value="<?= $lista_monitoreo["incentivo"]?>">
										</td >
									</tr>
									<tr >
										<td >
											Mutualidad
										</td >
										<td >
											<input readonly type="number" id="mutualidad" name="mutualidad"  value="<?= $lista_monitoreo["mutualidad"]?>">
											
										</td >
									</tr>
									
									<tr >
										<td >
											Seguridad
										</td >
										<td >
											<input  readonly type="number" id="seguridad" name="seguridad"  value="<?= $lista_monitoreo["seguridad"]?>">
											
										</td >
									</tr>
									<tr >
										<td >
											Fianza
										</td >
										<td >
											<input readonly type="number" id="fianza" name="fianza"   value="<?= $lista_monitoreo["fianza"]?>">
										</td >
									</tr>
									<tr >
										<td >
											TAG
										</td >
										<td >
											<input   type="number" id="tag" name="tag" value="<?= $lista_monitoreo["tag"]?>">
											
										</td >
									</tr>
									
									<tr >
										<td >
											Utilidad
										</td >
										<td >
											<input readonly type="number" id="utilidad" name="utilidad" step="any" value="<?= $lista_monitoreo["utilidad"]?>">
										</td >
									</tr>
									
									<tr >
										<td >
											Observaciones
										</td >
										<td >
											<input type="text" id="observaciones"  name="observaciones" size="60" value="<?= $lista_monitoreo["observaciones"]?>">
										</td >
									</tr>
								</table>
								
							</div>
						</div>
						
					</form>
					
				</div>
			</div>
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		
	</body>
	
</html>
