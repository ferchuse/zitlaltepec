<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_db_query($base,"SELECT * FROM empresas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsAccidente=mysql_db_query($base,"SELECT * FROM accidentes");
while($Accidente=mysql_fetch_array($rsAccidente)){
	$array_accidente[$Accidente['cve']]=$Accidente['folio'];
	$array_accidente_uni[$Accidente['cve']]=$Accidente['unidad'];
	$array_accidente_ope[$Accidente['cve']]=$Accidente['conductor'];
}

$rsParque=mysql_db_query($base,"SELECT * FROM unidades");
while($Parque=mysql_fetch_array($rsParque)){
	$array_unidad[$Parque['cve']]=$Parque['no_eco'].' - '.$array_tipo_vehiculo[$Parque['tipo_unidad']];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM operadores");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_conductor[$Conductor['cve']]=$Conductor['credencial'].' - '.$Conductor['nombre'];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM personal");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_personal[$Conductor['cve']]=$Conductor['folio'].' - '.$Conductor['nombre'];
}

//$array_porcentaje=array(0,20,30,40,50,60,97.95,100);
$array_porcentaje=array();
$res=mysql_db_query($base,"SELECT * FROM porcentajes_accidente ORDER BY porcentaje");
while($row=mysql_fetch_array($res)){
	$array_porcentaje[]=$row['porcentaje'];
}
$nivelUsuario=nivelUsuario();

/*** GUARDAR FOTO *************************************/
if($_POST['cmd']==6){
	$res=mysql_db_query($base,"SELECT * FROM imagenes_accidentes WHERE cve='".$_POST['cvefoto']."'");
	$row=mysql_fetch_array($res);
	unlink("imgaccidentes/".$row['nombre'].".jpg");
	mysql_db_query($base,"DELETE FROM imagenes_accidentes WHERE cve='".$_POST['cvefoto']."'");
	$_POST['cmd']=4;
}

if($_POST['cmd']==5){
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		/*if(file_exists("fotos/foto".$_POST['reg'].".jpg")){
			unlink("fotos/foto".$id.".jpg");
		}*/
		$arch = $_FILES['foto']['tmp_name'];
		$nombre="foto".$_POST['reg']."_".date("Y-m-d H:i:s");
		copy($arch,"imgaccidentes/".$nombre.".jpg");
		chmod("imgaccidentes/".$nombre.".jpg", 0777);
		mysql_db_query($base,"INSERT imagenes_accidentes SET accidente='".$_POST['reg']."',nombre='".$nombre."'");
	}
	$_POST['cmd']=4;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	
	if($_POST['reg']) {
			$res=mysql_db_query($base,"SELECT conclusion,porcentaje FROM accidentes WHERE cve='".$_POST['reg']."'");
			$row=mysql_fetch_array($res);
			if(trim($_POST['conclusion'])!=trim($row['conclusion'])){
				$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_accidente WHERE 1") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_accidente
							SET folio='".$Folio[0]."',valor='".trim($_POST['conclusion'])."',
							cve_accidente='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_SESSION['CveUsuario']."'";
				$ejecutar_estatus=mysql_db_query($base,$insert_infonavit);			
			}
			if(trim($_POST['porcentaje'])!=trim($row['porcentaje'])){
				$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_accidente WHERE 1") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_accidente
							SET folio='".$Folio[0]."',valor='Cambio de porcentaje del ".$row['porcentaje']." al ".$_POST['porcentaje']."',
							cve_accidente='".$_POST['reg']."',fecha='".fechaLocal()."',usuario='".$_SESSION['CveUsuario']."'";
				$ejecutar_estatus=mysql_db_query($base,$insert_infonavit);			
			}
			$update="Update accidentes
						SET fecha_accidente='".$_POST['fecha_accidente']."',unidad='".$_POST['unidad']."',conductor='".$_POST['conductor']."',
						costo='".$_POST['costo']."',porcentaje='".$_POST['porcentaje']."',estatus='".$_POST['estatus']."',gestor='".$_POST['gestor']."',
						lugar='".$_POST['lugar']."',descripcion='".$_POST['descripcion']."',abono_ant='".$_POST['abono_ant']."',tipo='".$_POST['tipo']."',
						diario='".$_POST['diario']."',conclusion='".$_POST['conclusion']."',porcentajeuni='".$_POST['porcentajeuni']."',
						unisinregistro='".$_POST['unisinregistro']."',opesinregistro='".$_POST['opesinregistro']."',unidadesexternas='".$_POST['unidadesexternas']."',
						unidadext='".$_POST['unidadext']."',condext='".$_POST['condext']."'
						WHERE cve='".$_POST['reg']."'";
			mysql_db_query($base,$update);
	} else {
			//Insertar el Registro
			$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM accidentes WHERE 1") or die(mysql_error());
			$Folio=mysql_fetch_array($rsfolio);
			$insert="INSERT accidentes
						SET folio='".$Folio[0]."',
						fecha_accidente='".$_POST['fecha_accidente']."',unidad='".$_POST['unidad']."',conductor='".$_POST['conductor']."',
						costo='".$_POST['costo']."',porcentaje='".$_POST['porcentaje']."',estatus='".$_POST['estatus']."',gestor='".$_POST['gestor']."',
						lugar='".$_POST['lugar']."',descripcion='".$_POST['descripcion']."',abono_ant='".$_POST['abono_ant']."',tipo='".$_POST['tipo']."',
						diario='".$_POST['diario']."',usuario='".$_SESSION['CveUsuario']."',fecha='".fechaLocal()."',conclusion='".$_POST['conclusion']."',
						porcentajeuni='".$_POST['porcentajeuni']."',unisinregistro='".$_POST['unisinregistro']."',opesinregistro='".$_POST['opesinregistro']."',
						unidadesexternas='".$_POST['unidadesexternas']."',unidadext='".$_POST['unidadext']."',condext='".$_POST['condext']."'";
			mysql_db_query($base,$insert) or die(mysql_error());
			$cve=mysql_insert_id();
			if(trim($_POST['conclusion'])!=""){
				$rsfolio=mysql_db_query($base,"SELECT IFNULL(MAX(folio)+1,1) FROM cambios_datos_accidente WHERE 1") or die(mysql_error());
				$Folio=mysql_fetch_array($rsfolio);
				$insert_infonavit="	INSERT cambios_datos_accidente
							SET folio='".$Folio[0]."',valor='".trim($_POST['conclusion'])."',
							cve_accidente='".$cve."',fecha='".fechaLocal()."',usuario='".$_SESSION['CveUsuario']."'";
				$ejecutar_estatus=mysql_db_query($base,$insert_infonavit);			
			}
	}
	header("Location: accidentes.php");
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de Parque
		$select= " SELECT * FROM accidentes WHERE 1 ";
		if ($_POST['fecha_inicio']!="") { $select.=" AND fecha>='".$_POST['fecha_inicio']."'"; }
		if ($_POST['fecha_fin']!="") { $select.=" AND fecha<='".$_POST['fecha_fin']."'"; }
		if ($_POST['unidad']!="all") { $select.=" AND unidad='".$_POST['unidad']."'"; }
		if ($_POST['conductor']!="all") { $select.=" AND conductor='".$_POST['conductor']."'"; }
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
//		if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
		if ($_POST['tipo']!="all") { $select.=" AND tipo='".$_POST['tipo']."'"; }
		
		$select.=" ORDER BY folio DESC";
		$rsAccidente=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rsAccidente);
		/*if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY folio desc  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rsAccidente=mysql_db_query($base,$select);*/
		
		if(mysql_num_rows($rsAccidente)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="13">'.mysql_num_rows($rsAccidente).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Editar</th><th>Imprimir</th>';
			if($_SESSION['PlazaUsuario']==0) echo '<th>Plaza</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Fecha Accidente</th><th>Tipo</th><th>Unidad</th><th>Conductor</th><th>Costo</th><th>Monto a pagar por el conductor</th>';
			echo '<th>Abono</th><th>Saldo</th><th>Estatus</th>';
			echo '</tr>';
			$total=0;
			$costos=0;
			$abonos=0;
			$i=0;
			while($Accidente=mysql_fetch_array($rsAccidente)) {
				rowb();
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'accidentes.php\',\'\',\'1\',\''.$Accidente['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar '.$Accidente['cve'].'"></a></td>';
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'imp_accidente.php\',\'_bank\',\'1\','.$Accidente['cve'].')"><img src="images/b_print.png" border="0" title="Editar '.$Accidente['folio'].'"></a></td>';
				
				if($_SESSION['PlazaUsuario']==0)
					echo '<td>'.htmlentities($array_plaza[$Accidente['plaza']]).'</td>';
				

				echo '<td align="center">'.$Accidente['folio'].'</td>';
				echo '<td align="left">'.$Accidente['fecha'].'</td>';
				echo '<td align="center">'.$Accidente['fecha_accidente'].'</td>';
				echo '<td align="center">'.$array_tipo_accidente[$Accidente['tipo']].'</td>';
				if($Accidente['unidadesexternas']==1){
					echo '<td align="left">'.$Accidente['unidadext'].'</td>';
					echo '<td align="left">'.$Accidente['condext'].'</td>';
				}
				elseif($Accidente['tipo']==4){
					echo '<td align="left">'.$Accidente['unisinregistro'].'</td>';
					echo '<td align="left">'.$array_personal[$Accidente['opesinregistro']].'</td>';
				}
				else{
					echo '<td align="left">'.$array_unidad[$Accidente['unidad']].'</td>';
					echo '<td align="left">'.$array_conductor[$Accidente['conductor']].'</td>';
				}
				$rsSalidas=mysql_db_query($base,"SELECT sum(monto) as costototal,SUM(if(pagadopor='0',monto,0)) as costocond FROM recibos_salidas_accidentes WHERE estatus='1' AND accidente='".$Accidente['cve']."'");
				$Salidas=mysql_fetch_array($rsSalidas);
				$costo=($Salidas[1]*$Accidente['porcentaje']/100);
				$rsEntradas=mysql_db_query($base,"SELECT sum(monto) FROM recibos_entradas_accidentes WHERE estatus<'2' AND accidente='".$Accidente['cve']."'");
				$Entradas=mysql_fetch_array($rsEntradas);
				$rsAbonados=mysql_db_query($base,"SELECT sum(abono) FROM cargos_conductores WHERE estatus='Accidente' AND folio='".$Accidente['folio']."' AND conductor='".$Accidente['conductor']."'");
				$Abonados=mysql_fetch_array($rsAbonados);
				$abono=$Entradas[0]+$Abonados[0];
				echo '<td align="center">'.number_format($Salidas[0],2).'</td>';
				echo '<td align="center">'.number_format($costo ,2).'</td>';
				echo '<td align="center">'.number_format($abono,2).'</td>';
				echo '<td align="center"><a href="#" onClick="atcr(\'accidentes.php\',\'\',10,'.$Accidente['cve'].');">'.number_format($costo-$abono,2).'</a></td>';
				echo '<td align="center">'.$array_estatus_accidentes[$Accidente['estatus']].'</td>';
				$i++;
				echo '</tr>';
				$costos+=$Salidas[0];
				$abonos+=$abono;
				$total+=$costo;
			}
			
			echo '	
				<tr>
				<td colspan="7" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
				<td bgcolor="#E9F2F8" align="right">Totales:&nbsp;</td>
				<td bgcolor="#E9F2F8" align="center">'.number_format($costos,2).'</td>
				<td bgcolor="#E9F2F8" align="center">'.number_format($total,2).'</td>
				<td bgcolor="#E9F2F8" align="center">'.number_format($abonos,2).'</td>
				<td bgcolor="#E9F2F8" align="center">'.number_format($total-$abonos,2).'</td>
				<td bgcolor="#E9F2F8">&nbsp;</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}

if($_POST['ajax']==2) {
		//Listado de Historial
		$select= " SELECT * FROM recibos_salidas_accidentes WHERE accidente='".$_POST['accidente']."' and estatus='1' ORDER BY cve DESC";
		$rssalida=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rssalida);
		/*if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rssalida=mysql_db_query($base,$select);*/

		if(mysql_num_rows($rssalida)>0) 
		{
		
			echo '<h3 align="center"> Salidas de Accidentes </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Folio</th>';
			echo '<th>Fecha</th><th>Monto</th><th>Descripcion</th><th>Estatus</th><th>Usuario</th>';
			echo '</tr>';
			$tot=0;
			while($Salida=mysql_fetch_array($rssalida)) {
				rowb();
			//	echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.regcve_unidad.value=\''.$Auxiliar['cve_unidad'].'\';document.forma.regplaza.value=\''.$Auxiliar['plaza'].'\';atcr(\'accidentes.php\',\'\',\'1\','.$Auxiliar['cve'].')">'.$Auxiliar['folio'].'</a></td>';
				echo '<td align="center">'.$Salida['cve'].'</td>';
				echo '<td align="center">'.$Salida['fecha'].'</td>';
				echo '<td align="right">'.number_format($Salida['monto'],2).'</td>';
				echo '<td align="left">'.htmlentities($Salida['descripcion']).'</td>';
				echo '<td align="left">'.$array_estatus_accidentes[$Salida['estatus']].'';
				echo '<td align="left">'.$array_usuario[$Salida['usuario']].'';
				$tot+=$Salida['monto'];
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="2" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
				<td bgcolor="#E9F2F8" align="right">'.number_format($tot,2).'</td>
				<td colspan="3" bgcolor="#E9F2F8" align="right">&nbsp;</td>
				</tr>
				<input type="hidden" name="totcosto" id="totcosto" value="'.$tot.'">
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros de Salidas de accidentes</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}	

if($_POST['ajax']==3){
	$rsParque=mysql_db_query($base,"SELECT * FROM unidades WHERE 1 ORDER BY no_eco");
	while($Parque=mysql_fetch_array($rsParque)){
		echo $Parque['cve'].','.$Parque['no_eco'].'|';
	}
	exit();
}	

if($_POST['ajax']==4){
	$rsConductor=mysql_db_query($base,"SELECT * FROM operadores WHERE 1 ORDER BY nombre");
	while($Conductor=mysql_fetch_array($rsConductor)){
		echo $Conductor['cve'].','.$Conductor['credencial'].' - '.$Conductor['nombre'].'|';
	}
	exit();
}

if($_POST['ajax']==5) {
		//Listado de Historial
		$select= " SELECT * FROM cambios_datos_accidente WHERE cve_accidente='".$_POST['accidente']."' ORDER BY cve DESC";
		$rscambios=mysql_db_query($base,$select);
		$totalRegistros = mysql_num_rows($rscambios);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Conclusiones </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8"><th>Fecha Mov</th><th>Folio</th>';
			echo '<th>Valor</th><th>Fecha</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
				echo '<td align="center">'.$Cambios['fecha'].'</td>';
				echo '<td align="center">'.$Cambios['folio'].'</td>';
				echo '<td align="left">'.$Cambios['valor'].'</td>';
				echo '<td align="center">'.$Cambios['fecha'].'</td>';
				echo '<td align="left">'.$array_usuario[$Cambios['usuario']].'';
				$i++;
				echo '</tr>';
			}
			
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion(); echo '</td>
				</tr>
			</table>';
			
		} else {
			echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros de conclusiones</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}



top($_SESSION);
/*********** Edo cuenta Accidentes ******************/

if($_POST['cmd']==10){
	echo '<table>';
		echo '
			<tr><td><a href="#" onClick="atcr(\'accidentes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
	$res=mysql_db_query($base,"SELECT * FROM accidentes WHERE cve='".$_POST['reg']."'");
	$row=mysql_fetch_array($res);
	echo '<table>';
	echo '<tr><td class="tableEnc">Estado de Cuenta del Accidente # '.$row['folio'].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$sumacargo=$sumaabono=$cargo=$abono=0;
	$x=0;
	$rsSalidas=mysql_db_query($base,"SELECT fechapag FROM recibos_salidas_accidentes WHERE estatus='1' AND pagadopor='0' AND accidente='".$row['cve']."' ORDER BY fechapag");
	$Salidas=mysql_fetch_array($rsSalidas);
	$fecha_ini=$row['fecha'];
	$fecha=$fecha_ini;
	$rsEntradas=mysql_db_query($base,"SELECT fecha FROM recibos_entradas_accidentes WHERE estatus<'2' AND accidente='".$row['cve']."' ORDER BY fecha DESC");
	$Entradas=mysql_fetch_array($rsEntradas);
	$rsAbonados=mysql_db_query($base,"SELECT fecha FROM cargos_conductores WHERE  estatus='Accidente' AND folio='".$row['folio']."' AND conductor='".$row['conductor']."' AND abono>0 ORDER BY fecha DESC");
	$Abonados=mysql_fetch_array($rsAbonados);
	/*if($Entradas[0]>$Abonados[0])
		$fecha_fin=$Entradas[0];
	else
		$fecha_fin=$Abonados[0];
	if($fecha_fin=="")*/
		$fecha_fin=fechaLocal();
	for($i=1;$fecha<=$fecha_fin;$i++){
		$rsSalida=mysql_db_query($base,"SELECT * FROM recibos_salidas_accidentes WHERE estatus='1' AND pagadopor='0' AND accidente='".$row['cve']."' AND fechapag='$fecha' ORDER BY cve");
		while($Salida=mysql_fetch_array($rsSalida)){
			$sumacargo+=round($Salida['monto']*$row['porcentaje']/100,2);
			rowb();
			echo '<td align=center>&nbsp;'.$Salida['fechapag'].'</td>';
			echo '<td align=left>&nbsp;Salida de Accidente #'.$Salida['cve'].'</td>';
			echo '<td align="right">'.number_format($Salida['monto']*$row['porcentaje']/100,2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($sumacargo-$sumaabono,2).'</td>';
			echo '<td>&nbsp;</td>';
			$x++;
		}
		$rsEntrada=mysql_db_query($base,"SELECT * FROM recibos_entradas_accidentes WHERE estatus<'2' AND accidente='".$row['cve']."' AND fecha='$fecha' ORDER BY cve");
		while($Entrada=mysql_fetch_array($rsEntrada)){
			$sumaabono+=$Entrada['monto'];
			rowb();
			echo '<td align=center>&nbsp;'.$Entrada['fecha'].'</td>';
			echo '<td align=left>&nbsp;Entrada de Accidente #'.$Entrada['cve'].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($Entrada['monto'],2).'</td>';
			echo '<td align="right">'.number_format($sumacargo-$sumaabono,2).'</td>';
			echo '<td>&nbsp;</td>';
			$x++;
		}
		$rsAbono=mysql_db_query($base,"SELECT * FROM cargos_conductores WHERE conductor='".$row['conductor']."' AND fecha='$fecha' AND estatus='Accidente' AND folio='".$row['folio']."' AND abono>0 ORDER BY cve");
		while ($Abono=mysql_fetch_array($rsAbono)) {	
			$sumaabono+=$Abono['abono'];
			rowb();
			echo '<td align=center>&nbsp;'.$Abono['fecha'].'</td>';
			echo '<td align=left>&nbsp;Abono a Accidente</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($Abono['abono'],2).'</td>';
			echo '<td align="right">'.number_format($sumacargo-$sumaabono,2).'</td>';
			$observaciones="";
			if($Cargo['desdesaldo']>0)
				$observaciones.="Desde Saldo a Favor &nbsp;&nbsp;";
			echo '<td align=left>&nbsp;'.$observaciones.'</td>';
			echo '</tr>';
			$x++;
		}
		$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($fecha_ini) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumacargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumaabono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumacargo-$sumaabono,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';

}

/********** IMAGENES***************************************/
	if($_POST['cmd']==4){
		$select1="SELECT * FROM accidentes WHERE cve='".$_POST['reg']."'";
		$rsaccidentes=mysql_db_query($base,$select1);
		$Accidente=mysql_fetch_array($rsaccidentes);
		if($_POST['reg']==0){
			$Accidente['folio']="Nuevo";
			$Accidente['fecha']=fechaLocal();
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
//			if($_SESSION[$archivo[(count($archivo)-1)]]>1){
			if($nivelUsuario>1){
				echo '<td><a href="#" onClick="
					atcr(\'accidentes.php\',\'\',\'5\',\''.$Accidente['cve'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
				
			}
			echo '<td><a href="#" onClick="atcr(\'accidentes.php\',\'\',\'1\',\''.$_POST['reg'].'\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Fotos del Accidente # '.$Accidente['folio'].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th>Nueva Foto</th><td><input type="file" name="foto" id="foto" class="textField"></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="cvefoto" value="">';
		echo '<table><tr><th>No.</th><th>Foto</th>';
//		if($_SESSION[$archivo[(count($archivo)-1)]]>1){
			if($nivelUsuario>1){
			echo '<th>Borrar</th>';
		}
		echo '</tr>';
		$rsFotos=mysql_db_query($base,"SELECT * FROM imagenes_accidentes WHERE accidente='".$_POST['reg']."' ORDER BY cve");
		$x=1;
		while($Foto=mysql_fetch_array($rsFotos)){
			rowb();
			echo '<td align="center"><b>'.$x.'</b></td>';
			echo '<td><img width="250" height="200" src="imgaccidentes/'.$Foto['nombre'].'.jpg" border="1"></td>';
			//if($_SESSION[$archivo[(count($archivo)-1)]]>1){
				if($nivelUsuario>1){
				echo '<td align="center"><a href="#" onClick="document.forma.cvefoto.value=\''.$Foto['cve'].'\';atcr(\'accidentes.php\',\'\',\'6\',\''.$_POST['reg'].'\')"><img src="images/basura.gif" border="0" title="Borrar"></a></td></td>';
			}
			echo '</tr>';
			$x++;
		}
		echo '</table>';


	}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		echo '<style>
		#Salidas {
			width: 70%;
			border-style: solid;
			border-width: 1px;
			border-color: #96BDE0;
		}
		</style>';
				
		$select1="SELECT * FROM accidentes WHERE cve='".$_POST['reg']."'";
		$rsaccidentes=mysql_db_query($base,$select1);
		$Accidente=mysql_fetch_array($rsaccidentes);
		if($_POST['reg']==0){
			$Accidente['folio']="Nuevo";
			$Accidente['fecha']=fechaLocal();
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
			//if($_SESSION[$archivo[(count($archivo)-1)]]>1){
			$nivelUsuario=nivelUsuario();
		if($nivelUsuario>1){
			
				echo '<td><a href="#" onClick="
				if((document.forma.diario.value/1)==0)
					alert(\'Necesita ingresar el cobro diario\');
				else{
					$(\'.deshabilitar\').removeAttr(\'disabled\');
					atcr(\'accidentes.php\',\'\',\'2\',\''.$Accidente['cve'].'\');
				}"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
				
			}
			if($_POST['reg']>0)
				echo '<td><a href="#" onClick="$(\'.deshabilitar\').removeAttr(\'disabled\');atcr(\'accidentes.php\',\'\',\'4\',\''.$_POST['reg'].'\');"><img src="images/historial.gif" border="0">&nbsp;Fotos</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'accidentes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Accidentes</td></tr>';
		echo '</table>';
	//	echo '<input type="hidden" name="cve_unidad" id="cve_unidad" value="'.$Parque['cve'].'">';
		echo '<table>';
		
		if($_SESSION['PlazaUsuario']==0 && $_POST['reg']==0){
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza" onChange="traeUnidades(this.value);"><option value="0">---Seleccione una Plaza---</option>';
			$rsPlazas=mysql_db_query($base,"SELECT * FROM plazas ORDER BY nombre");
			while($Plaza=mysql_fetch_array($rsPlazas)){
				echo '<option value="'.$Plaza['cve'].'"';
				if($Accidente['plaza']==$Plaza['cve']) echo ' selected';
				echo '>'.$Plaza['nombre'].'</option>';
			}
			echo '</select></td></tr>';
		}
		else if($_POST['reg']>0){
			echo '<tr><th align="left">Plaza</th><td><input type="text" value="'.$array_plaza[$Accidente['plaza']].'" class="readOnly" readonly><input type="hidden" name="plaza" id="plaza" value="'.$Accidente['plaza'].'"></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><th align="left">Referencia</th><td><input type="text" name="referencia" id="referencia"  size="10" value="'.$Accidente['folio'].'" class="readOnly" readonly></td></tr>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha"  size="15" value="'.$Accidente['fecha'].'" class="readOnly" readonly>';
		echo '<tr><th align="left">Fecha del Accidente</th><td><input type="text" name="fecha_accidente" id="fecha_accidente"  size="15" value="'.$Accidente['fecha_accidente'].'" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_accidente,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Tipo</th><td><select name="tipo" class="deshabilitar" id="tipo" onChange="
		$(\'.sinregistro\').hide();
		if(this.value==\'3\'){
			$(\'#idporcuni\').show();
		}
		else{
			$(\'#idporcuni\').hide();
			document.forma.porcentajeuni.options[0].selected=true;
		}
		if(this.value==\'4\'){
			$(\'.sinregistro\').show();
			$(\'.conregistro\').hide();
			document.forma.unidad.options[0].selected=true;
			document.forma.conductor.options[0].selected=true;
			document.forma.porcentajeuni.options[0].selected=true;
			document.forma.porcentaje.options[0].selected=true;
			document.forma.porcentajeuni.disabled=true;
			document.forma.porcentaje.disabled=true;
		}
		else{
			$(\'.sinregistro\').hide();
			$(\'.conregistro\').show();
			document.forma.porcentajeuni.disabled=false;
			document.forma.porcentaje.disabled=false;
		}
		if(this.value==\'5\'){
			$(\'.sinregistro\').show();
		}
		cambiarexternas();
		">';
		foreach($array_tipo_accidente as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Accidente['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Unidad Externa</th><td><input type="hidden" name="unidadesexternas" id="unidadesexternas" value="'.$Accidente['unidadesexternas'].'">
		<input type="checkbox" id="uniextcheck" class="deshabilitar" onClick="cambiarexternas()" value="1"'; if($Accidente['unidadesexternas']==1) echo ' checked'; echo '></td></tr>';
		echo '<tr class="conregistro cinterno"><th align="left">Unidad</th><td><select name="unidad" id="unidad" class="deshabilitar textField"><option value="0">--- Seleccione Unidad ---</option>';
		if($_POST['reg']>0){
			$rsParque=mysql_db_query($base,"SELECT * FROM unidades WHERE 1 ORDER BY no_eco");
			while($Parque=mysql_fetch_array($rsParque)){
				echo '<option value="'.$Parque['cve'].'"';
				if($Accidente['unidad']==$Parque['cve']) echo ' selected';
				echo '>'.$Parque['no_eco'].'</option>';
			}
		}
		else if($_SESSION['PlazaUsuario']>0){
			$rsParque=mysql_db_query($base,"SELECT * FROM unidades WHERE 1 ORDER BY no_eco");
			while($Parque=mysql_fetch_array($rsParque)){
				echo '<option value="'.$Parque['cve'].'"';
				echo '>'.$Parque['no_eco'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr class="conregistro cinterno"><th align="left">Conductor</th><td><select name="conductor" id="conductor" class="deshabilitar textField"><option value="0">--- Seleccione Conductor ---</option>';
		if($_POST['reg']>0){
			$rsConductor=mysql_db_query($base,"SELECT * FROM operadores WHERE 1 ORDER BY nombre");
			while($Conductor=mysql_fetch_array($rsConductor)){
				echo '<option value="'.$Conductor['cve'].'"';
				if($Accidente['conductor']==$Conductor['cve']) echo ' selected';
				echo '>'.$Conductor['credencial'].' - '.$Conductor['nombre'].'</option>';
			}
		}
		else if($_SESSION['PlazaUsuario']>0){
			$rsConductor=mysql_db_query($base,"SELECT * FROM operadores WHERE 1 ORDER BY nombre");
			while($Conductor=mysql_fetch_array($rsConductor)){
				echo '<option value="'.$Conductor['cve'].'"';
				echo '>'.$Conductor['credencial'].' - '.$Conductor['nombre'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr class="sinregistro cinterno"><th align="left">Unidad sin registro</th><td><input type="text" name="unisinregistro" id="unisinregistro"  size="20" value="'.$Accidente['unisinregistro'].'" class="deshabilitar textField"></td></tr>';
		echo '<tr class="sinregistro cinterno"><th align="left">Conductor sin registro</th><td><select name="opesinregistro" id="opesinregistro" class="deshabilitar textField"><option value="0">--- Seleccione Conductor ---</option>';
			$rsConductor=mysql_db_query($base,"SELECT * FROM personal WHERE 1 ORDER BY nombre");
			while($Conductor=mysql_fetch_array($rsConductor)){
				echo '<option value="'.$Conductor['cve'].'"';
				if($Accidente['opesinregistro']==$Conductor['cve']) echo ' selected';
				echo '>'.$Conductor['folio'].' - '.$Conductor['nombre'].'</option>';
			}
		echo '</select></td></tr>';
		echo '<tr class="cexterno"><th align="left">Unidad externa</th><td><input type="text" name="unidadext" id="unidadext"  size="20" value="'.$Accidente['unidadext'].'" class="deshabilitar textField"></td></tr>';
		echo '<tr class="cexterno"><th align="left">Conductor externo</th><td><input type="text" name="condext" id="condext"  size="50" value="'.$Accidente['condext'].'" class="deshabilitar textField"></td></tr>';
		echo '<tr><th align="left">Costo</th><td><input type="text" name="costo" id="costo"  size="15" value="'.$Accidente['costo'].'"  onKeyUp="calculaPorcentaje();" ></td></tr>';
		echo '<tr><th align="left">Abono de Administracion Anterior</th><td><input type="text" name="abono_ant" id="abono_ant"  size="15" value="'.$Accidente['abono_ant'].'" class="textField"></td></tr>';
		echo '<tr><th align="left">Porcentaje Conductor</th><td><select name="porcentaje" id="porcentaje" class="textField" onChange="calculaPorcentaje();">';
		foreach($array_porcentaje as $v){
			echo '<option value="'.$v.'"';
			if($v==$Accidente['porcentaje']) echo ' selected';
			echo '>'.$v.' %</option>';
		}
		echo '</select>&nbsp;&nbsp;<input type="text" style="text-align:right" name="porcdinero" id="porcdinero"  size="15" value="'.round(($Accidente['costo']*$Accidente['porcentaje']/100),2).'" class="readOnly" readonly></td></tr>';
		echo '<tr id="idporcuni"><th align="left">Porcentaje Unidad</th><td><select name="porcentajeuni" id="porcentajeuni" class="textField" onChange="calculaPorcentajeUni();">';
		foreach($array_porcentaje as $v){
			echo '<option value="'.$v.'"';
			if($v==$Accidente['porcentajeuni']) echo ' selected';
			echo '>'.$v.' %</option>';
		}
		echo '</select>&nbsp;&nbsp;<input type="text" style="text-align:right" name="porcunidinero" id="porcunidinero"  size="15" value="'.round(($Accidente['costo']*$Accidente['porcentajeuni']/100),2).'" class="readOnly" readonly></td></tr>';
		if($_POST['reg']==0) $Accidente['diario']=20;
		echo '<tr><th align="left">Cobro Diario</th><td><input type="text" name="diario" id="diario"  size="15" value="'.$Accidente['diario'].'" class="textField"></td></tr>';
		echo '<tr><th  align="left">Estatus</th><td><select name="estatus" id="estatus" class="textField">';
		foreach($array_estatus_accidentes as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$Accidente['estatus']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Gestor</th><td><input type="text" name="gestor" id="gestor"  size="30" value="'.$Accidente['gestor'].'" class="textField"></td></tr>';
		echo '<tr><th align="left" valign="top">Lugar</th><td><textarea name="lugar" id="lugar" class="textField" cols="50" rows="5" >'.$Accidente['lugar'].' </textarea></td> </tr>';
		echo '<tr><th align="left" valign="top">Descripcion</th><td><textarea name="descripcion" id="descripcion" class="textField" cols="50" rows="5" >'.$Accidente['descripcion'].' </textarea></td> </tr>';
		echo '<tr><th align="left" valign="top">Conclusion</th><td><textarea name="conclusion" id="conclusion" class="textField" cols="50" rows="5" >'.$Accidente['conclusion'].' </textarea></td> </tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Conclusiones">';
		echo '</div>';
		echo '<div id="Salidas">';
		echo '</div>';
		echo '<script language="javascript">
				/*if('.$_POST['reg'].' > 0 && '.$_SESSION['CveUsuario'].' != 1){
					$(".deshabilitar").attr("disabled","disabled");
				}*/
				function cambiarexternas(){
					if($("#uniextcheck").is(":checked")){
						document.forma.unidadesexternas.value=1;
						$(".cexterno").show();
						$(".cinterno").hide();
					}
					else{
						document.forma.unidadesexternas.value=0;
						$(".cexterno").hide();
						if(document.forma.tipo.value==\'3\'){
							$(\'#idporcuni\').show();
						}
						else{
							$(\'#idporcuni\').hide();
							document.forma.porcentajeuni.options[0].selected=true;
						}
						if(document.forma.tipo.value==\'4\'){
							$(\'.sinregistro\').show();
							$(\'.conregistro\').hide();
							document.forma.unidad.options[0].selected=true;
							document.forma.conductor.options[0].selected=true;
							document.forma.porcentajeuni.options[0].selected=true;
							document.forma.porcentaje.options[0].selected=true;
							document.forma.porcentajeuni.disabled=true;
							document.forma.porcentaje.disabled=true;
						}
						else{
							$(\'.sinregistro\').hide();
							$(\'.conregistro\').show();
							document.forma.porcentajeuni.disabled=false;
							document.forma.porcentaje.disabled=false;
						}
						if(document.forma.tipo.value==\'5\'){
							$(\'.sinregistro\').show();
						}
					}
				}
			
				if('.intval($Accidente['tipo']).'!=3){
					$(\'#idporcuni\').hide();
				}
				
				if('.intval($Accidente['tipo']).'!=4 && '.intval($Accidente['tipo']).'!=5){
					$(\'.sinregistro\').hide();
				}
				
				if('.intval($Accidente['tipo']).'==4){
					$(\'.conregistro\').hide();
				}
					
				function historialSalidas()
					{
						document.getElementById("Salidas").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","accidentes.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						//	objeto.send("ajax=2&cve_unidad="+document.getElementById("cve_unidad").value+"&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.send("ajax=2&accidente='.$_POST['reg'].'&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4){
									document.getElementById("Salidas").innerHTML = objeto.responseText;
									document.forma.costo.value=document.forma.totcosto.value;
									calculaPorcentaje();
								}
							}
						}
						document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
					}
					
				function historialConclusiones()
					{
						document.getElementById("Conclusiones").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","accidentes.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=5&accidente='.$_POST['reg'].'&plaza="+document.getElementById("plaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4){
									document.getElementById("Conclusiones").innerHTML = objeto.responseText;
									historialSalidas();	
								}
							}
						}
						document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
					}
				
				function moverPagina(x) {
					document.getElementById("numeroPagina").value = x;
					historialSalidas();
				}
				
				function calculaPorcentaje(){
					valor=document.forma.costo.value*document.forma.porcentaje.value/100;
					document.forma.porcdinero.value=valor.toFixed(2);
				}
				
				function calculaPorcentajeUni(){
					valor=document.forma.costo.value*document.forma.porcentajeuni.value/100;
					document.forma.porcunidinero.value=valor.toFixed(2);
				}
				
				function traeUnidades(plazavalor){
				  if(plazavalor==0){
					document.forma.unidad.options.length=0;
					document.forma.unidad.options[0]= new Option("---Seleccione Unidad---","0");
					document.forma.conductor.options.length=0;
					document.forma.conductor.options[0]= new Option("---Seleccione Conductor---","0");
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=3&plaza="+plazavalor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.unidad.options.length=0;
								document.forma.unidad.options[0]= new Option("---Seleccione Unidad---","0");
								var opciones2=objeto2.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.unidad.options[i+1]= new Option(datos[1], datos[0]);
								}
								traeConductores(plazavalor);
							}
						}
					}
				  }
				}
				
				function traeConductores(plazavalor){
				  if(plazavalor==0){
					document.forma.conductor.options.length=0;
					document.forma.conductor.options[0]= new Option("---Seleccione Conductor---","0");
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=4&plaza="+plazavalor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.conductor.options.length=0;
								document.forma.conductor.options[0]= new Option("---Seleccione Conductor---","0");
								var opciones2=objeto2.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.conductor.options[i+1]= new Option(datos[1], datos[0]);
								}
							}
						}
					}
				  }
				}
				
				historialConclusiones();	
				
				
				
			  </script>';
		
			
		
	}
	
	

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
		<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'accidentes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'imp_accidentes.php\',\'_blank\',\'0\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
			 </tr>';
			 
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" value="'.fechaLocal().'" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" onChange="traeUnidades(this.value);" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td><td></td><td>&nbsp;</td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><td>Unidad</td><td><select name="unidad" id="unidad" class="textField"><option value="all">---Todas---</option>';
		if($_SESSION['PlazaUsuario']>0){
			$rsParque=mysql_db_query($base,"SELECT * FROM unidades WHERE 1 ORDER BY no_eco");
			while($Parque=mysql_fetch_array($rsParque)){
				echo '<option value="'.$Parque['cve'].'"';
				echo '>'.$Parque['no_eco'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Conductor</td><td><select name="conductor" id="conductor" class="textField"><option value="all">---Todos---</option>';
		if($_SESSION['PlazaUsuario']>0){
			$rsConductor=mysql_db_query($base,"SELECT * FROM operadores WHERE 1 ORDER BY nombre");
			while($Conductor=mysql_fetch_array($rsConductor)){
				echo '<option value="'.$Conductor['cve'].'"';
				echo '>'.$Conductor['credencial'].' - '.$Conductor['nombre'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_accidentes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Tipo</td><td><select name="tipo" id="tipo"><option value="all">---Todos---</option>';
		foreach($array_tipo_accidente as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		echo '<input type="hidden" name="cve_unidad" id="cve_unidad" value="">';
		echo '<script language="javascript">
			//Funcion para navegacion de Registros. 20 por pagina.
			function moverPagina(x) {
				document.getElementById("numeroPagina").value = x;
				buscarRegistros();
			} </script>';
	}
	
bottom();



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
			objeto.open("POST","accidentes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&fecha_inicio="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&estatus="+document.getElementById("estatus").value+"&unidad="+document.getElementById("unidad").value+"&conductor="+document.getElementById("conductor").value+"&plaza="+document.getElementById("searchplaza").value+"&tipo="+document.getElementById("tipo").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	}
	function traeUnidades(plazavalor){
				  if(plazavalor=="all"){
					document.forma.unidad.options.length=0;
					document.forma.unidad.options[0]= new Option("---Todas---","all");
					document.forma.conductor.options.length=0;
					document.forma.conductor.options[0]= new Option("---Todos---","all");
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=3&plaza="+plazavalor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.unidad.options.length=0;
								document.forma.unidad.options[0]= new Option("---Todas---","all");
								var opciones2=objeto2.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.unidad.options[i+1]= new Option(datos[1], datos[0]);
								}
								traeConductores(plazavalor);
							}
						}
					}
				  }
				}
				
				function traeConductores(plazavalor){
				  if(plazavalor=="all"){
					document.forma.conductor.options.length=0;
					document.forma.conductor.options[0]= new Option("---Todos---","all");
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=4&plaza="+plazavalor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.conductor.options.length=0;
								document.forma.conductor.options[0]= new Option("---Todos---","all");
								var opciones2=objeto2.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.conductor.options[i+1]= new Option(datos[1], datos[0]);
								}
							}
						}
					}
				  }
				}';
	}
	echo '
	
	</Script>
';

?>

