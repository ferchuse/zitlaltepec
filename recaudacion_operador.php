<?php
include("main.php");
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_empresa=array();
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_empresa[$row['cve']]=$row['nombre'];
	$array_empresalogo[$row['cve']]=$row['logo'];
}

$array_recaudacion=array();
$res=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_recaudacion[$row['cve']]=$row['nombre'];
}

$res=mysql_query("SELECT * FROM operadores ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_cveconductor[$row['cve']]=$row['cve'];
	$array_nomconductor[$row['cve']]=$row['nombre'];
}

$rsUnidad=mysql_query("SELECT * FROM unidades");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
	$array_empunidad[$Unidad['cve']]=$Unidad['empresa'];
}
$array_derrotero=array();
$res=mysql_query("SELECT * FROM derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derrotero[$row['cve']]=$row['nombre'];
	$array_derroteromutualidad[$row['cve']]=$row['mutualidad'];
}

$res=mysql_query("SELECT * FROM motivos_abono_extraordinario ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}

$res=mysql_query("SELECT * FROM cat_cargos_operadores ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_cargo[$row['cve']]=$row['nombre'];
	$array_cargomonto[$row['cve']]=$row['monto'];
}
$array_cargo[-1]='Pago Curso';

$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()");
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];

if($_POST['ajax']==1.1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio Recaudacion</th>';
	echo '<th>Fecha Recaudacion</th><th>Folio Tarjeta</th><th>Fecha Viaje</th><th>Empresa</th><th>Operador</th><th>Unidad</th>
	<th>Derrotero</th><th>Mutualidad</th><th>Reimpresiones Tarjetas</th><th>Detalle Reimpresiones</th><th>Abono Adeudo Operador</th>
	<th>Total Recaudado</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM recaudacion_operador a ";
	if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	if($_POST['no_eco']!=""){ 
		$select.=" INNER JOIN ".$pre."parque as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['tarjeta']!='') $select.=" AND a.tarjeta='".$_POST['tarjeta']."'";
	if($_POST['derrotero']!='all') $select.=" AND a.derrotero='".$_POST['derrotero']."'";
	if($_POST['empresa']!='all') $select.=" AND a.empresa='".$_POST['empresa']."'";
	if($_POST['recaudacion']!='all') $select.=" AND a.recaudacion='".$_POST['recaudacion']."'";
//	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'recaudacion_operador.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.utf8_encode($array_recaudacion[$row['recaudacion']]).'</td>';
		echo '<td align="center">'.$row['tarjeta'].'</td>';
		echo '<td align="center">'.$row['fecha_viaje'].'</td>';
		echo '<td align="center">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="left">'.utf8_encode($array_derrotero[$row['ruta']]).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['mutualidad']*$factor,2).'</td>';
		$totales[$c]+=round($row['mutualidad']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['reimpresion_tarjeta']*$factor,2).'</td>';
		$totales[$c]+=round($row['reimpresion_tarjeta']*$factor,2);$c++;
		echo '<td align="right">';
		if($row['reimpresion_tarjeta'] > 0){
			echo '<table with="100%"><tr><th>Folio</th><th>Fecha Viaje</th></tr>';
			$res1=mysql_query("SELECT * FROM recaudacion_operador_reimpresion WHERE recaudacion='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				echo '<tr>';
				echo '<td>'.$row1['tarjeta'].'</td>';
				echo '<td align="center">'.$row1['fecha_viaje'].'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		else{
			echo '&nbsp;';
		}
		echo '</td>';
		$totales[$c]='A';$c++;
		echo '<td align="right">'.number_format($row['abono_adeudo_operador']*$factor,0).'</td>';
		$totales[$c]+=round($row['abono_adeudo_operador']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="10" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		if($total=='A')
			echo '<th align="center">&nbsp;</th>';
		else
			echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th>&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio Recaudacion</th><th>Fecha Creacion</th>';
	echo '<th>Fecha Recaudacion</th><th>Recaudacion</th><!--<th>Tarjeta</th><th>Fecha Cuenta</th>-->
	<th>Unidad</th><th>Operador</th><th>Motivo</th><th>Monto</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM recaudacion_operador a ";
	
	//if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['clave']!="") $select.=" AND a.operador='".$_POST['clave']."'";
	if($_POST['cargo']!='all') $select.=" AND a.cargo='".$_POST['cargo']."'";
	if($_POST['recaudacion']!='all') $select.=" AND a.recaudacion='".$_POST['recaudacion']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'recaudacion_operador.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '<a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha_creacion'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.utf8_encode($array_recaudacion[$row['recaudacion']]).'</td>';
		//echo '<td align="center">'.$row['tarjeta'].'</td>';
		//echo '<td align="center">'.$row['fecha_viaje'].'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="left">'.utf8_encode($array_cargo[$row['cargo']]).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="8" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		if($total=='A')
			echo '<th align="center">&nbsp;</th>';
		else
			echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th>&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	$res=mysql_query("SELECT * FROM tarjetas_unidad WHERE cve='".$_POST['tarjeta']."'");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']=='C'){
			echo "0|1";
			exit();
		}
		$res1=mysql_query("SELECT * FROM recaudacion_operador WHERE tarjeta='".$_POST['tarjeta']."' AND cargo='".$_POST['cargo']."' AND estatus!='C'");
		if($row1=mysql_fetch_array($res1)){
			echo "0|2";
			exit();
		}
		echo $row['cve']."|";
		echo $row['fecha_viaje'].'|';
		echo $row['empresa'].'|';
		echo utf8_encode($array_empresa[$row['empresa']]).'|';
		echo $row['unidad'].'|';
		echo $array_unidad[$row['unidad']].'|';
		echo $row['operador'].'|';
		echo '('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'|';
		echo $row['derrotero'].'|'.$array_derrotero[$row['derrotero']];
	}
	else{
		echo "0|0";
	}
	exit();
}

if($_POST['ajax']==3){
	$res = mysql_query("SELECT * FROM operadores WHERE cve='".$_POST['clave']."'");
	if($row=mysql_fetch_array($res)){
		$res=mysql_query("SELECT a.* FROM tarjetas_unidad a LEFT JOIN recaudacion_operador b ON a.cve = b.tarjeta AND b.estatus!='C' 
		WHERE a.estatus='A' AND a.operador='".$row['cve']."' AND ISNULL(b.cve)");
		if(mysql_num_rows($res)>0){
			echo utf8_encode($row['nombre']).'<br><table><tr><th>Tarjeta</th><th>Fecha Viaje</th></tr>';
			while($row1=mysql_fetch_array($res)){
				echo '<tr><td>'.$row1['cve'].'</td><td align="center">'.$row1['fecha_viaje'].'</td></tr>';
				$total_reimpresion+=$array_cargo[2];
			}
			echo '</table>';
		}
		else{
			echo '-1';
		}
	}
	else{
		echo '0';
	}
	
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM recaudacion_operador WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(40)."  ".$array_cargo[$row['cargo']];
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=$row['fecha']." ".$row['hora'];
	//$texto.='|';
	//$texto.=chr(27).'!'.chr(10)."TARJETA: ".$row['tarjeta'];
	//$texto.='|';
	//$texto.="FECHA:    ".$row['fecha_viaje'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
	//$texto.='|';
	//$texto.=chr(27).'!'.chr(10)."DERROTERO: ".$array_derrotero[$row['derrotero']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
	/*$res1=mysql_query("SELECT * FROM recaudacion_operador_reimpresion WHERE recaudacion='".$row['cve']."'");
	while($row1=mysql_fetch_array($res1)){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."R. TAR. ".$row1['tarjeta'].": ".number_format($row1['monto'],2);
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."REIMPRESION TOTAL: ".number_format($_POST['reimpresion_tarjeta'],2);
	if($_POST['abono_adeudo_operador'] > 0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
	}*/
	//$texto.='|';
	//$texto.=chr(27).'!'.chr(10)."TOTAL: ".number_format($mutualidad,0);
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&copia=1&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$cverecaudacion).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}

if($_POST['cmd']==12){
	$continuar = true;
	//if($_POST['reg']==1){
		//$res = mysql_query("SELECT * FROM recaudacion_operador WHERE estatus!='C' AND cargo=1 AND unidad>0 AND unidad = '".$_POST['unidad']."' AND fecha='".$_POST['fecha']."'");
		$res = mysql_query("SELECT * FROM recaudacion_operador WHERE estatus!='C' AND cargo='".$_POST['reg']."' AND tarjeta = '".$_POST['tarjeta']."'");
		if(mysql_num_rows($res) > 0)
			$continuar = false;
	//}
	if($continuar){
		$monto=$array_cargomonto[$_POST['reg']];
		if($_POST['reg']==1 && $array_derroteromutualidad[$_POST['derrotero']] > 0){
			$monto = $array_derroteromutualidad[$_POST['derrotero']];
		}
		mysql_query("INSERT recaudacion_operador SET tarjeta='".$_POST['tarjeta']."',fecha_viaje='".$_POST['fecha_viaje']."',fecha='".$_POST['fecha']."',
			fecha_creacion='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$monto."',mutualidad='".$mutualidad."',reimpresion_tarjeta='".$_POST['reimpresion_tarjeta']."',
			recaudacion='".$_POST['recaudacion']."',cargo='".$_POST['reg']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		/*foreach($_POST['reimpresionmonto'] as $k=>$v){
			mysql_query("INSERT recaudacion_operador_reimpresion SET recaudacion='$cverecaudacion',tarjeta='$k',
				fecha_viaje='".$_POST['reimpresionfecha'][$k]."',monto='$v'");
		}*/
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(40)."  ".$array_cargo[$_POST['reg']];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
		$texto.='|';
		$texto.=fechaLocal()." ".horaLocal();
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."TARJETA: ".$_POST['tarjeta'];
		//$texto.='|';
		//$texto.="FECHA:    ".$_POST['fecha_viaje'];
		//$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."DERROTERO: ".$array_derrotero[$row['derrotero']];
		//$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($monto,2);
		/*$res1=mysql_query("SELECT * FROM recaudacion_operador_reimpresion WHERE recaudacion='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."R. TAR. ".$row1['tarjeta'].": ".number_format($row1['monto'],2);
		}
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."REIMPRESION TOTAL: ".number_format($_POST['reimpresion_tarjeta'],2);
		if($_POST['abono_adeudo_operador'] > 0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
		}*/
		//$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."TOTAL: ".number_format($monto,0);
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$cverecaudacion).'" width=200 height=200></iframe>';
	}
	else{
		echo '<script>alert("La tarjeta ya tiene '.$array_cargo[$_POST['reg']].'");</script>';
	}
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}


top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE recaudacion_operador SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$continuar=true;
	if($_POST['cargo']==1){
		$res = mysql_query("SELECT * FROM recaudacion_operador WHERE estatus!='C' AND cargo=1 AND unidad>0 AND unidad = '".$_POST['unidad']."' AND fecha='".$_POST['fecha']."'");
		//$res = mysql_query("SELECT * FROM recaudacion_operador WHERE estatus!='C' AND cargo='".$_POST['cargo']."' AND tarjeta = '".$_POST['tarjeta']."'");
		if(mysql_num_rows($res)>0)
			$continuar=false;
	}
	if($continuar){
		//mysql_query("INSERT usuario_recaudacion SET usuario='".$_POST['cveusuario']."',recaudacion='".$_POST['recaudacion']."',fecha=CURDATE()");
		mysql_query("INSERT recaudacion_operador SET tarjeta='".$_POST['tarjeta']."',fecha_viaje='".$_POST['fecha_viaje']."',
			fecha_creacion='".fechaLocal()."',
			fecha='".$_POST['fecha']."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$_POST['monto']."',mutualidad='".$_POST['mutualidad']."',reimpresion_tarjeta='".$_POST['reimpresion_tarjeta']."',
			saldo_adeudo_operador='".$_POST['saldo_adeudo_operador']."',abono_adeudo_operador='".$_POST['abono_adeudo_operador']."',
			recaudacion='".$_POST['recaudacion']."',cargo='".$_POST['cargo']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		/*foreach($_POST['reimpresionmonto'] as $k=>$v){
			mysql_query("INSERT recaudacion_operador_reimpresion SET recaudacion='$cverecaudacion',tarjeta='$k',
				fecha_viaje='".$_POST['reimpresionfecha'][$k]."',monto='$v'");
		}*/
		$texto =chr(27)."@".'|';
		//$texto.=chr(27).'!'.chr(10)."RECAUDACION OPERADOR";
		$texto.=chr(27).'!'.chr(40)."  ".$array_cargo[$_POST['reg']];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
		$texto.='|';
		$texto.=fechaLocal()." ".horaLocal();
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."TARJETA: ".$_POST['tarjeta'];
		//$texto.='|';
		//$texto.="FECHA:    ".$_POST['fecha_viaje'];
		//$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$_POST['unidad']]; // cargar todos las unidades y operadores en un array es una completa estupidez, solo haz un leftjoin
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$_POST['operador']].')'.$array_nomconductor[$_POST['operador']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."DERROTERO: ".$array_derrotero[$_POST['derrotero']];
		//$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($_POST['monto'],2);
		/*$res1=mysql_query("SELECT * FROM recaudacion_operador_reimpresion WHERE recaudacion='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."R. TAR. ".$row1['tarjeta'].": ".number_format($row1['monto'],2);
		}
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."REIMPRESION TOTAL: ".number_format($_POST['reimpresion_tarjeta'],2);
		if($_POST['abono_adeudo_operador'] > 0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
		}
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TOTAL: ".number_format($_POST['monto'],0);*/
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	}
	else{
		echo '<script>alert("La tarjeta ya tiene '.$array_cargo[$_POST['cargo']].'");</script>';
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1.1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1 && $_POST['reg']==0)
		echo '<td><a href="#" onClick="
				if(document.forma.recaudacion.value==\'0\'){
					alert(\'Necesita seleccionar la recaudacion\');
				}
				else if(document.forma.tarjeta.value==\'\'){
					alert(\'No se ha cargado correctamente la tarjeta\');
				}
				else{
					atcr(\'recaudacion_operador.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<input type="hidden" name="plaza" id="plaza" value="14">';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly></td></tr>';
	echo '<tr><th align="left">Recaudacion</th><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="0">Seleccione</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Busqueda de Tarjeta por Clave de operador</th><td><input type="text" name="clave" id="clave" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeOpe();}"></td></tr>';
	echo '<tr><th align="left">Tarjetas Operador</th><td id="tdtarjetas"></td></tr>';
	echo '<input type="hidden" name="tarjeta" id="tarjeta" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="operador" id="operador" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="empresa" id="empresa" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<tr><th align="left">Folio del Tarjeta</th><td><input type="text" name="folio" id="folio" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeViaje();}"></td></tr>';
	echo '<tr><th align="left">Fecha Viaje</td><td><input type="text" class="readOnly tarjetas" size="15" name="fecha_viaje" id="fecha_viaje" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Empresa</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomempresa" id="nomempresa" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Unidad</td><td><input type="text" class="readOnly tarjetas" size="10" name="no_eco" id="no_eco" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Operador</td><td><input type="text" class="readOnly tarjetas" size="50" name="nomoperador" id="nomoperador" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Derrotero</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomderrotero" id="nomderrotero" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Mutualidad</td><td><input type="text" class="readOnly tarjetas" size="15" name="mutualidad" id="mutualidad" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Reimpresion Tarjeta</td><td id="tdreimpresion"></td></tr>';
	echo '<tr><th align="left">Total Reimpresion Tarjeta</td><td><input type="text" class="readOnly tarjetas" size="15" name="reimpresion_tarjeta" id="reimpresion_tarjeta" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Saldo Adeudo Operador</td><td><input type="text" class="readOnly tarjetas" size="15" name="saldo_adeudo_operador" id="saldo_adeudo_operador" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Abono Adeudo Operador</td><td><input type="text" class="textField" size="15" name="abono_adeudo_operador" id="abono_adeudo_operador" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Total Recaudado</td><td><input type="text" class="readOnly" size="15" name="monto" id="monto" value="" readOnly></td></tr>';
	echo '</table>';
	
	echo '<script>

				function calcular(){
					if((document.forma.abono_adeudo_operador.value/1) > (document.forma.saldo_adeudo_operador.value/1)){
						document.forma.abono_adeudo_operador.value = document.forma.saldo_adeudo_operador.value;
					}
					var total = 0;
					total = (document.forma.mutualidad.value/1)+(document.forma.reimpresion_tarjeta.value/1)+(document.forma.abono_adeudo_operador.value/1);
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","recaudacion_operador.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&plaza="+document.forma.plaza.value+"&tarjeta="+document.forma.folio.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									if(opciones2[1]=="0")
										alert("El folio no existe");
									else if(opciones2[1]=="1"){
										alert("El folio ya fue recaudado");
									}
									else if(opciones2[1]=="2"){
										alert("El folio ya se le pago la mutualidad");
									}
									$(".tarjetas").each(function(){
										this.value="";
									});
									$("#tdreimpresion").html("");
								}
								else{
									document.forma.tarjeta.value=opciones2[0];
									document.forma.fecha_viaje.value=opciones2[1];
									document.forma.empresa.value=opciones2[2];
									document.forma.nomempresa.value=opciones2[3];
									document.forma.unidad.value=opciones2[4];
									document.forma.no_eco.value=opciones2[5];
									document.forma.operador.value=opciones2[6];
									document.forma.nomoperador.value=opciones2[7];
									document.forma.derrotero.value=opciones2[8];
									document.forma.nomderrotero.value=opciones2[9];
									document.forma.mutualidad.value=opciones2[10];
									$("#tdreimpresion").html(opciones2[11]);
									document.forma.reimpresion_tarjeta.value=opciones2[12];
									document.forma.saldo_adeudo_operador.value=opciones2[14];
								}
								calcular();
							}
						}
					}
				}

				function traeOpe(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","recaudacion_operador.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=3&plaza="+document.forma.plaza.value+"&clave="+document.forma.clave.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								opciones2 = objeto1.responseText;
								if(opciones2=="0"){
									alert("La clave del operador no existe");
									$("#tdtarjetas").html("");
								}
								else if(opciones2=="-1"){
									alert("El operador no tiene tarjetas pendientes de pago de mutualidad");
									$("#tdtarjetas").html("");
								}
								else{
									$("#tdtarjetas").html(opciones2);
								}
							}
						}
					}
				}
				
				
		</script>';
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	$nivelUsuario=nivelUsuario();
	if($nivelUsuario>1)
		echo '<td><a href="#" onClick="
				if(document.forma.recaudacion.value==\'0\'){
					alert(\'Necesita seleccionar la recaudacion\');
				}
				else if(document.forma.cargo.value==\'\')
					alert(\'No ha cargado correctamente el motivo\');
				else if(document.forma.unidad.value==\'\')
					alert(\'No ha cargado correctamente la unidad\');
				else if(document.forma.operador.value==\'\')
					alert(\'No ha cargado correctamente el operador\');
				else{
					atcr(\'recaudacion_operador.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="viene_recaudacion" value="'.$_GET['viene_recaudacion'].'">';
	echo '<table>';
	echo '<tr><td class="tableEnc">Recaudacion Operador</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	if($nivelUsuario>2){
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	}
	echo '</td></tr>';
	echo '<tr><th align="left">Recaudacion</th><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="0">Seleccione</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="cargo" id="cargo" class="textField"><option value="">Seleccione</option>';
	foreach($array_cargo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['cargo']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Operador</th><td><select name="operador" id="operador" class="textField"><option value="">Seleccione</option>';
	foreach($array_nomconductor as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['operador']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad" onChange="
	document.forma.unidad.value=\'\';
	document.forma.no_eco.value=\'\';
	document.forma.empresa.value=\'\';
	document.forma.nomempresa.value=\'\';
	document.forma.derrotero.value=\'\';
	document.forma.nomderrotero.value=\'\';">';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['localidad']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="'.$row['unidad'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="'.$array_unidad[$row['unidad']].'" class="textField" onKeyUp="if(event.keyCode==13){ traeUni();}"></td></tr>';
	echo '<input type="hidden" name="empresa" id="empresa" size="10" value="'.$row['empresa'].'" class="readOnly" readOnly>';
	echo '<tr style="display:none;"><th align="left">Empresa</th><td><input type="text" name="nomempresa" id="nomempresa" size="50" value="'.$array_empresa[$row['empresa']].'" class="readOnly" readOnly></td></tr>';
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="'.$row['derrotero'].'" class="readOnly" readOnly>';
	echo '<tr style="display:none;"><th align="left">Derrotero</th><td><input type="text" name="nomderrotero" id="nomderrotero" size="50" value="'.$array_derrotero[$row['derrotero']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '</table>';
	
	echo '<script>
				function traeUni(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","tarjetas_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&localidad="+document.forma.localidad.value+"&no_eco="+document.forma.no_eco.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText;
								if(opciones2=="0"){
									alert("La unidad no existe");
									document.forma.no_eco.value="";
									document.forma.unidad.value="";
									document.forma.empresa.value="";
									document.forma.nomempresa.value="";
									document.forma.derrotero.value="";
									document.forma.nomderrotero.value="";
									document.forma.no_eco.focus();
								}
								else{
									var opciones3=objeto1.responseText.split("|");
									if(opciones3[1]=="1"){
										document.forma.unidad.value=opciones3[0];
										document.forma.empresa.value=opciones3[2];
										document.forma.nomempresa.value=opciones3[3];
										document.forma.derrotero.value=opciones3[4];
										document.forma.nomderrotero.value=opciones3[5];
										document.forma.monto.focus();
									}
									else if(opciones3[1]=="2"){
										alert("La unidad esta dada de baja");
										document.forma.no_eco.value="";
										document.forma.unidad.value="";
										document.forma.empresa.value="";
										document.forma.nomempresa.value="";
										document.forma.derrotero.value="";
										document.forma.nomderrotero.value="";
										document.forma.no_eco.focus();
									}
									else{
										alert("La unidad esta inactiva");
										document.forma.no_eco.value="";
										document.forma.unidad.value="";
										document.forma.derrotero.value="";
										document.forma.nomderrotero.value="";
										document.forma.no_eco.focus();
									}
								}
							}
						}
					}
				}
								
				function traeCond(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","tarjetas_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=4&empresa="+document.forma.empresa.value+"&credencial="+document.forma.credencial.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									alert("El operador no existe");
									document.forma.credencial.value="";
									document.forma.nomcond.value="";
									document.forma.operador.value="";
									document.forma.credencial.focus();
								}
								else{
									if(opciones2[2]=="1"){
										document.forma.operador.value=opciones2[0];
										document.forma.nomcond.value=opciones2[1];
										document.forma.credencial.focus();
									}
									else if(opciones2[2]=="2"){
										alert("El operador esta dado de baja");
										document.forma.credencial.value="";
										document.forma.nomcond.value="";
										document.forma.operador.value="";
										document.forma.credencial.focus();
									}
									else{
										alert("El operador esta inactivo");
										document.forma.credencial.value="";
										document.forma.nomcond.value="";
										document.forma.operador.value="";
										document.forma.credencial.focus();
									}
								}
							}
						}
					}
				}
				
				
		</script>';
}

if($_POST['cmd']==1.2){
	echo '<table>';
	echo '<tr>';
	$nivelUsuario=nivelUsuario();
	if($nivelUsuario>1)
		echo '<td><a href="#" onClick="
				if(document.forma.recaudacion.value==\'0\'){
					alert(\'Necesita seleccionar la recaudacion\');
				}
				else if(document.forma.cargo.value==\'\')
					alert(\'No ha cargado correctamente el motivo\');
				else if(document.forma.tarjeta.value==\'\')
					alert(\'No ha cargado correctamente la unidad\');
				else{
					atcr(\'recaudacion_operador.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<input type="hidden" name="viene_recaudacion" value="'.$_GET['viene_recaudacion'].'">';
	echo '<table>';
	echo '<tr><td class="tableEnc">Recaudacion Operador</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	if($nivelUsuario>2){
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	}
	echo '</td></tr>';
	echo '<tr><th align="left">Recaudacion</th><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="0">Seleccione</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="cargo" id="cargo" class="textField"><option value="">Seleccione</option>';
	foreach($array_cargo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['cargo']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="tarjeta" id="tarjeta" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="operador" id="operador" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="empresa" id="empresa" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<tr><th align="left">Folio del Tarjeta</th><td><input type="text" name="folio" id="folio" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeViaje();}"></td></tr>';
	echo '<tr style="display:none;"><th align="left">Fecha Viaje</td><td><input type="text" class="readOnly tarjetas" size="15" name="fecha_viaje" id="fecha_viaje" value="" readOnly></td></tr>';
	echo '<tr style="display:none;"><th align="left">Empresa</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomempresa" id="nomempresa" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Operador</td><td><input type="text" class="readOnly tarjetas" size="50" name="nomoperador" id="nomoperador" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Unidad</td><td><input type="text" class="readOnly tarjetas" size="10" name="no_eco" id="no_eco" value="" readOnly></td></tr>';
	echo '<tr style="display:none;"><th align="left">Derrotero</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomderrotero" id="nomderrotero" value="" readonly></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '</table>';
	
	echo '<script>


				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","recaudacion_operador.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&tarjeta="+document.forma.folio.value+"&cargo="+document.forma.cargo.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									if(opciones2[1]=="0")
										alert("El folio no existe");
									else if(opciones2[1]=="1"){
										alert("El folio esta cancelado");
									}
									else if(opciones2[1]=="2"){
										alert("El folio ya se le pago el cargo");
									}
									$(".tarjetas").each(function(){
										this.value="";
									});
								}
								else{
									document.forma.tarjeta.value=opciones2[0];
									document.forma.fecha_viaje.value=opciones2[1];
									document.forma.empresa.value=opciones2[2];
									document.forma.nomempresa.value=opciones2[3];
									document.forma.unidad.value=opciones2[4];
									document.forma.no_eco.value=opciones2[5];
									document.forma.operador.value=opciones2[6];
									document.forma.nomoperador.value=opciones2[7];
									document.forma.derrotero.value=opciones2[8];
									document.forma.nomderrotero.value=opciones2[9];
								}
							}
						}
					}
				}

				function traeUni(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","tarjetas_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&empresa="+document.forma.empresa.value+"&no_eco="+document.forma.no_eco.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText;
								if(opciones2=="0"){
									alert("La unidad no existe");
									document.forma.no_eco.value="";
									document.forma.unidad.value="";
									document.forma.empresa.value="";
									document.forma.nomempresa.value="";
									document.forma.derrotero.value="";
									document.forma.nomderrotero.value="";
									document.forma.no_eco.focus();
								}
								else{
									var opciones3=objeto1.responseText.split("|");
									if(opciones3[1]=="1"){
										document.forma.unidad.value=opciones3[0];
										document.forma.empresa.value=opciones3[2];
										document.forma.nomempresa.value=opciones3[3];
										document.forma.derrotero.value=opciones3[4];
										document.forma.nomderrotero.value=opciones3[5];
										document.forma.monto.focus();
									}
									else if(opciones3[1]=="2"){
										alert("La unidad esta dada de baja");
										document.forma.no_eco.value="";
										document.forma.unidad.value="";
										document.forma.empresa.value="";
										document.forma.nomempresa.value="";
										document.forma.derrotero.value="";
										document.forma.nomderrotero.value="";
										document.forma.no_eco.focus();
									}
									else{
										alert("La unidad esta inactiva");
										document.forma.no_eco.value="";
										document.forma.unidad.value="";
										document.forma.derrotero.value="";
										document.forma.nomderrotero.value="";
										document.forma.no_eco.focus();
									}
								}
							}
						}
					}
				}
								
				function traeCond(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","tarjetas_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=4&empresa="+document.forma.empresa.value+"&credencial="+document.forma.credencial.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									alert("El operador no existe");
									document.forma.credencial.value="";
									document.forma.nomcond.value="";
									document.forma.operador.value="";
									document.forma.credencial.focus();
								}
								else{
									if(opciones2[2]=="1"){
										document.forma.operador.value=opciones2[0];
										document.forma.nomcond.value=opciones2[1];
										document.forma.credencial.focus();
									}
									else if(opciones2[2]=="2"){
										alert("El operador esta dado de baja");
										document.forma.credencial.value="";
										document.forma.nomcond.value="";
										document.forma.operador.value="";
										document.forma.credencial.focus();
									}
									else{
										alert("El operador esta inactivo");
										document.forma.credencial.value="";
										document.forma.nomcond.value="";
										document.forma.operador.value="";
										document.forma.credencial.focus();
									}
								}
							}
						}
					}
				}
				
				
		</script>';
}

if($_POST['cmd']==0){
	if($impresio!=""){
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'recaudacion_operador.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'recaudacion_operador.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
		</tr>';
	echo '</table>';
	echo '<table>';
	$dia=date("w");
	if($dia==0) $restar=6;
	else $restar=$dia-1;
	$fechaini=date( "Y-m-d" , strtotime ( "-".$restar." day" , strtotime(fechaLocal())));
	$fechafin=date( "Y-m-d" , strtotime ( "+6 day" , strtotime($fechaini)));
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr style="display:none;"><td>Tarjeta</td><td><input type="text" name="tarjeta" id="tarjeta" class="textField" size="12" value=""></td></tr>';
	echo '<tr style="display:none;"><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Credencial</td><td><input type="text" name="clave" id="clave" class="textField" size="12" value=""></td></tr>';
	echo '<tr style="display:none;"><td>No. Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="12" value=""></td></tr>';
	echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">--- Todos ---</option>';
	foreach($array_empresa as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Motivo</td><td><select name="cargo" id="cargo"><option value="all">--- Todos ---</option>';
	foreach($array_cargo as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Recaudacion</td><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="all">--- Todos ---</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
	foreach($array_usuario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';

echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recaudacion_operador.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&cargo="+document.getElementById("cargo").value+"&recaudacion="+document.getElementById("recaudacion").value+"&empresa="+document.getElementById("empresa").value+"&tarjeta="+document.getElementById("tarjeta").value+"&derrotero="+document.getElementById("derrotero").value+"&no_eco="+document.getElementById("no_eco").value+"&clave="+document.getElementById("clave").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}';	
	if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
	
	</Script>
';
}
bottom();

?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script >
	$('#operador').select2();
	
</script>