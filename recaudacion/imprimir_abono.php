<?php 
	include('../conexi.php');
	$link = Conectarse();
	$filas = array();
	
	$consulta = "SELECT 
	*,
	recaudacion_monitoreo.monto as monto,
	recaudacion_monitoreo.cve  AS folio,
	operadores.nombre  AS operadores_nombre,
	recaudaciones.nombre  AS recaudaciones_nombre,
	usuarios.usuario  AS usuarios_nombre
	FROM recaudacion_monitoreo
	LEFT JOIN recaudaciones  ON recaudacion_monitoreo.recaudacion = recaudaciones.cve
	LEFT JOIN tarjetas_unidad  ON recaudacion_monitoreo.tarjeta = tarjetas_unidad.cve
	LEFT JOIN usuarios  ON recaudacion_monitoreo.usuario = usuarios.cve
	LEFT JOIN operadores  ON recaudacion_monitoreo.operador = operadores.cve
	LEFT JOIN unidades  ON recaudacion_monitoreo.unidad = unidades.cve
	
	WHERE recaudacion_monitoreo.cve = '{$_GET['folio']}'";
	
	
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
			
			$texto.="@";
			$texto.=   "RECAUDACION POR  MONITOREO\n";
			
			$texto.=chr(27).'!'.chr(40)."FOLIO: ".$registro['folio']."\n\n";
			
			$texto.= $registro['fecha']." ".$registro['hora']."\n\n";
			
			$texto.= "Tarjeta: ". $registro['tarjeta']."\n\n";
			$texto.= "F.C.: ".$registro['fecha_viaje']."\n\n";
			$texto.= "RD:  ". $registro['recaudaciones_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."Taquillero: ".$registro['usuarios_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$registro['no_eco']."\n\n";
			
			$texto.=chr(27).'!'.chr(20)."(".$registro['operador'].')'.$registro['operadores_nombre']."\n\n";
			
			$texto.=chr(27).'!'.chr(40)."Utilidad: $".number_format($registro['total_utilidad'],2)."\n";
			// $texto.=chr(27).'!'.chr(40)."Efectivo Recaudado: $".number_format($registro['monto'],2);
			if($i == 1){ // Cortar Primer Ticket
				$texto.="\n\n";
				$texto.= "VA"; // Cut
				
			}
			
		}
		
		echo base64_encode ( $texto );
		// echo  $texto ;
		exit(0);
		
		
		
		}
		else {
			echo "Error en ".$consulta.mysqli_Error($link);
			
		}
		
		
	?>
	
	
