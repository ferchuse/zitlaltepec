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
		
		<title>Bases</title>
		
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
					
					<div class="row pb-4">
						
						<div class="col-sm-12">
							<h4>Bases </h4>
						</div>
						<div class="col-sm-12">
							<form id="form_filtro" >
								
								<table >
									<tr >
										
										<td >
											<button type="button" class="btn btn-success nuevo" >
												<i class="fas fa-file"></i> Nuevo
											</button>
										</td >
									</tr >
								</table>
								
								
							</form>
						</div>
						
					</div>
					
					
					<div class="row">
						<div class="col-sm-12 table-responsive" id="lista_registros">
							
						</div>
					</div>
				</div>
				
			</div>
		</div>
		
		
		
		<?php include_once("forms/form_bases.php");?>
		<?php include_once("../scripts.php");?>
		
		
		<script src="js/bases.js" ></script>
		
		
		</body>
		
	</html>
