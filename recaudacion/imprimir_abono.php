<?php 
	include('../conexi.php');
	$link = Conectarse();
	$filas = array();
	
	$consulta = "SELECT 
	*,
	recaudacion_autobus.monto as monto,
	recaudacion_autobus.cve  AS folio,
	operadores.nombre  AS operadores_nombre,
	recaudaciones.nombre  AS recaudaciones_nombre,
	usuarios.usuario  AS usuarios_nombre
	FROM recaudacion_autobus
	LEFT JOIN recaudaciones  ON recaudacion_autobus.recaudacion = recaudaciones.cve
	LEFT JOIN tarjetas_unidad  ON recaudacion_autobus.tarjeta = tarjetas_unidad.cve
	LEFT JOIN usuarios  ON recaudacion_autobus.usuario = usuarios.cve
	LEFT JOIN operadores  ON recaudacion_autobus.operador = operadores.cve
	LEFT JOIN unidades  ON recaudacion_autobus.unidad = unidades.cve
	
	WHERE recaudacion_autobus.cve = '{$_GET['folio']}'";
	
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			
			die("<div class='alert alert-danger'>Registro no encontrado</div>");
			
			
		}
		
		while($fila = mysqli_fetch_assoc($result)){
			
			$registro = $fila ;
			
			}
		
		$texto = "";
		
		for ($i = 1 ; $i <3; $i++){
			
			$texto ="@";
			$texto.=   "RECAUDACION POR  MONITOREO\n";
			
			$texto.=chr(27).'!'.chr(40)."FOLIO: ".$registro['folio']."\n\n";
			
			$texto.= $registro['fecha']." ".$registro['hora']."\n\n";
			
			$texto.= "Tarjeta: ". $registro['tarjeta']."\n\n";
			$texto.= "F.C.: ".$registro['fecha_viaje']."\n\n";
			$texto.= "RD:  ". $registro['recaudaciones_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."Taquillero: ".$registro['usuarios_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$registro['no_eco']."\n\n";
			
			$texto.=chr(27).'!'.chr(20)."(".$registro['operador'].')'.$registro['operadores_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."Utilidad: $".number_format($registro['utilidad'],2)."\n\n";
			$texto.= "VA"; // Cut
			
			
		}
		
		echo base64_encode ( $texto );
		// echo  $texto ;
		exit(0);
		
		
		
		}
		else {
			echo "Error en ".$consulta.mysqli_Error($link);
			
		}
		
		
	?>
	
	
