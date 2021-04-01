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

$res=mysql_query("SELECT * FROM motivos_condonacion ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}




if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	//echo '<th>Validar</th>';
	echo '<th>Folio Alteracion</th>';
	echo '<th>Fecha Alteracion</th><th>Unidad</th><th>Motivo</th><th>Importe Alterado</th><th>Tarjetas</th><th>Importe a</br>Recaudar</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM condonacion_tarjetas_unidad as a 
	LEFT JOIN motivos_condonacion as c ON (c.cve=a.motivo)";
	if($_POST['no_eco']!=""){ 
		$select.=" INNER JOIN ".$pre."unidades as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['motivo']!='0') $select.=" AND a.motivo='".$_POST['motivo']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select) or die(mysql_error());
	$nivelUsuario=nivelUsuario();
	while($row=mysql_fetch_array($res)){

		$res2=mysql_query("SELECT a.cve FROM condonacion_tarjetas_unidad_detalle a INNER JOIN recaudacion_unidad b ON a.tarjeta = b.tarjeta WHERE a.condonacion='".$row['cve']."' AND b.estatus!='C'");
		rowb();
		echo '<td align="center">';
		if($row['estatus']!='C' && mysql_num_rows($res2)==0 && $nivelUsuario>2)

			echo '<a href="#" onClick="atcr(\'condonacion_tarjetas_unidad.php\',\'\',3,\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
	
		elseif($row['estatus']=='C'){
			echo 'CANCELADO';
			$row['monto']=0;
		}
		elseif(mysql_num_rows($res2)>0)
			echo 'TARJETA RECAUDADA';
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		//echo '<td align="center">'.$row['tarjeta'].'</td>';
		//echo '<td align="center">'.$array_empresa[$row['empresa']].'</td>';
		//echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.$array_nomconductor[$row['operador']].'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		//echo '<td align="center">'.$row['fecha_viaje'].'</td>';
		//echo '<td align="left">'.$array_derrotero[$row['ruta']].'</td>';
		echo '<td align="left">'.utf8_encode($array_motivos[$row['motivo']]).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td><table width="100%"><tr><th>Tarjeta</th><th>Fecha Viaje</th><th>Operador</th><th>Derrotero</th><th>Imp. Condonado</th></tr>';
		$arecaudar=0;
		$res1=mysql_query("SELECT a.monto, b.cve, b.fecha_viaje, b.operador, b.derrotero, b.monto as cuenta FROM condonacion_tarjetas_unidad_detalle a INNER JOIN tarjetas_unidad b ON b.cve = a.tarjeta WHERE a.condonacion = '".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td>'.$row1['cve'].'</td><td align="center">'.$row1['fecha_viaje'].'</td>
			<td>'.utf8_encode($array_nomconductor[$row1['operador']]).'</td><td>'.utf8_encode($array_derrotero[$row1['derrotero']]).'</td>
			<td align="right">'.number_format($row1['monto'],2).'</td></tr>';
			$arecaudar+=$row1['cuenta']-$row['monto'];
		}
		echo '</table></td>';
		//importe a recaudar
		/*$res1 = mysql_query("SELECT * FROM tarjetas_unidad WHERE unidad='".$row['unidad']."' AND estatus='A'");
		while($row1=mysql_fetch_array($res1)){
			$res2=mysql_query("SELECT SUM(b.monto) FROM condonacion_tarjetas_unidad a INNER JOIN condonacion_tarjetas_unidad_detalle b ON a.cve = b.condonacion WHERE a.unidad='".$row['unidad']."' AND a.estatus!='C' AND b.tarjeta='".$row1['unidad']."'");
			$row2=mysql_fetch_array($res2);
			echo '<td align="right">'.number_format($row1['monto']-$row2[0],2).'</td>';
		}*/
		echo '<td align="right">'.number_format($arecaudar,2).'</td>';
		echo '<td align="left">'.utf8_encode($row['obs']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
		$t2+=round($row['monto'],2);
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="5" align="left">'.$x.' Registro(s)</th>
	<th align="right">'.number_format($t2,2).'</th><th colspan="4">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	$res=mysql_query("SELECT cve,estatus,derrotero,empresa FROM unidades WHERE no_eco='".strtoupper($_POST['no_eco'])."' AND localidad='".$_POST['localidad']."'");
	if($row=mysql_fetch_array($res)){
		if($row['estatus']!=1){
			echo "0||1";
			exit();
		}
		echo $row['cve']."|".$row['empresa'].'|'.$array_empresa[$row['empresa']].'|';
		/*echo '<input type="hidden" name="empresa" id="empresa" value="'.$row['empresa'].'">';
		echo '<input type="hidden" name="unidad" id="unidad" value="'.$row['unidad'].'">';
		echo '<input type="hidden" name="operador" id="operador" value="'.$row['operador'].'">';
		echo '<input type="hidden" name="derrotero" id="derrotero" value="'.$row['derrotero'].'">';
		echo '<table>';
		echo '<tr><th align="left">Empresa</th><td><input type="text" name="nomempresa" id="nomempresa" size="30" value="'.$array_empresa[$row['empresa']].'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Fecha Viaje</th><td><input type="text" class="readOnly" name="fecha_viaje" id="fecha_viaje" value="'.trim($row['fecha']).'" size="15" readOnly></td></tr>';
		echo '<tr><th align="left">Operador</th><td><input type="text" name="clave" id="clave" size="10" value="'.$array_cveconductor[$row['operador']].'" class="readOnly" readOnly>&nbsp;<input type="text" class="readOnly" name="nomcond" id="nomcond" size="50" value="'.$array_nomconductor[$row['operador']].'" readOnly></td></tr>';
		echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="'.$array_unidad[$row['unidad']].'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Derrotero</th><td><input type="text" name="nomderrotero" id="nomderrotero" size="30" value="'.$array_derrotero[$row['derrotero']].'" class="readOnly" readOnly></td></tr>';
		$res1=mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE tarjeta='".$_POST['tarjeta']."' AND estatus!='C'");
		$row1=mysql_fetch_array($res1);
		$saldo = $row['monto']-$row1[0];
		echo '<tr><th align="left">Saldo</th><td><input type="text" name="saldo" id="saldo" size="30" value="'.$saldo.'" class="readOnly" readOnly></td></tr>';
		echo '</table>';*/
		echo '<table><tr><th>Tarjeta</th><th>Fecha Viaje</th><th>Operador</th><th>Derrotero</th><th>Cuenta</th><th>Saldo</th><th>Importe Condonacion</th></tr>';
		$res1 = mysql_query("SELECT * FROM tarjetas_unidad WHERE unidad='".$row['cve']."' AND estatus='A'");
		while($row1=mysql_fetch_array($res1)){
			$res2=mysql_query("SELECT SUM(b.monto) FROM condonacion_tarjetas_unidad a INNER JOIN condonacion_tarjetas_unidad_detalle b ON a.cve = b.condonacion WHERE a.unidad='".$row['cve']."' AND a.estatus!='C' AND b.tarjeta='".$row1['cve']."'");
			$row2=mysql_fetch_array($res2);
			echo '<tr><td>'.$row1['cve'].'</td><td align="center">'.$row1['fecha_viaje'].'</td>
			<td>'.utf8_encode($array_nomconductor[$row1['operador']]).'</td><td>'.utf8_encode($array_derrotero[$row1['derrotero']]).'</td>
			<td align="right">'.number_format($row1['monto'],2).'</td>
			<td align="right">'.number_format($row1['monto']-$row2[0],2).'</td>
			<td align="center"><input type="text" class="textField montos_tarjetas" saldo="'.round($row1['monto']-$row2[0],2).'" size="10" name="montos['.$row1['cve'].']" value="" onKeyUp="calcular()"></td>
			</tr>';
		}
		echo '</table>';
		echo '|0';
		
	}
	else{
		echo "0||0";
	}
	exit();
}

if($_POST['ajax']==3){
	$res=mysql_query("SELECT * FROM condonacion_tarjetas_unidad WHERE estatus!='C' AND motivo='".$_POST['motivo']."' AND tarjeta='".$_POST['tarjeta']."' AND cve!='".$_POST['cve']."'");
	if(mysql_num_rows($res)>0)
		echo "0";
	else
		echo "1";
	exit();
}



top($_SESSION);

if($_POST['cmd']==3){
	mysql_query("UPDATE condonacion_tarjetas_unidad SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$res = mysql_query("SELECT fecha, hora FROM condonacion_tarjetas_unidad WHERE estatus != 'C' ORDER BY cve DESC LIMIT 1");
	$row = mysql_fetch_array($res);
	$fecha_limite = date( "Y-m-d H:i:s" , strtotime ( "+ 30 second" , strtotime($row['fecha'].' '.$row['hora']) ) );
	if($fecha_limite < date('Y-m-d H:i:s')){
		mysql_query("INSERT condonacion_tarjetas_unidad SET tarjeta='".$_POST['tarjeta']."',fecha_viaje='".$_POST['fecha_viaje']."',
			fecha='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
			derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
			monto='".$_POST['monto']."',motivo='".$_POST['motivo']."', obs = '{$_POST['obs']}' ") or die(mysql_error());
		$cvealteracion=mysql_insert_id();
		foreach($_POST['montos'] as $k=>$v){
			if($v>0){
				mysql_query("INSERT condonacion_tarjetas_unidad_detalle SET condonacion='$cvealteracion',tarjeta='$k',monto='$v'") or die(mysql_error()."INSERT condonacion_tarjetas_unidad_detalle SET condonacion='$cvealteracion',tarjeta='$k',monto='$v'");
			}
		}
		$textoimp="    Alteracion de Cuenta|";
		$textoimp.="Folio: ".$folio."|";
		$textoimp.="Fecha: ".fechaLocal()."|";
		$textoimp.="Motivo: ".$array_motivos[$_POST['motivo']]."|";
		$textoimp.="Folio Tarjeta: ".$_POST['tarjeta']."|";
		$textoimp.="Fecha Viaje: ".$_POST['fecha_viaje']."|";
		$textoimp.="Ope: (".$array_cveconductor[$_POST['operador']].")".$array_nomconductor[$row['operador']]."|";
		$textoimp.="Uni: ".$array_unidad[$_POST['unidad']]."|";
		$textoimp.="Derrotero: ".$array_derrotero[$_POST['derrotero']]."|";
		$textoimp.="Monto Alterado: ".number_format($_POST['monto'],2)."||";
		$textoimp.="La alteracion entrara en consideracion para su aceptacion en caso de que|";
		$textoimp.="no se acepte se le generara el cargo por el monto alterado.|||";
		$textoimp.="______________________________________|";
		$textoimp.="Firma Operador";
		$impresion='<iframe src="http://localhost/impresiongeneral.php?textoimp='.$textoimp.'" width=200 height=200></iframe>';
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1 && $_POST['reg']==0)
		echo '<td><a href="#" onClick="
				if(document.forma.unidad.value==\'\'){
					alert(\'No se ha cargado correctamente la unidad\');
				}
				else if(document.forma.motivo.value==\'0\'){
					alert(\'Necesita seleccionar el motivo\');
				}
				else if(document.forma.obs.value==\'\'){
					alert(\'Necesita ingresar la observacion\');
				}
				else if((document.forma.monto.value/1)<=0){
					alert(\'Necesita ingresar el monto alterado\');
				}
				else{
					atcr(\'condonacion_tarjetas_unidad.php\',\'\',\'2\',\''.$_POST['reg'].'\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'condonacion_tarjetas_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<input type="hidden" name="plaza" id="plaza" value="14">';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">--- Seleccione ---</option>';
	foreach($array_motivos as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	//echo '<input type="hidden" name="tarjeta" id="tarjeta" size="10" value="" class="readOnly" readOnly>';
	//echo '<tr><th align="left">Folio del Tarjeta</th><td><input type="text" name="folio" id="folio" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeViaje();}"></td></tr>';
	echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad" onChange="
	document.forma.unidad.value=\'\';
	document.forma.no_eco.value=\'\';
	document.forma.empresa.value=\'\';
	document.forma.nomempresa.value=\'\';">';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['localidad']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="'.$row['unidad'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeViaje();}"></td></tr>';
	echo '<input type="hidden" name="empresa" id="empresa" size="10" value="'.$row['empresa'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Empresa</th><td><input type="text" name="nomempresa" id="nomempresa" size="50" value="'.$array_empresa[$row['empresa']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><td colspan="2" id="capaviaje"></td></tr>';
	echo '<tr><th align="left">Monto Alterado</td><td><input type="text" class="readOnly" size="15" name="monto" id="monto" value="" readOnly></td></tr>';
	echo '<tr><th align="left">Observacion</td><td><textarea name="obs" id="obs" cols="50" rows="5"></textarea></td></tr>';
	echo '</table>';
	
	echo '<script>

				function calcular(){
					var total=0;
					$(".montos_tarjetas").each(function(){
						campo=$(this);
						if((campo.attr("saldo")/1)<(campo.val()/1)){
							campo.val(campo.attr("saldo"));
						}
						total += campo.val()/1;
					});
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeViaje(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","condonacion_tarjetas_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&empresa="+document.forma.empresa.value+"&localidad="+document.forma.localidad.value+"&no_eco="+document.forma.no_eco.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								var opciones2=objeto1.responseText.split("|");
								if(opciones2[0]=="0"){
									if(opciones2[2]=="0")
										alert("La unidad no existe");
									else{
										alert("La unidad esta dada de baja o inactiva");
									}
									document.forma.unidad.value="";
									document.forma.empresa.value="";
									document.forma.nomempresa.value="";
									document.getElementById("capaviaje").innerHTML="";
									document.forma.monto.value="";
								}
								else{
									document.forma.unidad.value=opciones2[0];
									document.forma.empresa.value=opciones2[1];
									document.forma.nomempresa.value=opciones2[2];
									document.getElementById("capaviaje").innerHTML=opciones2[3];
									document.forma.monto.value="";
								}
							}
						}
					}
				}
				
				
		</script>';
}

if($_POST['cmd']==0){
	
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'condonacion_tarjetas_unidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onclick="atcr(\'condonacion_tarjetas_unidad.php\',\'_blank\',\'excel\',0);"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excel</a>&nbsp;&nbsp;</td>-->
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
	echo '<tr><td>Motivo</td><td><select name="motivo" id="motivo"><option value="0">--- Todos ---</option>';
	foreach($array_motivos as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><td>Credencial</td><td><input type="text" name="clave" id="clave" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>No. Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="12" value=""></td></tr>';
	echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">--- Todos ---</option>';
	foreach($array_empresa as $k=>$v){
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
}
bottom();
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","condonacion_tarjetas_unidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&empresa="+document.getElementById("empresa").value+"&tarjeta="+document.getElementById("tarjeta").value+"&derrotero="+document.getElementById("derrotero").value+"&no_eco="+document.getElementById("no_eco").value+"&clave="+document.getElementById("clave").value+"&motivo="+document.getElementById("motivo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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
?>