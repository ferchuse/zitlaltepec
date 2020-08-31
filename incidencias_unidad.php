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

$rsUnidad=mysql_query("SELECT * FROM unidades");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
	$array_empunidad[$Unidad['cve']]=$Unidad['empresa'];
}
$array_derrotero=array();
$res=mysql_query("SELECT * FROM derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derrotero[$row['cve']]=$row['nombre'];
	$array_montoderrotero[$row['cve']]=$row['monto'];
}

$res=mysql_query("SELECT * FROM motivos_incidencias ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivo[$row['cve']]=$row['nombre'];
}

if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
	echo '<th>Folio</th><th>Fecha Captura</th><th>Empresa</th><th>Unidad</th><th>Derrotero</th><th>Motivo</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Concepto</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$t=0;
	$filtro="";
	$filtrounidad="";
	if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['derrotero']!='all') $filtro.=" AND a.derrotero='".$_POST['derrotero']."'";
	if($_POST['empresa']!='all') $filtro.=" AND a.empresa='".$_POST['empresa']."'";
	if($_POST['no_eco']!="") $filtrounidad=" AND c.no_eco='".$_POST['no_eco']."'";
	if($_POST['usuario']!='') $filtro.=" AND a.usuario='".$_POST['usuario']."'";
	
	$res=mysql_query("SELECT a.* FROM incidencias_unidad as a 
	inner join unidades as c on (c.cve=a.unidad $filtrounidad) 
	WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
	$nivelUsuario=nivelUsuario();
	while($row=mysql_fetch_array($res)){
		rowb();
		$aux='';
		echo '<td align="center">';
		if($row['estatus']!='C'){
			if($row['estatus']!='P' && $nivelUsuario>2){
				echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ atcr(\'incidencias_unidad.php\',\'\',3,\''.$row['cve'].'\');"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
			}
		}
		else{
			echo 'CANCELADO';
			$aux='<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
		echo '<td align="left">'.utf8_encode($array_motivo[$row['motivo']]).'</td>';
		echo '<td align="center">'.$row['fecha_ini'].'</td>';
		echo '<td align="center">'.$row['fecha_fin'].'</td>';
		echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
		echo '<td align="left">'.utf8_encode($row['concepto']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="12" align="left">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	if($_POST['unidad']==0){
		$rsUni=mysql_query("SELECT cve,estatus,derrotero FROM unidades WHERE no_eco='".strtoupper($_POST['no_eco'])."' AND localidad='".$_POST['localidad']."' AND empresa='".$_POST['empresa']."'");
		if($Uni=mysql_fetch_array($rsUni)){
			$_POST['unidad']=$Uni['cve'].'|'.$Uni['estatus'].'|'.$Uni['derrotero'].'|'.$array_derrotero[$Uni['derrotero']];
		}
		else{
			$_POST['unidad']=0;
		}
	}
	echo $_POST['unidad'];
	exit();
}


top($_SESSION);



if($_POST['cmd']==3){
	if(nivelUsuario() > 2){
		mysql_query("UPDATE incidencias_unidad SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}
}

if($_POST['cmd']==2){
		mysql_query("INSERT incidencias_unidad SET fecha_ini='".$_POST['fecha_ini']."',fecha_fin='".$_POST['fecha_fin']."',
		fecha='".fechaLocal()."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
		derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
		motivo='".$_POST['motivo']."'") or die(mysql_error());
		$_POST['reg']=mysql_insert_id();
	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1 && $row['cverec']==0)
		echo '<td><a href="#" onClick="
				f(document.forma.unidad.value==\'\')
					alert(\'No ha cargado correctamente la unidad\');
				else if(document.forma.motivo.value==\'0\')
					alert(\'Necesita seleccionar el motivo\');
				else if(document.forma.fecha_ini.value==\'\')
					alert(\'Necesita seleccionar al fecha inicio\');
				else if(document.forma.fecha_fin.value==\'\')
					alert(\'Necesita seleccionar la fecha fin\');
				else{
					atcr(\'incidencias_unidad.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'incidencias_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><th align="left">Empresa</th><td><select name="empresa" id="empresa" onChange="
	document.forma.unidad.value=\'\';
	document.forma.no_eco.value=\'\';
	document.forma.derrotero.value=\'\';
	document.forma.nomderrotero.value=\'\';"><option value="0">Seleccione</option>';
	foreach($array_empresa as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['empresa']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad" onChange="
	document.forma.unidad.value=\'\';
	document.forma.no_eco.value=\'\';
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
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="'.$row['derrotero'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Derrotero</th><td><input type="text" name="nomderrotero" id="nomderrotero" size="50" value="'.$array_derrotero[$row['derrotero']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">Seleccione</option>';
	foreach($array_motivo as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['motivo']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Fecha Inicio</th><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><th align="left">Fecha Fin</th><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	
	echo '<tr><th align="left">Concepto</th><td><textarea name="concepto" id="concepto" cols="50" rows="3"></textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
				function traeUni(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","incidencias_unidad.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&localidad="+document.forma.localidad.value+"&empresa="+document.forma.empresa.value+"&no_eco="+document.forma.no_eco.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText;
								if(opciones2=="0"){
									alert("La unidad no existe");
									document.forma.no_eco.value="";
									document.forma.unidad.value="";
									document.forma.derrotero.value="";
									document.forma.nomderrotero.value="";
									document.forma.no_eco.focus();
								}
								else{
									var opciones3=objeto1.responseText.split("|");
									if(opciones3[1]=="1"){
										document.forma.unidad.value=opciones3[0];
										document.forma.derrotero.value=opciones3[2];
										document.forma.nomderrotero.value=opciones3[3];
										document.forma.monto.value=opciones3[4];
									}
									else if(opciones3[1]=="2"){
										alert("La unidad esta dada de baja");
										document.forma.no_eco.value="";
										document.forma.unidad.value="";
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
								
				
		</script>';
}

if($_POST['cmd']==0){
	if($mensaje!=""){
		echo $mensaje;
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'incidencias_unidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onClick="atcr(\'incidencias_unidad.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>-->
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="5" value=""></td></tr>';
	echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">--- Todos ---</option>';
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
			objeto.open("POST","incidencias_unidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&derrotero="+document.getElementById("derrotero").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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