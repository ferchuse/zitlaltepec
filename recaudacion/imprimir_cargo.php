<?php 
	include('../conexi.php');
	$link = Conectarse();
	$filas = array();
	
	$consulta = "SELECT 
	*,
	recaudacion_operador.monto  AS monto,
	recaudacion_operador.cve  AS folio,
	operadores.nombre  AS operadores_nombre,
	usuarios.usuario  AS usuarios_nombre
	FROM recaudacion_operador
	LEFT JOIN usuarios  ON recaudacion_operador.usuario = usuarios.cve
	LEFT JOIN operadores  ON recaudacion_operador.operador = operadores.cve
	LEFT JOIN unidades  ON recaudacion_operador.unidad = unidades.cve
	LEFT JOIN cat_cargos_operadores ON recaudacion_operador.cargo = cat_cargos_operadores.cve
	
	WHERE recaudacion_operador.cve = '{$_GET['folio']}'";
	
	
	$result = mysqli_query($link,$consulta);
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			
			die("<div class='alert alert-danger'>Registro no encontrado</div>");
			
			
		}
		
		while($fila = mysqli_fetch_assoc($result)){
			
			$registro = $fila ;
			
		}
		
		
		
		$texto ="@";
		$texto.=chr(27).'!'.chr(40)."  ".$_GET['tabla']."\n\n";
	
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$registro['folio']."\n\n";
	
		$texto.= $registro['fecha']." ".$registro['hora']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."TAQUILLERO: ".$registro['usuarios_nombre']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$registro['no_eco']."\n\n";
	
		$texto.=chr(27).'!'.chr(20)."(".$registro['operador'].')'.$registro['operadores_nombre']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."MONTO: ".number_format($registro['monto'],2)."\n\n";
		$texto.= "VA"; // Cut
		
	
		
		echo base64_encode ( $texto );
		exit(0);
		
		
		
	}
	else {
		echo "Error en ".$consulta.mysqli_Error($link);
		
	}
	
	
?>


