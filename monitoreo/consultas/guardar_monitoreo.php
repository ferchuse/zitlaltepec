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
	bases='{$_POST["bases"]}',
	fianza='{$_POST["fianza"]}',
	utilidad='{$_POST["utilidad"]}',
	vueltas='{$_POST["vueltas"]}',
	observaciones='{$_POST["observaciones"]}'";
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		$folio = mysqli_insert_id($link);
		$respuesta["estatus"]["monitoreo"] = "success";
		$respuesta["mensaje"]["monitoreo"] = "Guardado";
		
		
	}
	else {
		
		$respuesta["estatus"]["monitoreo"] = "error";
		$respuesta["mensaje"]["monitoreo"] = "Error en ".$consulta.mysqli_Error($link);
		
		
	}
	
	
	
	foreach($_POST["monitoreo_vueltas"] as $i => $vuelta){
		
		
		$consulta = 
		"INSERT INTO monitoreo_vueltas
		SET 
		id_monitoreo='{$folio}',	
		num_vuelta='{$vuelta['num_vuelta']}',	
		origen='{$vuelta['origen']}',	
		destino='{$vuelta['destino']}',	
		total_origen='{$vuelta['total_origen']}',	
		total_destino='{$vuelta['total_destino']}',	
		total_vuelta='{$vuelta['total_vuelta']}'
		";
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			$respuesta["estatus"]["vueltas"] = "success";
			$respuesta["mensaje"]["vueltas"] = "Guardado";
		}
		else {
			
			$respuesta["estatus"]["vueltas"] = "error";
			$respuesta["mensaje"]["vueltas"] = "Error en ".$consulta.mysqli_Error($link);
		}
		
	}
	
	foreach($_POST["monitoreo_boletos"] as $i => $boleto){
		
		$consulta = 
		"INSERT INTO monitoreo_boletos
		SET 
		id_monitoreo='{$folio}',	
		num_vuelta='{$boleto['num_vuelta']}',	
		tarifa='{$boleto['tarifa']}',	
		cant_origen='{$boleto['cant_origen']}',	
		cant_destino='{$boleto['cant_destino']}',	
		total_tarifa='{$boleto['total_tarifa']}'
		
		";
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			$respuesta["estatus"]["boletos"] = "success";
			$respuesta["mensaje"]["boletos"] = "Guardado";
		}
		else {
			
			$respuesta["estatus"]["boletos"] = "error";
			$respuesta["mensaje"]["boletos"] = "Error en ".$consulta.mysqli_Error($link);
		}
		
	}
	
	echo json_encode($respuesta);
	
	
?>						