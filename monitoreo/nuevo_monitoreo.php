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
					<?php //include_once("../main.php");?>
					<form id="form_monitoreo">
						<div class="row">
							<div class="col-sm-12">
								
								
								<table >
									
									<?php if(isset($_GET["id_monitoreo"])){?>
										<tr >
											<td class="text-left">
												<label for="">Folio:</label>
											</td >
											<td >
												<input type="number" name="id_monitoreo" id="id_monitoreo" value="<?php echo $_GET["id_monitoreo"]?>" > 
											</td >
											
										</tr>
										
										<?php 
											
										}
									?>
									
									<tr >
										<td class="text-left">
											<label for="">Tarjeta:</label>
										</td >
										<td >
											<input type="number" name="tarjeta" id="tarjeta" value="" > 
										</td >
										
									</tr>
									
									<tfoot id="respuesta_tarjeta">
										
										
									</tfoot>
									<tr >
										
										<td class="text-left">
											<label for="">Vueltas:</label>
										</td >
										<td >
											<input type="number" name="vueltas" form="form_monitoreo" id="vueltas" value="2" min="1" max="3"> 
										</td >
									</tr>
								</table>
								
									<hr>
									<div class="row vueltas" id="row_vueltas">
									
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
											<input readonly type="number" id="comision" name="comision" step="any">
										</td >
									</tr>
									<tr >
										<td >
											Incentivo
										</td >
										<td >
											<input type="number" id="incentivo" name="incentivo" >
										</td >
									</tr>
									<tr >
										<td >
											Mutualidad
										</td >
										<td >
											<input readonly type="number" id="mutualidad" name="mutualidad" value="20">
											
										</td >
									</tr>
									
									<tr >
										<td >
											Seguridad
										</td >
										<td >
											<input  readonly type="number" id="seguridad" name="seguridad" value="20">
											
										</td >
									</tr>
									<tr >
										<td >
											Fianza
										</td >
										<td >
											<input  type="number" id="fianza" name="fianza" required value="">
										</td >
									</tr>
									<tr >
										<td >
											TAG
										</td >
										<td >
											<input   type="number" id="tag" name="tag" value="">
											
										</td >
									</tr>
									
									<tr >
										<td >
											Utilidad
										</td >
										<td >
											<input readonly type="number" id="utilidad" name="utilidad" step="any">
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
			</div>
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		<script src="monitoreo.js?v=<?= date("Ymdis")?>"></script>
		
		
	</body>
	
</html>
