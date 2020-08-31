<?php
include("main.php");
include("numlet.php");

$res=mysql_db_query($base,"SELECT * FROM unidades ORDER BY cve");
while($row=mysql_fetch_array($res)){
	$array_parque[$row['cve']]=$row['no_eco'];
	$array_parque_[$row['no_eco']]=$row['cve'];
}

$array_propietario=array();
$res=mysql_db_query($base,"SELECT * FROM permisionarios ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_propietario[$row['cve']]=$row['nombre'];
}

$array_derrotero=array();
$res=mysql_db_query($base,"SELECT * FROM derroteros ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_derrotero[$row['cve']]=$row['nombre'];
}



if($_POST['ajax']==1){
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
	echo '<th>Folio</th><th>Fecha</th><th>Terminal</th><th>Estacion</th><th>Economico</th><th>Latitud</th><th>Longitud</th><th>Observaciones</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$t=0;
	$filtro="";
	$filtroconductor="";
	$filtrounidad="";
	$nivelUsuario = nivelUsuario();
	if($_POST['folio']!="") $filtro.=" AND a.folio='".$_POST['folio']."'";
	if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
	if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
	if($_POST['derrotero']!='all') $filtro.=" AND c.derrotero='".$_POST['derrotero']."'";
	if($_POST['no_eco']!="") $filtro=" AND c.no_eco='".$_POST['no_eco']."'";
	if($_POST['usuario']!='') $filtro.=" AND a.id_usuario='".$_POST['usuario']."'";
	if($_POST['terminal']!='') $filtro.=" AND a.id_terminal='".$_POST['terminal']."'";
	$res=mysql_query("SELECT a.*, c.no_eco, c.derrotero, c.permisionario FROM salidas_taquillamovil as a 
	INNER JOIN unidades as c on (c.cve=a.unidad) 
	WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
	$t=0;
	$t2=0;
	while($row=mysql_fetch_array($res)){
		rowb();
		$aux='';
		echo '<td align="center">';
		if($row['estatus']!='C'){
			if($nivelUsuario >2){
				echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ obs=prompt(\'Motivo:\'); atcr(\'salidas_taquillamovil.php?obs=\'+obs,\'\',3,\''.$row['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
			}
		}
		else{
			$row['monto']=0;
			$row['gastos']=0;
			echo 'CANCELADO<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
		}
		echo '</td>';
		echo '<td align="center">'.$row['folio'].'</td>';
		echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
		echo '<td align="center">'.$row['terminal'].'</td>';
		echo '<td align="center">'.$row['estacion'].'</td>';
		echo '<td align="center">'.$row['no_eco'].'</th>';
		echo '<td align="center">'.$row['latitud'].'</th>';
		echo '<td align="center">'.$row['longitud'].'</th>';
		echo '<td align="center">'.$row['observaciones'].'</th>';
		echo '<td align="center">'.$row['usuario'].'</th>';
		echo '</tr>';
		$x++;
		$t+=$row['monto'];
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="10" align="left">'.$x.' Registro(s)</th></tr>';
	echo '</table>';
	exit();
}

top($_SESSION);



if($_POST['cmd']==3){
	if(nivelUsuario() > 2){
			mysql_query("UPDATE salidas_taquillamovil SET estatus='C',obscan='".$_GET['obs']."',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
		$_POST['cmd']=0;
	}
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
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="'.fechaLocal().'">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Folio</td><td><input type="text" name="folio" id="folio" class="textField" size="5" value=""></td></tr>';
	echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="5" value=""></td></tr>';
	echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
	foreach($array_derrotero as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Terminal</td><td><select name="terminal" id="terminal"><option value="">--- Todos ---</option>';
	$res1 = mysql_query("SELECT idterminal,terminal FROM salidas_taquillamovil GROUP BY terminal ORDER BY terminal");
	while($row1 = mysql_fetch_array($res1)){
		echo '<option value="'.$row1[0].'">'.$row1[1].'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
	$res = mysql_query("SELECT idusuario,usuario FROM salidas_taquillamovil GROUP BY usuario ORDER BY usuario");
	while($row = mysql_fetch_array($res)){
		echo '<option value="'.$row[0].'">'.$row[1].'</option>';
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
			objeto.open("POST","salidas_taquillamovil.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&no_eco="+document.getElementById("no_eco").value+"&derrotero="+document.getElementById("derrotero").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&usuario="+document.getElementById("usuario").value+"&terminal="+document.getElementById("terminal").value);
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