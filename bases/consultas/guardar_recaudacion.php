<?php 
	session_start();
	include('../../conexi.php');
	$link = Conectarse();
	
	
	$consulta_tarjeta = "		
	SELECT * FROM tarjetas_unidad 
	WHERE cve = '{$_POST["tarjeta"]}'
	";
	
	$result_tarjeta = mysqli_query($link,$consulta_tarjeta);
	if($result_tarjeta){
		
		while($row = mysqli_fetch_assoc($result_tarjeta)){
			
			$fila = $row;
		}
		
		
		
		$consulta = 
		"INSERT INTO recaudacion_monitoreo					 
		SET 
		tarjeta='{$_POST['tarjeta']}',		
		fecha= CURDATE(),
		fecha_creacion= CURDATE(),
		fecha_viaje= '{$fila['fecha_viaje']}',
		operador='{$fila['operador']}',
		hora=CURTIME(),
		unidad='{$fila['unidad']}',
		derrotero='{$fila['derrotero']}',
		usuario='{$_SESSION['CveUsuario']}',
		empresa='{$fila['empresa']}',	
		estatus='A',
		obs='{$_POST['observaciones']}',
		monto='{$_POST['utilidad']}',
		efectivo_recaudado='{$_POST['efectivo_recaudado']}',
		efectivo_entregar='{$_POST['efectivo_entregar']}',
		monto_derrotero='{$_POST['monto_derrotero']}',
		cant_boletos='{$_POST['cant_boletos']}',
		vueltas='{$_POST['vueltas']}',
		monto_boletos='{$_POST['monto_boletos']}',
		monto_efectivo='{$_POST['efectivo_pagado']}',
		total_utilidad='{$_POST['utilidad']}',
		recaudacion='{$_POST['recaudacion']}',
		diesel='{$_POST['diesel']}',
		comision='{$_POST['comision']}',
		lavada='{$_POST['lavada']}',
		vale_comida='{$_POST['vale_comida']}',
		bono_productividad='{$_POST['bono_productividad']}',
		casetas='{$_POST['casetas']}',
		despachadores='{$_POST['despachadores']}',
		excedente='{$_POST['excedente']}',
		total_gasto='{$_POST['total_gasto']}',
		concepto='{$_POST['concepto']}',
		cant_boletos_tijera='{$_POST['cant_boletos_tijera']}',
		monto_boletos_tijera='{$_POST['boletos_tijera']}',
		cant_boletos_abordo='{$_POST['cant_boletos_abordo']}',
		monto_boletos_abordo='{$_POST['monto_boletos_abordo']}',
		total_boletos='{$_POST['total_boletos']}',
		deuda_operador='{$_POST['deuda_operador']}',
		motivo_deuda='{$_POST['motivo_deuda']}', 
		monto_vale_dinero='{$_POST['vale_dinero']}',
		cant_taqmovil='{$_POST['cant_taqmovil']}',
		monto_taqmovil='{$_POST['monto_taqmovil']}',
		cant_abonomovil='{$_POST['cant_abonomovil']}',
		monto_abonomovil='{$_POST['monto_abonomovil']}',
		litros_vale_diesel='{$_POST['litros_vale_diesel']}',
		monto_vale_diesel='{$_POST['monto_vale_diesel']}',
		diesel_manual='{$_POST['diesel_manual']}', 
		cant_sencillos='{$_POST['cant_sencillos']}',
		monto_sencillos='{$_POST['importe_sin_guia']}'";
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			$folio_recaudacion = mysqli_insert_id($link);
			
			$respuesta["folio"] = $folio_recaudacion;
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "Guardado";
			
			
		}
		else {
			
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
			
			
		}
	}
	
	$respuesta["consulta"] = $consulta;
	
	//Actualiza Tarjeta
	
	$update = 
	"UPDATE tarjetas_unidad
	SET 
	estatus='P'		
	WHERE cve ='{$_POST["tarjeta"]}'";
	
	$result_update = mysqli_query($link,$update);
	if($result_update){
		$respuesta["estatus_update"] = "success";
		$respuesta["mensaje_update"] = "Guardado";	
	}
	else {
		$respuesta["estatus_update"] = "error";
		$respuesta["mensaje_update"] = "Error en ".$update.mysqli_Error($link);
	}
	
	
	
	//Actualiza Guias
	$update_guia = 
	"UPDATE guia
	SET folio_recaudacion='$folio_recaudacion',
	fecha_recaudacion = CURDATE()
	WHERE taquilla > 0 
	AND unidad='{$fila['unidad']}' AND folio_recaudacion=0";
	
	$respuesta["guia_update"] = $update_guia;
	
	$result_guia = mysqli_query($link,$update_guia);
	if($result_update){
		$respuesta["guia_estatus"] = "success";
		$respuesta["guia_mensaje"] = "Guia Actualizada";	
	}
	else {
		$respuesta["guia_estatus"] = "error";
		$respuesta["guia_mensaje"] = mysqli_Error($link);
	}
	
	
	//Actualiza Vales de Dinero
	$update_vales = 
	"UPDATE vale_dinero
	SET
	recaudacion='$folio_recaudacion',
	fecha_recaudacion=CURDATE() 
	WHERE estatus!='C' AND recaudacion=0 AND unidad='{$fila['unidad']}' ";
	
	$respuesta["vales_update"] = "success";
	
	$result_vales = mysqli_query($link,$update_vales);
	
	if($result_vales){
		$respuesta["vales_estatus"] = "success";
		$respuesta["vales_mensaje"] = "Vales Actualizados";	
	}
	else {
		$respuesta["vales_estatus"] = "error";
		$respuesta["vales_mensaje"] = mysqli_Error($link);
	}
	
	//Actualiza Boletos Sencillos
	
	foreach($_POST["folio_boleto"] as $i => $folio_boleto){
		
		$update_boletos_sencillos = 
		"UPDATE boletos_sencillos
		SET
		folio_recaudacion='$folio_recaudacion',
		fecha_recaudacion=CURDATE() ,
		tipo_recaudacion = 1
		WHERE 
		taquilla ='{$_POST["taquilla"][$i]}' 
		AND folio ='{$folio_boleto}' 
		
		";
		
		$respuesta["boletos_sencillos_update"][] = $update_boletos_sencillos;
		
		$result_boletos_sencillos = mysqli_query($link,$update_boletos_sencillos);
		
		if($result_boletos_sencillos){
			$respuesta["boletos_sencillos_estatus"] = "success";
			$respuesta["boletos_sencillos_mensaje"] = "boletos_sencillos Actualizados";	
		}
		else {
			$respuesta["boletos_sencillos_estatus"] = "error";
			$respuesta["boletos_sencillos_mensaje"] = mysqli_Error($link);
		}
		
		
	}
	/*
		
		foreach($_POST['guias'] as $guia){
		$datos = explode("_",$guia);
		mysql_query("UPDATE guia SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE taquilla='".$datos[0]."' AND folio='".$datos[1]."'");
		mysql_query("UPDATE boletos SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE taquilla='".$datos[0]."' AND guia='".$datos[1]."'");
		}
		if($_POST['cant_taqmovil'] > 0){
		mysql_query("UPDATE boletos_taquillamovil SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".fechaLocal()."' WHERE unidad='".$_POST['unidad']."' AND folio_recaudacion='0' AND estatus!='C' ORDER BY cve LIMIT ".intval($_POST['cant_taqmovil']));
		}
		if($_POST['cant_abonomovil'] > 0){
		mysql_query("UPDATE abono_unidad_taquillamovil SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".fechaLocal()."' WHERE unidad='".$_POST['unidad']."' AND folio_recaudacion='0' AND estatus!='C' ORDER BY cve LIMIT ".intval($_POST['cant_abonomovil']));
		}
		$boletos = json_decode($_POST['boletossencillos'], true)
		
		
		;
		foreach($boletos as $boleto){
		mysql_query("UPDATE boletos_sencillos SET folio_recaudacion='$cverecaudacion', fecha_recaudacion='".fechaLocal()."', tipo_recaudacion=1 WHERE taquilla = '".$boleto['taquilla']."' AND folio='".$boleto['folio']."'");
		}
		
		
		foreach($_POST['vales_dinero'] as $vale){
		mysql_query("UPDATE vale_dinero SET recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE cve='".$vale."'");
		}
	*/
	
	
	
	echo json_encode($respuesta);
	
	
?>							