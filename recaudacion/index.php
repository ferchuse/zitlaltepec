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
		
		
		<div class="container-fluid">
			
			<div class="row">
				
				<div class="col-sm-12">
					<h4>Recaudación </h4>
				</div>
				<div class="col-sm-10">
					<form id="form_recaudacion" >
						
						<table >
							<tr >
								<td class="text-left">
									<label for="">Tarjeta: </label>
								</td >
								<td >
									<input type="number" name="tarjeta" id="tarjeta" value="">
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Fecha de Viaje: </label>
								</td >
								<td >
									<input type="date" name="fecha_viaje" id="fecha_viaje" readonly value="">
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Unidad: </label>
								</td >
								<td >
									<input type="text" name="no_eco" id="no_eco" readonly value="">
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Operador: </label>
								</td >
								<td >
									<input type="text" name="nombre_operador" id="nombre_operador" readonly value="">
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Utilidad: </label>
								</td >
								<td >
									<input type="number" name="utilidad" id="utilidad" readonly value="">
								</td >
							</tr>
							
							<tr >
								<td class="text-left">
									<label for="">Vale de Dinero: </label>
								</td >
								<td >
									<input type="number" name="vale_dinero" id="vale_dinero"  readonly value="">
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Importe Boletos con Guia: </label>
								</td >
								<td >
									<input type="number" name="importe_con_guia" id="importe_con_guia" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Importe Boletos sin Guia: </label>
								</td >
								<td >
									<input type="number" name="importe_sin_guia" id="importe_sin_guia" value="" readonly>
								</td >
								<td >
									<button type="button" id="btn_ponchar" value="" > Ponchar</button>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Cantidad de Boletos sin Guia: </label>
								</td >
								<td >
									<input type="number" name="cant_boletos" id="cant_boletos" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Mutualidad: </label>
								</td >
								<td >
									<input type="number" name="mutualidad" id="mutualidad" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Seguridad: </label>
								</td >
								<td >
									<input type="number" name="seguridad" id="seguridad" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Fianza: </label>
								</td >
								<td >
									<input type="number" name="fianza" id="fianza" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Efectivo a entregar: </label>
								</td >
								<td >
									<input type="number" name="efectivo_entregar" id="efectivo_entregar" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Efectivo pagado: </label>
								</td >
								<td >
									<input type="number" name="efectivo_pagado" id="efectivo_pagado" value="" >
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Abono Utilidad: </label>
								</td >
								<td >
									<input type="number" name="abono" id="abono" value="" readonly>
								</td >
							</tr>
							<tr >
								<td class="text-left">
									<label for="">Observaciones: </label>
								</td >
								<td >
									<input type="text" name="observaciones" id="observaciones" value="" >
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
						
						
					</form>
				</div>
				
			</div>
			
			
			
		</div>
		
		
		
		<?php include_once("modal_ponchar.php");?>
		<?php include_once("../scripts.php");?>
		
		<script src="../plugins/pos_print/websocket-printer.js" > </script>
		<script src="recaudacion.js?v=<?= date("Ymdi")?>"></script>
		
		
	</body>
	
</html>
