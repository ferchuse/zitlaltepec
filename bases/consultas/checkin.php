<?php 
	// session_start();
	include('../../conexi.php');
	$link = Conectarse();
	
	$folio_tarjeta = substr ($_GET["barcode"], 6,6);
	
	$respuesta["folio_tarjeta"] = $folio_tarjeta;
	
	
	$consulta_tarjeta = "		
	SELECT 
	fecha_viaje,
	tarjetas_unidad.cva as folio_tarjeta,
	unidades.num_eco as num_eco,
	tarjetas_unidad.estatus as estatus_tarjeta
	
	FROM tarjetas_unidad 
	LEFT JOIN unidades ON tarjetas_unidad.unidad = unidades.cve
	WHERE tarjetas_unidad.cve = '{$folio_tarjeta}'
	";
	
	$result_tarjeta = mysqli_query($link,$consulta_tarjeta);
	
	
	
	
	if($result_tarjeta){
		
		while($row = mysqli_fetch_assoc($result_tarjeta)){
			
			$tarjeta = $row;
		}
		
		$respuesta["tarjeta"] = $tarjeta;
		
		$insert_registro = 
		"INSERT INTO  bases_registros					 
		SET 
		tarjeta = '{$tarjeta["folio_tarjeta"]}',		
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
	else {
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$consulta_tarjeta.mysqli_Error($link);
		}
	
	
	echo json_encode($respuesta);
	
	
?>							