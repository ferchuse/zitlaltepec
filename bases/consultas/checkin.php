<?php 
	// session_start();
	include('../../conexi.php');
	$link = Conectarse();
	
	$tarjeta = substr ($_GET["barcode"], 6,6);
	
	$respuesta["tarjeta"] = $tarjeta;
	
	
	$consulta_tarjeta = "		
	SELECT * FROM tarjetas_unidad 
	LEFT JOIN unidades ON tarjetas_unidad.unidad = unidades.cve
	WHERE tarjetas_unidad.cve = '{$tarjeta}'
	";
	
	$result_tarjeta = mysqli_query($link,$consulta_tarjeta);
	
	
	if($result_tarjeta){
		
		while($row = mysqli_fetch_assoc($result_tarjeta)){
			
			$tarjeta = $row;
		}
		
		$insert_registro = 
		"INSERT INTO  bases_registros					 
		SET 
		tarjeta = '{$tarjeta["cve"]}',		
		id_checadores= '{$_GET["id_checadores"]}',	
		id_base= '{$_GET["id_base"]}',	
		fecha_registro= NOW()
		";
		
		$result = mysqli_query($link,$insert_registro);
		if($result){
			
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "Guardado";
		}
		else {
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$insert_registro.mysqli_Error($link);
		}
	}
	
	
	
	echo json_encode($respuesta);
	
	
?>							