<?php 
	session_start();
	include('../../conexi.php');
	
	// include('../../funciones/dame_permiso.php');
	$link = Conectarse();
	
	
	$update_boleto =  "UPDATE boletos_sencillos 
	SET estatus = 0
	WHERE folio = '{$_GET["folio"]}'
	AND taquilla = '{$_GET["taquilla"]}'";
	
	$result_update_boleto = mysqli_query($link, $update_boleto);
	
	$resultado['update_boleto'] = $result_update_boleto ;
	
	
	
	
	echo json_encode($resultado);
?>			