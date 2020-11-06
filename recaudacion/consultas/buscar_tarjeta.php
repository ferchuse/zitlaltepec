<?php 
	
	include('../../conexi.php');
	
	$link = Conectarse();
	$filas = array();
	
	
	
	$consulta = "		
	SELECT
	tarjetas_unidad.cve AS tarjeta,
	tarjetas_unidad.estatus AS estatus_tarjetas,
	fecha_viaje,
	unidades.no_eco,
	CONCAT(
	'(',
	operadores.cve,
	') ',
	operadores.nombre
	) AS nombre_operador,
	monitoreo.mutualidad,
	monitoreo.seguridad,
	utilidad,
	monitoreo.tag,
	fianza
	FROM
	tarjetas_unidad
	LEFT JOIN derroteros ON tarjetas_unidad.derrotero = derroteros.cve
	LEFT JOIN operadores ON tarjetas_unidad.operador = operadores.cve
	LEFT JOIN empresas ON tarjetas_unidad.empresa = empresas.cve
	LEFT JOIN unidades ON tarjetas_unidad.unidad = unidades.cve
	LEFT JOIN monitoreo ON tarjetas_unidad.cve = monitoreo.tarjeta
	WHERE
	tarjetas_unidad.cve = '{$_GET["tarjeta"]}'
	
	
	
	";
	
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			
			
			$respuesta["existe"] = "NO";
		}
		else{
			$respuesta["existe"] = "SI";
			
		}
		
		while($row = mysqli_fetch_assoc($result)){
			
			$fila = $row ;
			
		}
		
		
		$respuesta["tarjeta"] = $fila;
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
	}
	
	echo json_encode($respuesta);
	
?>						