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
		"INSERT INTO recaudacion_autobus					 
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
		monto='{$_POST['abono']}',
		monto_derrotero='{$_POST['monto_derrotero']}',
		cant_boletos='{$_POST['cant_boletos']}',
		vueltas='{$_POST['vueltas']}',
		monto_boletos='{$_POST['monto_boletos']}',
		monto_efectivo='{$_POST['efectivo_pagado']}',
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
		monto_boletos_tijera='{$_POST['monto_boletos_tijera']}',
		cant_boletos_abordo='{$_POST['cant_boletos_abordo']}',
		monto_boletos_abordo='{$_POST['monto_boletos_abordo']}',
		total_boletos='{$_POST['total_boletos']}',
		deuda_operador='{$_POST['deuda_operador']}',
		motivo_deuda='{$_POST['motivo_deuda']}', 
		monto_vale_dinero='{$_POST['monto_vale_dinero']}',
		cant_taqmovil='{$_POST['cant_taqmovil']}',
		monto_taqmovil='{$_POST['monto_taqmovil']}',
		cant_abonomovil='{$_POST['cant_abonomovil']}',
		monto_abonomovil='{$_POST['monto_abonomovil']}',
		litros_vale_diesel='{$_POST['litros_vale_diesel']}',
		monto_vale_diesel='{$_POST['monto_vale_diesel']}',
		diesel_manual='{$_POST['diesel_manual']}', 
		cant_sencillos='{$_POST['cant_sencillos']}',
		monto_sencillos='{$_POST['monto_sencillos']}'";
		
		$result = mysqli_query($link,$consulta);
		if($result){
			
			
			
			$respuesta["folio"] = mysqli_insert_id($link);
			$respuesta["estatus"] = "success";
			$respuesta["mensaje"] = "Guardado";
			
			
		}
		else {
			
			$respuesta["estatus"] = "error";
			$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
			
			
		}
	}
	
	$respuesta["consulta"] = $consulta;
	
	
	
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
	
	
	echo json_encode($respuesta);
	
	
?>							