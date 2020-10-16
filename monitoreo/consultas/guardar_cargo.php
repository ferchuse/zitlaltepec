<?php 
	
	include('../../conexi.php');
	
	$link = Conectarse();
	$filas = array();
	
	
	
	$consulta_tarjeta = "		
	SELECT * FROM tarjetas_unidad 
	WHERE cve = '{$_GET["tarjeta"]}'
	";
	
	$result_tarjeta = mysqli_query($link,$consulta_tarjeta);
	if($result_tarjeta){
		
		while($row = mysqli_fetch_assoc($result_tarjeta)){
			
			$fila = $row;
		}
		
			$respuesta["estatus_tarjeta"] = "success";
		// $respuesta["mensaje_tarjeta"] = "Error en ".$consulta_tarjeta.mysqli_Error($link);
		
		
		
		$insert_cargo =
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
		cargo='{$_POST['cargo']}'";
		
		$result = mysqli_query($link,$insert_cargo);
		if($result){
			
			
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "Cargo guardado";
			
			
		}
		else {
			
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$insert_cargo.mysqli_Error($link);
			
			
		}
		
		
	}
	else {
		
		$respuesta["estatus_tarjeta"] = "error";
		$respuesta["mensaje_tarjeta"] = "Error en ".$consulta_tarjeta.mysqli_Error($link);
		
		// echo json_encode($respuesta);
	}
	
	
	
	echo json_encode($respuesta);
?>						