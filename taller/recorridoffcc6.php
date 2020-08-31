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
	
	$select= " SELECT * FROM geocercas WHERE base = 1 AND ruta = '$ruta' AND orden > 0 ORDER BY orden";
	$array_puntos = array();
	$res = mysql_query($select);
	$cambio = false;
	$ultimopunto=array();
	while($row = mysql_fetch_array($res)){
		$array_puntos[$row['direccion']][$row['cvebase']] = array($row['duracion'], $row['codigo']);
		$ultimopunto[$row['direccion']]=$row['cvebase'];
	}
	$primerpunto = key($array_puntos[0]);
	$primerpunto2 = key($array_puntos[1]);
	

	mysql_connect('198.74.60.165', 'lusa', '7ezqWZ7FT53Zn82C');
	mysql_select_db('enero_aaz') or die(mysql_error());

	$fechaanterior = date( "Y-m-d" , strtotime ( "-1 day" , strtotime($_POST['fecha']) ) );

	$resultadoidaa=array();
	$resultadosa = array();
	$res = mysql_query("SELECT a.hora, b.folio, c.clave, d.no_eco, d.imei, b.estatus, b.fecha, b.hora as hora_tarjeta FROM rolesfenix a LEFT JOIN viajesfenix b ON a.cve = b.turno AND b.estatus != 'C' LEFT JOIN conductoresfenix c ON c.cve = b.conductor LEFT JOIN parquefenix d ON d.cve = b.unidad WHERE a.plaza=1 AND a.fecha='".$fechaanterior."' ORDER BY a.hora") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$resultadosa[] = $row;
	}

	$resultadoida=array();
	$resultados = array();
	$res = mysql_query("SELECT a.hora, b.folio, c.clave, d.no_eco, d.imei, b.estatus, b.fecha, b.hora as hora_tarjeta FROM rolesfenix a LEFT JOIN viajesfenix b ON a.cve = b.turno AND b.estatus != 'C' LEFT JOIN conductoresfenix c ON c.cve = b.conductor LEFT JOIN parquefenix d ON d.cve = b.unidad WHERE a.plaza=1 AND a.fecha='".$_POST['fecha']."' AND b.destino BETWEEN 0 AND 5 ORDER BY a.hora") or die(mysql_error());
	while($row=mysql_fetch_array($res)){
		$resultados[] = $row;
	}

	mysql_connect('localhost', 'road_gps', 'ballena');
	mysql_select_db('road_gps_otra_plataforma');
	//ANTERIOR
	$resultadosamedias = array();
	for($z=0;$z<count($resultadosa);$z++){
		$row = $resultadosa[$z];
		$row2 = obtener_siguiente_rol($resultadosa, $z, $row['no_eco']);
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
			$datos = array();
			$datos['no_eco'] = $row['no_eco'];
			$datos['clave'] = $row['clave'];
			$datos['estatus'] = $row['estatus'];
			$datos['folio'] = $row['folio'];
			$datos['fecha'] = $row['fecha'];
			$datos['hora_tarjeta'] = $row['hora_tarjeta'];
			$datos['hora'] = $row['hora'];
			$datos['imei'] = $row['imei'];
			$datos['tiempoida'] = '00:00:00';
			$datos['vacio'] = true;


			$horar = $row['hora'];
			

			$cvepuntos = "";
			foreach($array_puntos[0] as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$array_resultadopuntos = array();
			$hora = tiene_rol_anterior($resultadosa, $z, $row['no_eco']);
			$datos['ida'] = array();


			$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
				WHERE a.fecha = '".$fechaanterior."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
				ORDER BY a.fecha,a.hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $fechaanterior.' 00:00:00';
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
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
			$i=0;
			$html = '';
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			$horapropuesta='';
			foreach($array_puntos[0] as $cve => $dat){
				$duracion=$dat[0];
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
						if($resultadosa[$z]['primerahora'] == '') $resultadosa[$z]['primerahora'] = $horapropuesta;

						$datos['vacio'] = false;

						if($datos['primerahora'] == '') $datos['primerahora'] = $horapropuesta;
						$datopunto=array();
						$datopunto['horapropuesta'] = $horapropuesta;
						$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
						$datopunto['html']='<td style="background-color: #CCCCFF!important;border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
						$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

						$datopunto['diferencia'] = $diferencia;

						$datopunto['html'] .= '<td align="center" style="background-color: #CCCCFF!important;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</td>';

						if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
						elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
						else
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

						$datos['ida'][$cve] = $datopunto;
						$datos['ultimopunto'] = $cve;
						$datos['tipoultimopunto'] = 0;

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
					$datopunto = array();
					$i=$puntoinicio;
					if($cve == $primerpunto)
						$horapropuesta = $row['hora'];
					else
						$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
					$datopunto['horapropuesta'] = $horapropuesta;

					$datopunto['html'] = '<td style="background-color: #CCCCFF!important;border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
					$datopunto['html'] .= '<td style="background-color: #CCCCFF!important;">&nbsp;</td><td style="background-color: #CCCCFF!important;border-right: solid 5px #000">&nbsp;</td>';

					$datos['ida'][$cve] = $datopunto;
				}
			}
			$datos['tiempoida'] = $tiempo;
			$datos['ultimahora'] = $horapropuesta;


			$cvepuntos = "";
			foreach($array_puntos[1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($horapropuesta) ) );
			$array_resultadopuntos = array();
			$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
				WHERE a.fecha = '".$fechaanterior."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '".$hora."' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$row['imei']."' AND geocerca IN ($cvepuntos)
				ORDER BY a.fecha,a.hora") or die(mysql_error());
			$primera = true;
			$mindist = 0;
			$horapunto = $fechaanterior.' 00:00:00';
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
			$datos['vuelta'] = array();
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
			$i=0;
			$html = '';
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			foreach($array_puntos[1] as $cve => $dat){
				$duracion = $dat[0];
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


						$datopunto=array();
						$datopunto['horapropuesta'] = $horapropuesta;
						$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
						$datopunto['html']='<td style="background-color: #CCCCFF!important;border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
						$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

						$datopunto['diferencia'] = $diferencia;

						$datopunto['html'] .= '<td align="center" style="background-color: #CCCCFF!important;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</td>';

						if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
						elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
						else
							$datopunto['html'].= '<td style="background-color: #CCCCFF!important;border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

						$datos['vuelta'][$cve] = $datopunto;


						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

						$datos['ultimopunto'] = $cve;
						$datos['tipoultimopunto'] = 1;
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

					$datopunto = array();
					$i=$puntoinicio;
					if($cve == $primerpunto)
						$horapropuesta = $row['hora'];
					else
						$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
					$datopunto['horapropuesta'] = $horapropuesta;

					$datopunto['html'] = '<td style="background-color: #CCCCFF!important;border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
					$datopunto['html'] .= '<td style="background-color: #CCCCFF!important;">&nbsp;</td><td style="background-color: #CCCCFF!important;border-right: solid 5px #000">&nbsp;</td>';

					$datos['vuelta'][$cve] = $datopunto;
				}
			}
			$datos['tiempovuelta'] = $tiempo;
			$datos['ultimahora'] = $horapropuesta;

			$resultadosa[$z]['ultimahora'] = $horapropuesta;

			$resultadosamedias[$datos['primerahora'].'_'.$datos['folio']] = $datos;
		}
	}

	//echo '<pre>';
	//echo print_r($resultadosamedias);
	//echo '</pre>';

	ksort($resultadosamedias);


	$array_datosactuales = array();

	for($z=0;$z<count($resultados);$z++){
		$row = $resultados[$z];
		$row2 = obtener_siguiente_rol($resultados, $z, $row['no_eco']);
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
			$datos = array();
			$datos['no_eco'] = $row['no_eco'];
			$datos['clave'] = $row['clave'];
			$datos['estatus'] = $row['estatus'];
			$datos['folio'] = $row['folio'];
			$datos['fecha'] = $row['fecha'];
			$datos['hora_tarjeta'] = $row['hora_tarjeta'];
			$datos['hora'] = $row['hora'];
			$datos['tiempoida'] = '00:00:00';


			$horar = $row['hora'];
			

			$cvepuntos = "";
			foreach($array_puntos[0] as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$array_resultadopuntos = array();
			//$hora = tiene_rol_anterior($resultados, $z, $row['no_eco']);
			$hora = $row['hora_tarjeta'];
			$datos['ida'] = array();

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
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
			$i=0;
			$html = '';
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			$horapropuesta='';
			foreach($array_puntos[0] as $cve => $dat){
				$duracion=$dat[0];
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
						if($resultados[$z]['primerahora'] == '') $resultados[$z]['primerahora'] = $horapropuesta;

						if($datos['primerahora'] == '') $datos['primerahora'] = $horapropuesta;
						$datopunto=array();
						$datopunto['horapropuesta'] = $horapropuesta;
						$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
						$datopunto['html']='<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
						$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

						$datopunto['diferencia'] = $diferencia;

						$datopunto['html'] .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

						if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
						elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
						else
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

						$datos['ida'][$cve] = $datopunto;

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
					$datopunto = array();
					$i=$puntoinicio;
					if($cve == $primerpunto)
						$horapropuesta = $row['hora'];
					else
						$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
					$datopunto['horapropuesta'] = $horapropuesta;

					$datopunto['html'] = '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
					$datopunto['html'] .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';

					$datos['ida'][$cve] = $datopunto;
				}
			}
			$datos['tiempoida'] = $tiempo;
			$datos['ultimahora'] = $horapropuesta;


			$cvepuntos = "";
			foreach($array_puntos[1] as $cve => $punto) $cvepuntos .= ",'".$cve."'";
			$cvepuntos = substr($cvepuntos, 1);

			$hora = date( "H:i:s" , strtotime ( "-20 minute" , strtotime($horapropuesta) ) );
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
			$datos['vuelta'] = array();
			$resultadovueltas = $array_resultadopuntos[1];
			if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
			$i=0;
			$html = '';
			$puntos = count($resultadovueltas);
			$puntos_encontrados = 0;
			foreach($array_puntos[1] as $cve => $dat){
				$duracion = $dat[0];
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


						$datopunto=array();
						$datopunto['horapropuesta'] = $horapropuesta;
						$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
						$datopunto['html']='<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
						$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

						$datopunto['diferencia'] = $diferencia;

						$datopunto['html'] .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

						if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
						elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
						else
							$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

						$datos['vuelta'][$cve] = $datopunto;


						$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);


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

					$datopunto = array();
					$i=$puntoinicio;
					if($cve == $primerpunto)
						$horapropuesta = $row['hora'];
					else
						$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
					$datopunto['horapropuesta'] = $horapropuesta;

					$datopunto['html'] = '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
					$datopunto['html'] .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';

					$datos['vuelta'][$cve] = $datopunto;
				}
			}
			$datos['tiempovuelta'] = $tiempo;
			$datos['ultimahora'] = $horapropuesta;

			$resultados[$z]['ultimahora'] = $horapropuesta;

			$array_datosactuales[$datos['primerahora'].'_'.$datos['folio']] = $datos;
		}
	}
	$resultadosfechaanterior = array();
	foreach($resultadosamedias as $datos){
		if($datos['primerahora'] >= '12:50:00' && !$datos['vacio'] && $ultimopunto[1] != $datos['ultimopunto']){
			$encontradoultimopunto = false;
			$primerpuntoencontrado = true;
			$row['hora'] = '00:00:00';
			$row2 = obtener_siguiente_rol($resultados, 0, $datos['no_eco']);

			$puntosfaltantes = array();
			$primerpuntofaltante=0;
			foreach($array_puntos[0] as $cve => $dat){
				if($encontradoultimopunto){
					$puntosfaltantes[$cve]=$dat;
					if($primerpuntofaltante==0) $primerpuntofaltante = $cve;
				}
				if($cve == $datos['ultimopunto']){
					$encontradoultimopunto=true;
				}

			}

			
			if(count($puntosfaltantes)>0){
				$cvepuntos = "";
				foreach($puntosfaltantes as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
				$cvepuntos = substr($cvepuntos, 1);

				$array_resultadopuntos = array();
				$hora = '04:00:00';

				$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
					WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '04:00:00' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$datos['imei']."' AND geocerca IN ($cvepuntos)
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
						if($row1['geofenceid'] == $primerpuntofaltante && $nvuelta==0){
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
				$resultadovueltas = $array_resultadopuntos[1];
				if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
				$i=0;
				$html = '';
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$horapropuesta='';
				foreach($puntosfaltantes as $cve => $dat){
					$duracion=$dat[0];
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve == $primerpuntofaltante || $primerpuntoencontrado){
								$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							}
							$primerpuntoencontrado = false;
							$datopunto=array();
							$datopunto['horapropuesta'] = $horapropuesta;
							$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
							$datopunto['html']='<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
							$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

							$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

							$datopunto['diferencia'] = $diferencia;

							$datopunto['html'] .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

							if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
							elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
							else
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

							$datos['ida'][$cve] = $datopunto;

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
						$datopunto = array();
						$i=$puntoinicio;
						if($cve == $primerpunto)
							$horapropuesta = $row['hora'];
						else
							$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
						$datopunto['horapropuesta'] = $horapropuesta;

						$datopunto['html'] = '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$datopunto['html'] .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';

						$datos['ida'][$cve] = $datopunto;
					}
				}
				$datos['tiempoida'] = sumar_tiempo($datos['tiempoida'], $tiempo);
			}
			else{
				$datos['tipoultimopunto'] = 1;
			}

			$puntosfaltantes = array();
			$primerpuntofaltante=0;
			foreach($array_puntos[1] as $cve => $dat){
				if($encontradoultimopunto){
					$puntosfaltantes[$cve]=$dat;
					if($primerpuntofaltante==0) $primerpuntofaltante = $cve;
				}
				if($cve == $datos['ultimopunto']){
					$encontradoultimopunto=true;
				}

			}

			if(count($puntosfaltantes)>0){
				$cvepuntos = "";
				foreach($puntosfaltantes as $cve => $duracion) $cvepuntos .= ",'".$cve."'";
				$cvepuntos = substr($cvepuntos, 1);

				$array_resultadopuntos = array();
				$hora = ($row['hora']=='00:00:00')?'04:00:00':$row['hora'];

				$res1 = mysql_query("SELECT a.geocerca as geofenceid, concat(a.fecha, ' ', a.hora) as servertime FROM eventos a INNER JOIN dispositivos b ON a.base = b.base AND a.dispositivo = b.cvebase
					WHERE a.fecha = '".$_POST['fecha']."' AND a.tipo = 'geofenceEnter' AND a.base='1' AND a.hora >= '$hora' AND a.hora < '".$row2['hora']."' AND b.uniqueid = '".$datos['imei']."' AND geocerca IN ($cvepuntos)
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
						if($row1['geofenceid'] == $primerpuntofaltante && $nvuelta==0){
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
				$resultadovueltas = $array_resultadopuntos[1];
				if(count($resultadovueltas) == 0) $resultadovueltas = $array_resultadopuntos[0];
				$i=0;
				$html = '';
				$puntos = count($resultadovueltas);
				$puntos_encontrados = 0;
				$horapropuesta='';
				
				foreach($puntosfaltantes as $cve => $dat){
					$duracion=$dat[0];
					$puntoinicio = $i;
					$encontrado = false;
					while($i<$puntos){
						if($resultadovueltas[$i]['idpunto'] == $cve){
							if($cve == $primerpuntofaltante || $primerpuntoencontrado){
								$horapropuesta = substr($resultadovueltas[$i]['horapunto'],-8);
							}
							else{
								$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
							}
							$primerpuntoencontrado = false;
							$datopunto=array();
							$datopunto['horapropuesta'] = $horapropuesta;
							$datopunto['horagps'] = substr($resultadovueltas[$i]['horapunto'],-8);
							$datopunto['html']='<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
							$limiteini = date( "H:i:s" , strtotime ( "-2 minute" , strtotime($horapropuesta) ) );
							$limitefin = date( "H:i:s" , strtotime ( "+ 2 minute" , strtotime($horapropuesta) ) );

							$diferencia=$diferencia = restahoras(substr($resultadovueltas[$i]['horapunto'],-8),$horapropuesta,0);

							$datopunto['diferencia'] = $diferencia;

							$datopunto['html'] .= '<td align="center"><div width="100%" style="background-color: #FFFF00;">'.substr($resultadovueltas[$i]['horapunto'],-8).'</div></td>';

							if(substr($resultadovueltas[$i]['horapunto'],-8) <= $limitefin && substr($resultadovueltas[$i]['horapunto'],-8) >= $limiteini)
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="GREEN">'.$diferencia.'</font></td>';
							elseif(substr($resultadovueltas[$i]['horapunto'],-8)<$limiteini)
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="BLUE">'.$diferencia.'</font></td>';
							else
								$datopunto['html'].= '<td style="border-right: solid 5px #000" align="center"><font color="RED">'.$diferencia.'</font></td>';

							$datos['vuelta'][$cve] = $datopunto;

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
						$datopunto = array();
						$i=$puntoinicio;
						if($cve == $primerpunto)
							$horapropuesta = $row['hora'];
						else
							$horapropuesta = date( "H:i:s" , strtotime ( "+ ".$duracion." minute" , strtotime($horapropuesta) ) );
						$datopunto['horapropuesta'] = $horapropuesta;

						$datopunto['html'] = '<td style="border-left: solid 5px #000" align="center">'.$horapropuesta.'</td>';
						$datopunto['html'] .= '<td>&nbsp;</td><td style="border-right: solid 5px #000">&nbsp;</td>';

						$datos['vuelta'][$cve] = $datopunto;
					}
				}
				$datos['tiempovuelta'] = sumar_tiempo($datos['tiempovuelta'], $tiempo);
			}


			$resultadosfechaanterior[$datos['primerahora'].'_'.$datos['folio']] = $datos;
		}
	}

	

	//echo '<pre>';
	//echo print_r($resultadosfechaanterior);
	//echo '</pre>';

	

	//echo '<pre>';
	//print_r($array_datosactuales);
	//echo '</pre>';




	echo '<h3>IDAS</h3>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="yourTableID2"><thead>';
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Economico</th><th rowspan="2">Clave Operador</th><th rowspan="2">Folio Tarjeta</th><th rowspan="2">Fecha Tarjeta</th><th rowspan="2">Hora Tarjeta</th><!--<th rowspan="2">Hora Rol</th>-->';
	foreach($array_puntos[0] as $k=>$v){
		echo '<th colspan="3">'.$k.''.$v[1].'</th>';
	}
	echo '<th rowspan="2">Tiempo Ida</th><th rowspan="2">&nbsp;&nbsp;</th>';
	echo '</tr>';
	
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_puntos[0] as $v){
		echo '<th>H.P.</th><th>H.R.</th><th>DIF.</th>';
	}
	echo '</tr>';
	echo '</thead><tbody>';

	ksort($array_datosactuales);


	foreach($resultadosfechaanterior as $row){
		if($row['tipoultimopunto'] == 0){
			//rowb();
			echo '<tr>';
			if($row['folio'] == ''){
				echo '<td style="background-color: #CCCCFF!important;" colspan="3">&nbsp;</td>';
			}
			else{
				
				echo '<td align="center" style="background-color: #CCCCFF!important;">'.$row['no_eco'];
				if($_SESSION['CveUsuario'] == 1) echo '<br>'.$row['imei'];
				echo '</td><td align="center">'.$row['clave'].'</td>
				<td align="center"';
				if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
				echo '>'.$row['folio'];
				echo '</td><td align="center">'.$row['fecha'].'</td><td align="center">'.$row['hora_tarjeta'].'</td>';
			}
			$llegoultimo = false;
			foreach($array_puntos[0] as $cve => $v){
				echo $row['ida'][$cve]['html'];
			}
			echo '<td>'.$row['tiempoida'].'</td>';
			echo '</tr>';
		}
	}



	foreach($array_datosactuales as $row){
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
		foreach($array_puntos[0] as $cve => $v){
			echo $row['ida'][$cve]['html'];
		}
		echo '<td>'.$row['tiempoida'].'</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';

	echo '<h3>REGRESOS</h3>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="yourTableID3"><thead>';
	echo '<tr bgcolor="#E9F2F8"><th rowspan="2">Economico</th><th rowspan="2">Clave Operador</th><th rowspan="2">Folio Tarjeta</th><th rowspan="2">Fecha Tarjeta</th><th rowspan="2">Hora Tarjeta</th><!--<th rowspan="2">Hora Rol</th>-->';
	$auxiliar=array();
	foreach($array_puntos[1] as $k=>$v){
		$auxiliar[] = array($k,$v);
	}
	krsort($auxiliar);
	$array_puntos[1] = array();
	foreach($auxiliar as $v){
		$array_puntos[1][$v[0]] = $v[1];
	}

	foreach($array_puntos[1] as $k=>$v){
		echo '<th colspan="3">'.$k.''.$v[1].'</th>';
	}
	echo '<th rowspan="2">Tiempo Ida</th><th rowspan="2">&nbsp;&nbsp;</th>';
	echo '</tr>';
	
	echo '<tr bgcolor="#E9F2F8">';
	foreach($array_puntos[1] as $v){
		echo '<th>H.P.</th><th>H.R.</th><th>DIF.</th>';
	}
	echo '</tr>';
	echo '</thead><tbody>';

	foreach($resultadosfechaanterior as $row){
			//rowb();
			echo '<tr>';
			if($row['folio'] == ''){
				echo '<td style="background-color: #CCCCFF!important;" colspan="3">&nbsp;</td>';
			}
			else{
				
				echo '<td align="center" style="background-color: #CCCCFF!important;">'.$row['no_eco'];
				echo '</td><td align="center">'.$row['clave'].'</td>
				<td align="center"';
				if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
				echo '>'.$row['folio'];
				echo '</td><td align="center">'.$row['fecha'].'</td><td align="center">'.$row['hora_tarjeta'].'</td>';
			}
			$llegoultimo = false;
			foreach($array_puntos[1] as $cve => $v){
				echo $row['vuelta'][$cve]['html'];
			}
			echo '<td>'.$row['tiempovuelta'].'</td>';
			echo '</tr>';
	}

	foreach($array_datosactuales as $row){
		rowb();
		if($row['folio'] == ''){
			echo '<td colspan="3">&nbsp;</td>';
		}
		else{
			
			echo '<td align="center">'.$row['no_eco'];
			echo '</td><td align="center">'.$row['clave'].'</td>
			<td align="center"';
			if($row['estatus']=='P') echo 'style="background-color: #00FF00!important;"';
			echo '>'.$row['folio'];
			echo '</td><td align="center">'.$row['fecha'].'</td><td align="center">'.$row['hora_tarjeta'].'</td>';
		}
		foreach($array_puntos[1] as $cve => $v){
			echo $row['vuelta'][$cve]['html'];
		}
		echo '<td>'.$row['tiempovuelta'].'</td>';
		echo '</tr>';
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
			objeto.open("POST","recorridoffcc6.php",true);
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

					$("#yourTableID3").chromatable({
		
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


