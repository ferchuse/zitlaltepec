<?php 
	session_start();
	include('../../conexi.php');
	header("Content-Type: application/json");
	$link = Conectarse();
	$filas = array();
	
	$fecha_cancelacion = date("Y-m-d H:i:s");
	
	
	$cancelar = "UPDATE monitoreo 
	SET estatus_monitoreo='Cancelado',
	datos_cancelacion='Usuario: {$_POST["nombre_usuarios"]} <br> Fecha: $fecha_cancelacion'
	
	WHERE id_monitoreo ='{$_POST['folio']}'";
	
	$result_cancelar  = mysqli_query($link, $cancelar ) or die(mysqli_error($link));;
	
	$respuesta["result_cancelar"] = $result_cancelar;
	$respuesta["cancelar"] = $cancelar;
	
	
	echo json_encode($respuesta);
?>						