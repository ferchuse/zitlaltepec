<?php
include("main.php");
/*$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$array_taquilla=array();
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_taquilla[$row['cve']]=$row['nombre'];
}
$array_recaudacion=array();
$res=mysql_db_query($base,"SELECT * FROM recaudaciones ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_recaudacion[$row['cve']]=$row['nombre'];
}
$res=mysql_db_query($base,"SELECT * FROM costo_boletos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_costo[$row['cve']]=$row['nombre'];
}*/
$res=mysql_db_query($base,"SELECT * FROM unidades ORDER BY cve");
while($row=mysql_fetch_array($res)){
	$array_parque[$row['cve']]=$row['no_eco'];
	$array_parque_[$row['no_eco']]=$row['cve'];
}




if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Terminal</th><th>Folio</th>';
	echo '<th>Fecha</th><th>Hora</th><th>No Eco</th><th>Costo</th><th>Usuario</th>
	<th>Folio Recaudacion</th><th>Fecha Recaudacion</th>
	</tr>'; 
	$x=0;
	$nivelUsuario = nivelUsuario();
	$select="SELECT * FROM boletos_taquillamovil WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
//	if($_POST['taquilla'] != 'all') $select .= " AND taquilla='".$_POST['taquilla']."'";
//	if($_POST['costo'] != 'all') $select .= " AND costo='".$_POST['costo']."'";
//	if($_POST['usuario'] != '') $select .= " AND usuario='".$_POST['usuario']."'";
//	if($_POST['folio_recaudacion'] != '') $select .= " AND folio_recaudacion='".$_POST['folio_recaudacion']."'";
	if($_POST['no_eco'] != '') $select .= " AND unidad='".$array_parque_[$_POST['no_eco']]."'";
	$select.=" ORDER BY fecha,hora";
	$res=mysql_db_query($base,$select) or die(mysql_error());
	$totales==0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']!="A"){
			echo 'CANCELADO';
			$factor=0;
			$row['monto']=0;
		}
		elseif($nivelUsuario > 2 && $row['folio_recaudacion']==0 && $row['estatus']=="A"){
			echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el boleto?\')) cancelar_boleto(\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
		}
		else{
			echo '&nbsp;';
		}
		echo '</td>';
		echo '<td align="center">'.$row['terminal'].'</td>';
		echo '<td align="center">'.substr($row['codigo'],3,6).'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['hora'].'</td>';
	echo '<td align="center">'.$array_parque[$row['unidad']].'</td>';
		echo '<td align="right">'.number_format($row['monto'],2).'</td>';
		$c=0;
		$totales=$totales+$row['monto'];
		echo '<td align="center">'.$row['usuario'].'</td>';
		echo '<td align="center">'.$row['folio_recaudacion'].'</td>';
		echo '<td align="center">'.$row['fecha_recaudacion'].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="5" align="left">'.$x.' Registro(s)</th>';
		echo '<th align="right">Total</th>';
		echo '<th align="right">'.number_format($totales,2).'</th>';
	echo '<th colspan="3">&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==3){
	mysql_db_query($base,"UPDATE boletos_taquillamovil SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['cveboleto']."'");
	exit();
}

top($_SESSION);




if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr style="display:none;"><td>Taquilla</td><td><select name="taquilla" id="taquilla"><option value="all">--- Todos ---</option>';
	foreach($array_taquilla as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><td>Precio</td><td><select name="costo" id="costo"><option value="all">--- Todos ---</option>';
	foreach($array_costo as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr style="display:none;"><td>Folio Recaudacion</td><td><input type="text" name="folio_recaudacion" id="folio_recaudacion" class="textField" size="12" value=""></td></tr>';
	echo '<tr><td>No. Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="12" value=""></td></tr>';
	echo '<tr style="display:none;"><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
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
			objeto.open("POST","boletos_taquillamovil.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&costo="+document.getElementById("costo").value+"&folio_recaudacion="+document.getElementById("folio_recaudacion").value+"&taquilla="+document.getElementById("taquilla").value+"&no_eco="+document.getElementById("no_eco").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&usuario="+document.getElementById("usuario").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function cancelar_boleto(cveboleto)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","boletos_taquillamovil.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=3&cveboleto="+cveboleto+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
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