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

$res=mysql_query("SELECT * FROM operadores");
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
}

$res=mysql_query("SELECT * FROM motivos_abono_extraordinario ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}


$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()") or die(mysql_error());
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];
if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();

	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM recaudacion_unidad a ";
	if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	if($_POST['no_eco']!="" || $_POST['ordenamiento']==1){ 
		$select.=" INNER JOIN ".$pre."unidades as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha_creacion>='".$_POST['fecha_ini']."' AND a.fecha_creacion<='".$_POST['fecha_fin']."'";
	if($_POST['tarjeta']!='') $select.=" AND a.tarjeta='".$_POST['tarjeta']."'";
	if($_POST['derrotero']!='all') $select.=" AND a.derrotero='".$_POST['derrotero']."'";
	if($_POST['empresa']!='all') $select.=" AND a.empresa='".$_POST['empresa']."'";
	if($_POST['recaudacion']!='all') $select.=" AND a.recaudacion='".$_POST['recaudacion']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	if($_POST['ordenamiento']==1){
		if($_POST['orden']==1){
			$select.=" ORDER BY e.no_eco, a.cve DESC";
			$orden1=2;
		}
		else{
			$select.=" ORDER BY e.no_eco DESC, a.cve DESC";
			$orden1=1;
		}	
	}
	else{
		$orden1 = 1;
		$select.=" ORDER BY a.cve DESC";
	}
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio Recaudacion</th><th>Fecha Captura</th>';
	echo '<th>Fecha Recaudacion</th><th>Recaudacion</th><th>Folio Tarjeta</th><th>Fecha Viaje</th><th>Empresa</th><th>Operador</th><th><a href="#" onclick="buscarRegistros(1,'.$orden1.');">Unidad</a></th>
	<th>Derrotero</th><th>Vueltas</th><th>Cuenta</th><th>Condonacion</th><th>Total a Recaudar</th><th>Cant. Boletos</th><th>Importe Boletos</th>
	<th>Abono Extraordinario</th><th>Motivo</th><th>Efectivo</th><th>Total Recaudado</th><th>Adeudo Operador</th><th>Abono Adeudo Operador</th><th>Usuario</th>
	</tr>'; 
	
	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'recaudacion_unidad.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'recaudacion_unidad.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
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
		echo '<td align="center">'.$row['tarjeta'].'</td>';
		echo '<td align="center">'.$row['fecha_viaje'].'</td>';
		echo '<td align="center">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
		echo '<td align="center">'.($row['vueltas']*$factor).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['monto_derrotero']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_derrotero']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_condonaciones']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_condonaciones']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_recaudar']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_recaudar']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['cant_boletos']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_boletos']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_boletos']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_boletos']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_extraordinario']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_extraordinario']*$factor,2);$c++;
		echo '<td align="left">'.utf8_encode($array_motivos[$row['motivo_extraordinario']]).'</td>';
		$totales[$c]='A';$c++;
		echo '<td align="right">'.number_format($row['monto_efectivo']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_efectivo']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_adeudo_operador']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_adeudo_operador']*$factor,2);$c++;
		//echo '<td align="right">'.number_format($row['cargo_extraordinario_operador']*$factor,2).'</td>';
		//$totales[$c]+=round($row['cargo_extraordinario_operador']*$factor,2);$c++;
		//echo '<td align="left">'.utf8_encode($row['obs_cargo_extraordinario']).'</td>';
		//$totales[$c]='A';$c++;
		echo '<td align="right">'.number_format($row['abono_adeudo_operador']*$factor,2).'</td>';
		$totales[$c]+=round($row['abono_adeudo_operador']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="12" align="left">'.$x.' Registro(s)</th>';
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
		if($row['estatus']!='A'){
			echo "0|1";
			exit();
		}
		/*$res1=mysql_query("SELECT * FROM recaudacion_operador WHERE tarjeta='".$_POST['tarjeta']."' AND estatus!='C'");
		if(!$row1=mysql_fetch_array($res1)){
			echo "0|2";
			exit();
		}*/
		echo $row['cve']."|";
		echo $row['fecha_viaje'].'|';
		echo $row['empresa'].'|';
		echo utf8_encode($array_empresa[$row['empresa']]).'|';
		echo $row['unidad'].'|';
		echo $array_unidad[$row['unidad']].'|';
		echo $row['operador'].'|';
		echo '('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'|';
		echo $row['derrotero'].'|';
		echo utf8_encode($array_derrotero[$row['derrotero']]).'|';
		echo $row['monto'].'|';
		$res1=mysql_query("SELECT SUM(b.monto) FROM condonacion_tarjetas_unidad a INNER JOIN condonacion_tarjetas_unidad_detalle b ON a.cve = b.condonacion WHERE a.unidad='".$row['unidad']."' AND a.estatus!='C' AND b.tarjeta='".$_POST['tarjeta']."'");
		$row1=mysql_fetch_array($res1);	
		echo round($row1[0],2).'|';
		echo round($row['monto']-$row1[0],2).'|';
		$res1=mysql_query("SELECT SUM(monto_adeudo_operador),SUM(abono_adeudo_operador) FROM recaudacion_unidad WHERE estatus!='C' AND operador='".$row['operador']."' AND unidad='".$row['unidad']."'");
		$row1=mysql_fetch_array($res1);
		$res2=mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE estatus!='C' AND operador='".$row['operador']."' AND unidad='".$row['unidad']."'");
		$row2=mysql_fetch_array($res2);
		echo round($row1[0]-$row1[1]-$row2[0],2).'|';
	}
	else{
		echo "0|0";
	}
	exit();
}

if($_POST['ajax']==3){
	$res=mysql_query("SELECT * FROM recaudacion_unidad WHERE estatus!='C' AND tarjeta='".$_POST['tarjeta']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else
		echo "0";
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM recaudacion_unidad WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(10)."RECAUDACION";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$_POST['reg'];
	$texto.='|';
	$texto.=fechaLocal()." ".horaLocal();
	$texto.='|';
	//$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
	//$texto.='|';
	$texto.=chr(27).'!'.chr(10)."TARJETA: ".$row['tarjeta'];
	$texto.='|';
	$texto.="FECHA CUENTA:    ".$row['fecha_viaje'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."RECAUDACION: ".$array_recaudacion[$row['recaudacion']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
	$texto.='|';
	//$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$_POST['operador']].')'.$array_nomconductor[$_POST['operador']];
	$texto.=chr(27).'!'.chr(10)."OPERADOR: ".$array_cveconductor[$row['operador']];
	/*$texto.='|';
	$texto.=chr(27).'!'.chr(10)."DEERROTERO: ".$array_derrotero[$_POST['derrotero']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."VUELTAS: ".number_format(floatval($_POST['vueltas']),0);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."CUENTA: ".number_format($_POST['monto_derrotero'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."CONDONACION: ".number_format($_POST['monto_condonaciones'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."A RECAUDAR: ".number_format($_POST['monto_recaudar'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."CANT. BOLETOS: ".number_format(floatval($_POST['cant_boletos']),0);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."IMP. BOLETOS: ".number_format(floatval($_POST['monto_boletos']),2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."MOTIVO EXT: ".$array_motivos[$_POST['motivo_extraordinario']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."ABONO EXT: ".number_format(floatval($_POST['monto_extraordinario']),2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."EFECTIVO: ".number_format(floatval($_POST['monto_efectivo']),2);*/
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."TOTAL RECAUDADO: ".number_format($row['monto'],2);
	$texto.='|';
	/*$texto.=chr(27).'!'.chr(10)."ADEUDO OPERADOR: ".number_format($_POST['monto_adeudo_operador'],2);
	if($_POST['abono_adeudo_operador'] > 0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
	}*/
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE recaudacion_unidad SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT tarjeta FROM recaudacion_unidad WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE tarjetas_unidad SET estatus='A' WHERE cve='".$row[0]."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	//mysql_query("INSERT usuario_recaudacion SET usuario='".$_POST['cveusuario']."',recaudacion='".$_POST['recaudacion']."',fecha=CURDATE()");
	$res = mysql_query("SELECT * FROM recaudacion_unidad WHERE estatus!='C' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	if($row['tarjeta']!=$_POST['tarjeta']){
		mysql_query("UPDATE tarjetas_unidad SET estatus='P' WHERE cve='".$_POST['tarjeta']."'");
		mysql_query("INSERT recaudacion_unidad SET tarjeta='".$_POST['tarjeta']."',fecha_viaje='".$_POST['fecha_viaje']."',fecha='".$_POST['fecha']."',
			fecha_creacion='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$_POST['monto']."',monto_derrotero='".$_POST['monto_derrotero']."',monto_condonaciones='".$_POST['monto_condonaciones']."',
			monto_recaudar='".$_POST['monto_recaudar']."',cant_boletos='".$_POST['cant_boletos']."',vueltas='".$_POST['vueltas']."',
			monto_boletos='".$_POST['monto_boletos']."',monto_extraordinario='".$_POST['monto_extraordinario']."',
			motivo_extraordinario='".$_POST['motivo_extraordinario']."',monto_efectivo='".$_POST['monto_efectivo']."',
			monto_adeudo_operador='".$_POST['monto_adeudo_operador']."',saldo_adeudo_operador='".$_POST['saldo_adeudo_operador']."',
			abono_adeudo_operador='".$_POST['abono_adeudo_operador']."',recaudacion='".$_POST['recaudacion']."',
			cargo_extraordinario_operador='".$_POST['cargo_extraordinario_operador']."',
			obs_cargo_extraordinario='".$_POST['obs_cargo_extraordinario']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)."RECAUDACION";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
		$texto.='|';
		$texto.=fechaLocal()." ".horaLocal();
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
		//$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TARJETA: ".$_POST['tarjeta'];
		$texto.='|';
		$texto.="FECHA CUENTA:    ".$_POST['fecha_viaje'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."RECAUDACION: ".$array_recaudacion[$_POST['recaudacion']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$_POST['unidad']];
		$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$_POST['operador']].')'.$array_nomconductor[$_POST['operador']];
		$texto.=chr(27).'!'.chr(10)."OPERADOR: ".$array_cveconductor[$_POST['operador']];
		/*$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DEERROTERO: ".$array_derrotero[$_POST['derrotero']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."VUELTAS: ".number_format(floatval($_POST['vueltas']),0);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."CUENTA: ".number_format($_POST['monto_derrotero'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."CONDONACION: ".number_format($_POST['monto_condonaciones'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."A RECAUDAR: ".number_format($_POST['monto_recaudar'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."CANT. BOLETOS: ".number_format(floatval($_POST['cant_boletos']),0);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."IMP. BOLETOS: ".number_format(floatval($_POST['monto_boletos']),2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MOTIVO EXT: ".$array_motivos[$_POST['motivo_extraordinario']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."ABONO EXT: ".number_format(floatval($_POST['monto_extraordinario']),2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."EFECTIVO: ".number_format(floatval($_POST['monto_efectivo']),2);*/
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TOTAL RECAUDADO: ".number_format($_POST['monto'],2);
		$texto.='|';
		/*$texto.=chr(27).'!'.chr(10)."ADEUDO OPERADOR: ".number_format($_POST['monto_adeudo_operador'],2);
		if($_POST['abono_adeudo_operador'] > 0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
		}*/
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
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
				else if((document.forma.abono_adeudo_operador.value/1)<0){
					alert(\'El abono a adeudo del operador no puede ser menor a cero\');
				}
				else{
					atcr(\'recaudacion_unidad.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="if(document.forma.tarjeta.value==\'\'){
					alert(\'No se ha cargado correctamente la tarjeta\');
				}
				else{ atcr(\'tarjetas_unidad.php?viene_recaudacion=1\',\'_blank\',\'1\',\'0\');}"><img src="images/nuevo.gif" border="0">&nbsp;Generar Tarjeta</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'recaudacion_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<input type="hidden" name="plaza" id="plaza" value="14">';
	if(nivelUsuario()>2){echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	echo'&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo'</td></tr>';
}else{
	echo '<input type="hidden" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	
}
	
	echo '<tr><th align="left">Recaudacion</th><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="0">Seleccione</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="tarjeta" id="tarjeta" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="operador" id="operador" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="empresa" id="empresa" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="" class="readOnly tarjetas" readOnly>';
	echo '<tr><th align="left">Folio del Tarjeta</th><td><input type="text" name="folio" id="folio" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeViaje();}"></td></tr>';
	echo '<tr><th align="left">Fecha Viaje</td><td><input type="text" class="readOnly tarjetas" size="15" name="fecha_viaje" id="fecha_viaje" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Empresa</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomempresa" id="nomempresa" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Unidad</td><td><input type="text" class="readOnly tarjetas" size="10" name="no_eco" id="no_eco" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Operador</td><td><input type="text" class="readOnly tarjetas" size="50" name="nomoperador" id="nomoperador" value="" readOnly>&nbsp;<input type="button" value="Generar Mutualidad" onClick="generaCargo(1)"><!--&nbsp;<input type="button" value="Generar Reimpresion de Tarjeta" onClick="generaCargo(2)">&nbsp;<input type="button" value="Generar Multa por Servicios" onClick="generaCargo(3)">-->&nbsp;<input type="button" value="Generar Seguridad" onClick="generaCargo(4)"></td></tr>';
	echo '<tr><th align="left">Derrotero</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomderrotero" id="nomderrotero" value="" readonly></td></tr>';
	echo '<tr><th align="left">Vueltas</td><td><input type="text" class="textField" size="15" name="vueltas" id="vueltas" value=""></td></tr>';
	echo '<tr><th align="left">Cuenta</td><td><input type="text" size="15" name="monto_derrotero" id="monto_derrotero" value="" onKeyUp="calcular()"';if(nivelUsuario()<3){echo'class="readOnly tarjetas" readonly ';} echo'></td></tr>';
	echo '<tr><th align="left">Condonacion</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_condonaciones" id="monto_condonaciones" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Total</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_recaudar" id="monto_recaudar" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Cant. Boletos</td><td><input type="text" class="textField" size="15" name="cant_boletos" id="cant_boletos" value=""></td></tr>';
	echo '<tr><th align="left">Importe Boletos</td><td><input type="text" class="textField" size="15" name="monto_boletos" id="monto_boletos" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr style="display:none;"><th align="left">Motivo Abono Extraordinario</th><td><select name="motivo_extraordinario" id="motivo_extraordinario" onChange="activar_extraordinario()"><option value="0">--- Seleccione ---</option>';
	foreach($array_motivos as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><th align="left">Abono Extraordinario</td><td><input type="text" class="readOnly" size="15" name="monto_extraordinario" id="monto_extraordinario" value="" onKeyUp="calcular()" readOnly></td></tr>';
	echo '<tr><th align="left">Efectivo</td><td><input type="text" class="textField" size="15" name="monto_efectivo" id="monto_efectivo" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Total Recaudado</td><td><input type="text" class="readOnly" size="15" name="monto" id="monto" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Adeudo Operador</td><td><input type="text" class="readOnly" size="15" name="monto_adeudo_operador" id="monto_adeudo_operador" value="" readOnly></td></tr>';
	//echo '<tr><th align="left">Cargo Extraordinario Operador</td><td><input type="text" class="textField" size="15" name="cargo_extraordinario_operador" id="cargo_extraordinario_operador" value=""></td></tr>';
	//echo '<tr><th align="left">Cargo Extraordinario Operador</td><td><textarea class="textField" cols="30" rows="3" name="cargo_extraordinario_operador" id="obs_cargo_extraordinario"></textarea></td></tr>';
	echo '<tr style="display:none;"><th align="left">Saldo Adeudo Operador</td><td><input type="text" class="readOnly tarjetas" size="15" name="saldo_adeudo_operador" id="saldo_adeudo_operador" value="" readOnly></td></tr>';
	echo '<tr style="display:none;"><th align="left">Abono Adeudo Operador</td><td><input type="text" class="textField" size="15" name="abono_adeudo_operador" id="abono_adeudo_operador" value="" onKeyUp="calcular()"></td></tr>';
	echo '</table>';
	
	echo '<script>

				function generaCargo(cvecargo){
					if((document.forma.tarjeta.value/1)>0 && document.forma.recaudacion.value != "0"){
						atcr("recaudacion_operador.php","_blank",12,cvecargo);
					}
					else{
						if((document.forma.tarjeta.value/1)<=0)
							alert("Necesita cargar primero la tarjeta");
						else
							alert("Necesita seleccionar la recaudacion");
					}
				}

				function calcular(){
					var total = 0;
					total = (document.forma.monto_derrotero.value/1)-(document.forma.monto_condonaciones.value/1);
					document.forma.monto_recaudar.value=total.toFixed(2);
					total = (document.forma.monto_boletos.value/1)+(document.forma.monto_extraordinario.value/1)+(document.forma.monto_efectivo.value/1);
					if((total/1) > (document.forma.monto_recaudar.value/1)){
						total = (document.forma.monto_extraordinario.value/1);
					}
					document.forma.monto.value=total.toFixed(2);
					total = (document.forma.monto_recaudar.value/1) - (document.forma.monto.value/1);
					document.forma.monto_adeudo_operador.value=total.toFixed(2);
					if((document.forma.abono_adeudo_operador.value/1) > (document.forma.saldo_adeudo_operador.value/1)){
						document.forma.abono_adeudo_operador.value = document.forma.saldo_adeudo_operador.value;
						if((document.forma.abono_adeudo_operador.value/1)<0){
							document.forma.abono_adeudo_operador.value=0;
						}
					}
				}

				function activar_extraordinario(){
					if(document.forma.motivo_extraordinario.value=="0"){
						$("#monto_extraordinario").removeClass("textField").addClass("readOnly").attr("readOnly","readOnly");
						document.forma.monto_extraordinario.value="";
					}
					else{
						$("#monto_extraordinario").removeClass("readOnly").addClass("textField").removeAttr("readOnly");
					}
					calcular();
				}
				
				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","recaudacion_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&plaza="+document.forma.plaza.value+"&tarjeta="+document.forma.folio.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									if(opciones2[1]=="0")
										alert("El folio no existe");
									else if(opciones2[1]=="1"){
										alert("El folio ya fue cobrado");
									}
									else if(opciones2[1]=="2"){
										alert("No se ha pagado la mutualidad de la tarjeta");
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
									document.forma.monto_derrotero.value=opciones2[10];
									document.forma.monto_condonaciones.value=opciones2[11];
									document.forma.monto_recaudar.value=opciones2[12];
									document.forma.saldo_adeudo_operador.value=opciones2[13];
								}
								calcular();
							}
						}
					}
				}
				
				
		</script>';
}

if($_POST['cmd']==0){
	if($impresion!=""){
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros(0,0);"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'recaudacion_unidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'recaudacion_unidad.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
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
	echo '<tr><td>Tarjeta</td><td><input type="text" name="tarjeta" id="tarjeta" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Credencial</td><td><input type="text" name="clave" id="clave" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>No. Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">--- Todos ---</option>';
	foreach($array_empresa as $k=>$v){
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

	function buscarRegistros(ordenamiento,orden)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recaudacion_unidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&ordenamiento="+ordenamiento+"&orden="+orden+"&recaudacion="+document.getElementById("recaudacion").value+"&empresa="+document.getElementById("empresa").value+"&tarjeta="+document.getElementById("tarjeta").value+"&derrotero="+document.getElementById("derrotero").value+"&no_eco="+document.getElementById("no_eco").value+"&clave="+document.getElementById("clave").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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