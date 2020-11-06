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
		
		$texto = "";
		
		
		// $texto.=   "\x1b"."@";
		// $texto.= "\x1b"."E".chr(1); // Bold
		// $texto.= "!";
		// $texto.=   "VALE DE GASTOS \n";
		// $texto.=  "\x1b"."E".chr(0); // Not Bold
		// $texto.= "!\x10";
		// $texto.= "\x1b"."d".chr(1); // 4 Blank lines
		// $texto.= "Folio:". $registro["id_gastos"]. "\n";
		// $texto.= "Corrida:". $registro["id_corridas"]. "\n";
		// $texto.= "Num Eco:". $registro["num_eco"]. "\n";
		// $texto.= "Fecha:" . ($registro["fecha_gastos"])."\n";
		// $texto.= "Recibe :". $registro["recibe"]."\n";
		// $texto.= "Concepto :". $registro["descripcion_gastos"]."\n";
		// $texto.= "Importe: $ ". $registro["importe"]."\n";
		// $texto.=  "Taquillero:" . $_COOKIE["nombre_usuarios"]."\n\n";
		// $texto.= "\x1b"."d".chr(1); // Blank line
		// $texto.= "  _________________\n\n"; // Blank line
		// $texto.= "aFIRMA DE RECIBIDO\n"; // Blank line
		// $texto.= "\x1b"."d".chr(1). "\n"; // Blank line
		// $texto.= "VA"; // Cut
		
		
		$texto ="@";
		$texto.=chr(27).'!'.chr(40)."  ".$_GET['tabla']."\n\n";
	
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$registro['folio']."\n\n";
	
		$texto.= $registro['fecha']." ".$registro['hora']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."TAQUILLERO: ".$registro['usuarios_nombre']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$registro['no_eco']."\n\n";
	
		$texto.=chr(27).'!'.chr(20)."(".$registro['operador'].')'.$registro['operadores_nombre']."\n\n";
		
		$texto.=chr(27).'!'.chr(40)."MONTO: ".number_format($registro['monto'],2)."\n\n";
		$texto.= "VA"; // Cut
		
		// /* Output an example receipt */
		// echo ESC."@"; // Reset to defaults
		// echo ESC."E".chr(1); // Bold
		// echo "FOO CORP Ltd.\n"; // Company
		// echo ESC."E".chr(0); // Not Bold
		// echo ESC."d".chr(1); // Blank line
		// echo "Receipt for whatever\n"; // Print text
		// echo ESC."d".chr(4); // 4 Blank lines
		
		// /* Bar-code at the end */
		// echo ESC."a".chr(1); // Centered printing
		
		// echo ESC."d".chr(1); // Blank line
		// echo "987654321\n"; // Print number
		// $texto.= " \x1d"."V\x41".chr(3); // Cut
		
		// $texto = "@@aHello World
		// !aESC/POS Printer Test
		// !aGoodbye World
		// VA"; 
		
		echo base64_encode ( $texto );
		exit(0);
		
		
		
	}
	else {
		echo "Error en ".$consulta.mysqli_Error($link);
		
	}
	
	
?>


