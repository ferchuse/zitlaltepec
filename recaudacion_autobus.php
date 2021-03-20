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
	//Listar Registros
	$nivelUsuario = nivelUsuario();
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM recaudacion_autobus a ";
	if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	if($_POST['no_eco']!="" || $_POST['ordenamiento']==1){ 
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
	<th>Derrotero</th><th>Vueltas</th><th>Cant.Boletos</th><th>Importe Guias</th><th>Vales de Dinero</th>
	<th>Cant. Boletos Tijera</th><th>Importe Boletos Tijera</th>
	<th>Cant. Boletos Abordo</th><th>Importe Boletos Abordo</th>
	<th>Cant. Boletos Taq Movil</th><th>Importe Boletos Taq Movil</th>
	<th>Cant. Abono Taq Movil</th><th>Importe Abono Taq Movil</th>
	<th>Cant. Boletos sin Guia</th><th>Importe Boletos sin Guia</th>
	<th>Diesel Manual</th><th>Total de Boletos</th>
	<th>Diesel</th><th>Vale Diesel</th><th>Comision</th><th>Lavada</th><th>Vale Comida</th><th>Bono de Productividad</th><th>Casetas</th>
	<th>Despachadores</th><th>Excedente</th><th>Total de Gastos</th><th>Efectivo</th><th>Utilidad</th><th>Concepto</th><th>Deuda de Operador</th><th>Motivo de Deuda</th><th>Usuario</th>
	</tr>'; 
	
	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'recaudacion_autobus.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'recaudacion_autobus.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
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
		echo '<td align="right">'.number_format($row['monto_vale_dinero']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_vale_dinero']*$factor,2);$c++;
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
		echo '<td align="right">'.number_format($row['cant_sencillos']*$factor,0).'</td>';
		$totales[$c]+=round($row['cant_sencillos']*$factor,0);$c++;
		echo '<td align="right">'.number_format($row['monto_sencillos']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_sencillos']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['diesel_manual']*$factor,2).'</td>';
		$totales[$c]+=round($row['diesel_manual']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['total_boletos']*$factor,2).'</td>';
		$totales[$c]+=round($row['total_boletos']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['diesel']*$factor,2).'</td>';
		$totales[$c]+=round($row['diesel']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto_vale_diesel']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto_vale_diesel']*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['comision']*$factor,2).'</td>';
		$totales[$c]+=round($row['comision']*$factor,2);$c++;
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
		echo '<td align="right">'.number_format($row['deuda_operador'],2).'</td>';
		echo '<td align="">'.$row['motivo_deuda'].'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$do+=$row['deuda_operador'];
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="12" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		if($total=='A')
			echo '<th align="center">&nbsp;</th>';
		else
			echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th colspan="">&nbsp;</th>';
	echo '<th colspan="right">'.number_format($do,2).'</th>';
	echo '<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

//comando Buscar Tarjeta
if($_POST['ajax']==2){
	$res=mysql_query("SELECT * FROM tarjetas_unidad WHERE cve='".$_POST['tarjeta']."'");
	if($row=mysql_fetch_array($res)){
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
		
		// Buscar Boletos con guia
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
		echo '</table>|'.$tboletos.'|'.$iboletos.'|<table><tr><th>Folio</th><th>Fecha</th><th>Importe</th></tr>';
		
		//Buscar Vale de Dinero 
		$tvale=0;
		
		
		$res1=mysql_query("SELECT * FROM vale_dinero WHERE estatus!='C' AND recaudacion=0 AND unidad='".$row['unidad']."' ORDER BY cve");
		while($row1 = mysql_fetch_array($res1)){
			echo '<tr><td>'.$row1['cve'].'<input type="hidden" name="vales_dinero[]" value="'.$row1['cve'].'"></td><td>'.$row1['fecha'].'</td><td align="right">'.$row1['monto'].'</td></tr>';
			$tvale += $row1['monto'];
		}
		echo '</table>|'.$tvale.'|';
		
		//Deuda del operador
		$res = mysql_query("SELECT SUM(a.deuda_operador - a.abono_deuda) as saldo
		FROM recaudacion_autobus a INNER JOIN unidades b ON b.cve = a.unidad 
		WHERE a.estatus != 'C' AND a.deuda_operador > a.abono_deuda AND a.operador='".$row['operador']."'");
		$row = mysql_fetch_array($res);
		echo $row[0];
		
		
		$res1=mysql_query("SELECT COUNT(cve), SUM(monto) FROM boletos_taquillamovil WHERE estatus!='C' AND folio_recaudacion=0 AND unidad='".$unidad."'");
		$row1=mysql_fetch_array($res1);
		echo '|'.$row1[0].'|'.$row1[1];
		$res1=mysql_query("SELECT COUNT(cve), SUM(monto) FROM abono_unidad_taquillamovil WHERE estatus!='C' AND folio_recaudacion=0 AND unidad='".$unidad."'");
		$row1=mysql_fetch_array($res1);
		echo '|'.$row1[0].'|'.$row1[1].'|';
		$res1 = mysql_query("SELECT SUM(litros),SUM(monto) FROM vale_disel WHERE tarjeta='".$_POST['tarjeta']."' AND estatus=1");
		$row1=mysql_fetch_array($res1);
		echo $row1[0].'|'.$row1[1];
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

if($_POST['ajax']==4){
	$res=mysql_query("SELECT * FROM costo_boletos_sencillos ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_costo[$row['cve']]=$row['nombre'];
	}
	$taq = intval(substr($_POST['boleto'],1,2));
	$costo = intval(substr($_POST['boleto'],3,4));
	$folio = intval(substr($_POST['boleto'],7,7));
	$resultado = array('error' => 0, 'mensaje' => '', 'html' => '');
	$res = mysql_query("SELECT *, DATEDIFF(CURDATE(), fecha) as dias FROM boletos_sencillos WHERE taquilla = '$taq' AND folio='$folio'");
	if($row = mysql_fetch_array($res)){
		if($row['folio_recaudacion'] > 0){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto ya se poncho';
		}
		elseif($row['estatus']==1){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto esta cancelado';
		}
		elseif($row['dias']>5){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto esta caducado';
		}
		else{
			$resultado['html'] .= rowb(false);
			$resultado['html'] .= '<td align="center">';
			$resultado['html'] .= '<a href="#" class="aboletos" onClick="quitar_boletosencillo($(this))" taquilla="'.$row['taquilla'].'" folio="'.$row['folio'].'" monto="'.$row['monto'].'"><img src="images/validono.gif" border="0" title="Quitar"></a>';
			$resultado['html'] .= '</td>';
			$resultado['html'] .= '<td align="left">'.utf8_encode($array_taquilla[$row['taquilla']]).'</td>';
			$resultado['html'] .= '<td align="center">'.$row['folio'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['fecha'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['hora'].'</td>';
			$resultado['html'] .= '<td align="center">'.utf8_encode($array_costo[$row['costo']]).'</td>';
			$resultado['html'] .= '<td align="right">'.number_format($row['monto'],2).'</td>';
			$resultado['html'] .= '</tr>';
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje'] = 'No se encontro el boleto';
	}
	echo json_encode($resultado);
	exit();
}

//Comando Imprimir

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(10)."RECAUDACION AUTOBUS";
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
	if($row['monto_vale_diesel'] > 0){
		//$texto.='|';
		//$texto.=chr(27).'!'.chr(10)."LITROS VALE DIESEL: ".number_format(floatval($row['litros_vale_diesel']),3);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO VALE DIESEL: ".number_format(floatval($row['monto_vale_diesel']),2);
	}
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."TOTAL RECAUDADO: ".number_format($row['monto'],2);
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
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}

top($_SESSION);
//Comando Cancelar
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
	mysql_query("UPDATE vale_dinero SET recaudacion=0,fecha_recaudacion='0000-00-00' WHERE recaudacion='".$_POST['reg']."'");
	mysql_query("UPDATE boletos_taquillamovil SET folio_recaudacion=0,fecha_recaudacion='0000-00-00' WHERE folio_recaudacion='".$_POST['reg']."'");
	mysql_query("UPDATE abono_unidad_taquillamovil SET folio_recaudacion = 0, fecha_recaudacion='0000-00-00' WHERE folio_recaudacion = '".$_POST['reg']."'");
	mysql_query("UPDATE boletos_sencillos SET folio_recaudacion=0, fecha_recaudacion='0000-00-00', tipo_recaudacion=0 WHERE folio_recaudacion='".$_POST['reg']."' AND tipo_recaudacion=1");
	$_POST['cmd']=0;
}

//Comando Guardar Recaudacion
if($_POST['cmd']==2){
	//mysql_query("INSERT usuario_recaudacion SET usuario='".$_POST['cveusuario']."',recaudacion='".$_POST['recaudacion']."',fecha=CURDATE()");
	$res1 = mysql_query("SELECT * FROM tarjetas_unidad WHERE cve = '".$_POST['tarjeta']."'");
	$row1 = mysql_fetch_array($res1);
	$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE estatus!='C' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	if(($row['tarjeta']!=$_POST['tarjeta']) && ($row1['fecha'] == fechaLocal() || $array_derrotero_varias_recaudaciones[$row1['derrotero']] != 1)){
		mysql_query("UPDATE tarjetas_unidad SET estatus='P' WHERE cve='".$_POST['tarjeta']."'");
		mysql_query("UPDATE vale_disel SET estatus='2' WHERE tarjeta='".$_POST['tarjeta']."' AND estatus=1");
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
			motivo_deuda='".$_POST['motivo_deuda']."', monto_vale_dinero='".$_POST['monto_vale_dinero']."',
			cant_taqmovil='".$_POST['cant_taqmovil']."',monto_taqmovil='".$_POST['monto_taqmovil']."',
			cant_abonomovil='".$_POST['cant_abonomovil']."',monto_abonomovil='".$_POST['monto_abonomovil']."',
			litros_vale_diesel='".$_POST['litros_vale_diesel']."',monto_vale_diesel='".$_POST['monto_vale_diesel']."',
			diesel_manual='".$_POST['diesel_manual']."', cant_sencillos='{$_POST['cant_sencillos']}', monto_sencillos='{$_POST['monto_sencillos']}'") or die(mysql_error());
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
		$boletos = json_decode($_POST['boletossencillos'], true);
		foreach($boletos as $boleto){
			mysql_query("UPDATE boletos_sencillos SET folio_recaudacion='$cverecaudacion', fecha_recaudacion='".fechaLocal()."', tipo_recaudacion=1 WHERE taquilla = '".$boleto['taquilla']."' AND folio='".$boleto['folio']."'");
		}
		foreach($_POST['vales_dinero'] as $vale){
			mysql_query("UPDATE vale_dinero SET recaudacion='".$cverecaudacion."',fecha_recaudacion='".$_POST['fecha']."' WHERE cve='".$vale."'");
		}
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)."RECAUDACION AUTOBUS";
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
		if($_POST['monto_vale_diesel'] > 0){
			//$texto.='|';
			//$texto.=chr(27).'!'.chr(10)."LITROS VALE DIESEL: ".number_format(floatval($_POST['litros_vale_diesel']),3);
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."MONTO VALE DIESEL: ".number_format(floatval($_POST['monto_vale_diesel']),2);
		}
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TOTAL RECAUDADO: ".number_format($_POST['monto'],2);
		if($_POST['deuda_operador'] > 0)
		{
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."DEUDA OPE: ".number_format($_POST['deuda_operador'],2);
			$texto.='|';
			$texto.=chr(27).'!'.chr(10)."MOTIVO:|".$_POST['motivo_deuda'];
		}
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
	echo '<div id="dialogboletossencillos" style="display:none;">
	<table>
	<tr><th>Boleto</th><td><input type="text" class="textField" id="boletosencillo" autocomplete="off" value="" onKeyPress="
		if(document.getElementById(\'capturadosencillo\').value == 0){
			setTimeout(\'limpiarboletosencillo()\',2000);
			document.getElementById(\'capturadosencillo\').value = 1;
		}
		if(event.keyCode==13){
			agregarBoletoSencillo();
		}"></td></tr>
	</table>
	<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tablaboletossencillos">
	<tr bgcolor="#E9F2F8"><th>&nbsp;</th>
	<th>Taquilla</th><th>Folio</th>
	<th>Fecha</th><th>Hora</th><th>Boleto</th><th>Costo</th>
	</tr></table>
	<input type="hidden" id="capturadosencillo" value="0">
	</div>';
	echo '<textarea style="display:none;" name="boletossencillos" id="boletossencillos"></textarea>';

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
				else if(validarboletossencillo()){
					atcr(\'recaudacion_autobus.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'tarjetas_unidad.php?viene_recaudacion=1\',\'_blank\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Generar Tarjeta</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'recaudacion_autobus.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
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
	<input type="button" value="Generar Multa por Servicios" onClick="generaCargo(3)">&nbsp;-->
	<input type="button" value="Generar Seguridad" onClick="generaCargo(4)">
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
	echo '<tr><th align="left">Vales de Dinero</td><td id="tdvales"></td></tr>';
	echo '<tr><th align="left">Importe Vales de Dinero</td><td><input type="text" class="readOnly tarjetas" size="15" name="monto_vale_dinero" id="monto_vale_dinero" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Tijera</td><td><input type="text" class="textField" size="15" name="cant_boletos_tijera" id="cant_boletos_tijera" value=""></td></tr>';
	echo '<tr><th align="left">Importe Boletos Tijera</td><td><input type="text" class="textField" size="15" name="monto_boletos_tijera" id="monto_boletos_tijera" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Abordo</td><td><input type="text" class="textField" size="15" name="cant_boletos_abordo" id="cant_boletos_abordo" value=""></td></tr>';
	echo '<tr><th align="left">Importe Boletos Abordo</td><td><input type="text" class="textField" size="15" name="monto_boletos_abordo" id="monto_boletos_abordo" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Cant. Boletos Sin Guia</td><td><input type="text" class="readOnly" size="15" name="cant_sencillos" id="cant_sencillos" value="" readOnly>&nbsp;<input type="button" value="Ponchar" onClick="poncharsencillos()" class="textField"></td></tr>';
	echo '<tr><th align="left">Importe Boletos Sin Guia</td><td><input type="text" class="readOnly" size="15" name="monto_sencillos" id="monto_sencillos" value="" onKeyUp="calcular()" readOnly></td></tr>';
	echo '<tr><th align="left">Diesel Manual</th><td><input type="text" class="textField" size="15" name="diesel_manual" id="diesel_manual" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr><th align="left">Total Boletos</td><td><input type="text" class="readOnly" size="15" name="total_boletos" id="total_boletos" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Diesel</td><td><input type="text" class="textField" size="15" name="diesel" id="diesel" value="" onKeyUp="calcular()"></td></tr>';
	echo '<tr style="display:none;"><th align="left">Litros Vale Diesel</td><td><input type="text" class="readOnly" size="15" name="litros_vale_diesel" id="litros_vale_diesel" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Importe Vale Diesel</td><td><input type="text" class="readOnly" size="15" name="monto_vale_diesel" id="monto_vale_diesel" value="" readOnly></td></tr>';
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
	echo '<tr><th align="left">Total de Deuda del Operador</td><td><input type="text" class="readOnly" size="15" name="totaldeuda" id="totaldeuda" value="" readOnly></td></tr>';
	echo '</table>';
	
	echo '<script>

				$("#dialogboletossencillos").dialog({ 
					bgiframe: true,
					autoOpen: false,
					modal: true,
					width: 600,
					height: 400,
					autoResize: true,
					position: "center",
					beforeClose: function( event, ui ) {
						calcular();
					},
					buttons: {
						"Cerrar": function(){ 
							$(this).dialog("close"); 
						}
					},
				}); 

				function poncharsencillos(){
					$("#dialogboletossencillos").dialog("open");
				}

				function limpiarboletosencillo(){
					document.getElementById(\'capturadosencillo\').value = 0;
					document.getElementById(\'boletosencillo\').value = "";
					document.getElementById(\'boletosencillo\').focus();
				}

				function agregarBoletoSencillo(){
					$.ajax({
					  url: "recaudacion_autobus.php",
					  type: "POST",
					  async: false,
					  dataType: "json",
					  data: {
						boleto: document.getElementById("boletosencillo").value,
						ajax: 4
					  },
						success: function(data) {
							if(data.error == 1){
								alert(data.mensaje);
							}
							else{
								$("#tablaboletossencillos").append(data.html);
							}

							document.getElementById(\'capturadosencillo\').value = 0;
							document.getElementById(\'boletosencillo\').value = "";
							document.getElementById(\'boletosencillo\').focus();
							calcularboletosencillo();
						}
					});
				}

				function validarboletossencillo(){
					boletos = [];
					$(".aboletos").each(function(){
						campo = $(this);
						boleto = {};
						boleto.taquilla = campo.attr("taquilla");
						boleto.folio = campo.attr("folio");
						boleto.monto = campo.attr("monto");
						boletos.push(boleto);
					});
					document.forma.boletossencillos.value = JSON.stringify(boletos);
					regresar = true;
					$.ajax({
					  url: "ponchado_sencillos.php",
					  type: "POST",
					  async: false,
					  dataType: "json",
					  data: {
						boletos: document.getElementById("boletossencillos").value,
						ajax: 4
					  },
						success: function(data) {
							if(data.error == 1){
								alert(data.mensaje);
								regresar = false;
								$("#panel").hide();
							}
						}
					});
					return regresar;
				}

				function quitar_boletosencillo(aref){
					aref.parents("tr:first").remove();
					calcularboletosencillo();
					document.getElementById(\'boletosencillo\').focus();
				}

				function calcularboletosencillo(){
					cantidad=0;
					monto = 0;
					$(".aboletos").each(function(){
						monto += $(this).attr("monto")/1;
						cantidad++;
					});
					document.forma.cant_sencillos.value=cantidad.toFixed(0);
					document.forma.monto_sencillos.value=monto.toFixed(2);
				}


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
					total = (document.forma.monto_boletos_tijera.value/1)+(document.forma.monto_boletos.value/1)+(document.forma.monto_boletos_abordo.value/1)+(document.forma.monto_vale_dinero.value/1)+(document.forma.monto_taqmovil.value/1)+(document.forma.monto_abonomovil.value/1)+(document.forma.diesel_manual.value/1)+(document.forma.monto_sencillos.value/1);
					document.forma.total_boletos.value=total.toFixed(2);
					
					//Total Gasto
					total = (document.forma.diesel.value/1)
					+(document.forma.comision.value/1)
					+(document.forma.lavada.value/1)
					+(document.forma.vale_comida.value/1)
					+(document.forma.bono_productividad.value/1)
					+(document.forma.casetas.value/1)
					+(document.forma.despachadores.value/1)
					+(document.forma.excedente.value/1) 
					+ (document.forma.monto_vale_diesel.value/1);
					
					document.forma.total_gasto.value=total.toFixed(2);
					
					
					total = (document.forma.total_boletos.value/1)+(document.forma.monto_efectivo.value/1)-(document.forma.total_gasto.value/1);
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","recaudacion_autobus.php",true);
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
									$(".tarjetas").each(function(){
										this.value="";
									});
									$("#tdguias").html("");
									$("#tdvales").html("");
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
									$("#tdvales").html(opciones2[16]);
									document.forma.monto_vale_dinero.value=opciones2[17];
									document.forma.totaldeuda.value=opciones2[18];
									document.forma.cant_taqmovil.value=opciones2[19];
									document.forma.monto_taqmovil.value=opciones2[20];
									document.forma.cant_abonomovil.value=opciones2[21];
									document.forma.monto_abonomovil.value=opciones2[22];
									document.forma.litros_vale_diesel.value=opciones2[23];
									document.forma.monto_vale_diesel.value=opciones2[24];
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
			<td><a href="#" onClick="atcr(\'recaudacion_autobus.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'recaudacion_autobus.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
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
			objeto.open("POST","recaudacion_autobus.php",true);
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