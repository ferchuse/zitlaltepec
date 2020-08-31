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


if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
	echo '<th>Folio</th><th>Fecha Captura</th><th>Fecha Aplicacion</th><th>Unidad Origen</th><th>Empresa Origen</th>
	<th>Unidad Destino</th><th>Empresa Destino</th><th>Monto</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$filtro = "";
	if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['no_ecoori']!="") $filtro=" AND b.no_eco='".$_POST['no_ecoori']."'";
	if($_POST['no_ecodes']!="") $filtro=" AND c.no_eco='".$_POST['no_ecodes']."'";
	if($_POST['usuario']!='') $filtro.=" AND a.usuario='".$_POST['usuario']."'";
	$res=mysql_query("SELECT a.* FROM traspaso_unidades as a 
	inner join unidades as b on b.cve = a.unidad_origen
	inner join unidades as c on c.cve = a.unidad_destino
	WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
	$total=0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$aux='';
		echo '<td align="center">';
		if($row['estatus']!='C'){
			if(nivelUsuario()>2){
				echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ atcr(\'traspaso_unidades.php\',\'\',3,\''.$row['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
			}
		}
		else{
			echo 'CANCELADO';
			$row['monto']=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['fecha_aplicacion'].'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad_origen']].'</td>';
		echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa_origen']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad_destino']].'</td>';
		echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa_destino']]).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		echo '<td align="left">'.utf8_encode($row['obs']).'</td>';
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
		$total+=$row['monto'];
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="8" align="left">'.$x.' Registro(s)</th>
	<th align="right">'.number_format($total,2).'</th>
	<th colspan="2">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	if($_POST['unidad']==0){
		$rsUni=mysql_query("SELECT cve,estatus,empresa FROM unidades WHERE no_eco='".strtoupper($_POST['no_eco'])."' AND localidad='".$_POST['localidad']."'");
		if($Uni=mysql_fetch_array($rsUni)){
			$_POST['unidad']=$Uni['cve'].'|'.$Uni['estatus'].'|'.$Uni['empresa'].'|'.utf8_encode($array_empresa[$Uni['empresa']]).'|';
			if($_POST['postfijo']=='origen') $_POST['unidad'] .= saldo_unidad($Uni['cve']);
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
		mysql_query("UPDATE traspaso_unidades SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}
}

if($_POST['cmd']==2){
		mysql_query("INSERT traspaso_unidades SET fecha_aplicacion='".$_POST['fecha_aplicacion']."',
		fecha='".fechaLocal()."',unidad_origen='".$_POST['unidad_origen']."',hora='".horaLocal()."',
		unidad_destino='".$_POST['unidad_destino']."',empresa_origen='".$_POST['empresa_origen']."',
		usuario='".$_POST['cveusuario']."',empresa_destino='".$_POST['empresa_destino']."',estatus='A',
		monto='".$_POST['monto']."',saldo='".$_POST['saldo']."',obs='".$_POST['obs']."'") or die(mysql_error());
		$_POST['reg']=mysql_insert_id();


	$_POST['cmd']=0;
}

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	if(nivelUsuario()>1 && $row['cverec']==0)
		echo '<td><a href="#" onClick="
				if(document.forma.fecha_aplicacion.value==\'\')
					alert(\'Necesita ingresar la fecha de viaje\');
				else if(document.forma.unidad_origen.value==\'\')
					alert(\'No ha cargado correctamente la unidad origen\');
				else if(document.forma.unidad_destino.value==\'\')
					alert(\'No ha cargado correctamente la unidad destino\');
				else if(document.forma.unidad_origen.value==document.forma.unidad_destino.value)
					alert(\'No pueden ser igual la unidad origen y destino\');
				else if((document.forma.monto.value/1)<=0)
					alert(\'El monto debe de ser mayor a cero\');
				else{
					atcr(\'traspaso_unidades.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="atcr(\'traspaso_unidades.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Traspaso entre Unidades</td></tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" class="readOnly" name="fecha_aplicacion" id="fecha_aplicacion" value="'.fechaLocal().'" size="15" readOnly>';
	echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	echo '</td></tr>';
	echo '<tr><th align="left">Localidad Origen</th><td><select name="localidad_origen" id="localidad_origen" onChange="
	document.forma.unidad_origen.value=\'\';
	document.forma.no_eco_origen.value=\'\';
	document.forma.empresa_origen.value=\'\';
	document.forma.nomempresa_origen.value=\'\';">';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['localidad_origen']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="unidad_origen" id="unidad_origen" size="10" value="'.$row['unidad_origen'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Unidad Origen</th><td><input type="text" name="no_eco_origen" id="no_eco_origen" size="10" value="'.$array_unidad[$row['unidad_origen']].'" class="textField" onKeyUp="if(event.keyCode==13){ traeUni(\'origen\');}"></td></tr>';
	echo '<input type="hidden" name="empresa_origen" id="empresa_origen" size="10" value="'.$row['empresa_origen'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Empresa Origen</th><td><input type="text" name="nomempresa_origen" id="nomempresa_origen" size="50" value="'.$array_empresa[$row['empresa_origen']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Saldo</th><td><input type="text" name="saldo" id="saldo" size="50" value="'.$row['saldo'].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Localidad Destino</th><td><select name="localidad_destino" id="localidad_destino" onChange="
	document.forma.unidad_destino.value=\'\';
	document.forma.no_eco_destino.value=\'\';
	document.forma.empresa_destino.value=\'\';
	document.forma.nomempresa_destino.value=\'\';">';
	foreach($array_localidad as $k=>$v){
		echo '<option value="'.$k.'"';
		if($k==$row['localidad_origen']) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<input type="hidden" name="unidad_destino" id="unidad_destino" size="10" value="'.$row['unidad_destino'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Unidad Destino</th><td><input type="text" name="no_eco_destino" id="no_eco_destino" size="10" value="'.$array_unidad[$row['unidad_destino']].'" class="textField" onKeyUp="if(event.keyCode==13){ traeUni(\'destino\');}"></td></tr>';
	echo '<input type="hidden" name="empresa_destino" id="empresa_destino" size="10" value="'.$row['empresa_destino'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Empresa Destino</th><td><input type="text" name="nomempresa_destino" id="nomempresa_destino" size="50" value="'.$array_empresa[$row['empresa_destino']].'" class="readOnly" readOnly></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="50" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" cols="30" rows="3">'.$row['obs'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
				function traeUni(postfijo){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","traspaso_unidades.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&localidad="+document.getElementById("localidad_"+postfijo).value+"&no_eco="+document.getElementById("no_eco_"+postfijo).value+"&postfijo="+postfijo);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText;
								if(opciones2=="0"){
									alert("La unidad no existe");
									document.getElementById("no_eco_"+postfijo).value="";
									document.getElementById("unidad_"+postfijo).value="";
									document.getElementById("empresa_"+postfijo).value="";
									document.getElementById("nomempresa_"+postfijo).value="";
									document.getElementById("no_eco_"+postfijo).focus();
									if(postfijo=="origen")
										document.forma.saldo.value="";
								}
								else{
									var opciones3=objeto1.responseText.split("|");
									if(opciones3[1]=="1"){
										document.getElementById("unidad_"+postfijo).value=opciones3[0];
										document.getElementById("empresa_"+postfijo).value=opciones3[2];
										document.getElementById("nomempresa_"+postfijo).value=opciones3[3];
										if(postfijo=="origen")
											document.forma.saldo.value=opciones3[4];
									}
									else if(opciones3[1]=="2"){
										alert("La unidad esta dada de baja");
										document.getElementById("no_eco_"+postfijo).value="";
										document.getElementById("unidad_"+postfijo).value="";
										document.getElementById("empresa_"+postfijo).value="";
										document.getElementById("nomempresa_"+postfijo).value="";
										document.getElementById("no_eco_"+postfijo).focus();
										if(postfijo=="origen")
											document.forma.saldo.value="";
									}
									else{
										alert("La unidad esta inactiva");
										document.getElementById("no_eco_"+postfijo).value="";
										document.getElementById("unidad_"+postfijo).value="";
										document.getElementById("empresa_"+postfijo).value="";
										document.getElementById("nomempresa_"+postfijo).value="";
										document.getElementById("no_eco_"+postfijo).focus();
										if(postfijo=="origen")
											document.forma.saldo.value="";
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
			<td><a href="#" onClick="atcr(\'traspaso_unidades.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>No Eco Origen</td><td><input type="text" name="no_ecoori" id="no_ecoori" class="textField" size="5" value=""></td></tr>';
	echo '<tr><td>No Eco Destino</td><td><input type="text" name="no_ecodes" id="no_ecodes" class="textField" size="5" value=""></td></tr>';
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
			objeto.open("POST","traspaso_unidades.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_ecoori="+document.getElementById("no_ecoori").value+"&no_ecodes="+document.getElementById("no_ecodes").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
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