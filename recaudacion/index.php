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
		
		
		<?php include_once("../menu.php");?>
		
		
		<div class="container-fluid">
			
			<div class="row">
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
							
						</table>
						
						<table id="respuesta_tarjeta">
							
							
						</table>
						
						
					</form>
				</div>
				
			</div>
			
			
			
		</div>
		
		
		
		<?php include_once("../scripts.php");?>
		
		<script src="../plugins/pos_print/websocket-printer.js" > </script>
		<script src="recaudacion.js?v=<?= date("Ymdi")?>"></script>
		
		
	</body>
	
</html>
