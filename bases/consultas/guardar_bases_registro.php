<?php 
	include('../../conexi.php');
	$link = Conectarse();
	
	$respuesta = array();
	
	
	$consulta ="INSERT IGNORE INTO bases_registros SET  
	tarjeta = '{$_POST['tarjeta']}' , 
	id_checadores = '{$_POST['id_checadores']}' , 
	id_base = '{$_POST['id_base']}' , 
	fecha_registro = '{$_POST['fecha_registro']}' , 
	id_unidades = '{$_POST['id_unidades']}' 
	num_eco = '{$_POST['num_eco']}' 
	
	
	";	
	
	$result = 	mysqli_query($link,$consulta);
	
	if($result){
		
		$respuesta["action"] = "insert";
		$respuesta["estatus"] = "success";
		$respuesta["mensaje"] = "Guardado";
		$id_usuarios = mysqli_insert_id($link);
	}
	else{
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en $q_usuario ".mysqli_error($link);		
	}
	
	
	
	
	
	
	
	echo json_encode($respuesta);
	
?>