<?php
set_time_limit(0);
include("main.php");
$array_puntos = array();

function restahoras($horaf,$horai,$tipo=0){
	$datosf=explode(":",$horaf);
	$segundosf=($datosf[0]*3600)+($datosf[1]*60)+$datosf[2];
	$datosi=explode(":",$horai);
	$segundosi=($datosi[0]*3600)+($datosi[1]*60)+$datosi[2];
	$resta=$segundosf-$segundosi;
	$negativo = 0;
	//return $resta.'='.$segundosf.'-'.$segundosi;
	if($tipo==1 && $resta<0){
		return "00:00:00";
	}
	elseif($resta<0){
		$negativo = 1;
		$resta = abs($resta);
	}
	$horadif=intval($resta/3600);
	$minutosdif=intval(($resta-($horadif*3600))/60);
	$segundosdif=intval($resta-($horadif*3600)-($minutosdif*60));
	if($horadif<10) $horadif="0".$horadif;
	if($minutosdif<10) $minutosdif="0".$minutosdif;
	if($segundosdif<10) $segundosdif="0".$segundosdif;
	if($negativo) $horadif='-'.$horadif;
	return $horadif.':'.$minutosdif.':'.$segundosdif;
}

function obtener_siguiente_rol(&$resultados, &$i, &$no_eco){
	$resultado = array();
	for($j=($i+1); $j<count($resultados); $j++){
		if($resultados[$j]['no_eco'] == $no_eco){
			$resultado = $resultados[$j];
			break;
		}
	}
	return $resultado;
}

if($_POST['ajax']==1){

	mysql_select_db('road_gps_otra_plataforma');
	$ruta=18;
	$taruni = array();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="yourTableID2">';
	echo '<thead><tr bgcolor="#E9F2F8"><th rowspan="2">Economico</th><th rowspan="2">Clave Operador</th><th rowspan="2">Folio Tarjeta</th><th rowspan="2">Hora Rol</th>';
	$select= " SELECT * FROM geocercas WHERE base = 1 AND ruta = '$ruta' AND orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	while($row = mysql_fetch_array($res)){
		echo '<th colspan="3">'.$row['codigo'].'</th>';
		$array_puntos[$row['cvebase']] = $row['duracion'];
	}
	echo '</tr>';
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_puntos as $k=>$v){
		echo '<th>H.P.</th><th>H.R.</th><th>DIF.</th>';
	}
	echo '</tr></thead><tbody>';
	$resultados = array();
	mysql_connect('198.74.60.165', 'lusa', '7ezqWZ7FT53Zn82C');
	mysql_select_db('enero_aaz') or die(mysql_error());
	$res = mysql_query("SELECT a.hora, b.folio, c.clave, d.no_eco, d.imei, b.estatus FROM rolesfenix a LEFT JOIN viajesfenix b ON a.cve = b.turno AND b.estatus != 'C' LEFT JOIN conductoresfenix c ON c.cve = b.conductor LEFT JOIN parquefenix d ON d.cve = b.unidad WHERE a.plaza=1 AND a.fecha='".$_POST['fecha']."' ORDER BY a.hora") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$resultados[] = $row;
	}
	$primerpunto = key($array_puntos);
	mysql_connect('localhost', 'road_gps', 'ballena');
	mysql_select_db('road_gps_otra_plataforma');
	$primeros_puntos = array();
	for($z=0;$z<count($resultados);$z++){
		$row = $resultados[$z];
		$res1 = mysql_query("SELECT cvebase FROM dispositivos WHERE base = 1 AND uniqueid = '".$row['imei']."'");
		$row1 = mysql_fetch_array($res1);
		$dispositivo = $row1[0];
		$mostrar = 1;
		if(($_POST['salida'] != '' && $_POST['salida'] != $row1['folio']) || $row['folio'] == ""){
			$mostrar = 0;
		}
		else{
			if($_POST['no_eco']!='' && $_POST['no_eco']!=$row['no_eco']) $mostrar = 0;
			if($_POST['clave']!='' && $_POST['clave']!=$row['clave']) $mostrar = 0;
		}
		$taruni[$row['no_eco']]++;
		if($mostrar){
			rowb();
			if($row['folio'] == ''){
				echo '<td colspan="3">&nbsp;</td>';
			}
			else{
				
				echo '<td align="center">'.$row['no_eco'].'</td><td align="center">'.$row['clave'].'</td>
				<td align="center"';
				if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
				echo '>'.$row['folio'];
				echo '</td>';
			}

			echo '<td align="center">'.$row['hora'].'</td>';

			$primero=true;
			$totalpuntos = count($array_puntos);
			$npunto = 1;
			$fechab = $_POST['fecha'];
			$primerahora = $_POST['fecha'].' '.$row['hora'];
			$ultimahora='';
			$rutas = '';
	//		$rutas.= '<td>';
			foreach($array_puntos as $k=>$v){
				if($primero){
					$hora=$row['hora'];
					$primero=false;
					$horat = '<div width="100%" style="background-color:#0000FF;color:#FFFFFF;">'.$fechab.'<br>'.$hora.'</div>';
				}
				else{
					$hora=date( "H:i:s" , strtotime ( "+ ".$v." minute" , strtotime($hora) ) );
					$horat=$hora;
					
				}
				$rutas.= '<td style="border-left: solid 5px #000" align="center">'.$horat.'</td>';
				$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($fechab.' '.$hora) ) );
				$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($fechab.' '.$hora) ) );
				$inicio = date( "Y-m-d H:i:s" , strtotime ( "-1 hour" , strtotime($fechab.' '.$hora) ) );
				$fin = date( "Y-m-d H:i:s" , strtotime ( "+ 90 minute" , strtotime($fechab.' '.$hora) ) );
				if($npunto == $totalpuntos){
					$res2 = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as event_time FROM eventos  
						WHERE fecha = '".$_POST['fecha']."' AND tipo = 'geofenceEnter' AND base='1' AND dispositivo = '$dispositivo' AND hora BETWEEN '$inicio' AND '$fin' AND geocerca = '$k'
						ORDER BY hora") or die(mysql_error());
				}
				else{
					$res2 = mysql_query("SELECT geocerca as geofenceid, concat(fecha, ' ', hora) as event_time FROM eventos  
						WHERE fecha = '".$_POST['fecha']."' AND tipo = 'geofenceEnter' AND base='1' AND dispositivo = '$dispositivo' AND hora BETWEEN '$inicio' AND '$fin' AND geocerca = '$k'
						ORDER BY hora DESC") or die(mysql_error());
				}
				$row2=mysql_fetch_array($res2);
				$divini='';
				$divfin='';
				if($row2['event_time'] != ''){
					$divini='<div width="100%" style="background-color: #FFFF00;">&nbsp;';
					$divfin='</div>';
				}
				if($row2['event_time'] > '0000-00-00 00:00:00')
					$diferencia=$diferencia = restahoras(substr($row2['event_time'],11),$hora,0);
				else
					$diferencia='&nbsp;';
				$rutas.= '<td align="center">'.$divini.substr($row2['event_time'],11).$divfin.'</td>';
				if($row2['event_time'] <= $limitefin && $row2['event_time'] >= $limiteini)
					$rutas.= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
				elseif($row2['event_time']<$limiteini)
					$rutas.= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
				else
					$rutas.= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';
				$npunto++;
			}

			echo $rutas;
			echo '</tr>';
		}
	}
	echo '</tbody></table>';
	exit();
}

top($_SESSION);
echo '<script type="text/javascript" src="js/jquery.chromatable.js"></script>';

	
/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'gps_ffcc.php\',\'_blank\',10,0);"><img src="images/b_print.png" border="0"></a>&nbsp;&nbsp;Imprimir&nbsp;&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		
		echo '<tr><td>Fecha </td><td><input type="text" name="fecha" id="fecha" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Folio Salida</td><td><input type="text" class="textField" name="salida" id="salida" size="10" value=""></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" class="textField" name="no_eco" id="no_eco" size="10" value=""></td></tr>';
		echo '<tr><td>Clave Operador</td><td><input type="text" class="textField" name="clave" id="clave" size="10" value=""></td></tr>';
		echo '</table>';
		echo '<br>';
		//echo 'El numeo de credencial parpadeando significa que no tiene asignacion vigente';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recorridoffcc2.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&salida="+document.getElementById("salida").value+"&clave="+document.getElementById("clave").value+"&fecha="+document.getElementById("fecha").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					$("#yourTableID2").chromatable({
		
						width: "100%",
						height: "300px",
						scrolling: "yes"
						
					});	
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}
	
	</Script>
';
	}
	
bottom();

?>