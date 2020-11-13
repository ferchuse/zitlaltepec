<?php 
	session_start();
	include('../../conexi.php');
	$link = Conectarse();
	
	
	$consulta = 
	"INSERT INTO monitoreo 
	SET 
	tarjeta='{$_POST['tarjeta']}',		
	usuario='{$_SESSION['CveUsuario']}',		
	fecha_monitoreo= NOW(),
	ingreso_bruto='{$_POST["ingreso_bruto"]}',
	casetas='{$_POST["casetas"]}',
	diesel='{$_POST["diesel"]}',
	despachadores='{$_POST["despachadores"]}',
	comision='{$_POST["comision"]}',
	incentivo='{$_POST["incentivo"]}',
	mutualidad='{$_POST["mutualidad"]}',
	seguridad='{$_POST["seguridad"]}',
	tag='{$_POST["tag"]}',
	fianza='{$_POST["fianza"]}',
	utilidad='{$_POST["utilidad"]}',
	vueltas='{$_POST["vueltas"]}',
	observaciones='{$_POST["observaciones"]}'";
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		$folio = mysqli_insert_id($link);
		$respuesta["estatus"] = "success";
		$respuesta["mensaje"] = "Guardado";
		
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
		
	}
	
	
	
	foreach($_POST["vueltas"] as $i => $vuelta){
		
		
		
		
		
		$consulta = 
		"INSERT INTO vueltas
		SET 
		id_monitoreo='{$folio}',	
		num_vuelta='{$vuelta['num_vuelta']}',	
		origen='{$vuelta['origen']}',	
		destino='{$vuelta['destino']}',	
		tarifa='{$vuelta['tarifa']}',	
		cant_origen='{$vuelta['cant_origen']}',	
		cant_destino='{$vuelta['cant_destino']}',	
		total_tarifa='{$vuelta['total_tarifa']}',	
		total_origen='{$vuelta['total_origen']}',	
		total_destino='{$vuelta['total_destino']}',	
		total_vuelta='{$vuelta['total_vuelta']}'
		";
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "Guardado";
			
			
		}
		else {
			
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
			
			
		}
	}
	
	echo json_encode($respuesta);
	
	
?>						