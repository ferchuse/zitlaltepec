<?php
include ("main.php");

$rsMotivo=mysql_query("SELECT * FROM usuarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_personal[$Motivo['cve']]=$Motivo['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM usuarios where 1 ORDER by usuario asc");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_usuarios_movil[$Motivo['idpersonal']]=$Motivo['login'];
	$array_usuarios_movi[$Motivo['id']]=$Motivo['idpersonal'];
}


if($_POST['ajax']==1){

$base='road_gps_sky_media';
	$selec= " SELECT column_name FROM information_schema.columns
WHERE table_schema = 'road_gps_sky_media' 
AND table_name = 'data_v2' ";
	$re=mysql_db_query($base,$selec) or die(mysql_error());
//	echo''.$select.'';
	/*echo'<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" overflow: auto;>';
			
			echo'<tr bgcolor="#E9F2F8">';
			while($row=mysql_fetch_array($re)){
			$i=0;
	      echo'
			<th>'.$row[$i].'</th>';
				$i++;
			}
			echo'</tr></table>';*/
	echo '<div style="height: 350px; overflow: auto;">
			<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			
			echo'<tr bgcolor="#E9F2F8">';
			while($row=mysql_fetch_array($re)){
			$i=0;
	      echo'
			<th>'.$row[$i].'</th>';
				$i++;
			}
			echo'</tr>';
			   
	$select= " SELECT a.* FROM data_v2 a left join placas b on a.idtracker=b.idtracker
	            WHERE a.idtracker not in (998,999) and a.date >= '".$_POST['fecha_ini']."' AND a.date <= '".$_POST['fecha_fin']."' ";
		//	if($_POST['no_eco']!="") $select .= " AND a.idunidad = '".$array_cveeconomico[$_POST['no_eco']]."'";
//	if($_POST['folio']!="") $select .= " AND a.id = '".$_POST['folio']."'";
//	if($_POST['terminal']!="") $select .= " AND a.idterminal = '".$_POST['terminal']."'";
//	if($_POST['empresa']!="") $select .= " AND b.empresa= '".$_POST['empresa']."'";
	if($_POST['terminal']!="") {$select .= " AND a.idtracker='".$_POST['terminal']."'";}else{if($_POST['plaza']==1){$plaz=" and b.idempresa='4'";} }
	//if($_POST['usu']!="") $select .= " AND b.nombre='".$_POST['usu']."'";
	$select .= "".$plaz." ORDER BY a.iddata asc";
	$res=mysql_db_query($base,$select) or die(mysql_error());
//	echo''.$select.'';

	while($row=mysql_fetch_array($res)){
	rowb();
	
		echo'<td align="center">'.$row[0].'</td>';
		echo'<td align="center">'.$row[1].'</td>';
		echo'<td align="center">'.$row[2].'</td>';
		echo'<td align="center">'.$row[3].'</td>';
		echo'<td align="center">'.$row[4].'</td>';
		echo'<td align="center">'.$row[5].'</td>';
		echo'<td align="center">'.$row[6].'</td>';
		echo'<td align="center">'.$row[7].'</td>';
		echo'<td align="center">'.$row[8].'</td>';
		echo'<td align="center">'.$row[9].'</td>';
		echo'<td align="center">'.$row[10].'</td>';
		echo'<td align="center">'.$row[11].'</td>';
		echo'<td align="center">'.$row[12].'</td>';
		echo'<td align="center">'.$row[13].'</td>';
		echo'<td align="center">'.$row[14].'</td>';
		echo'<td align="center">'.$row[15].'</td>';
		echo'<td align="center">'.$row[16].'</td>';
		echo'<td align="center">'.$row[17].'</td>';
		echo'<td align="center">'.$row[18].'</td>';
		echo'<td align="center">'.$row[19].'</td>';
		echo'<td align="center">'.$row[20].'</td>';
		echo'<td align="center">'.$row[21].'</td>';
		echo'</tr>';		 

	}
	echo'<tr bgcolor="#E9F2F8"><td colspan="21" align="left">'.mysql_num_rows($res).' Registro(s)</td>';

	 echo'</table></div>';
exit();
}


 top($_SESSION);


 	if ($_POST['cmd']<1) {
	if($_POST['plazausuario']==1){$plaz=" and idempresa='4'";}else{$plaz="";}
		$base='road_gps_sky_media';
		$select="SELECT * FROM placas where 1 ".$plaz." GROUP by idtracker asc";
		$rsMotivo=mysql_db_query($base,$select);
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_terminal[$Motivo['idtracker']]=$Motivo['idtracker'];
}
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
//		echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		//echo '<tr><td>No Eco</td><td><input type="text" class="textField" size="5" name="no_eco" id="no_eco"></td></tr>';
		echo '<tr style="display:none;"><td>Folio</td><td><input type="text" class="textField" size="5" name="folio" id="folio"></td></tr>';
		//echo '<tr style="display:none;"><td>Id Tipo Evento</td><td><input type="text" class="textField" size="5" name="idtipo" id="idtipo"></td></tr>';
		/*echo '<tr style="display:none;"><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todas</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr"><td>Terminal</td><td><select name="terminal" id="terminal"><option value="">Todos</option>';
		foreach($array_terminal as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr><td>Terminal</td><td><select name="terminal" id="terminal"><option value="">Todos</option>';
		foreach($array_terminal as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>
		';
		echo '<br><hr/>';

		//Listado
		echo '<div id="Resultados" style="height: 400px; overflow: auto;">';

		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">
	function buscarRegistros(){
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else{
			objeto.open("POST","auto_datos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&terminal="+document.getElementById("terminal").value+"&plaza="+document.getElementById("plaza").value);
			objeto.onreadystatechange = function(){
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;

	}';
	
	echo '
	</Script>';
	}
 ?>
<?
bottom();
?>
