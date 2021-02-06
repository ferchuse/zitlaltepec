<?php
	// include("../login/login_check.php");
	// $link_activo = "guias";
	include("../funciones/generar_select.php");
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
		
		
		<?php 
			
			
			//include_once("../menu.php");
			// echo "<pre>";
			// print var_dump($_SESSION);
			// echo "<pre>";
		?>
		
		
		<?php include("../navbar.php")?>
		<div id="wrapper" class="d-print-none">
			<?php include_once("../menu.php");?>
			
			<div id="content-wrapper">	
				<div class="container-fluid">
					
					<div class="row">
						
						<div class="col-sm-12">
							<h4>Recaudación </h4>
						</div>
						<div class="col-sm-10">
							<form id="form_abono" >
								
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
											<input type="hidden" name="utilidad_monitoreo" id="utilidad_monitoreo" readonly value="">
											
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Recaudación: </label>
										</td >
										<td >
											<?= generar_select($link, "recaudaciones", "cve" , "nombre", false, false, true, 0, 0, "recaudacion", "recaudacion")?>
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
											<div id="guias">
											</div>
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
											<label for="">Importe de B. Tijera: </label>
										</td >
										<td >
											<input type="number" name="boletos_tijera" id="boletos_tijera" value="" >
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Mutualidad: </label>
										</td >
										<td >
											<input  type="number" name="mutualidad" id="mutualidad" value="20" readonly>
											<button
											data-id_cargo="1"
											data-nombre_cargo="Mutualidad" 
											data-monto="20" 
											id="btn_mutualidad" class="btn btn-secondary btn-sm" type="button" >
												Cobrar
											</button >
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Seguridad: </label>
										</td >
										<td >
											<input  type="number" name="seguridad" id="seguridad" value="20" readonly>
											<button 
											
											data-id_cargo="4"
											data-nombre_cargo="Seguridad" 
											data-monto="20" 
											
											id="btn_seguridad" class="btn btn-secondary btn-sm" type="button">
												Cobrar
											</button >
										</td >
									</tr>
									<tr >
										<td >
											TAG
										</td >
										<td >
											<input  readonly type="number" id="tag" name="tag" value="">
											<button
											data-id_cargo="6"
											data-nombre_cargo="TAG" 
											data-monto="" 
											
											
											id="btn_tag" class="btn btn-secondary btn-sm" type="button">
												Cobrar
											</button >
										</td >
									</tr>
									<tr >
										<td >
											Fianza
										</td >
										<td >
											<input  readonly type="number" id="fianza" name="fianza" value="50">
											<button
											data-id_cargo="5"
											data-nombre_cargo="Fianza" 
											data-monto="50" 
											
											
											id="btn_fianza" class="btn btn-secondary btn-sm" type="button">
												Cobrar
											</button >
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
									<tr hidden >
										<td class="text-left">
											<label for="">Abono Utilidad: </label>
										</td >
										<td >
											<input type="number" name="abono" id="abono" value="" readonly>
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Deuda Operador: </label>
										</td >
										<td >
											<input readonly type="number" name="deuda_operador" id="deuda_operador" value="" >
										</td >
									</tr>
									<tr >
										<td class="text-left">
											<label for="">Devolución Excedente: </label>
										</td >
										<td >
											<input readonly type="number" name="devolucion" id="devolucion" value="" >
										</td >
									</tr>
									<tr >
										<td colspan="3">
											<label>Observaciones:</label> <br>
											<textarea rows="5" cols="40" id="observaciones"  name="observaciones"></textarea >
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
			</div>
		</div>
		
		
		
		<?php include_once("modal_ponchar.php");?>
		<?php include_once("../scripts.php");?>
		
		<script src="../plugins/pos_print/websocket-printer.js" > </script>
		<script src="js/recaudacion.js?v=<?= date("Ymdis")?>"></script>
		<script src="js/boletos.js?v=<?= date("Ymdis")?>"></script>
		
		
	</body>
	
</html>
