<?
include("main.php");
$array_cargo = array(1=>'Administracion', 2=>'Seguro Interno', 3=>'Mutualidad', 4=>'Prorrata',5=>'Seguridad', 6=>'Fianza');

//ARREGLOS
$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].' - '.$Unidad['propietario'];
}

$rsconductor=mysql_db_query($base,"SELECT * FROM conductores");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_conductor[$Conductor['cve']]=$Conductor['credencial'].' - '.$Conductor['nombre'];
}

$rsBenef=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas WHERE 1");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_benef[$Benef['cve']]=$Benef['nombre'];
}

$rspersonal=mysql_db_query($base,"SELECT * FROM personal");
while($Personal=mysql_fetch_array($rspersonal)){
	$array_personal[$Personal['cve']]=$Personal['nombre'];
}

$rsMotivos=mysql_db_query($base,"SELECT * FROM motivos_salida WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivos)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$array_estatussalidas=array("En Proceso","Pagado","Cancelado");

if($_POST['ajax']==1){
	$filtro="";
	$select="SELECT b.cve,b.nombre,sum(a.monto) as importe FROM recibos_salidas as a
	INNER JOIN motivos_salida as b on (b.cve=a.motivo)";
	$select.="
	WHERE a.estatus='A' ";
	if ($_POST['fecha_ini']!="") {$select.=" AND a.fecha_aplicacion>='".$_POST['fecha_ini']."'";}
	if ($_POST['fecha_fin']!="") {$select.=" AND a.fecha_aplicacion<='".$_POST['fecha_fin']."'";}
	if ($_POST['cargo']!="all") { $select.=" AND a.cargo='".$_POST['cargo']."' "; }
//	if ($_POST['plaza']!="all") {$select.=" AND a.plaza='".$_POST['plaza']."'";}
	$select.=" GROUP BY a.motivo ORDER BY b.nombre";
	$rsmotivos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsmotivos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		if($_SESSION['PlazaUsuario']==0){ 
			$col=3;
		}
		else{
			$col=2;
		}
		
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_SESSION['PlazaUsuario']==0)
			echo '<th>Plaza</th>';
		echo '<th>Motivo</th><th>Importe</th>';
		echo '</tr>'; 
		$x=0;
		$total=0;
		while ($Motivos=mysql_fetch_array($rsmotivos)) {
			rowb();
			echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'salidas_motivos.php\',\'\',\'1\','.$Motivos['cve'].')"><img src="images/b_search.png" border="0" title="Ver"></a></td>';
			if($_SESSION['PlazaUsuario']==0)
				echo '<td>'.htmlentities($array_plaza[$Motivos['plaza']]).'</td>';
			echo '<td>'.htmlentities(utf8_encode($Motivos['nombre'])).'</td>';
			echo '<td align="right">'.number_format($Motivos['importe'],2).'</td>';
			echo '</tr>';
			$x++;
			$total+=$Motivos['importe'];
		}
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">'.number_format($total,2).'</td>';
		echo '</tr>';
		echo '</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}

top($_SESSION);

if($_POST['cmd']==1){
	echo '<input type="hidden" name="plaza" id="plaza">';
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'salidas_motivos.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
		</tr>';
	echo '</table>';
	echo '<br>';
	$tit="";
	$select="SELECT * FROM recibos_salidas WHERE motivo='".$_POST['reg']."' ";
	if ($_POST['fecha_ini']!="") {$select.=" AND fecha_aplicacion>='".$_POST['fecha_ini']."'"; $tit.=" del ".$_POST['fecha_ini'];}
	if ($_POST['fecha_fin']!="") {$select.=" AND fecha_aplicacion<='".$_POST['fecha_fin']."'"; $tit.=" al ".$_POST['fecha_ini'];}
	if ($_POST['cargo']!="all") { $select.=" AND cargo='".$_POST['cargo']."' "; }
//	if ($_POST['searchplaza']!="all") {$select.=" AND plaza='".$_POST['searchplaza']."'";}
	$select.=" ORDER BY fecha DESC";
	//echo $select;
	$rsmotivos=mysql_db_query($base,$select) or die(mysql_error());
	echo '<table>';
	echo '<tr><td class="tableEnc">Salidas por Motivo '.$array_motivo[$_POST['reg']].$tit.'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	if($_SESSION['PlazaUsuario']==0) $col=8;
	else $col=7;
	echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsmotivos).' Registro(s)</td></tr>';
	echo '<tr bgcolor="#E9F2F8"><th>Imprimir</th>';
	if($_SESSION['PlazaUsuario']==0) echo '<th>Plaza</th>';
	echo '<th>Folio</th><th>Fecha</th><th>Monto</th><th>Beneficiario</th><th>Estatus</th><th>Usuario</th>';
	echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
	$total=0;
	$i=0;
	while($Salida=mysql_fetch_array($rsmotivos)) {
		rowb();
		echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.plaza.value=\''.$Salida['plaza'].'\';atcr(\'imp_recibo_salida.php\',\'_bank\',\'1\','.$Salida['cve'].')"><img src="images/b_print.png" border="0" title="Editar '.$Salida['nombre'].'"></a></td>';
		if($_SESSION['PlazaUsuario']==0)
			echo '<td>'.htmlentities($array_plaza[$Salida['plaza']]).'</td>';
		echo '<td align="center">'.$Salida['cve'].'</td>';
		echo '<td align="center">'.$Salida['fecha'].'</td>';
		if($Salida['estatus']=="C"){$Salida['monto']=0;}
		echo '<td align="center">$ '.number_format($Salida['monto'],2).'</td>';
		$beneficiario="";
//		if($Salida['tipo_beneficiario']==0)
			$beneficiario=$array_benef[$Salida['beneficiario']];
//		elseif($Salida['tipo_beneficiario']==1)
//			$beneficiario=$array_unidad[$Salida['beneficiario']];
//		elseif($Salida['tipo_beneficiario']==2)
//			$beneficiario=$array_conductor[$Salida['beneficiario']];
//		elseif($Salida['tipo_beneficiario']==3)
//			$beneficiario=$array_personal[$Salida['beneficiario']];
		echo '<td align="left">'.htmlentities($beneficiario).'</td>';
		if($Salida['estatus']=="A"){$esta="Activo";}else{$esta="Cancelado";}
		echo '<td align="center">'.$esta.'</td>';
		echo '<td align="left">'.htmlentities($array_usuario[$Salida['usuario']]).'</td>';
		$total+=$Salida['monto'];
		$i++;
		echo '</tr>';
	}
	if($_SESSION['PlazaUsuario']==0)$col=3;
	else $col=2;
	echo '	
		<tr>
		<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
		<td bgcolor="#E9F2F8" align="right">Total:</td>
		<td bgcolor="#E9F2F8" align="center">$ '.number_format($total,2).'</td>
		<td colspan="4" bgcolor="#E9F2F8">&nbsp;</td>
		</tr>
	</table>';
}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Seccion</td><td><select name="cargo" id="cargo">';
		echo '<option value="all">--- Todos ---</option>';
		foreach($array_cargo as $k=>$v){
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
			objeto.open("POST","salidas_motivos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("searchplaza").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cargo="+document.getElementById("cargo").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
			
	';	
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

