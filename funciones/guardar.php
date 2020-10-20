<?php 
	// session_start();
	// if(count($_SESSION) == 0){
		// $respuesta["estatus"] = "error";
		// $respuesta["mensaje"] = "Tu sesion a caducado, vuelve a entrar ";		
		
		// echo json_encode($respuesta);
		// exit();
	// }
	include('../conexi.php');
	$link = Conectarse();
	
	$respuesta = array();
	
	$tabla = $_POST["tabla"];
	$campos_valores = $_POST["datos"];
	$str_pairs = "";
	
	if(empty($campos_valores[0]['value'])){ //Si el primer input (id) esta vacio, insertar sino actualizar  
		$query ="INSERT INTO $tabla SET ";	
		
		foreach($campos_valores as $arr_field_value){
			$str_pairs.= $arr_field_value["name"]. " = '" . $arr_field_value["value"] . "',";
		}
		
		// $str_pairs  = trim($str_pairs, ",");
		$query.= $str_pairs;
		$query.= " id_administrador = '1'";
		
    }else{
		
		$query ="UPDATE $tabla SET ";	
		
		foreach($campos_valores as $arr_field_value){
			$str_pairs.= $arr_field_value["name"]. " = '" . $arr_field_value["value"] . "',";
		}
    
		$str_pairs  = trim($str_pairs, ",");
		$query.= $str_pairs." WHERE ".$campos_valores[0]['name']."='".$campos_valores[0]['value']."'";
	}	
	
	$exec_query = 	mysqli_query($link,$query);
	
	if($exec_query){
		$respuesta["estatus"] = "success";
		$respuesta["mensaje"] = "Agregado";
		$respuesta["insert_id"] = mysqli_insert_id($link);
		$respuesta["query"] = $query;
		
    }else{
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en insert: $query  ".mysqli_error($link);		
	}
	
	echo json_encode($respuesta);
	
?>