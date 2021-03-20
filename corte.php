<?php
	include("main.php");
	$x=0;
	$rsUsuario=mysql_query("SELECT * FROM usuarios");
	while($Usuario=mysql_fetch_array($rsUsuario)){
		$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
		if($x>0){
			$usuarios.=",".$Usuario['cve']."";	
			}else{
			$usuarios.="".$Usuario['cve']."";
		}
		$x++;
	}
	
	$array_empresa=array();
	$x=0;
	$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_empresa[$row['cve']]=$row['nombre'];
		$array_empresalogo[$row['cve']]=$row['logo'];
		if($x>0){
			$empresas.=",".$row['cve']."";	
			}else{
			$empresas.="".$row['cve']."";
		}
		$x++;
	}
	$x=0;
	$rsMotivo=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
	while($Motivo=mysql_fetch_array($rsMotivo)){
		$array_recaudacion[$Motivo['cve']]=$Motivo['nombre'];
		if($x>0){
			$recaudaciones.=",".$Motivo['cve']."";	
			}else{
			$recaudaciones.="".$Motivo['cve']."";
		}
		$x++;
	}
	
	if($_POST['ajax']==12){
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th rowspan="2">Usuario</th><th rowspan="2">Empresa</th><th rowspan="2">Abono Unidades</th>
		<th rowspan="2">Abono Deuda Operador</th><th rowspan="2">Abono General</th>
		<th rowspan="2">Cargo por Servicios</th><th rowspan="2">Fianza</th><th rowspan="2">Mutualidad</th><th rowspan="2">Seguridad</th>
		<th rowspan="2">Tarjeta Reposicion</th><th rowspan="2">Pago Curso</th><th rowspan="2">TAG</th>
		
		<th colspan="8">Recaudacion por Monitoreo</th>
		<th colspan="3">Recaudacion Pachuca</th>
		<th rowspan="2">Total</th>
		</tr>
		<tr bgcolor="#E9F2F8">
		<th>Boletos</th><th>Boletos Tijera</th><th>Boletos a Bordo</th><th>Boletos Movil</th><th>Abono Movil</th><th>Vale Dinero</th><th>Utilidad</th><th>Total Gastos</th>
		<th>Boletos</th><!--<th>Boletos Tijera</th><th>Boletos a Bordo</th><th>Boletos Movil</th>--><th>Abono Movil</th><!--<th>Vale Dinero</th>--><th>Utilidad</th></tr>'; 
		$array_total = array(0,0,0,0,0);
		$total_gastos=0;
		if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion']."";$fil_rec1=" AND b.recaudacion = ".$_POST['recaudacion'].""; }
		/*	$res = mysql_query("
			SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			SUM(a.general) as general, SUM(a.mutualidad) as mutualidad, SUM(a.monitoreo) as monitoreo, 
			SUM(a.boletos) as boletos, SUM(a.boletos_tijera) as boletos_tijera, SUM(a.boletos_abordo) as boletos_abordo,
			a.nomusuario, a.nomempresa 
			FROM (
			(SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			0 as general, 0 as mutualidad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo,
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 0 as general, 
			SUM(IF(a.cargo=1,a.monto,0)) as mutualidad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, SUM(a.monto) as general, 
			0 as mutualidad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general, 
			0 as mutualidad, SUM(a.monto) as monitoreo, SUM(a.monto_boletos) as boletos, 
			SUM(a.monto_boletos_tijera) as boletos_tijera, SUM(a.monto_boletos_abordo) as boletos_abordo, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.monto) as abono_adeudo_operador, 0 as general, 
			0 as mutualidad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM deuda_operador_abono a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		) a GROUP BY a.usuario, a.empresa ORDER BY a.nomusuario, a.nomempresa");*/
		$res = mysql_query("
		SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
		SUM(a.general) as general,
		SUM(a.pago_curso) as pago_curso,SUM(a.tarjeta_reposicion) as tarjeta_reposicion,SUM(a.cargo_servicios) as cargo_servicios,
		SUM(a.mutualidad) as mutualidad,SUM(a.seguridad) as seguridad, SUM(a.monitoreo) as monitoreo, 
		SUM(a.boletos) as boletos, SUM(a.boletos_tijera) as boletos_tijera, SUM(a.boletos_abordo) as boletos_abordo, SUM(a.boletos_movil) as boletos_movil, 
		SUM(a.abonos_movil) as abonos_movil, SUM(a.vales_dinero) as vales_dinero, SUM(a.fianza) as fianza, SUM(a.monitoreop) as monitoreop, 
		SUM(a.boletosp) as boletosp, SUM(a.boletos_tijerap) as boletos_tijerap, SUM(a.boletos_abordop) as boletos_abordop, SUM(a.boletos_movilp) as boletos_movilp,
		SUM(a.abonos_movilp) as abonos_movilp, SUM(a.vales_dinerop) as vales_dinerop,
		a.nomusuario, a.nomempresa,SUM(a.total_gast) as total_gastos, SUM(a.tag) as tag
		FROM (
		(SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
		0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 0 as boletos_movil, 
		0 as abonos_movil, 0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop, 0 as boletos_movilp,
		0 as abonos_movilp, 0 as vales_dinerop,
		b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast, 0 as tag
		FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		UNION ALL 
		(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 0 as general,
		SUM(IF(a.cargo=-1,a.monto,0)) as pago_curso,SUM(IF(a.cargo=2,a.monto,0)) as tarjeta_reposicion,SUM(IF(a.cargo=3,a.monto,0)) as cargo_servicios,
		SUM(IF(a.cargo=1,a.monto,0)) as mutualidad,SUM(IF(a.cargo=4,a.monto,0)) as seguridad, 0 as monitoreo, 0 as boletos, 
		0 as boletos_tijera, 0 as boletos_abordo, 0 as boletos_movil, 0 as abonos_movil, 0 as vales_dinero, SUM(IF(a.cargo=5,a.monto,0)) as fianza, 0 as monitoreop, 
		0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop, 0 as boletos_movilp, 0 as abonos_movilp, 
		0 as vales_dinerop, 
		b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast, SUM(IF(a.cargo=6, a.monto, 0)) as tag
		FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		UNION ALL 
		(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, SUM(a.monto) as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 0 as boletos_movil, 
		0 as abonos_movil, 0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop, 0 as boletos_movilp,
		0 as abonos_movilp, 0 as vales_dinerop,
		b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast, 0 as tag
		FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		UNION ALL 
		(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, SUM(a.monto) as monitoreo, SUM(a.monto_boletos) as boletos, 
		SUM(a.monto_boletos_tijera) as boletos_tijera, SUM(a.monto_boletos_abordo) as boletos_abordo, SUM(a.monto_taqmovil) as boletos_movil,
		SUM(a.monto_abonomovil) as abonos_movil, SUM(a.monto_vale_dinero) as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop, 0 as boletos_movilp,
		0 as abonos_movilp, 0 as vales_dinerop, 
		b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast, 0 as tag
		FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero!=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		UNION ALL 
		(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 
		0 as boletos_tijera, 0 as boletos_abordo, 0 as boletos_movil, 0 as abonos_movil,
		0 as vales_dinero, 0 as fianza, SUM(a.monto) as monitoreop, 
		SUM(a.monto_boletos) as boletosp, SUM(a.monto_boletos_tijera) as boletos_tijerap, 
		SUM(a.monto_boletos_abordo) as boletos_abordop, SUM(a.monto_taqmovil) as boletos_movilp,
		SUM(a.monto_abonomovil) as abonos_movilp, SUM(a.monto_vale_dinero) as vales_dinerop, 
		b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast, 0 as tag
		FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		UNION ALL 
		(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.monto) as abono_adeudo_operador, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 0 as boletos_movil,
		0 as abonosmovil, 0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop, 0 as boletos_movilp,
		0 as abonosmovilp, 0 as vales_dinerop,
		b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast, 0 as tag
		FROM deuda_operador_abono a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
		GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		) a GROUP BY a.usuario, a.empresa ORDER BY a.nomusuario, a.nomempresa") or die(mysql_error());
		while($row = mysql_fetch_array($res)){
			rowb();
			echo '<td>'.utf8_encode($row['nomusuario']).'';echo'</td>';
			echo '<td>'.utf8_encode($row['nomempresa']).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="right">'.number_format($row['abono_adeudo_operador'],2).'</td>';
			echo '<td align="right">'.number_format($row['general'],2).'</td>';
			echo '<td align="right">'.number_format($row['cargo_servicios'],2).'</td>';
			echo '<td align="right">'.number_format($row['fianza'],2).'</td>';
			echo '<td align="right">'.number_format($row['mutualidad'],2).'</td>';
			echo '<td align="right">'.number_format($row['seguridad'],2).'</td>';
			echo '<td align="right">'.number_format($row['tarjeta_reposicion'],2).'</td>';
			echo '<td align="right">'.number_format($row['pago_curso'],2).'</td>';
			echo '<td align="right">'.number_format($row['tag'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletos'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletos_tijera'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletos_abordo'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletos_movil'],2).'</td>';
			echo '<td align="right">'.number_format($row['abonos_movil'],2).'</td>';
			echo '<td align="right">'.number_format($row['vales_dinero'],2).'</td>';
			$t1=$row['monto']+$row['abono_adeudo_operador']+$row['general']+$row['pago_curso']+$row['tag']+$row['tarjeta_reposicion']+$row['cargo_servicios']
			+$row['mutualidad']+$row['fianza']+$row['seguridad']+$row['boletos']+$row['boletos_tijera']+$row['boletos_abordo']+$row['boletos_movil']
			+$row['abonos_movil']+$row['vales_dinero'];
			echo '<td align="right">'.number_format($t1,2).'</td>';
			//echo '<td align="right">'.number_format($row['monitoreo'],2).'</td>';
			echo '<td align="right">'.number_format($row['total_gastos'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletosp'],2).'</td>';
			//		echo '<td align="right">'.number_format($row['boletos_tijerap'],2).'</td>';
			//		echo '<td align="right">'.number_format($row['boletos_abordop'],2).'</td>';
			//		echo '<td align="right">'.number_format($row['boletos_movilp'],2).'</td>';
			echo '<td align="right">'.number_format($row['abonos_movilp'],2).'</td>';
			//		echo '<td align="right">'.number_format($row['vales_dinerop'],2).'</td>';
			$t2=$row['boletosp']+$row['boletos_tijerap']+$row['boletos_abordop']+$row['boletos_movilp']+$row['abonos_movilp']+$row['vales_dinerop']-$row['total_gastos'];
			echo '<td align="right">'.number_format($t2,2).'</td>';
			//echo '<td align="right">'.number_format($row['monitoreop'],2).'</td>';
			echo '<td align="right">'.number_format($t1+$t2,2).'</td>';
			//		echo '<td align="right">'.number_format($row['monto']+$row['abono_adeudo_operador']+$row['general']+$row['mutualidad']+$row['fianza']+$row['seguridad']+$row['boletos']+$row['monitoreo']+$row['monitoreop'],2).'</td>';
			echo '</tr>';
			$c=0;
			$array_total[$c]+=round($row['monto'],2);$c++;
			$array_total[$c]+=round($row['abono_adeudo_operador'],2);$c++;
			$array_total[$c]+=round($row['general'],2);	$c++;
			$array_total[$c]+=round($row['cargo_servicios'],2);$c++;
			$array_total[$c]+=round($row['fianza'],2);$c++;
			$array_total[$c]+=round($row['mutualidad'],2);$c++;
			$array_total[$c]+=round($row['seguridad'],2);$c++;
			$array_total[$c]+=round($row['tarjeta_reposicion'],2);$c++;
			$array_total[$c]+=round($row['pago_curso'],2);$c++;
			$array_total[$c]+=round($row['tag'],2);$c++;
			
			
			
			
			
			$array_total[$c]+=round($row['boletos'],2);$c++;
			$array_total[$c]+=round($row['boletos_tijera'],2);$c++;
			$array_total[$c]+=round($row['boletos_abordo'],2);$c++;
			$array_total[$c]+=round($row['boletos_movil'],2);$c++;
			$array_total[$c]+=round($row['abonos_movil'],2);$c++;
			$array_total[$c]+=round($row['vales_dinero'],2);$c++;
			$array_total[$c]+=round($t1,2);$c++;
			$array_total[$c]+=round($row['total_gastos'],2);$c++;
			$array_total[$c]+=round($row['boletosp'],2);$c++;
			$array_total[$c]+=round($row['boletos_tijerap'],2);$c++;
			$array_total[$c]+=round($row['boletos_abordop'],2);$c++;
			$array_total[$c]+=round($row['boletos_movilp'],2);$c++;
			$array_total[$c]+=round($row['abonos_movilp'],2);$c++;
			$array_total[$c]+=round($row['vales_dinerop'],2);$c++;
			$array_total[$c]+=round($t2,2);$c++;
			//		$array_total[17]+=round($row['monto']+$row['abono_adeudo_operador']+$row['general']+$row['mutualidad']+$row['seguridad']+$row['monitoreo']+$row['monitoreop'],2);
			$array_total[$c]+=round($t1+$t2,2);
			$total_gastos+=round($t1+$t2,2);
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="2">Totales</th>';
		foreach($array_total as $k=>$v) {
			if(($k!='19') && ($k!='20') && ($k!='21') && ($k!='23')){
				echo '<th align="right">'.number_format($v,2).'</th>';
			}
		}
		echo '</tr></table>';
		echo'|';
		////
		//$array_total = array(0,0,0,0,0);
		//	if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion']."";$fil_rec1=" AND b.recaudacion = ".$_POST['recaudacion'].""; }
		/*$res = mysql_query("
			SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			SUM(a.general) as general, SUM(a.mutualidad) as mutualidad,SUM(a.seguridad) as seguridad, SUM(a.monitoreo) as monitoreo, 
			SUM(a.boletos) as boletos, SUM(a.boletos_tijera) as boletos_tijera, SUM(a.boletos_abordo) as boletos_abordo, 
			SUM(a.vales_dinero) as vales_dinero, SUM(a.fianza) as fianza, SUM(a.monitoreop) as monitoreop, 
			SUM(a.boletosp) as boletosp, SUM(a.boletos_tijerap) as boletos_tijerap, SUM(a.boletos_abordop) as boletos_abordop, 
			SUM(a.vales_dinerop) as vales_dinerop,
			a.nomusuario, a.nomempresa 
			FROM (
			(SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			0 as general, 0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 0 as general, 
			SUM(IF(a.cargo=1,a.monto,0)) as mutualidad,SUM(IF(a.cargo=4,a.monto,0)) as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 0 as vales_dinero, SUM(IF(a.cargo=5,a.monto,0)) as fianza, 0 as monitoreop, 
			0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, SUM(a.monto) as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, SUM(a.monto) as monitoreo, SUM(a.monto_boletos) as boletos, 
			SUM(a.monto_boletos_tijera) as boletos_tijera, SUM(a.monto_boletos_abordo) as boletos_abordo, 
			SUM(a.monto_vale_dinero) as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero!=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, SUM(a.monto) as monitoreop, 
			SUM(a.monto_boletos) as boletosp, SUM(a.monto_boletos_tijera) as boletos_tijerap, 
			SUM(a.monto_boletos_abordo) as boletos_abordop,
			SUM(a.monto_vale_dinero) as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.monto) as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa
			FROM deuda_operador_abono a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$usuarios.") AND a.empresa IN (".$empresas.") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		) a GROUP BY a.usuario, a.empresa ORDER BY a.nomusuario, a.nomempresa") or die(mysql_error());*/
		/*		$res = mysql_query("
			SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			SUM(a.general) as general, SUM(a.mutualidad) as mutualidad,SUM(a.seguridad) as seguridad, SUM(a.monitoreo) as monitoreo, 
			SUM(a.boletos) as boletos, SUM(a.boletos_tijera) as boletos_tijera, SUM(a.boletos_abordo) as boletos_abordo, 
			SUM(a.vales_dinero) as vales_dinero, SUM(a.fianza) as fianza, SUM(a.monitoreop) as monitoreop, 
			SUM(a.boletosp) as boletosp, SUM(a.boletos_tijerap) as boletos_tijerap, SUM(a.boletos_abordop) as boletos_abordop, 
			SUM(a.vales_dinerop) as vales_dinerop,
			a.nomusuario, a.nomempresa,SUM(a.total_gast) as total_gastos
			FROM (
			(SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			0 as general, 0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 0 as general, 
			SUM(IF(a.cargo=1,a.monto,0)) as mutualidad,SUM(IF(a.cargo=4,a.monto,0)) as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 0 as vales_dinero, SUM(IF(a.cargo=5,a.monto,0)) as fianza, 0 as monitoreop, 
			0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, SUM(a.monto) as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, SUM(a.monto) as monitoreo, SUM(a.monto_boletos) as boletos, 
			SUM(a.monto_boletos_tijera) as boletos_tijera, SUM(a.monto_boletos_abordo) as boletos_abordo, 
			SUM(a.monto_vale_dinero) as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero!=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, SUM(a.monto) as monitoreop, 
			SUM(a.monto_boletos) as boletosp, SUM(a.monto_boletos_tijera) as boletos_tijerap, 
			SUM(a.monto_boletos_abordo) as boletos_abordop,
			SUM(a.monto_vale_dinero) as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.monto) as abono_adeudo_operador, 0 as general, 
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM deuda_operador_abono a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
		) a GROUP BY a.usuario, a.empresa ORDER BY a.nomusuario, a.nomempresa") or die(mysql_error());*/
		/*		$res = mysql_query("
			SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			SUM(a.general) as general,
			SUM(a.pago_curso) as pago_curso,SUM(a.tarjeta_reposicion) as tarjeta_reposicion,SUM(a.cargo_servicios) as cargo_servicios,
			SUM(a.mutualidad) as mutualidad,SUM(a.seguridad) as seguridad, SUM(a.monitoreo) as monitoreo, 
			SUM(a.boletos) as boletos, SUM(a.boletos_tijera) as boletos_tijera, SUM(a.boletos_abordo) as boletos_abordo, 
			SUM(a.vales_dinero) as vales_dinero, SUM(a.fianza) as fianza, SUM(a.monitoreop) as monitoreop, 
			SUM(a.boletosp) as boletosp, SUM(a.boletos_tijerap) as boletos_tijerap, SUM(a.boletos_abordop) as boletos_abordop, 
			SUM(a.vales_dinerop) as vales_dinerop,
			a.nomusuario, a.nomempresa,SUM(a.total_gast) as total_gastos
			FROM (
			(SELECT a.usuario, a.empresa, SUM(a.monto) as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 
			0 as general,
			0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.abono_adeudo_operador) as abono_adeudo_operador, 0 as general,
			SUM(IF(a.cargo=-1,a.monto,0)) as pago_curso,SUM(IF(a.cargo=2,a.monto,0)) as tarjeta_reposicion,SUM(IF(a.cargo=3,a.monto,0)) as cargo_servicios,
			SUM(IF(a.cargo=1,a.monto,0)) as mutualidad,SUM(IF(a.cargo=4,a.monto,0)) as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 0 as vales_dinero, SUM(IF(a.cargo=5,a.monto,0)) as fianza, 0 as monitoreop, 
			0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, SUM(a.monto) as general,
			0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general,
			0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
			0 as mutualidad,0 as seguridad, SUM(a.monto) as monitoreo, SUM(a.monto_boletos) as boletos, 
			SUM(a.monto_boletos_tijera) as boletos_tijera, SUM(a.monto_boletos_abordo) as boletos_abordo, 
			SUM(a.monto_vale_dinero) as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero!=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, 0 as abono_adeudo_operador, 0 as general,
			0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 
			0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, SUM(a.monto) as monitoreop, 
			SUM(a.monto_boletos) as boletosp, SUM(a.monto_boletos_tijera) as boletos_tijerap, 
			SUM(a.monto_boletos_abordo) as boletos_abordop,
			SUM(a.monto_vale_dinero) as vales_dinerop, 
			b.usuario as nomusuario, c.nombre as nomempresa, sum(a.total_gasto) as total_gast
			FROM recaudacion_autobus a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.derrotero=11 AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			UNION ALL 
			(SELECT a.usuario, a.empresa, 0 as monto, SUM(a.monto) as abono_adeudo_operador, 0 as general,
			0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
			0 as mutualidad,0 as seguridad, 0 as monitoreo, 0 as boletos, 0 as boletos_tijera, 0 as boletos_abordo, 
			0 as vales_dinero, 0 as fianza, 0 as monitoreop, 0 as boletosp, 0 as boletos_tijerap, 0 as boletos_abordop,
			0 as vales_dinerop,
			b.usuario as nomusuario, c.nombre as nomempresa, 0 as total_gast
			FROM deuda_operador_abono a INNER JOIN usuarios b ON b.cve = a.usuario INNER JOIN empresas c ON c.cve = a.empresa
			WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
			a.usuario IN (".$_POST['usuarios'].") AND a.empresa IN (".$_POST['empresas'].") 
			GROUP BY a.usuario, a.empresa ORDER BY b.usuario, c.nombre)
			) a GROUP BY a.usuario, a.empresa ORDER BY a.nomusuario, a.nomempresa") or die(mysql_error());
			while($row = mysql_fetch_array($res)){
			
			$array_total[0]+=round($row['monto'],2);
			$array_total[1]+=round($row['abono_adeudo_operador'],2);
			$array_total[2]+=round($row['general'],2);
			
			$array_total[3]+=round($row['pago_curso'],2);
			$array_total[4]+=round($row['tarjeta_reposicion'],2);
			$array_total[5]+=round($row['cargo_servicios'],2);
			$array_total[6]+=round($row['mutualidad'],2);
			$array_total[7]+=round($row['fianza'],2);
			$array_total[8]+=round($row['seguridad'],2);
			$array_total[9]+=round($row['boletos'],2);
			$array_total[10]+=round($row['boletos_tijera'],2);
			$array_total[11]+=round($row['boletos_abordo'],2);
			$array_total[12]+=round($row['vales_dinero'],2);
			$t1=$row['monto']+$row['abono_adeudo_operador']+$row['general']+$row['pago_curso']+$row['tarjeta_reposicion']+$row['cargo_servicios']
			+$row['mutualidad']+$row['fianza']+$row['seguridad']+$row['boletos']+$row['boletos_tijera']+$row['boletos_abordo']
			+$row['vales_dinero'];
			$array_total[13]+=round($t1,2);
			$array_total[14]+=round($row['boletosp'],2);
			$array_total[15]+=round($row['boletos_tijerap'],2);
			$array_total[16]+=round($row['boletos_abordop'],2);
			$array_total[17]+=round($row['vales_dinerop'],2);
			$t2=$row['boletosp']+$row['boletos_tijerap']+$row['boletos_abordop']+$row['vales_dinerop']-$row['total_gastos'];
			$array_total[18]+=round($t2,2);
			$array_total[19]+=round($t1+$t2,2);
		}*/
		echo'<th id="total_general" > &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total de Gastos: '.number_format($total_gastos,2).'</th>';	
		exit();
	}
	
	if($_POST['ajax']==1){
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8">';
		echo '<th rowspan="2">Usuario</th><th rowspan="2">Abono Unidades</th><th rowspan="2">Abono General</th><th rowspan="2">Cargo por Servicios</th><th rowspan="2">Fianza</th><th rowspan="2">Mutualidad</th><th rowspan="2">Seguridad</th><th rowspan="2">Tarjeta Reposicion</th><th rowspan="2">Pago Curso</th><th rowspan="2">TAG</th><th rowspan="2">Recibos Entradas</th>
		<th colspan="6">Recaudacion por Monitoreo</th>
		
		<th colspan="2">Ventas</th>
		<th rowspan="2">Efectivo a Entregar</th>
		</tr>
		<tr bgcolor="#E9F2F8"><th>Vale Dinero</th><th>Boletos a Bordo</th><th>Boletos sin guia</th><th>Total Gastos</th>
		<th>Utilidad</th>
		<th>MRecaucación</th>
		<th>Vales de dinero</th><th>Boletos sin guia</th></tr>'; 
		$array_total = array(0,0,0,0,0);
		$total_gastos=0;
		if ($_POST['recaudacion']!="") { $fil_rec=" AND a.recaudacion = ".$_POST['recaudacion']."";$fil_rec1=" AND b.recaudacion = ".$_POST['recaudacion'].""; }
		
		$res = mysql_query("
		SELECT SUM(a.efectivo_recaudado) as efectivo_recaudado, a.usuario, SUM(a.monto) as monto, 
		SUM(a.general) as general,
		SUM(a.pago_curso) as pago_curso,SUM(a.tarjeta_reposicion) as tarjeta_reposicion,SUM(a.cargo_servicios) as cargo_servicios,
		SUM(a.mutualidad) as mutualidad,SUM(a.seguridad) as seguridad, 
		SUM(a.boleto_singuia) as boleto_singuia, SUM(a.boletos_abordo) as boletos_abordo, SUM(a.vales_dinero) as vales_dinero, SUM(a.fianza) as fianza, 
		a.nomusuario,SUM(a.total_gast) as total_gastos, SUM(a.tag) as tag, SUM(a.venta_vales_dinero) as venta_vales_dinero, SUM(a.venta_boleto_singuia) as venta_boleto_singuia, SUM(a.recibos_entradas) as recibos_entradas
		FROM (
		(SELECT 0 AS efectivo_recaudado,a.usuario, SUM(a.monto) as monto, 
		0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 0 as boletos_abordo, 0 as vales_dinero, 0 as fianza,
		b.usuario as nomusuario, 0 as total_gast, 0 as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas
		FROM recaudacion_unidad a INNER JOIN usuarios b ON b.cve = a.usuario 
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].")
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado,a.usuario, 0 as monto, 0 as general,
		SUM(IF(a.cargo=-1,a.monto,0)) as pago_curso,SUM(IF(a.cargo=2,a.monto,0)) as tarjeta_reposicion,SUM(IF(a.cargo=3,a.monto,0)) as cargo_servicios,
		SUM(IF(a.cargo=1,a.monto,0)) as mutualidad,SUM(IF(a.cargo=4,a.monto,0)) as seguridad, 0 as boleto_singuia, 
		0 as boletos_abordo, 0 as vales_dinero, SUM(IF(a.cargo=5,a.monto,0)) as fianza, 
		b.usuario as nomusuario, 0 as total_gast, SUM(IF(a.cargo=6, a.monto, 0)) as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas
		FROM recaudacion_operador a INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].")
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado, a.usuario, 0 as monto, SUM(a.monto) as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 0 as boletos_abordo, 0 as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, 0 as total_gast, 0 as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas
		FROM abono_general_unidad a INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus != 'C'".$fil_rec1." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado, a.usuario, 0 as monto, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, SUM(a.monto_sencillos) as boleto_singuia, 
		SUM(a.monto_boletos_abordo) as boletos_abordo, SUM(a.monto_vale_dinero) as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, sum(a.total_gasto) as total_gast, 0 as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas
		FROM recaudacion_autobus a 
		INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT SUM(efectivo_recaudado) AS efectivo_recaudado, a.usuario, 0 as monto, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 
		0 as boletos_abordo, 0 as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, sum(a.total_gasto) as total_gast, 0 as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas
		FROM recaudacion_monitoreo a 
		INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus != 'C'".$fil_rec." AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado,a.usuario, 0 as monto, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 
		0 as boletos_abordo, 0 as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, 0 as total_gast, 0 as tag, SUM(a.monto) as venta_vales_dinero, 0 as venta_boleto_singuia, 0 as recibos_entradas 
		FROM vale_dinero a INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado,a.usuario, 0 as monto, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 
		0 as boletos_abordo, 0 as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, 0 as total_gast, 0 as tag, 0 as venta_vales_dinero, SUM(a.monto) as venta_boleto_singuia, 0 as recibos_entradas 
		FROM boletos_sencillos a INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus!='1' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		UNION ALL 
		(SELECT 0 AS efectivo_recaudado,a.usuario, 0 as monto, 0 as general,
		0 as pago_curso,0 as tarjeta_reposicion,0 as cargo_servicios,
		0 as mutualidad,0 as seguridad, 0 as boleto_singuia, 
		0 as boletos_abordo, 0 as vales_dinero, 0 as fianza, 
		b.usuario as nomusuario, 0 as total_gast, 0 as tag, 0 as venta_vales_dinero, 0 as venta_boleto_singuia, SUM(a.monto) as recibos_entradas 
		FROM recibos_entradas a INNER JOIN usuarios b ON b.cve = a.usuario
		WHERE a.estatus!='C' AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' AND 
		a.usuario IN (".$_POST['usuarios'].") 
		GROUP BY a.usuario ORDER BY b.usuario)
		) a GROUP BY a.usuario ORDER BY a.nomusuario") or die(mysql_error());
		
		
		
		while($row = mysql_fetch_array($res)){
			rowb();
			echo '<td>'.utf8_encode($row['nomusuario']).'';echo'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="right">'.number_format($row['general'],2).'</td>';
			echo '<td align="right">'.number_format($row['cargo_servicios'],2).'</td>';
			echo '<td align="right">'.number_format($row['fianza'],2).'</td>';
			echo '<td align="right">'.number_format($row['mutualidad'],2).'</td>';
			echo '<td align="right">'.number_format($row['seguridad'],2).'</td>';
			echo '<td align="right">'.number_format($row['tarjeta_reposicion'],2).'</td>';
			echo '<td align="right">'.number_format($row['pago_curso'],2).'</td>';
			echo '<td align="right">'.number_format($row['tag'],2).'</td>';
			echo '<td align="right">'.number_format($row['recibos_entradas'],2).'</td>';
			echo '<td align="right">'.number_format($row['vales_dinero'],2).'</td>';
			echo '<td align="right">'.number_format($row['boletos_abordo'],2).'</td>';
			echo '<td align="right">'.number_format($row['boleto_singuia'],2).'</td>';
			echo '<td align="right">'.number_format($row['total_gastos'],2).'</td>';
			
			$row['utilidad']=$row['vales_dinero']+$row['boletos_abordo']+$row['boleto_singuia']-$row['total_gastos'];
			
			echo '<td align="right">'.number_format($row['utilidad'],2).'</td>';
			echo '<td align="right">'.number_format($row['efectivo_recaudado'],2).'</td>';
			
			
			echo '<td align="right">'.number_format($row['venta_vales_dinero'],2).'</td>';
			echo '<td align="right">'.number_format($row['venta_boleto_singuia'],2).'</td>';
			$row['efectivo_entregar'] = $row['monto']+$row['general']+$row['cargo_servicios']+$row['fianza']+$row['mutualidad']+$row['seguridad']+$row['tarjeta_reposicion']+$row['pago_curso']+$row['tag']+$row['boletos_abordo']-$row['total_gastos']+$row['venta_vales_dinero']+$row['venta_boleto_singuia']+$row['recibos_entradas'] + $row['efectivo_recaudado'];
			
			
			echo '<td align="right">'.number_format($row['efectivo_entregar'],2).'</td>';
			echo '</tr>';
			$c=0;
			$array_total[$c]+=round($row['monto'],2);$c++;
			$array_total[$c]+=round($row['general'],2);	$c++;
			$array_total[$c]+=round($row['cargo_servicios'],2);$c++;
			$array_total[$c]+=round($row['fianza'],2);$c++;
			$array_total[$c]+=round($row['mutualidad'],2);$c++;
			$array_total[$c]+=round($row['seguridad'],2);$c++;
			$array_total[$c]+=round($row['tarjeta_reposicion'],2);$c++;
			$array_total[$c]+=round($row['pago_curso'],2);$c++;
			$array_total[$c]+=round($row['tag'],2);$c++;
			$array_total[$c]+=round($row['recibos_entradas'],2);$c++;
			$array_total[$c]+=round($row['vales_dinero'],2);$c++;
			$array_total[$c]+=round($row['boletos_abordo'],2);$c++;
			$array_total[$c]+=round($row['boleto_singuia'],2);$c++;
			$array_total[$c]+=round($row['total_gastos'],2);$c++;
			$array_total[$c]+=round($row['utilidad'],2);$c++;
			$array_total[$c]+=round($row['efectivo_recaudado'],2);$c++;
			
			
			$array_total[$c]+=round($row['venta_vales_dinero'],2);$c++;
			$array_total[$c]+=round($row['venta_boleto_singuia'],2);$c++;
			$array_total[$c]+=round($row['efectivo_entregar'],2);$c++;
			$total_gastos+=round($row['total_gastos'],2);
		}
		echo '<tr bgcolor="#E9F2F8"><th align="left" colspan="1">Totales</th>';
		foreach($array_total as $k=>$v) {
			echo '<th align="right">'.number_format($v,2).'</th>';
		}
		echo '</tr></table>';
		echo'|';
		
		echo'<th id="total_general" > &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total de Gastos: '.number_format($total_gastos,2).'</th>';	
		exit();
	}
	
	top($_SESSION);
	
	if($_POST['cmd']==0){
		if($impresio!=""){
			echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
		<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Usuarios</td><td><select name="usuarios" id="usuarios" multiple="multiple">';
		$array_usuariomov = array();
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN recaudacion_unidad b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN abono_general_unidad b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN recaudacion_autobus b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN vale_dinero b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN boletos_sencillos b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		$res=mysql_query("SELECT a.* FROM usuarios a INNER JOIN recibos_entradas b ON a.cve=b.usuario GROUP BY a.cve ORDER BY a.usuario");
		while($row = mysql_fetch_array($res)){
			$array_usuariomov[$row['cve']] = $row['usuario'];
		}
		asort($array_usuariomov);
		foreach($array_usuariomov as $k=>$v){
			echo '<option value="'.$k.'" selected>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		
		echo '
		<Script language="javascript">
		
		$("#usuarios").multipleSelect({
		width: 500
		});	
		
		function buscarRegistros()
		{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
		alert("Error: El Navegador no soporta AJAX");
		} else {
		objeto.open("POST","corte.php",true);
		objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto.send("ajax=1&usuarios="+$("#usuarios").multipleSelect("getSelects")+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
		objeto.onreadystatechange = function()
		{
		if (objeto.readyState==4){
		var opciones2=objeto.responseText.split("|");
		document.getElementById("Resultados").innerHTML = opciones2[0];
		}
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