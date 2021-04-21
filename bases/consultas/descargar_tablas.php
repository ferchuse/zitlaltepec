<?php 
	// session_start();
	include('../../conexi.php');
	$link = Conectarse();
		
	$consulta_checadores = "		
	SELECT 
	*
	FROM checadores 
	LEFT JOIN bases USING(id_base)
	
	
	";
	
	$result_checadores = mysqli_query($link,$consulta_checadores);
	
	
	
	
	if($result_checadores){
		
		while($row = mysqli_fetch_assoc($result_checadores)){
			
			$checadores[] = $row;
		}
		
		$respuesta["checadores"] = $checadores;
	}
	else {
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$consulta_checadores.mysqli_Error($link);
		}
	
	
	echo json_encode($respuesta);
	
	
?>							