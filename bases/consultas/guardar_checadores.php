<?php 
	include('../../conexi.php');
	$link = Conectarse();
	
	$respuesta = array();
	
	
	$consulta ="INSERT INTO checadores SET  
	id_checadores = '{$_POST['id_checadores']}' , 
	id_base = '{$_POST['id_base']}' , 
	nombre = '{$_POST['nombre']}' , 
	estatus = '{$_POST['estatus']}' , 
	password = '{$_POST['password']}' 
	
	ON DUPLICATE KEY UPDATE
	 
	nombre = '{$_POST['nombre']}' , 
	id_base = '{$_POST['id_base']}' , 
	estatus = '{$_POST['estatus']}' , 
	password = '{$_POST['password']}' 
	
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