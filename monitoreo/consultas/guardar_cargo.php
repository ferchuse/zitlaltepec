<?php 
	
	include('../../conexi.php');
	
	$link = Conectarse();
	$filas = array();
	
	
	
	$consulta_tarjeta = "		
	SELECT * FROM tarjetas_unidad 
	WHERE tarjeta = '{$_GET["tarjeta"]}'
	";
	
	$result_tarjeta = mysqli_query($link,$consulta_tarjeta);
	if($result_tarjeta){
		
		while($row = mysqli_fetch_assoc($result_tarjeta){
			
			
			$fila = $row;
		}
		
		
		
		$insert_mutualidad
		"INSERT recaudacion_operador 
		SET tarjeta='{$fila['tarjeta']}',
		fecha_viaje='{$fila['fecha_viaje']}',
		fecha= CURDATE(),
		fecha_creacion= CURDATE(),
		operador='{$fila['operador']}',
		hora= CURTIME(),
		unidad='{$fila['unidad']}',
		derrotero='{$fila['derrotero']}',
		usuario='{$_SESSION['cveusuario']}',
		empresa ='{$fila['empresa']}',
		estatus='A',
		monto='{$_POST["monto"]}',
		
		recaudacion='{$_POST['recaudacion']}',
		cargo='{$_POST['reg']}'"
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "M utualidad generadaa";
			
			
		}
		else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
		echo json_encode($respuesta);
		}
		
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
		echo json_encode($respuesta);
		}
	
	
	
	
?>						