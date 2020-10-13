<?php 
	
	include('../../conexi.php');
	
	$link = Conectarse();
	$filas = array();
	
	
	
	$consulta = "		
	SELECT
	tarjetas_unidad.cve AS tarjeta,
	tarjetas_unidad.estatus AS estatus_tarjetas,
	fecha_viaje,
	unidades.no_eco,
	CONCAT(
	'(',
	operadores.cve,
	') ',
	operadores.nombre
	) AS nombre_operador,
	derroteros.mutualidad
	FROM
	tarjetas_unidad
	LEFT JOIN derroteros ON tarjetas_unidad.derrotero = derroteros.cve
	LEFT JOIN operadores ON tarjetas_unidad.operador = operadores.cve
	LEFT JOIN empresas ON tarjetas_unidad.empresa = empresas.cve
	LEFT JOIN unidades ON tarjetas_unidad.unidad = unidades.cve
	WHERE
	tarjetas_unidad.cve = '{$_GET["tarjeta"]}'
	";
	
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			
			die("<div class='alert alert-danger'>Tarjeta No encontrada</div>");
			
			
		}
		
		while($row = mysqli_fetch_assoc($result)){
			
			$fila = $row ;
			
		}
		
		if($fila["estatus_tarjetas"] == 'P' ){
			
			die("<div class='alert alert-danger '>Tarjeta Ya recaudada</div>");
			
		}
		
		if($fila["estatus_tarjetas"] == 'C'  ){
			
			die("<div class='alert alert-danger '>Tarjeta Cancelada</div>");
			
		}
		
	?>
	<tr >
		<td >
			<label for="">Fecha de Viaje: </label>
		</td >
		<td >
			<input  readonly type="date" name="fecha_viaje" id="fecha_viaje" value="<?= $fila["fecha_viaje"]?>">
		</td >
	</tr>
	<tr >
		<td >
			<label for="">Unidad: </label>
		</td >
		<td >
			<input readonly type="number" name="unidad" id="unidad" value="<?= $fila["no_eco"]?>">
		</td >
	</tr>
	<tr >
		<td >
			<label for="">Operador: </label>
		</td >
		<td >
			<input readonly type="text" name="operador" id="operador" value="<?= $fila["nombre_operador"]?>">
		</td >
	</tr>
	
	
	<?php
		
	}
	else {
		
		$respuesta["estatus"] = "error";
		$respuesta["mensaje"] = "Error en ".$consulta.mysqli_Error($link);
		
		echo json_encode($respuesta);
	}
	
	
?>						