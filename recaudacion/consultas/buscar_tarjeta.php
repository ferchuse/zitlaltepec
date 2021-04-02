<?php 
	
	include('../../conexi.php');
	
	$link = Conectarse();
	$filas = array();
	
	
	
	$consulta = "		
	SELECT
	tarjetas_unidad.cve AS tarjeta,
	tarjetas_unidad.estatus AS estatus_tarjetas,
	tarjetas_unidad.unidad AS unidad,
	fecha_viaje,
	unidades.no_eco,
	CONCAT(
	'(',
	operadores.cve,
	') ',
	operadores.nombre
	) AS nombre_operador,
	monitoreo.mutualidad,
	monitoreo.seguridad,
	utilidad,
	monitoreo.tag,
	monitoreo.bases,
	fianza
	FROM
	tarjetas_unidad
	LEFT JOIN derroteros ON tarjetas_unidad.derrotero = derroteros.cve
	LEFT JOIN operadores ON tarjetas_unidad.operador = operadores.cve
	LEFT JOIN empresas ON tarjetas_unidad.empresa = empresas.cve
	LEFT JOIN unidades ON tarjetas_unidad.unidad = unidades.cve
	LEFT JOIN monitoreo ON tarjetas_unidad.cve = monitoreo.tarjeta
	
	
	WHERE
	tarjetas_unidad.cve = '{$_GET["tarjeta"]}'
	
	
	
	";
	
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			
			
			$respuesta["existe"] = "NO";
		}
		else{
			$respuesta["existe"] = "SI";
			
		}
		
		while($row = mysqli_fetch_assoc($result)){
			
			$fila = $row ;
			
		}
		
		// Buscar Boletos con guia+
		$consulta_guia = "SELECT taquilla, folio 
		FROM guia WHERE taquilla > 0 
		AND unidad='{$fila['unidad']}' AND folio_recaudacion=0";
		$respuesta["consulta_guia"] = $consulta_guia;
		
		$cant_boletos=0;
		$importe_boletos=0;
		
		$result_guia=  mysqli_query($link,$consulta_guia);
		
		$tabla_guias = "<tr><th>Guia</th><th>Cant. Boletos</th><th>Imp. Boletos</th></tr>";
		
		while($fila_guia = mysqli_fetch_assoc($result_guia)){
			$consulta_boletos = "SELECT COUNT(cve), SUM(monto) 
			FROM boletos
			WHERE taquilla='".$fila_guia['taquilla']."' 
			AND guia = '".$fila_guia['folio']."' AND estatus = 0";
			
			$respuesta["consulta_boletos"] = $consulta_boletos;
			
			$result_boletos = mysqli_query($link,$consulta_boletos);
			
			$fila_boletos = mysqli_fetch_array($result_boletos);
			
			$tabla_guias.= '<tr><td>'.$fila_guia['folio'].'<input type="hidden" name="guias[]" value="'.$fila_guia['taquilla'].'_'.$fila_guia['folio'].'"></td>
			<td align="right">'.$fila_boletos[0].'</td><td align="right">'.number_format($fila_boletos[1],2).'</td></tr>';
			$cant_boletos+=$fila_boletos[0];
			$importe_boletos+=$fila_boletos[1];
		}
	
		$respuesta["tabla_guias"] = $tabla_guias;
		$respuesta["importe_con_guia"] = $importe_boletos;
		
		
		
		
		// Buscar Vale de Dinero 
		$total_vale_dinero=0;
		$tabla_vale_dinero = "<tr><th>Folio</th><th>Importe</th></tr>";
		$consulta_vale_dinero= "SELECT * FROM vale_dinero 
		WHERE estatus!='C' AND recaudacion=0 AND unidad='{$fila['unidad']}' ORDER BY cve";
		
		$respuesta["consulta_vale_dinero"] = $consulta_vale_dinero;
		
		
		$result_vale_dinero = mysqli_query($link,$consulta_vale_dinero);
		
		while($fila_vale_dinero = mysqlI_fetch_assoc($result_vale_dinero)){
			$tabla_vale_dinero.= '<tr><td>'.$fila_vale_dinero['cve'].'</td><td align="right">'.$fila_vale_dinero['monto'].'</td></tr>';
			$total_vale_dinero += $fila_vale_dinero['monto'];
		}
		
		$respuesta["tabla_vale_dinero"] = $tabla_vale_dinero;
		$respuesta["vale_dinero"] = $total_vale_dinero;
		
		$respuesta["tarjeta"] = $fila;
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
	}
	
	echo json_encode($respuesta);
	
?>						