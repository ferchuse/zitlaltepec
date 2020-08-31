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


$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()");
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th><th>Recaudacion</th>';
	echo '<th>Fecha Captura</th><th>Fecha Aplicacion</th><th>Operador</th><th>Unidad</th><th>Folio de Recaudacion</th>
	<th>Monto</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM abono_deuda_monitoreo a ";
	if($_POST['clave']!="") $select.=" INNER JOIN operadores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
	if($_POST['no_eco']!=""){ 
		$select.=" INNER JOIN unidades as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
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
			echo '<a href="#" onClick="atcr(\'abono_deuda_monitoreo.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'abono_deuda_monitoreo.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.utf8_encode($array_recaudacion[$row['recaudacion']]).'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['fecha_apl'].'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="center">'.$row['cverecaudacion'].'</td>';
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.utf8_encode($row['obs']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="8" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax'] == 4){
	$res = mysql_query("SELECT a.cve, a.fecha, (a.deuda_operador - a.abono_deuda) as saldo, b.no_eco 
		FROM recaudacion_autobus a INNER JOIN unidades b ON b.cve = a.unidad 
		WHERE a.estatus != 'C' AND a.operador='".$_POST['operador']."' AND a.deuda_operador > a.abono_deuda ORDER BY a.cve");
	echo '<option value="" saldo="" no_eco="">Seleccione</option>';
	while($row = mysql_fetch_array($res))
		echo '<option value="'.$row['cve'].'" no_eco="'.$row['no_eco'].'" saldo="'.$row['saldo'].'">'.$row['cve'].', No Eco: '.$row['no_eco'].'</option>';
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM abono_deuda_monitoreo WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(40)."  ABONO DEUDA MONITOREO";
	$texto.='||';
	$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
	$texto.='|';
	$texto.=$row['fecha']." ".$row['hora'];
	$texto.='|';
	$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
	$texto.='|';
	$texto.=chr(27).'!'.chr(10)."OBS:|".$row['obs'];
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&copia=1&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$cverecaudacion).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}



top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE abono_deuda_monitoreo SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$res = mysql_query("SELECT * FROM abono_deuda_monitoreo WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	mysql_query("UPDATE recaudacion_autobus SET abono_deuda = abono_deuda - ".$row['monto']." WHERE cve='".$row['cverecaudacion']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
		$res = mysql_query("SELECT * FROM recaudacion_autobus WHERE cve='".$_POST['cverecaudacion']."'");
		$row = mysql_fetch_array($res);
		mysql_query("INSERT abono_deuda_monitoreo SET fecha_apl='".$_POST['fecha_apl']."',
			fecha='".fechaLocal()."',empresa='".$row['empresa']."',derrotero='".$row['derrotero']."',
			operador='".$row['operador']."',hora='".horaLocal()."',unidad='".$row['unidad']."',
			usuario='".$_POST['cveusuario']."',estatus='A',cverecaudacion='".$_POST['cverecaudacion']."',
			monto='".$_POST['monto']."',obs='".$_POST['obs']."',
			recaudacion='".$_POST['recaudacion']."'") or die(mysql_error());
		mysql_query("UPDATE recaudacion_autobus SET abono_deuda = abono_deuda + ".$_POST['monto']." WHERE cve='".$_POST['cverecaudacion']."'");
		$cverecaudacion=mysql_insert_id();
		$res = mysql_query("SELECT * FROM abono_deuda_monitoreo WHERE cve='".$cverecaudacion."'");
		$row = mysql_fetch_array($res);
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(40)."  ABONO DEUDA MONITOREO";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=$row['fecha']." ".$row['hora'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."OBS:|".$row['obs'];
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	$_POST['cmd']=0;
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
				else if(document.forma.operador.value==\'\')
					alert(\'No ha cargado correctamente el operador\');
				else if(document.forma.cverecaudacion.value==\'\')
					alert(\'No ha cargado el folio de recaudacion correctamente\');
				else if((document.forma.saldo.value/1)<(document.forma.monto.value/1))
					alert(\'El monto no puede ser mayor a la deuda\');
				else{
					atcr(\'abono_deuda_monitoreo.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'abono_deuda_monitoreo.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Abono Deuda Monitoreo</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" class="readOnly" name="fecha_aplicacion" id="fecha_aplicacion" value="'.fechaLocal().'" size="15" readOnly>';
	if($nivelUsuario>2){
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
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
	echo '<tr><th align="left">Operador</th><td><select name="operador" id="operador" class="textField" onChange="traer_deuda()"><option value="">Seleccione</option>';
	$res1 = mysql_query("SELECT a.operador, b.nombre
		FROM recaudacion_autobus a INNER JOIN operadores b ON b.cve = a.operador 
		WHERE a.estatus != 'C' AND a.deuda_operador > a.abono_deuda GROUP BY a.operador ORDER BY b.nombre");
	while($row1=mysql_fetch_array($res1)){
		echo '<option value="'.$row['operador'].'"';
		if($row['operador']==$row1['operador']) echo ' selected';
		echo '>'.$row1['nombre'].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Folio Recaudacion</th><td><select name="cverecaudacion" id="cverecaudacion" class="textField" onChange="mostrar_datos()"><option value="" saldo="" no_eco="">Seleccione</option>';
	echo '</select></td></tr>';
	echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="" class="readOnly"></td></tr>';
	echo '<tr><th align="left">Saldo</th><td><input type="text" name="saldo" id="saldo" size="10" value="" class="readOnly"></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" cols="30" rows="3">'.$row['obs'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
				function mostrar_datos(){
					option = $("#cverecaudacion").find("option:selected");
					document.forma.no_eco.value = option.attr("no_eco");
					document.forma.saldo.value = option.attr("saldo");
				}
								
				function traer_deuda(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","abono_deuda_monitoreo.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=4&operador="+document.forma.operador.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								$("#cverecaudacion").html(objeto1.responseText);
								document.forma.no_eco.value="";
								document.forma.saldo.value="";
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
			<td><a href="#" onClick="atcr(\'abono_deuda_monitoreo.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
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
	echo '<tr><td>Credencial</td><td><input type="text" name="clave" id="clave" class="textField" size="12" value=""></td></tr>';
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
			objeto.open("POST","abono_deuda_monitoreo.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&recaudacion="+document.getElementById("recaudacion").value+"&clave="+document.getElementById("clave").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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