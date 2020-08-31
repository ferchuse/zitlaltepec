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

$array_estatus=array();
$res=mysql_query("SELECT * FROM cat_estatus ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_estatus[$row['cve']]=$row['nombre'];
}


$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()");
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];

if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th>';
	echo '<th>Fecha</th><th>Tarjeta</th><th>Operador</th><th>Unidad</th>
	<th>Litros</th><th>Monto</th><th>Observaciones</th><th>Estatus</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$filtroruta="";
	$select="SELECT a.* FROM vale_disel a ";
	if($_POST['operador']!="") $select.=" INNER JOIN operadores as d ON (d.cve=a.operador AND d.cve='".$_POST['operador']."')";
	if($_POST['no_eco']!=""){ 
		$select.=" INNER JOIN unidades as e ON (e.cve=a.unidad";
		if($_POST['no_eco']!="") $select.=" AND e.no_eco='".$_POST['no_eco']."'";
		$select.=")";
	}
	$select.=" WHERE a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."'";
//	if($_POST['recaudacion']!='all') $select.=" AND a.recaudacion='".$_POST['recaudacion']."'";
	if($_POST['folio']!='') $select.=" AND a.cve='".$_POST['folio']."'";
	if($_POST['estatus']!='') $select.=" AND a.estatus='".$_POST['estatus']."'";
	if($_POST['usuario']!='') $select.=" AND a.usuario='".$_POST['usuario']."'";
	$select.=" ORDER BY a.cve DESC";
	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!='0'){
			echo '<a href="#" onClick="atcr(\'vale_disel.php\',\'_blank\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			if($nivelUsuario > 2 && $row['estatus']!=2)
				echo '&nbsp;&nbsp;<a href="#" onClick="alerta('.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar"></a>
						<input type="hidden" name="opcion" id="opcion" size="" value="'.$row['opcion'].'" class="readOnly" readOnly>';
						//"atcr(\'vale_disel.php\',\'\',3,\''.$row['cve'].'\')"
		}
		elseif($row['estatus']=='0'){
			echo 'CANCELADO';
			
			$factor=0;
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['tarjeta'].'</td>';
		echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
		echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
		echo '<td align="right">'.number_format($row['litros'],2).'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		//$totales[$c]+=round($row['monto'],2);
		$tot+=$row['monto'];
		$c++;
		echo '<td align="left">'.utf8_encode($row['obs']).'</td>';
		if($row['estatus']=='0'){
			echo '<td align="left">Cancelado</td>';
		}else{
		echo '<td align="left">'.$array_estatus[$row['estatus']].'</td>';
		}
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="7" align="left">'.$x.' Registro(s)</th>';
//	foreach($totales as $total)
		echo '<th align="right">'.number_format($tot,2).'</th>';
	echo '<th colspan="3">&nbsp;</th></tr>';
	echo '</table>';
	echo'<Script>
		function alerta(reg) 
{
var mensaje;
var opcion = prompt("Desea Cancelar el Registro:");
 
if (opcion != null) {
		 document.forma.opcion.value=opcion;
         atcr("vale_disel.php","",3,reg);
        } 

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
	
	</Script>';
	exit();
}

if($_POST['ajax']==2){
		

		$rsUni=mysql_query("SELECT cve,unidad,operador FROM tarjetas_unidad WHERE cve='".$_POST['tarjeta']."'");
		if($Uni=mysql_fetch_array($rsUni)){
			$_POST['unidad']=$Uni['cve'].'|'.$Uni['unidad'].'|'.$array_unidad[$Uni['unidad']].'|'.$Uni['operador'].'|'.$array_nomconductor[$Uni['operador']].'|';
		}
		else{
			$_POST['unidad']=0;
		}
	
	echo $_POST['unidad'];
		exit();
}

if($_POST['cmd']==10){
	$res = mysql_query("SELECT * FROM vale_disel WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(40)."  Vale de Diesel";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO DE TARJETA: ".$row['Tarjeta'];
		$texto.='|';
		$texto.=$row['fecha']." ".$row['hora'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."LITROS: ".number_format($row['litros'],2);
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
	mysql_query("UPDATE vale_disel SET estatus='0',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."',motivocan='".$_POST['opcion']."'
	WHERE cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
		mysql_query("INSERT vale_disel SET fecha='".fechaLocal()."',
			hora='".horaLocal()."',estatus='1',
			operador='".$_POST['operador']."',unidad='".$_POST['unidad']."',
			usuario='".$_POST['cveusuario']."',
			monto='".$_POST['monto']."',obs='".$_POST['obs']."',tarjeta='".$_POST['tarjeta']."',
			litros='".$_POST['litros']."'") or die(mysql_error());
		$cverecaudacion=mysql_insert_id();
		$res = mysql_query("SELECT * FROM vale_disel WHERE cve='".$cverecaudacion."'");
		$row = mysql_fetch_array($res);
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(40)."  Vale de Diesel";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO DE TARJETA: ".$row['tarjeta'];
		$texto.='|';
		$texto.=$row['fecha']." ".$row['hora'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."(".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."LITROS: ".number_format($row['litros'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."MONTO: ".number_format($row['monto'],2);
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."OBS:|".$row['obs'];
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&copia=1&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
	$_POST['cmd']=0;
}


if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>';
	$nivelUsuario=nivelUsuario();
	if($nivelUsuario>1)
		echo '<td><a href="#" onClick="
				if(document.forma.unidad.value==\'\')
					alert(\'No ha cargado correctamente la unidad\');
				else if(document.forma.operador.value==\'\')
					alert(\'No ha cargado correctamente el operador\');
				else{
					atcr(\'vale_disel.php\',\'\',\'2\',\'0\');
				}
				"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="atcr(\'vale_disel.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
	
	echo '</tr>';
	echo '</table>';
	echo '<br>';
	echo '<table>';
	echo '<tr><td class="tableEnc">Vale Diesel</td></tr>';
	echo '</table>';
	echo '<table>';
	
/*	echo '<tr><th align="left">Operador</th><td><select name="operador" id="operador" class="textField"><option value="">Seleccione</option>';
	foreach($array_nomconductor as $k=>$v){
		echo '<option value="'.$k.'"';
		if($row['operador']==$k) echo ' selected';
		echo '>'.$v.'</option>';
	}
	echo '</select></td></tr>';*/
	
	echo '<tr><th align="left">Tarjeta</th><td><input type="text" name="tarjeta_" id="tarjeta_" size="10" value="'.$row['tarjeta'].'" class="textField"><a href="#" onClick="traeUni();">**Buscar</a></td></tr>';
	echo '<tr><th align="left">Operador</th><td><input type="text" name="operador_" id="operador_" size="10" value="'.$row['operador'].'" class="textField"></td></tr>';
	echo '<input type="hidden" name="tarjeta" id="tarjeta" size="10" value="'.$row['tarjeta'].'" class="readOnly" readOnly>';
	echo '<input type="hidden" name="operador" id="operador" size="10" value="'.$row['operador'].'" class="readOnly" readOnly>';
	echo '<input type="hidden" name="unidad" id="unidad" size="10" value="'.$row['unidad'].'" class="readOnly" readOnly>';
	echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="'.$array_unidad[$row['unidad']].'" class="textField""></td></tr>';
	echo '<tr style="display:none"><th align="left">Litros</th><td><input type="text" name="litros" id="litros" size="10" value="'.$row['litros'].'" class="textField"></td></tr>';
	echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="textField"></td></tr>';
	echo '<tr><th align="left">Observaciones</th><td><textarea name="obs" id="obs" class="textField" cols="30" rows="3">'.$row['obs'].'</textarea></td></tr>';
	echo '</table>';
	
	echo '<script>
				function traeUni(){
					objeto1=crearObjeto();
					if (objeto1.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto1.open("POST","vale_disel.php",true);
						objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto1.send("ajax=2&tarjeta="+document.forma.tarjeta_.value);
						objeto1.onreadystatechange = function(){
							if (objeto1.readyState==4){
								//alert(objeto1.responseText);
								var opciones2=objeto1.responseText;
								if(opciones2=="0"){
									alert("La Tarjeta no existe");
									document.forma.no_eco.value="";
									document.forma.unidad.value="";
									document.forma.operador.value="";
									document.forma.operador_.value="";
									document.forma.litros.value="";
									document.forma.monto.value="";
									document.forma.tarjeta.value="";
									document.forma.tarjeta_.value="";
									document.forma.no_eco.tarjeta_();
								}
								else{
									var opciones3=objeto1.responseText.split("|");
									
										document.forma.tarjeta.value=opciones3[0];
										document.forma.unidad.value=opciones3[1];
										document.forma.no_eco.value=opciones3[2];
										document.forma.operador.value=opciones3[3];
										document.forma.operador_.value=opciones3[4];
										document.forma.litros.focus();
									
								}
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
			<td><a href="#" onClick="atcr(\'vale_disel.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
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
	echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>Operador</td><td><select name="operador" id="operador">';

		echo '<option value="">--- Todos ---</option>';
	foreach($array_nomconductor as $k=>$v){

			echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">--- Todos ---</option>
	<option value=0>Cancelado</option>';
	
	foreach($array_estatus as $k=>$v){
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
			objeto.open("POST","vale_disel.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&operador="+document.getElementById("operador").value+"&folio="+document.getElementById("folio").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&estatus="+document.getElementById("estatus").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	function alerta(reg) 
{
var mensaje;
var opcion = prompt("Desea Cancelar el Registro:");
 
if (opcion != null) {
		 document.forma.opcion.value=opcion;
         atcr("vale_disel.php","",3,reg);
        } 

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