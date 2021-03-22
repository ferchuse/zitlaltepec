<?php 
	include('../../conexi.php');
	$link = Conectarse();
	
	$respuesta = array();
	
	
	$consulta ="INSERT INTO bases SET  
	id_base = '{$_POST['id_base']}' , 
	base = '{$_POST['base']}' 
	
	ON DUPLICATE KEY UPDATE
	 
	id_base = '{$_POST['id_base']}' , 
	base = '{$_POST['base']}' 
	
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