<?php 
	include('../../conexi.php');
	$link = Conectarse();
	
	
	$consulta = 
	"INSERT monitoreo 
	SET 
	tarjeta='{$_POST['tarjeta']}',		
	fecha_monitoreo= NOW(),
	ingreso_bruto='{$_POST["ingreso_bruto"]}',
	casetas='{$_POST["casetas"]}',
	diesel='{$_POST["diesel"]}',
	despachadores='{$_POST["despachadores"]}',
	comision='{$_POST["comision"]}',
	incentivo='{$_POST["incentivo"]}',
	mutualidad='{$_POST["mutualidad"]}',
	seguridad='{$_POST["seguridad"]}',
	fianza='{$_POST["fianza"]}',
	utilidad='{$_POST["utilidad"]}',
	observaciones='{$_POST["observaciones"]}'";
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		
		$respuesta["estatus"] = "success";
		$respuesta["mensaje"] = "Guardado";
		
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
		
	}
	
	
	echo json_encode($respuesta);
	
	
?>						