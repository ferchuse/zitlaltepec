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



$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()");
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];



if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th><th>Fecha</th>';
	echo '<th>Recaudacion</th>
	<th>Operador</th><th>Unidad</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM devolucion_operador a ";
	if($_POST['clave']!="") $select.=" INNER JOIN ".$pre."conductores as d ON (d.cve=a.operador AND d.credencial='".$_POST['clave']."')";
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
			echo '<a href="#" onClick="atcr(\'devolucion_operador.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2)
				echo '<a href="#" onClick="atcr(\'devolucion_operador.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.utf8_encode($array_recaudacion[$row['recaudacion']]).'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td>'.utf8_encode($row['obs']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="6" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM devolucion_operador WHERE cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	$texto =chr(27)."@".'|';
	$texto.=chr(27).'!'.chr(40)."  DEVOLUCION DE OPERADOR";
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
	$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&copia=1&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$cverecaudacion).'" width=200 height=200></iframe>';
	echo '<html><body>'.$impresion.'</body></html>';
	echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
}



top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE devolucion_operador SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$res = mysql_query("SELECT * FROM devolucion_operador WHERE estatus!='C' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	$tiempo = date( "Y-m-d H:i:s" , strtotime ( "+ 30 second" , strtotime($row['fecha'].' '.$row['hora']) ) );
	if($row['operador']!=$_POST['operador'] || $row['unidad']!=$_POST['unidad'] || $row['monto']!=$_POST['monto'] || $tiempo<date('Y-m-d H:i:s')){
		mysql_query("INSERT devolucion_operador SET 
			fecha='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$_POST['monto']."',recaudacion='".$_POST['recaudacion']."',obs='".$_POST['obs']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)."DEVOLUCION DE OPERDOR";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$cverecaudacion;
		$texto.='|';
		$texto.=fechaLocal()." ".horaLocal();
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$_POST['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$_POST['operador']].')'.$array_nomconductor[$_POST['operador']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($_POST['monto'],2);
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	}
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
				else if(document.forma.unidad.value==\'\')
					alert(\'No ha cargado correctamente la unidad\');
				else if(document.forma.operador.value==\'\')
					alert(\'No ha cargado correctamente el operador\');
				else{
					atcr(\'devolucion_operador.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'devolucion_operador.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Devolucion de Operador</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	echo '</td></tr>';
	echo '<tr><th align="left">Recaudacion</th><td><select name="recaudacion" id="recaudacion">';
	if($recaudacion_usuario==0)
		echo '<option value="0">Seleccione</option>';
	foreach($array_recaudacion as $k=>$v){
		if($recaudacion_usuario==0 || $recaudacion_usuario==$k)
			echo '<option value="'.$k.'">'.$v.'</option>';
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
	echo '<tr><th align="left">Observaciones</td><td><textarea class="textField" name="obs" id="obs" rows="3" cols="30">'.$row['obs'].'</textarea></td></tr>';
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

if($_POST['cmd']==0){
	if($impresio!=""){
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'devolucion_operador.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'devolucion_operador.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
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
	echo '<tr style="display:none;"><td>Motivo</td><td><select name="cargo" id="cargo"><option value="all">--- Todos ---</option>';
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
			objeto.open("POST","devolucion_operador.php",true);
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