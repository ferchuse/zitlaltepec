<?php
include('main.php');

$res = mysql_query("SELECT kmsodo FROM usuarios WHERE cve = '1' ORDER BY cve DESC LIMIT 1");
$row = mysql_fetch_array($res);
$kmsodo = $row[0];


mysql_select_db('gps_otra_plataforma');
$select= " SELECT * FROM estancias WHERE 1 ORDER BY nombre";
$array_estancias = array();
$res = mysql_query($select);
while($row = mysql_fetch_array($res)){
	$array_estancias[$row['cve']] = $row['nombre'];
}


mysql_select_db('gps');

function sumar_tiempo($tiempo1, $tiempo2){
	$datos1 = explode(':', $tiempo1);
	$datos2 = explode(':', $tiempo2);

	$segundos1 = ($datos1[0] * 3600) + ($datos1[1] * 60) + $datos1[2];
	$segundos2 = ($datos2[0] * 3600) + ($datos2[1] * 60) + $datos2[2];

	$segundos = $segundos1 + $segundos2;

	$hora = intval($segundos/3600);
	$segundos -= ($hora*3600);
	$minutos = intval($segundos/60);
	$segundos -= ($minutos*60);
	if($hora < 10) $hora='0'.$hora;
	if($minutos < 10) $minutos='0'.$minutos;
	if($segundos < 10) $segundos='0'.$segundos;
	$resultado = $hora.':'.$minutos.':'.$segundos;
	return $resultado;
}

function calcular_tiempo($base, $dispositivo, $geocerca, $fecha_ini, $fecha_fin){
	$base='gps_otra_plataforma';
	mysql_select_db($base);
	$datos = explode(',', $geocerca);
	$geocerca = $datos[1];

	$tiempo = '00:00:00';

	$res = mysql_query("SELECT fecha, hora, tipo FROM eventos WHERE base = '$base' AND dispositivo = '$dispositivo' AND geocerca = '$geocerca' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin' AND tipo IN ('geofenceEnter', 'geofenceExit') ORDER BY fecha, hora");
	$fecha1 = '';
	while($row = mysql_fetch_array($res)){
		if($row['tipo'] == 'geofenceEnter'){
			$fecha1 = $row['fecha'].' '.$row['hora'];
		}
		elseif($row['tipo'] == 'geofenceExit' && $fecha1 != ''){
			$res1 = mysql_query("SELECT TIMEDIFF('".$row['fecha']." ".$row['hora']."', '$fecha1')");
			$row1 = mysql_fetch_array($res1);
			$tiempo = sumar_tiempo($tiempo, $row1[0]);
			$fecha1 = '';
		}
	}
	return $tiempo;
}
function obtener_anden($datos, $horamaxima){
	$resultado = array('llegada' => 'NA', 'salida' => 'NA');
	if($con = mysql_connect('198.74.60.165', 'lusa', '7ezqWZ7FT53Zn82C')){
		mysql_select_db('enero_aaz') or die(mysql_error());

		if($datos['salida']=='') $datos['salida'] = substr($datos['llegada'],0,11).$horamaxima;

		$res = mysql_query("SELECT fechayhora,a.cve as sesion, CONCAT(a.fecha_imp,' ', a.hora) as salida FROM lusa_boletoponchadosupervisor a INNER JOIN parquefenix b ON a.no_eco = b.no_eco AND b.imei = '".$datos['uniqueid']."' 
			WHERE a.empresa IN (1, 6, 7, 8, 9) AND a.fechayhora BETWEEN '".$datos['llegada']."' AND '".$datos['salida']."' AND folio_sesion > 0");
		$row = mysql_fetch_array($res);

		$resultado['llegada'] = $row[0];

		if($row['sesion'] > 0){
			if($row['salida'] <= '0000-00-00 00:00:00'){
				$res = mysql_query("SELECT MAX(fechayhora) FROM lusa_boletoponchadosupervisor a 
				WHERE a.empresa IN (1, 6, 7, 8, 9) AND a.sesion = '".$row['sesion']."'");
				$row = mysql_fetch_array($res);

				$resultado['salida'] = $row[0];
			}
			else{
				$resultado['salida'] = $row['salida'];
			}
		}
		else{
			$resultado['llegada'] = '00:00:00';
			$resultado['salida'] = '00:00:00';
		}

		mysql_connect('localhost', 'gps', 'ballena');
		mysql_select_db('gps_otra_plataforma') or die(mysql_error());
	}
	return $resultado;
}

if($_POST['ajax']==1){

$base='gps_otra_plataforma';
mysql_select_db($base);

	
	$res = mysql_query("SELECT * FROM estancias WHERE cve='".$_POST['estancia']."'");
	$row = mysql_fetch_array($res);

	$llegada = $row['geocerca_llegada'];
	$salida = $row['geocerca_salida'];
	$anden = $row['geocerca_anden'];

	$tipo = 'geofenceEnter';
	if($llegada == $salida) $tipo = 'geofenceExit';


	
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">
	      <tr bgcolor="#E9F2F8"><th>Eco</th>
		  <th>Llegada</th><th>Salida</th><th>Tiempo</th><th>Anden LLegada</th><th>Anden Salida</th><th>Tiempo</th><th>Anden LLegada GPS</th><th>Anden Salida GPS</th><th>Tiempo GPS</th></tr>';

	$resultado = array();
	$indice = -1;
	$res = mysql_query("SELECT a.fecha, a.hora, c.cve as geocerca, b.nombre as dispositivo, b.uniqueid, b.base, b.cvebase, b.ruta FROM eventos a INNER JOIN dispositivos b on b.cvebase = a.dispositivo  AND b.base = a.base INNER JOIN geocercas c ON c.base = a.base AND c.cvebase = a.geocerca AND b.ruta = c.ruta WHERE a.fecha='".$_POST['fecha']."' AND a.tipo='geofenceEnter' AND c.cve = '$llegada' ORDER BY a.fecha, a.hora");
	while($row = mysql_fetch_array($res)){
		if($row['dispositivo'] != $resultado[$indice]['dispositivo']){
			$indice++;
			$resultado[$indice]['dispositivo'] = $row['dispositivo'];
			$resultado[$indice]['uniqueid'] = $row['uniqueid'];
			$resultado[$indice]['ruta'] = $row['ruta'];
			$resultado[$indice]['cvedispositivo'] = $row['cvebase'];
			$resultado[$indice]['base'] = $row['base'];
			$resultado[$indice]['llegada'] = $row['fecha'].' '.$row['hora'];
		}
	}

	
	$tiempo1 = '00:00:00';
	$tiempo2 = '00:00:00';
	$tiempo3 = '00:00:00';
	$primera = true;
	foreach($resultado as $datos){
		rowb();
		echo'<td align="center">'.$datos['dispositivo'].'</td>';
		echo'<td align="center">'.substr($datos['llegada'],-8).'</td>';
		$horamaxima = date( "H:i:s" , strtotime ( "90 minute" , strtotime($datos['llegada']) ) );
//		echo "SELECT a.fecha, a.hora, c.cve as geocerca FROM eventos a INNER JOIN geocercas c ON c.base = a.base AND c.cvebase = a.geocerca WHERE a.fecha='".$_POST['fecha']."' AND a.base='".$datos['base']."' AND a.dispositivo='".$datos['cvedispositivo']."' AND c.cve IN ('$salida','$anden') AND a.hora BETWEEN '".substr($datos['llegada'],-8)."' AND '".$horamaxima."' AND a.tipo='geofenceEnter' GROUP BY c.cve";
		$res = mysql_query("SELECT a.fecha, a.hora, c.cve as geocerca FROM eventos a INNER JOIN geocercas c ON c.base = a.base AND c.cvebase = a.geocerca WHERE a.fecha='".$_POST['fecha']."' AND a.base='".$datos['base']."' AND a.dispositivo='".$datos['cvedispositivo']."' AND c.ruta='".$datos['ruta']."' AND c.cve = '$salida' AND a.hora BETWEEN '".substr($datos['llegada'],-8)."' AND '".$horamaxima."' AND a.tipo='$tipo' GROUP BY c.cve");
		while($row = mysql_fetch_array($res)){
				$datos['salida'] = $row['fecha'].' '.$row['hora'];
		}
		echo'<td align="center">'.substr($datos['salida'],-8).'</td>';
		$res1 = mysql_query("SELECT TIMEDIFF('".$datos['salida']."', '".$datos['llegada']."')");
		$row1 = mysql_fetch_array($res1);
		echo'<td align="center">'.$row1[0].'</td>';
		$tiempo1 = sumar_tiempo($tiempo1, $row1[0]);
		$datos['anden'] = obtener_anden($datos, $horamaxima);
		echo'<td align="center">'.substr($datos['anden']['llegada'],-8).'</td>';
		echo'<td align="center">'.substr($datos['anden']['salida'],-8).'</td>';
		$res1 = mysql_query("SELECT TIMEDIFF('".$datos['anden']['salida']."', '".$datos['anden']['llegada']."')");
		$row1 = mysql_fetch_array($res1);
		echo'<td align="center">'.$row1[0].'</td>';
		$tiempo2 = sumar_tiempo($tiempo2, $row1[0]);
		$horafin = date( "H:i:s" , strtotime ( "+ 10 minute" , strtotime(substr($datos['salida'],-8)) ) );
		$res = mysql_query("SELECT a.fecha, a.hora, c.cve as geocerca, a.tipo FROM eventos a INNER JOIN geocercas c ON c.base = a.base AND c.cvebase = a.geocerca WHERE a.fecha='".$_POST['fecha']."' AND a.base='".$datos['base']."' AND a.dispositivo='".$datos['cvedispositivo']."' AND c.ruta='".$datos['ruta']."' AND c.cve = '$anden' AND a.hora BETWEEN '".substr($datos['llegada'],-8)."' AND '".$horafin."'  GROUP BY c.cve, a.tipo");
		while($row = mysql_fetch_array($res)){
				$datos['anden'][$row['tipo']] = $row['fecha'].' '.$row['hora'];
		}

		echo'<td align="center">'.substr($datos['anden']['geofenceEnter'],-8).'</td>';
		echo'<td align="center">'.substr($datos['anden']['geofenceExit'],-8).'</td>';
		$res1 = mysql_query("SELECT TIMEDIFF('".$datos['anden']['geofenceExit']."', '".$datos['anden']['geofenceEnter']."')");
		$row1 = mysql_fetch_array($res1);
		echo'<td align="center">'.$row1[0].'</td>';
		$tiempo3 = sumar_tiempo($tiempo3, $row1[0]);
		echo'</tr>';
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="3">Total Tiempo</th><th>'.$tiempo1.'</th><th colspan="2">&nbsp;</th><th>'.$tiempo2.'</th><th colspan="2">&nbsp;</th><th>'.$tiempo3.'</th></tr>';
	echo '</table>';
exit();
mysql_select_db("gps");
}


 top($_SESSION);

 	if ($_POST['cmd']<1) {
		$base='gps_otra_plataforma';
		//Busqueda
		echo '<table><input type="hidden" name="plaza" id="plaza" value="'.$_POST['plazausuario'].'">';
	//	echo''.$select.'';
		echo '<tr>
				<td><a href="#" onclick="if(document.forma.estancia.value==\'\') alert(\'Necesita seleccionar una estancia\'); else buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>

			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr ><td>Fecha</td><td><input type="text" name="fecha" id="fecha" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		
		echo '<tr><td>Estancia</td><td><select name="estancia" id="estancia" class="textField"><option value="">Seleccione</option>';
		foreach ($array_estancias as $k=>$v) { 
	    		echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>
		';
		echo '<br><hr/>';

		//Listado
		echo '<div id="Resultados">';

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
			objeto.open("POST","reportetiempoestancias.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha="+document.getElementById("fecha").value+"&estancia="+document.getElementById("estancia").value+"&plaza="+document.getElementById("plaza").value+"&plazausuario="+document.getElementById("plazausuario").value+"&cveusuario="+document.getElementById("cveusuario").value);
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

bottom();
?>