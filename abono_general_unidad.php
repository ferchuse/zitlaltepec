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

$res=mysql_query("SELECT * FROM motivos_abono_extraordinario ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivos[$row['cve']]=$row['nombre'];
}

$array_estatusviaje=array("A"=>'<font color="RED">Por pagar</font>',"P"=>"Pagado","C"=>"Cancelado");
$array_estatusviaje2=array("A"=>'Por pagar',"P"=>"Pagado","C"=>"Cancelado");



if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
	echo '<th>Folio</th><th>Fecha Captura</th><th>Fecha Aplicacion</th><th>Empresa</th><th>Unidad</th>
	<th>Motivo</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$t=0;
	$filtro="";
	$filtroconductor="";
	$filtrounidad="";
	if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['empresa']!='all') $filtro.=" AND a.empresa='".$_POST['empresa']."'";
	if($_POST['no_eco']!="") $filtrounidad=" AND c.no_eco='".$_POST['no_eco']."'";
	if($_POST['usuario']!='') $filtro.=" AND a.usuario='".$_POST['usuario']."'";
	
	$res=mysql_query("SELECT a.* FROM abono_general_unidad as a 
	inner join unidades as c on (c.cve=a.unidad $filtrounidad) 
	WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
	$total=0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$aux='';
		echo '<td align="center">';
		if($row['estatus']!='C'){
			echo '<a href="#" onClick="atcr(\'abono_general_unidad.php\',\'\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if(nivelUsuario()>2){
				echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ atcr(\'abono_general_unidad.php\',\'\',3,\''.$row['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
			}
		}
		else{
			echo 'CANCELADO';
			$aux='<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
			$row['monto']=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['fecha_aplicacion'].'</td>';
		echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="left">'.utf8_encode($array_motivos[$row['motivo']]).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="left">'.utf8_encode($row['obs']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$total +=$row['monto'];
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="7" align="left">'.$x.' Registro(s)</th><th align="right">'.number_format($total,2).'</th>
	<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	if($_POST['unidad']==0){
		$rsUni=mysql_query("SELECT cve,estatus,empresa,derrotero,monto_cuenta FROM unidades WHERE no_eco='".strtoupper($_POST['no_eco'])."' AND localidad='".$_POST['localidad']."'");
		if($Uni=mysql_fetch_array($rsUni)){
			$_POST['unidad']=$Uni['cve'].'|'.$Uni['estatus'].'|'.$Uni['empresa'].'|'.utf8_encode($array_empresa[$Uni['empresa']]).'|'.$Uni['derrotero'].'|'.utf8_encode($array_derrotero[$Uni['derrotero']]);
		}
		else{
			$_POST['unidad']=0;
		}
	}
	echo $_POST['unidad'];
	exit();
}




if($_GET['viene_recaudacion'] == 1)
	top($_SESSION, 0, true);
else
	top($_SESSION);

if($_POST['cmd']==10){
	$res=mysql_query("SELECT * FROM abono_general_unidad WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	$mensaje="&nbsp;";
	$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)." ABONO GENERAL";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10).'|';
		$texto.="FECHA: ".$row['fecha'].' '.$row['hora'];
		$texto.='|';
		$texto.="FECHA APL: ".$row['fecha_aplicacion'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."EMPRESA: ".$array_empresa[$row['empresa']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MOTIVO: ".$array_motivo[$row['motivo']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
		$texto.='|OBSERVACIONES:|'.$row['obs'];
		
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$row['cve']).'" width=200 height=200></iframe>';
	
	$_POST['cmd']=0;
}


if($_POST['cmd']==3){
	if(nivelUsuario() > 2){
		mysql_query("UPDATE abono_general_unidad SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}
}

if($_POST['cmd']==2){
		mysql_query("INSERT abono_general_unidad SET fecha_aplicacion='".$_POST['fecha_aplicacion']."',
		fecha='".fechaLocal()."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
		derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
		monto='".$_POST['monto']."',motivo='".$_POST['motivo']."',obs='".$_POST['obs']."'") or die(mysql_error());
		$_POST['reg']=mysql_insert_id();

	
		$mensaje="<b>Se genero el abono general: ".$_POST['reg']." de la unidad ".$array_unidad[$_POST['unidad']]."</b>";
		$res=mysql_query("SELECT * FROM abono_general_unidad WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)." ABONO GENERAL";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10).'|';
		$texto.="FECHA: ".$row['fecha'].' '.$row['hora'];
		$texto.='|';
		$texto.="FECHA APL: ".$row['fecha_aplicacion'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."EMPRESA: ".$array_empresa[$row['empresa']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MOTIVO: ".$array_motivo[$row['motivo']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
		$texto.='|OBSERVACIONES:|'.$row['obs'];
		
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$row['cve']).'" width=200 height=200></iframe>';

	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();
				if(document.forma.fecha_aplicacion.value==\'\'){
					alert(\'Necesita ingresar la fecha de aplicacion\');
					$(\'#panel\').hide();
				}
				else if(document.forma.unidad.value==\'\'){
					alert(\'No ha cargado correctamente la unidad\');
					$(\'#panel\').hide();
				}
				else if(document.forma.motivo.value==\'0\'){
					alert(\'Necesita seleccionar el motivo\');
					$(\'#panel\').hide();
				}
				else if((document.forma.monto.value/1)<=0){
					alert(\'El abono debe de ser mayor a cero\');
					$(\'#panel\').hide();
				}
				else{
					atcr(\'abono_general_unidad.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'abono_general_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Abono General</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" class="readOnly" name="fecha_aplicacion" id="fecha_aplicacion" value="'.fechaLocal().'" size="15" readOnly>';
	if(nivelUsuario()>2) echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
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
	echo '<tr><th align="left">Empresa</th><td><input type="text" name="nomempresa" id="nomempresa" size="50" value="'.$array_empresa[$row['empresa']].'" class="readOnly" readOnly></td></tr>';
	echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="'.$row['derrotero'].'" class="readOnly" readOnly>';
	echo '<tr style="display:none;"><th align="left">Derrotero</th><td><input type="text" name="nomderrotero" id="nomderrotero" size="50" value="'.$array_derrotero[$row['derrotero']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo"><option value="0">--- Seleccione ---</option>';
	foreach($array_motivos as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea cols="50" rows="3" class="textField" name="obs" id="obs">'.$row['obs'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
				function traeUni(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","abono_general_unidad.php",true);
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
								
				
		</script>';
}

if($_POST['cmd']==0){
	if($mensaje!=""){
		echo $mensaje;
		echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
	}
	if($_POST['viene_recaudacion']==1){
		echo '<script>setTimeout("window.close()",4000);</script>';
	exit();
	}
	//Busqueda
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'abono_general_unidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			<!--<td><a href="#" onClick="atcr(\'abono_general_unidad.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>-->
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="5" value=""></td></tr>';
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
			objeto.open("POST","abono_general_unidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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