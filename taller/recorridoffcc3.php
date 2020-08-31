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

function obtener_siguiente_rol(&$resultados, $i, &$no_eco){
	$resultado = array();
	for($j=($i+1); $j<count($resultados); $j++){
		if($resultados[$j]['no_eco'] == $no_eco){
			$resultado = $resultados[$j];
			break;
		}
	}
	return $resultado;
}

function tiene_rol_anterior($resultados, $i, $no_eco){
	for($j=($i-1); $j>=0; $j--){
		if($resultados[$j]['no_eco'] == $no_eco){
			return $resultados[$j]['ultimahora'];
		}
	}
	return '04:00:00';
}

if($_POST['ajax']==1){

	mysql_select_db('road_gps_otra_plataforma');
	$ruta=18;
	$taruni = array();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="yourTableID2"><thead>';
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Economico</th><th rowspan="2">Clave Operador</th><th rowspan="2">Folio Tarjeta</th><th rowspan="2">Fecha Tarjeta</th><th rowspan="2">Hora Tarjeta</th><!--<th rowspan="2">Hora Rol</th>-->';
	$select= " SELECT * FROM geocercas WHERE base = 1 AND ruta = '$ruta' AND orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	$cambio = false;
	while($row = mysql_fetch_array($res)){
		
		if($row['direccion'] == 1 && $cambio == false){
			echo '<th rowspan="2">Tiempo Ida</th>';
			$cambio = true;
		}
		echo '<th colspan="3">'.$row['cvebase'].''.$row['codigo'].'</th>';
		$array_puntos[$row['direccion']][$row['cvebase']] = $row['duracion'];
	}
	echo '<th rowspan="2">Tiempo Regreso</th><th rowspan="2">&nbsp;&nbsp;</th>';
	echo '</tr>';
	$primerpunto = key($array_puntos[0]);
	$primerpunto2 = key($array_puntos[1]);
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_puntos[0] as $v){
		echo '<th>H.P.</th><th>H.R.</th><th>DIF.</th>';
	}
	foreach($array_puntos[1] as $v){
		echo '<th>H.P.</th><th>H.R.</th><th>DIF.</th>';
	}
	echo '</thead><tbody>';
	$fecha_respaldo = $_POST['fecha'];
	$_POST['fecha'] = date( "Y-m-d" , strtotime ( "-1 day" , strtotime($_POST['fecha']) ) );
	$resultadosa = array();
	mysql_connect('198.74.60.165', 'lusa', '7ezqWZ7FT53Zn82C');
	mysql_select_db('enero_aaz') or die(mysql_error());
	$array_conf = array();
	$res = mysql_query("SELECT * FROM lusa_conf_roles_ffcc ORDER BY salida");
	while($row = mysql_fetch_array($res)){
		$array_conf[$row['salida']] = array(
			'apaxcoida' => $row['apaxcoida'],
			'zumpangoida' => $row['zumpangoida'],
			'tlahuelilpan' => $row['tlahuelilpan'],
			'zumpangoreg' => $row['zumpangoreg'],
		);
	}


	$res = mysql_query("SELECT a.hora, b.folio, c.clave, d.no_eco, d.imei, b.estatus, b.fecha, b.hora as hora_tarjeta FROM rolesfenix a LEFT JOIN viajesfenix b ON a.cve = b.turno AND b.estatus != 'C' LEFT JOIN conductoresfenix c ON c.cve = b.conductor LEFT JOIN parquefenix d ON d.cve = b.unidad WHERE a.plaza=1 AND a.fecha='".$_POST['fecha']."' AND a.hora>='15:00:00' ORDER BY a.hora") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$resultadosa[] = $row;
	}


	$resultados = array();
	$res = mysql_query("SELECT a.hora, b.folio, c.clave, d.no_eco, d.imei, b.estatus, b.fecha, b.hora as hora_tarjeta FROM rolesfenix a LEFT JOIN viajesfenix b ON a.cve = b.turno AND b.estatus != 'C' LEFT JOIN conductoresfenix c ON c.cve = b.conductor LEFT JOIN parquefenix d ON d.cve = b.unidad WHERE a.plaza=1 AND a.fecha='".$fecha_respaldo."' ORDER BY a.hora") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$resultados[] = $row;
	}
	
	mysql_connect('localhost', 'road_gps', 'ballena');
	mysql_select_db('road_gps_otra_plataforma');
	$primeros_puntos = array();
	/*for($z=0;$z<count($resultadosa);$z++){
		$row = $resultadosa[$z];

		$row2 = obtener_siguiente_rol($resultados, 0, $row['no_eco']);
		if($row2['hora'] == '') $row2['hora'] = '23:59:59';
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
				echo '<td colspan="5">&nbsp;</td>';
			}
			else{
				
				echo '<td align="center" style="background-color: #0000FF!important;text-color: #FFFFFF">'.$row['no_eco'].'</td><td align="center">'.$row['clave'].'</td>
				<td align="center"';
				if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
				echo '>'.$row['folio'];
				echo '</td><td align="center">'.$row['fecha'].'</td><td align="center">'.$row['hora_tarjeta'].'<br>'.$row['hora'].'</td>';
			}

			//echo '<td align="center">'.$row['hora'].'</td>';
			$conf = $array_conf[$row['hora']];
			if($conf['apaxcoida'] != '' || $conf['zumpangoida'] != ''){
				if($conf['apaxcoida'] != '') $row['hora'] = $conf['apaxcoida'];
				elseif($conf['zumpangoida'] != '') $row['hora'] = $conf['zumpangoida'];

				$cvepuntos = "";
				foreach($array_puntos[0] as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
				$cvepuntos = substr($cvepuntos, 1);

				$array_resultadopuntos = array();

				$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($row['hora']) ) );

				$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
					WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
					ORDER BY a.fecha,a.hora") or die(mysql_error());
				$primera = true;
				$mindist = 0;
				$horapunto = $_POST['fecha'].' 00:00:00';
				$nvuelta = 0;
				$empieza = false;
				$horaanterior = '';
				$tiempo = '00:00:00';
				while($row1 = mysql_fetch_array($res1)){
					$diferencia = diferenciapunto($horapunto, $row1['servertime']);
					if($diferencia > '00:00:05'){
						if($row1['geofenceid'] == $primerpunto && $nvuelta==0){
							$nvuelta==0;
						}
						else{
							$array_resultadopuntos[$nvuelta][] = array(
								'idpunto' => $row1['geofenceid'],
								'punto' => $row1['geocerca'],
								'horapunto' => $row1['servertime']
							);
							$horapunto = $row1['servertime'];
						}
					}
				}
				//foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
					$resultadovueltas = $array_resultadopuntos[0];
					$i=0;
					$html = '';
					$puntos = count($resultadovueltas);
					$puntos_encontrados = 0;
					$horapropuesta='';
					foreach($array_puntos[0] as $cve => $duracion){
						$puntoinicio = $i;
						$encontrado = false;
						while($i<$puntos){
							if($resultadovueltas[$i]['idpunto'] == $cve){
								if($cve == $primerpunto){
									$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
								}
								else{
									$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
								}
								$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
								$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
								$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

								$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);
								$html .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

								if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
									$html.= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
								elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
									$html.= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
								else
									$html.= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';



								$row['hora'] = substr($resultadovueltas[$i]['horapunto'],-8);
								if($horaanterior == ''){
									$horaanterior = substr($resultadovueltas[$i]['horapunto'],-8);
								}
								else{
									$res1 = mysql_query("SELECT TIMEDIFF('".$row['hora']."', '".$horaanterior."')");
									$row1 = mysql_fetch_array($res1);
									$horaanterior = $row['hora'];
									$tiempo = sumar_tiempo($tiempo, $row1[0]);
								}
								$i++;
								$puntos_encontrados++;
								$encontrado = true;
								break;
							}
							$i++;
						}
						if(!$encontrado){
							$i=$puntoinicio;
							if($cve == $primerpunto)
								$horapropuesta = $row['hora'];
							else
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$html .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';
						}
					}
					if($puntos_encontrados >= 0) echo $html;
				//}
				echo '<td>'.$tiempo.'</td>';
			}
			else{
				echo '<td colspan="'.(count($array_puntos[0])*3+1).'">&nbsp;</td>';
			}

			if($conf['zumpangoida']=='' && $conf['apaxcoida'] == '' && $conf['zumpangoreg'] == '') $row['hora'] = $conf['tlahuelilpan'];
			elseif($conf['zumpangoida']=='' && $conf['apaxcoida'] == '' && $conf['zumpangoreg'] != '') $row['hora'] = $conf['zumpangoreg'];
			

			$cvepuntos = "";
			foreach($array_puntos[1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($row['hora']) ) );
			$array_resultadopuntos = array();
			$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
				WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
				ORDER BY a.fecha,a.hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $_POST['fecha'].' 00:00:00';
			$nvuelta = 0;
			$empieza = false;
			$horaanterior = '';
			$tiempo = '00:00:00';
			while($row1 = mysql_fetch_array($res1)){
				$diferencia = diferenciapunto($horapunto, $row1['servertime']);
				if($diferencia > '00:00:05'){
					if($row1['geofenceid'] == $primerpunto2 && $nvuelta == 0){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row1['geofenceid'],
						'punto' => $row1['geocerca'],
						'horapunto' => $row1['servertime']
					);
					$horapunto = $row1['servertime'];
				}
			}
			//echo '<pre>';
			//print_r($array_resultadopuntos);
			//echo '</pre>';
			//foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0 || $conf['zumpangoreg'] != '') $resultadovueltas = $array_resultadopuntos[0];
				$i=0;
				$html = '';
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				foreach($array_puntos[1] as $cve => $duracion){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve == $primerpunto2){
								$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							}
							$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
							$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

							$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);
							$html .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

							if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
							elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
							else
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';



							$row['hora'] = substr($resultadovueltas[$i]['horapunto'],-8);
							if($horaanterior == ''){
								$horaanterior = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$res1 = mysql_query("SELECT TIMEDIFF('".$row['hora']."', '".$horaanterior."')");
								$row1 = mysql_fetch_array($res1);
								$horaanterior = $row['hora'];
								$tiempo = sumar_tiempo($tiempo, $row1[0]);
							}
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						if($cve == $primerpunto)
							$horapropuesta = $row['hora'];
						else
							$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
						$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$html .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';
					}
				}
				if($puntos_encontrados >= 0) echo $html;
				echo '<td>'.$tiempo.'</td>';
			//}
			echo '</tr>';
		}
		
	}*/



	//DIA ACTUAL
	
	$_POST['fecha'] = $fecha_respaldo;
	mysql_connect('localhost', 'road_gps', 'ballena');
	mysql_select_db('road_gps_otra_plataforma');
	$primeros_puntos = array();
	for($z=0;$z<count($resultados);$z++){
		$row = $resultados[$z];
		$row2 = obtener_siguiente_rol($resultados, $z, $row['no_eco']);
		$row2['hora'] = ($row2['ultimahora'] == '') ? $row2['hora'] : $row2['ultimahora'];
		if($row2['hora'] == '') $row2['hora'] = '23:59:59';
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
				
				echo '<td align="center">'.$row['no_eco'];
				if($_SESSION['CveUsuario'] == 1) echo '<br>'.$row['imei'];
				echo '</td><td align="center">'.$row['clave'].'</td>
				<td align="center"';
				if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
				echo '>'.$row['folio'];
				echo '</td><td align="center">'.$row['fecha'].'</td><td align="center">'.$row['hora_tarjeta'].'</td>';
			}

			//echo '<td align="center">'.$row['hora'].'</td>';


			$cvepuntos = "";
			foreach($array_puntos[0] as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$array_resultadopuntos = array();
			/*if(tiene_rol_anterior($resultados, $z, $row['no_eco']))
				$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($row['hora']) ) );
			else
				$hora = '04:00:00';*/
			$hora = tiene_rol_anterior($resultados, $z, $row['no_eco']);

			$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
				WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
				ORDER BY a.fecha,a.hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $_POST['fecha'].' 00:00:00';
			$nvuelta = 0;
			$empieza = false;
			$horaanterior = '';
			$tiempo = '00:00:00';
			while($row1 = mysql_fetch_array($res1)){
				$diferencia = diferenciapunto($horapunto, $row1['servertime']);
				if($diferencia > '00:00:05'){
					if($row1['geofenceid'] == $primerpunto && $nvuelta==0){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row1['geofenceid'],
						'punto' => $row1['geocerca'],
						'horapunto' => $row1['servertime']
					);
					$horapunto = $row1['servertime'];
				}
			}
			//foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
				$i=0;
				$html = '';
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$horapropuesta='';
				foreach($array_puntos[0] as $cve => $duracion){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve == $primerpunto){
								$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							}
							$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
							$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

							$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);
							$html .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

							if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
							elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
							else
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';



							$row['hora'] = substr($resultadovueltas[$i]['horapunto'],-8);
							if($horaanterior == ''){
								$horaanterior = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$res1 = mysql_query("SELECT TIMEDIFF('".$row['hora']."', '".$horaanterior."')");
								$row1 = mysql_fetch_array($res1);
								$horaanterior = $row['hora'];
								$tiempo = sumar_tiempo($tiempo, $row1[0]);
							}
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						if($cve == $primerpunto)
							$horapropuesta = $row['hora'];
						else
							$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
						$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$html .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';
					}
				}
				if($puntos_encontrados >= 0) echo $html;
			//}
			echo '<td>'.$tiempo.'</td>';

			$cvepuntos = "";
			foreach($array_puntos[1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($row['hora']) ) );
			$array_resultadopuntos = array();
			$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
				WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
				ORDER BY a.fecha,a.hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $_POST['fecha'].' 00:00:00';
			$nvuelta = 0;
			$empieza = false;
			$horaanterior = '';
			$tiempo = '00:00:00';
			while($row1 = mysql_fetch_array($res1)){
				$diferencia = diferenciapunto($horapunto, $row1['servertime']);
				if($diferencia > '00:00:05'){
					if($row1['geofenceid'] == $primerpunto2 && $nvuelta == 0){
						$nvuelta++;
					}
					$array_resultadopuntos[$nvuelta][] = array(
						'idpunto' => $row1['geofenceid'],
						'punto' => $row1['geocerca'],
						'horapunto' => $row1['servertime']
					);
					$horapunto = $row1['servertime'];
				}
			}
			//echo '<pre>';
			//print_r($array_resultadopuntos);
			//echo '</pre>';
			//foreach($array_resultadopuntos as $vuelta => $resultadovueltas){
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
				$i=0;
				$html = '';
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				foreach($array_puntos[1] as $cve => $duracion){
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve == $primerpunto2){
								$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							}
							$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
							$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

							$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);
							$html .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

							if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
							elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
							else
								$html.= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';



							$row['hora'] = substr($resultadovueltas[$i]['horapunto'],-8);
							if($horaanterior == ''){
								$horaanterior = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$res1 = mysql_query("SELECT TIMEDIFF('".$row['hora']."', '".$horaanterior."')");
								$row1 = mysql_fetch_array($res1);
								$horaanterior = $row['hora'];
								$tiempo = sumar_tiempo($tiempo, $row1[0]);
							}
							$i++;
							$puntos_encontrados++;
							$encontrado = true;
							break;
						}
						$i++;
					}
					if(!$encontrado){
						$i=$puntoinicio;
						if($cve == $primerpunto)
							$horapropuesta = $row['hora'];
						else
							$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
						$html .= '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$html .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';
					}
				}
				if($puntos_encontrados >= 0) echo $html;
				echo '<td>'.$tiempo.'</td>';
				$resultados[$z]['ultimahora'] = $horapropuesta;
			//}
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
			objeto.open("POST","recorridoffcc3.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&salida="+document.getElementById("salida").value+"&clave="+document.getElementById("clave").value+"&fecha="+document.getElementById("fecha").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					$("#yourTableID2").chromatable({
		
						width: "100%",
						height: "370px",
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