<?php 
	session_start();
	include('../../conexi.php');
	header("Content-Type: application/json");
	$link = Conectarse();
	$filas = array();
	
	$cancelar = "UPDATE recaudacion_autobus 
	SET estatus='C',
	fechacan= NOW(),
	usucan = '{$_POST['id_usuarios']}' 
	WHERE cve='{$_POST['folio']}'";
	
	$result_cancelar  = mysqli_query($link, $cancelar ) or die(mysqli_error($link));;
	
	$respuesta["result_cancelar"] = $result_cancelar;
	$respuesta["cancelar"] = $cancelar;
	
	
	$result_tarjeta = mysqli_query($link, "SELECT tarjeta FROM recaudacion_autobus WHERE cve='{$_POST['folio']}'");
	
	$tarjeta = mysqli_fetch_assoc($result_tarjeta);
	
	$respuesta["tarjeta"] = $tarjeta;
	
	$res1 = mysqli_query($link, "SELECT COUNT(cve) FROM recaudacion_autobus WHERE tarjeta = '{$tarjeta}' AND estatus!='C'");
	
	$row1 = mysql_fetch_array($res1);
	
	$respuesta["activa_tarjeta"] = mysqli_query($link, "UPDATE tarjetas_unidad SET estatus='A' WHERE cve='{$tarjeta["cve"]}'");

	$respuesta["guia"] = mysqli_query($link, "UPDATE guia SET folio_recaudacion = 0, fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='{$_POST['folio']}'");
	
	$respuesta["boletos"] = mysqli_query($link, "UPDATE boletos SET folio_recaudacion = 0, fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='{$_POST['folio']}'");
	
	$respuesta["boletos"] = mysqli_query($link, "UPDATE vale_dinero SET recaudacion = 0,fecha_recaudacion='0000-00-00' 
	WHERE recaudacion='{$_POST['folio']}'");
	
	$respuesta["boletos"] = mysqli_query($link, "UPDATE boletos_taquillamovil SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='{$_POST['folio']}'");
	
	$respuesta["boletos"] = mysqli_query($link, "UPDATE abono_unidad_taquillamovil SET folio_recaudacion = 0, fecha_recaudacion='0000-00-00' WHERE folio_recaudacion = '{$_POST['folio']}'");
	
	$respuesta["boletos"] = mysqli_query($link, "UPDATE boletos_sencillos SET folio_recaudacion=0, fecha_recaudacion='0000-00-00', tipo_recaudacion=0 WHERE folio_recaudacion='{$_POST['folio']}' AND tipo_recaudacion=1");
	
	
	echo json_encode($respuesta);
?>						