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
	$array_derrotero_varias_recaudaciones[$row['cve']]=$row['varias_recaudaciones'];
}



$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()") or die(mysql_error());
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];
if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio Recaudacion</th><th>Fecha Captura</th>';
	echo '<th>Fecha Recaudacion</th><th>Recaudacion</th><th>Folio Tarjeta</th><th>Fecha Viaje</th><th>Empresa</th><th>Operador</th><th>Unidad</th>
	<th>Derrotero</th><th>Vueltas</th><th>Cant. Boletos</th><th>Importe Boletos</th>
	<th>Cant. Boletos Tijera</th><th>Importe Boletos Tijera</th>
	<th>Cant. Boletos Abordo</th><th>Importe Boletos Abordo</th>
	<th>Cant. Boletos Taq Movil</th><th>Importe Boletos Taq Movil</th>
	<th>Cant. Abono Taq Movil</th><th>Importe Abono Taq Movil</th>
	<th>Total de Boletos</th>
	<th>Diesel</th><th>Comision</th><th>Lavada</th><th>Vale Comida</th><th>Bono de Productividad</th><th>Casetas</th>
	<th>Despachadores</th><th>Excedente</th><th>Total de Gastos</th><th>Efectivo</th><th>Utilidad</th><th>Concepto</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM recaudacion_autobus a ";
	if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	if($_POST['no_eco']!=""){ 
		$select.=" INNER JOIN ".$pre."unidades as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['tarjeta']!='') $select.=" AND a.tarjeta='".$_POST['tarjeta']."'";
	if($_POST['derrotero']!='all') $select.=" AND a.derrotero='".$_POST['derrotero']."'";
	if($_POST['empresa']!='all') $select.=" AND a.empresa='".$_POST['empresa']."'";
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
			echo '<a href="#" onClick="atcr(\'pachuca_recaudacion_autobus.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'pachuca_recaudacion_autobus.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
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
		echo '<td align="right">'.number_format($row['cant_boletos']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_boletos']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_boletos']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_boletos']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['cant_boletos_tijera']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_boletos_tijera']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_boletos_tijera']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_boletos_tijera']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['cant_boletos_abordo']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_boletos_abordo']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_boletos_abordo']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_boletos_abordo']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['cant_taqmovil']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_taqmovil']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_taqmovil']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_taqmovil']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['cant_abonomovil']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_abonomovil']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_abonomovil']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_abonomovil']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['total_boletos']*$factor,2).'</td>';
		$totales[$c]+=round($row['total_boletos']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['diesel']*$factor,2).'</td>';
		$totales[$c]+=round($row['diesel']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['comsion']*$factor,2).'</td>';
		$totales[$c]+=round($row['comsion']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['lavada']*$factor,2).'</td>';
		$totales[$c]+=round($row['lavada']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['vale_comida']*$factor,2).'</td>';
		$totales[$c]+=round($row['vale_comida']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['bono_productividad']*$factor,2).'</td>';
		$totales[$c]+=round($row['bono_productividad']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['casetas']*$factor,2).'</td>';
		$totales[$c]+=round($row['casetas']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['despachadores']*$factor,2).'</td>';
		$totales[$c]+=round($row['despachadores']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['excedente']*$factor,2).'</td>';
		$totales[$c]+=round($row['excedente']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['total_gasto']*$factor,2).'</td>';
		$totales[$c]+=round($row['total_gasto']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_efectivo']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_efectivo']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.$row['concepto'].'</td>';
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
	echo '<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	$res=mysql_query("SELECT * FROM tarjetas_unidad WHERE cve='".$_POST['tarjeta']."'");
	if($row=mysql_fetch_array($res)){
		if($row['derrotero']!=11){
			echo '0|5';
			exit();
		}
		if($row['estatus'] == 'C'){
			echo "0|3";
			exit();
		}
		if($row['estatus']!='A' && $array_derrotero_varias_recaudaciones[$row['derrotero']] != 1){
			echo "0|1";
			exit();
		}
		if($array_derrotero_varias_recaudaciones[$row['derrotero']] == 1 && $row['fecha'] != fechaLocal()){
			echo "0|4";
			exit();
		}
		/*$res1=mysql_query("SELECT * FROM recaudacion_operador WHERE tarjeta='".$_POST['tarjeta']."' AND estatus!='C'");
		if(!$row1=mysql_fetch_array($res1)){
			echo "0|2";
			exit();
		}*/
		$unidad = $row['unidad'];
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
		echo round($row['monto']-$row1[0],2).'|<table border="1"><tr><th>Guia</th><th>Cant. Boletos</th><th>Imp. Boletos</th>';
		$res1=mysql_query("SELECT taquilla, folio FROM guia WHERE taquilla>0 AND unidad='".$row['unidad']."' AND folio_recaudacion=0");
		$tboletos=0;
		$iboletos=0;
		while($row1 = mysql_fetch_array($res1)){
			$res2=mysql_query("SELECT COUNT(cve), SUM(monto) FROM boletos WHERE taquilla='".$row1['taquilla']."' AND guia = '".$row1['folio']."' AND estatus = 0");
			$row2=mysql_fetch_array($res2);
			echo '<tr><td>'.$row1['folio'].'<input type="hidden" name="guias[]" value="'.$row1['taquilla'].'_'.$row1['folio'].'"></td>
			<td align="right">'.$row2[0].'</td><td align="right">'.number_format($row2[1],2).'</td></tr>';
			$tboletos+=$row2[0];
			$iboletos+=$row2[1];
		}
		echo '</table>|'.$tboletos.'|'.$iboletos;
		$res1=mysql_query("SELECT COUNT(cve), SUM(monto) FROM boletos_taquillamovil WHERE estatus!='C' AND folio_recaudacion=0 AND unidad='".$unidad."'");
		$row1=mysql_fetch_array($res1);
		echo '|'.$row1[0].'|'.$row1[1];
		$res1=mysql_query("SELECT COUNT(cve), SUM(monto) FROM abono_unidad_taquillamovil WHERE estatus!='C' AND folio_recaudacion=0 AND unidad='".$unidad."'");
		$row1=mysql_fetch_array($res1);
		echo '|'.$row1[0].'|'.$row1[1].'|';
	}
	else{
		echo "0|0";
	}
	exit();
}

if($_POST['ajax']==3){
	$res=mysql_query("SELECT * FROM recaudacion_autobus WHERE estatus!='C' AND tarjeta='".$_POST['tarjeta']."'");
	if(mysql_num_rows($res)>0)
		echo "1";
	else
		echo "0";
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(10)."RECAUDACION PACHUCA";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$_POST['reg'];
	$texto.='|';
	$texto.=fechaLocal()." ".horaLocal();
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."TARJETA: ".$row['tarjeta'];
	$texto.='|';
	$texto.="FECHA CUENTA:    ".$row['fecha_viaje'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."NO. VUELTAS: ".$row['vueltas'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
	$texto.='|';
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
	$texto.=chr(27).'!'.chr(10)."ABONO EXT: ".number_format(floatval($_POST['monto_extraordinario']),2);*/
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."EFECTIVO: ".number_format(floatval($row['monto_efectivo']),2);
	if($row['diesel']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DIESEL: ".number_format(floatval($row['diesel']),2);
	}
	if($row['comision']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."COMISION: ".number_format(floatval($row['comision']),2);
	}
	if($row['lavada']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."LAVADA: ".number_format(floatval($row['lavada']),2);
	}
	if($row['vale_comida']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."V. COMIDA: ".number_format(floatval($row['vale_comida']),2);
	}
	if($row['bono_productividad']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."BONO PROD.: ".number_format(floatval($row['bono_productividad']),2);
	}
	if($row['casetas']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."CASETAS: ".number_format(floatval($row['casetas']),2);
	}
	if($row['despachadores']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DESPACHADORES: ".number_format(floatval($row['despachadores']),2);
	}
	if($row['excedente']>0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."EXCEDENTE: ".number_format(floatval($row['excedente']),2);
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."TOTAL GASTOS: ".number_format(floatval($row['total_gasto']),2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."UTILIDAD: ".number_format($row['monto'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."CONCEPTO:|".$row['concepto'];
	if($row['deuda_operador'] > 0)
	{
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DEUDA OPE: ".number_format($row['deuda_operador'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MOTIVO:|".$row['motivo_deuda'];
	}
	/*$texto.=chr(27).'!'.chr(10)."ADEUDO OPERADOR: ".number_format($_POST['monto_adeudo_operador'],2);
	if($_POST['abono_adeudo_operador'] > 0){
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
	}*/
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",6000);</script>';
	exit();
}

top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE recaudacion_autobus SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT tarjeta FROM recaudacion_autobus WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$res1 = mysql_query("SELECT COUNT(cve) FROM recaudacion_autobus WHERE tarjeta = '".$row[0]."' AND estatus!='C'");
	$row1 = mysql_fetch_array($res1);
	if($row1[0] == 0)
		mysql_query("UPDATE tarjetas_unidad SET estatus='A' WHERE cve='".$row[0]."'");
	mysql_query("UPDATE guia SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='".$_POST['reg']."'");
	mysql_query("UPDATE boletos SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='".$_POST['reg']."'");
	mysql_query("UPDATE boletos_taquillamovil SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='".$_POST['reg']."'");
	mysql_query("UPDATE abono_unidad_taquillamovil SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	//mysql_query("INSERT usuario_recaudacion SET usuario='".$_POST['cveusuario']."',recaudacion='".$_POST['recaudacion']."',fecha=CURDATE()");
	$res1 = mysql_query("SELECT * FROM tarjetas_unidad WHERE cve = '".$_POST['tarjeta']."'");
	$row1 = mysql_fetch_array($res1);
	$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE estatus!='C' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	if(($row['tarjeta']!=$_POST['tarjeta']) && ($row1['fecha'] == fechaLocal() || $array_derrotero_varias_recaudaciones[$row1['derrotero']] != 1)){
		mysql_query("UPDATE tarjetas_unidad SET estatus='P' WHERE cve='".$_POST['tarjeta']."'");
		mysql_query("INSERT recaudacion_autobus SET tarjeta='".$_POST['tarjeta']."',fecha_viaje='".$_POST['fecha_viaje']."',fecha='".$_POST['fecha']."',
			fecha_creacion='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$_POST['monto']."',monto_derrotero='".$_POST['monto_derrotero']."',cant_boletos='".$_POST['cant_boletos']."',
			vueltas='".$_POST['vueltas']."',monto_boletos='".$_POST['monto_boletos']."',monto_efectivo='".$_POST['monto_efectivo']."',
			recaudacion='".$_POST['recaudacion']."',diesel='".$_POST['diesel']."',comision='".$_POST['comision']."',
			lavada='".$_POST['lavada']."',vale_comida='".$_POST['vale_comida']."',bono_productividad='".$_POST['bono_productividad']."',
			casetas='".$_POST['casetas']."',despachadores='".$_POST['despachadores']."',excedente='".$_POST['excedente']."',
			total_gasto='".$_POST['total_gasto']."',concepto='".$_POST['concepto']."',
			cant_boletos_tijera='".$_POST['cant_boletos_tijera']."',monto_boletos_tijera='".$_POST['monto_boletos_tijera']."',
			cant_boletos_abordo='".$_POST['cant_boletos_abordo']."',monto_boletos_abordo='".$_POST['monto_boletos_abordo']."',
			total_boletos='".$_POST['total_boletos']."',deuda_operador='".$_POST['deuda_operador']."',
			motivo_deuda='".$_POST['motivo_deuda']."',cant_taqmovil='".$_POST['cant_taqmovil']."',
			monto_taqmovil='".$_POST['monto_taqmovil']."',cant_abonomovil='".$_POST['cant_abonomovil']."',
			monto_abonomovil='".$_POST['monto_abonomovil']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		foreach($_POST['guias'] as $guia){
			$datos = explode("_",$guia);
			mysql_query("UPDATE guia SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE taquilla='".$datos[0]."' AND folio='".$datos[1]."'");
			mysql_query("UPDATE boletos SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE taquilla='".$datos[0]."' AND guia='".$datos[1]."'");
		}
		if($_POST['cant_taqmovil'] > 0){
			mysql_query("UPDATE boletos_taquillamovil SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".fechaLocal()."' WHERE unidad='".$_POST['unidad']."' AND folio_recaudacion='0' AND estatus!='C' ORDER BY cve LIMIT ".intval($_POST['cant_taqmovil']));
		}
		if($_POST['cant_abonomovil'] > 0){
			mysql_query("UPDATE abono_unidad_taquillamovil SET folio_recaudacion='".$cverecaudacion."',fecha_recaudacion='".fechaLocal()."' WHERE unidad='".$_POST['unidad']."' AND folio_recaudacion='0' AND estatus!='C' ORDER BY cve LIMIT ".intval($_POST['cant_abonomovil']));
		}
		$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE cve='".$cverecaudacion."'");
		$row = mysql_fetch_array($res);
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)."RECAUDACION PACHUCA";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
		$texto.='|';
		$texto.=fechaLocal()." ".horaLocal();
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TARJETA: ".$row['tarjeta'];
		$texto.='|';
		$texto.="FECHA CUENTA:    ".$row['fecha_viaje'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."NO. VUELTAS: ".$row['vueltas'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
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
		$texto.=chr(27).'!'.chr(10)."ABONO EXT: ".number_format(floatval($_POST['monto_extraordinario']),2);*/
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."EFECTIVO: ".number_format(floatval($row['monto_efectivo']),2);
		if($row['diesel']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."DIESEL: ".number_format(floatval($row['diesel']),2);
		}
		if($row['comision']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."COMISION: ".number_format(floatval($row['comision']),2);
		}
		if($row['lavada']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."LAVADA: ".number_format(floatval($row['lavada']),2);
		}
		if($row['vale_comida']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."V. COMIDA: ".number_format(floatval($row['vale_comida']),2);
		}
		if($row['bono_productividad']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."BONO PROD.: ".number_format(floatval($row['bono_productividad']),2);
		}
		if($row['casetas']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."CASETAS: ".number_format(floatval($row['casetas']),2);
		}
		if($row['despachadores']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."DESPACHADORES: ".number_format(floatval($row['despachadores']),2);
		}
		if($row['excedente']>0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."EXCEDENTE: ".number_format(floatval($row['excedente']),2);
		}
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TOTAL GASTOS: ".number_format(floatval($row['total_gasto']),2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."UTILIDAD: ".number_format($row['monto'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."CONCEPTO:|".$row['concepto'];
		if($row['deuda_operador'] > 0)
		{
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."DEUDA OPE: ".number_format($row['deuda_operador'],2);
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."MOTIVO:|".$row['motivo_deuda'];
		}
		/*$texto.=chr(27).'!'.chr(10)."ADEUDO OPERADOR: ".number_format($_POST['monto_adeudo_operador'],2);
		if($_POST['abono_adeudo_operador'] > 0){
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."ABONO ADEUDO: ".number_format($_POST['abono_adeudo_operador'],2);
		}*/
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$cverecaudacion).'" width=200 height=200></iframe>';
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
				else{
					atcr(\'pachuca_recaudacion_autobus.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'tarjetas_unidad.php?viene_recaudacion=1\',\'_blank\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Generar Tarjeta</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'pachuca_recaudacion_autobus.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
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
	echo '<tr><th align="left">Operador</td><td><input type="text" class="readOnly tarjetas" size="50" name="nomoperador" id="nomoperador" value="" readOnly>&nbsp;
	<input type="button" value="Generar Mutualidad" onClick="generaCargo(1)">&nbsp;<!--
	<input type="button" value="Generar Reimpresion de Tarjeta" onClick="generaCargo(2)">&nbsp;
	<input type="button" value="Generar Multa por Servicios" onClick="generaCargo(3)">&nbsp;
	<input type="button" value="Generar Seguridad" onClick="generaCargo(4)">-->
	</td></tr>';
	echo '<tr><th align="left">Derrotero</td><td><input type="text" class="readOnly tarjetas" size="30" name="nomderrotero" id="nomderrotero" value="" readonly></td></tr>';
	echo '<tr><th align="left">Vueltas</td><td><input type="text" class="textField" size="15" name="vueltas" id="vueltas" value=""></td></tr>';
	echo '<tr><th align="left">Guias</td><td id="tdguias"></td></tr>';
	echo '<tr><th align="left">Cant. Boletos</td><td><input type="text" class="readOnly tarjetas" size="15" name="cant_boletos" id="cant_boletos" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Importe Boletos</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_boletos" id="monto_boletos" value="" onKeyUp="calcular()" readOnly></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Taq Movil</td><td><input type="text" class="readOnly tarjetas" size="15" name="cant_taqmovil" id="cant_taqmovil" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Importe Boletos Taq Movil</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_taqmovil" id="monto_taqmovil" value="" onKeyUp="calcular()" readOnly></td></tr>';
	echo '<tr><th align="left">Cant. Abono Taq Movil</td><td><input type="text" class="readOnly tarjetas" size="15" name="cant_abonomovil" id="cant_abonomovil" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Importe Abono Taq Movil</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_abonomovil" id="monto_abonomovil" value="" onKeyUp="calcular()" readOnly></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Tijera</td><td><input type="text" class="textField" size="15" name="cant_boletos_tijera" id="cant_boletos_tijera" value=""></td></tr>';
	echo '<tr><th align="left">Importe Boletos Tijera</td><td><input type="text" class="textField" size="15" name="monto_boletos_tijera" id="monto_boletos_tijera" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Abordo</td><td><input type="text" class="textField" size="15" name="cant_boletos_abordo" id="cant_boletos_abordo" value=""></td></tr>';
	echo '<tr><th align="left">Importe Boletos Abordo</td><td><input type="text" class="textField" size="15" name="monto_boletos_abordo" id="monto_boletos_abordo" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Total Boletos</td><td><input type="text" class="readOnly" size="15" name="total_boletos" id="total_boletos" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Diesel</td><td><input type="text" class="textField" size="15" name="diesel" id="diesel" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Comision</td><td><input type="text" class="textField" size="15" name="comision" id="comision" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Lavada</td><td><input type="text" class="textField" size="15" name="lavada" id="lavada" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Vale de Comida</td><td><input type="text" class="textField" size="15" name="vale_comida" id="vale_comida" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Bono de Productividad</td><td><input type="text" class="textField" size="15" name="bono_productividad" id="bono_productividad" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Casetas</td><td><input type="text" class="textField" size="15" name="casetas" id="casetas" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Despachadores</td><td><input type="text" class="textField" size="15" name="despachadores" id="despachadores" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Excedente</td><td><input type="text" class="textField" size="15" name="excedente" id="excedente" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Total Gastos</td><td><input type="text" class="readOnly" size="15" name="total_gasto" id="total_gasto" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Efectivo</td><td><input type="text" class="textField" size="15" name="monto_efectivo" id="monto_efectivo" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Utilidad</td><td><input type="text" class="readOnly" size="15" name="monto" id="monto" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Concepto</td><td><textarea class="textField" name="concepto" id="concepto" rows="3" cols="30"></textarea></td></tr>';
	echo '<tr><th align="left">Deuda Operador</td><td><input type="text" class="textField" size="15" name="deuda_operador" id="deuda_operador" value=""></td></tr>';
	echo '<tr><th align="left">Motivo Deuda</td><td><textarea class="textField" name="motivo_deuda" id="motivo_deuda" rows="3" cols="30"></textarea></td></tr>';
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
					total = (document.forma.monto_boletos_tijera.value/1)+(document.forma.monto_boletos.value/1)+(document.forma.monto_boletos_abordo.value/1)+(document.forma.monto_taqmovil.value/1)+(document.forma.monto_abonomovil.value/1);
					document.forma.total_boletos.value=total.toFixed(2);
					total = (document.forma.diesel.value/1)+(document.forma.comision.value/1)+(document.forma.lavada.value/1)+(document.forma.vale_comida.value/1)+(document.forma.bono_productividad.value/1)+(document.forma.casetas.value/1)+(document.forma.despachadores.value/1)+(document.forma.excedente.value/1);
					document.forma.total_gasto.value=total.toFixed(2);
					total = (document.forma.total_boletos.value/1)+(document.forma.monto_efectivo.value/1)-(document.forma.total_gasto.value/1);
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","pachuca_recaudacion_autobus.php",true);
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
									else if(opciones2[1]=="3"){
										alert("La tarjeta esta cancelada");
									}
									else if(opciones2[1]=="4"){
										alert("La tarjeta debe de ser del mismo dia");
									}
									else if(opciones2[1]=="5"){
										alert("La tarjeta debe de ser del rol Pachuca");
									}
									$(".tarjetas").each(function(){
										this.value="";
									});
									$("#tdguias").html("");
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
									$("#tdguias").html(opciones2[13]);
									document.forma.cant_boletos.value=opciones2[14];
									document.forma.monto_boletos.value=opciones2[15];
									document.forma.cant_taqmovil.value=opciones2[16];
									document.forma.monto_taqmovil.value=opciones2[17];
									document.forma.cant_abonomovil.value=opciones2[18];
									document.forma.monto_abonomovil.value=opciones2[19];
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
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'pachuca_recaudacion_autobus.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'pachuca_recaudacion_autobus.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
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
	echo '<tr style="display:none;"><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==11) echo ' selected';
		echo '>'.$v.'</option>';
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

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","pachuca_recaudacion_autobus.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&recaudacion="+document.getElementById("recaudacion").value+"&empresa="+document.getElementById("empresa").value+"&tarjeta="+document.getElementById("tarjeta").value+"&derrotero="+document.getElementById("derrotero").value+"&no_eco="+document.getElementById("no_eco").value+"&clave="+document.getElementById("clave").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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