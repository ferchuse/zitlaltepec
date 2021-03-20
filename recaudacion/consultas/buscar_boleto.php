<?php 
	session_start();
	include('../../conexi.php');

	include('../../funciones/dame_permiso.php');
	$link = Conectarse();
	
	
	
	
	$res=mysqli_query($link, "SELECT * FROM costo_boletos_sencillos ORDER BY nombre");
	while($row=mysqli_fetch_array($res)){
		$array_costo[$row['cve']]=$row['nombre'];
	}
	// 0 1 2  3 4 5 6   7 8 9 10111213 
	// 9 0 1  0 0 1 0   0 0 5 8 9 6 6
	// 90100100129205
	
	
	/*
	90100100148739
	
	90100100058963
	90100100058964
	90100100058965
	90100100058966
	90100100058967
	*/
	
	
	$taq = intval(substr($_POST['boleto'],1,2));
	$costo = intval(substr($_POST['boleto'],3,4));
	$folio = intval(substr($_POST['boleto'],7,7));
	$resultado = array('error' => 0, 'mensaje' => '', 'html' => '');
	
	
	
	$buscar_boleto="
		SELECT *, 
		taquillas_sencillos.nombre as taquilla_nombre,
		DATEDIFF(CURDATE(), fecha) as dias 
		FROM boletos_sencillos 
		LEFT JOIN taquillas_sencillos 
		ON boletos_sencillos.taquilla = taquillas_sencillos.cve
		WHERE taquilla = '$taq' 
		AND folio='$folio'";
	
	
	$resultado['buscar_boleto'] = $buscar_boleto;
	
	$res = mysqli_query($link, $buscar_boleto);
	if($row = mysqli_fetch_array($res)){
		if($row['folio_recaudacion'] > 0){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto ya se poncho';
		}
		elseif($row['estatus']==1){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto esta cancelado';
		}
		elseif($row['estatus']==2){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto ya esta recaudado';
		}
		elseif($row['dias']>5){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto esta caducado';
		}
		else{
			//El boleto si existe
			
			// $resultado['html'] .= rowb(false);
			$resultado['html'] .= '<tr><td align="center">';
			$resultado['html'] .= "<input type='hidden'  name='taquilla[]' value='{$row['taquilla']}'>";
			$resultado['html'] .= "<input type='hidden' name='folio_boleto[]' value='{$row['folio']}'>";
			$resultado['html'] .= '<button class="btn btn-danger btn-sm btn_borrar   data-taquilla="'.$row['taquilla'].'" data-folio="'.$row['folio'].'" data-monto="'.$row['monto'].'" title="Quitar">
			<i class="fas fa-trash"></i> 
			</button>';
			$resultado['html'] .= '</td>';
			$resultado['html'] .= '<td align="left">'.utf8_encode($row['taquilla_nombre']).'</td>';
			$resultado['html'] .= '<td align="center">'.$row['folio'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['fecha'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['hora'].'</td>';
			$resultado['html'] .= '<td align="center">'.utf8_encode($array_costo[$row['costo']]).'</td>';
			$resultado['html'] .= '<td align="right" class="monto">'.number_format($row['monto'],2).'</td>';
			$resultado['html'] .= '</tr>';
			
			//Cambiar a recaudado
			
			$update_boleto =  "UPDATE boletos_sencillos SET estatus = 2 WHERE folio = '$folio'";
			
			$result_update_boleto = mysqli_query($link, $update_boleto);
			
			$resultado['update_boleto'] = $result_update_boleto ;
			
			
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['´buscar_boleto'] = $buscar_boleto;
		$resultado['mensaje'] = 'No se encontro el boleto';
	}
	
	
	
	echo json_encode($resultado);
	exit();
?>			