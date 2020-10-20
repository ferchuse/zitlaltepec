<?php
	include("main.php");
	
	
	$rsUsuario=mysql_query("SELECT * FROM usuarios");
	while($Usuario=mysql_fetch_array($rsUsuario)){
		$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	}
	
	$array_empresa=array();
	$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_empresa[$row['cve']]=$row['nombre'];
		$array_empresalogo[$row['cve']]=$row['logo'];
	}
	
	$res=mysql_query("SELECT * FROM operadores ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_cveconductor[$row['cve']]=$row['cve'];
		$array_nomconductor[$row['cve']]=$row['nombre'];
		$array_empconductor[$row['cve']]=$row['empresa'];
	}
	
	$rsUnidad=mysql_query("SELECT * FROM unidades");
	while($Unidad=mysql_fetch_array($rsUnidad)){
		$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
		$array_empunidad[$Unidad['cve']]=$Unidad['empresa'];
	}
	$array_derrotero=array();
	$res=mysql_query("SELECT * FROM derroteros ORDER BY nombre");
	while($row=mysql_fetch_array($res)){
		$array_derrotero[$row['cve']]=$row['nombre'];
		$array_montoderrotero[$row['cve']]=$row['monto'];
	}
	
	
	$array_estatusviaje=array("A"=>'<font color="RED">Por pagar</font>',"P"=>"Pagado","C"=>"Cancelado");
	$array_estatusviaje2=array("A"=>'Por pagar',"P"=>"Pagado","C"=>"Cancelado");
	
	
	
	if($_POST['ajax']==1){
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</td>';
		echo '<th>Folio</th><th>Empresa</th><th>Operador</th><th>Unidad</th><th>Fecha Captura</th><th>Fecha Viaje</th><th>Derrotero</th><th>Monto</th><th>Usuario</th><th>Estatus</th>
		</tr>'; 
		$x=0;
		$t=0;
		$filtro="";
		$filtroconductor="";
		$filtrounidad="";
		if($_POST['fecha_ini']!="") $filtro.=" AND a.fecha>='".$_POST['fecha_ini']."'";
		if($_POST['fecha_fin']!="") $filtro.=" AND a.fecha<='".$_POST['fecha_fin']."'";
		if($_POST['estatus']!='0') $filtro.=" AND a.estatus='".$_POST['estatus']."'";
		if($_POST['derrotero']!='all') $filtro.=" AND a.derrotero='".$_POST['derrotero']."'";
		if($_POST['empresa']!='all') $filtro.=" AND a.empresa='".$_POST['empresa']."'";
		if($_POST['credencial']!="") $filtroconductor=" AND b.cve='".$_POST['credencial']."'";
		if($_POST['no_eco']!="") $filtrounidad=" AND c.no_eco='".$_POST['no_eco']."'";
		if($_POST['usuario']!='') $filtro.=" AND a.usuario='".$_POST['usuario']."'";
		
		$res=mysql_query("SELECT a.*, TIMEDIFF(NOW(), fecha) as diferencia FROM tarjetas_unidad as a 
		inner join operadores as b on (b.cve=a.operador $filtroconductor) 
		inner join unidades as c on (c.cve=a.unidad $filtrounidad) 
		WHERE 1 $filtro ORDER BY a.cve DESC") or die(mysql_error());
		while($row=mysql_fetch_array($res)){
			rowb();
			$aux='';
			echo '<td align="center">';
			if($row['estatus']!='C'){
				if(substr($row['diferencia'],0,8)<='00:03:00' || $row['pagado_reimpresion']>0)
				echo '<a href="#" onClick="atcr(\'tarjetas_unidad.php\',\'\',10,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
				if($row['estatus']!='P' && nivelUsuario()>2){
					echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){ obs=prompt(\'Motivo:\'); atcr(\'tarjetas_unidad.php?obs=\'+obs,\'\',3,\''.$row['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar"></a>';
				}
			}
			else{
				echo '<a href="#" onClick="atcr(\'tarjetas_unidad.php\',\'\',11,\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
				$aux='<br>'.$array_usuario[$row['usucan']].'<br>'.$row['fechacan'];
			}
			echo '</td>';
			echo '<td align="center">'.$row['cve'].'</td>';
			echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
			echo '<td align="left">('.$array_cveconductor[$row['operador']].') '.utf8_encode($array_nomconductor[$row['operador']]).'</td>';
			echo '<td align="center">'.$array_unidad[$row['unidad']].'</td>';
			echo '<td align="center">'.$row['fecha'].'</td>';
			echo '<td align="center">'.$row['fecha_viaje'].'</td>';
			echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
			echo '<td align="right">'.number_format($row['monto'],2).'</td>';
			echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
			echo '<td align="left">'.$array_estatusviaje[$row['estatus']].$aux.'</td>';
			echo '</tr>';
			$x++;
		}
		echo '<tr bgcolor="#E9F2F8"><th colspan="11" align="left">'.$x.' Registro(s)</th></tr>';
		echo '</table>';
		exit();
	}
	
	if($_POST['ajax']==2){
		if($_POST['unidad']==0){
			$rsUni=mysql_query("SELECT cve,estatus,empresa,derrotero,IF(liberada=1,0,monto_cuenta) as monto_cuenta FROM unidades WHERE no_eco='".strtoupper($_POST['no_eco'])."' AND localidad='".$_POST['localidad']."'");
			if($Uni=mysql_fetch_array($rsUni)){
				$_POST['unidad']=$Uni['cve'].'|'.$Uni['estatus'].'|'.$Uni['empresa'].'|'.utf8_encode($array_empresa[$Uni['empresa']]).'|'.$Uni['derrotero'].'|'.utf8_encode($array_derrotero[$Uni['derrotero']]).'|'.$Uni['monto_cuenta'];
			}
			else{
				$_POST['unidad']=0;
			}
		}
		echo $_POST['unidad'];
		exit();
	}
	
	if($_POST['ajax']==3){
		
		/*$res=mysql_query("SELECT * FROM tarjetas_unidad WHERE unidad='".$_POST['unidad']."' AND fecha_viaje='".$_POST['fecha_viaje']."' AND estatus!='C'");
			if(mysql_num_rows($res)>0){
			echo "0";
			}
		else{*/
		$res=mysql_query("SELECT * FROM incidencias_unidad WHERE unidad='".$_POST['unidad']."' AND fecha_ini<='".$_POST['fecha_viaje']."' AND fecha_fin>='".$_POST['fecha_viaje']."' AND estatus!='C'");
		if(mysql_num_rows($res)>0){
			echo "-1";
		}
		else{
			$res = mysql_query("SELECT * FROM tarjetas_unidad WHERE unidad = '".$_POST['unidad']."' AND fecha_viaje <= '".$_POST['fecha_viaje']."' AND estatus = 'A'");
			if($row = mysql_fetch_array($res)){
				echo "-2";
			}
			else{
				$fecha=date( "Y-m-d" , strtotime ( "-1 day" , strtotime($_POST['fecha_viaje']) ) );
				$res1 = mysql_query("SELECT fecha_viaje FROM tarjetas_unidad WHERE unidad = '".$_POST['unidad']."' AND fecha_viaje < '".$_POST['fecha_viaje']."' AND estatus != 'C' ORDER BY fecha_viaje DESC LIMIT 1");
				if($row1=mysql_fetch_array($res1)){
					$res = mysql_query("SELECT * FROM tarjetas_unidad WHERE unidad = '".$_POST['unidad']."' AND fecha_viaje = '".$fecha."' AND estatus != 'C'");
					if(!$row = mysql_fetch_array($res)){
						echo "-3|".$fecha;
					}
				}
			}
		}
		//}
		exit();
	}
	
	if($_POST['ajax']==4){
		if($_POST['conductor']==0){
			$rsUni=mysql_query("SELECT cve,nombre,estatus FROM operadores WHERE cve='".strtoupper($_POST['credencial'])."'");
			if($Uni=mysql_fetch_array($rsUni)){
				$_POST['conductor']=$Uni['cve'].'|'.$Uni['nombre'].'|'.$Uni['estatus'];
			}
			else
			$_POST['conductor']=0;
		}
		echo $_POST['conductor'];
		exit();
	}
	
	
	
	
	if($_GET['viene_recaudacion'] == 1)
	top($_SESSION, 0, true);
	else
	top($_SESSION);
	
	if($_POST['cmd']==10){
		mysql_query("UPDATE tarjetas_unidad SET pagado_reimpresion=pagado_reimpresion-1 WHERE cve='".$_POST['reg']."' AND pagado_reimpresion>0");
		$res=mysql_query("SELECT * FROM tarjetas_unidad WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		$mensaje="&nbsp;";
		$texto ='|';
		$texto.=chr(27).'!'.chr(10)." TARJETA";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TTAQUILLERO: ".$array_usuario[$row['usuario']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."RDERROTERO: ".$array_derrotero[$row['derrotero']];
		$texto.=chr(27).'!'.chr(10).'||';
		$texto.="FECHA:    ".$row['fecha_viaje'];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."((".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='||';
		$texto.=chr(27).'!'.chr(10);
		$texto.="SSALIDA         DESTINO           FIRMA";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.='|';
		$texto.="___________________  ____________________";
		$texto.='|';
		$texto.="OPERADOR             DESPACHADOR";
		/*$texto.='-------------------- VIAJE 1 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
			$texto.='Sauces     ______    __________    _______________||';
			$texto.='-------------------- VIAJE 2 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
			$texto.='Sauces     ______    __________    _______________||';
			$texto.='-------------------- VIAJE 3 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
		$texto.='Sauces     ______    __________    _______________||';*/
		
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$row['cve']).'" width=200 height=200></iframe>';
		
		$_POST['cmd']=0;
	}
	
	if($_POST['cmd']==11){
		$res=mysql_query("SELECT * FROM unidades_tarjeta WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		$mensaje="&nbsp;";
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)." CANCELACION TARJETA";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$row['cve'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TAQUILLERO: ".$array_usuario[$row['usuario']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DERROTERO: ".$array_derrotero[$row['derrotero']];
		$texto.=chr(27).'!'.chr(10).'||';
		$texto.="FECHA:    ".$row['fecha_viaje'];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$row['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."OPERADOR: (".$array_cveconductor[$row['operador']].')'.$array_nomconductor[$row['operador']];
		$texto.='||';
		$texto.=chr(27).'!'.chr(10);
		$texto.="FECHA CANC:".substr($row['fechacan'],0,10);
		$texto.="|";
		$texto.="HORA CANC:".substr($row['fechacan'],11,8);
		$texto.="|";
		$texto.="USU CANC:".$array_usuario[$row['usucan']];
		$texto.='||';
		$texto.='|';
		$texto.="___________________ ";
		$texto.='|';
		$texto.="USUARIO CANCELO";
		
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$row['empresa']].'&textoimp='.$texto.'" width=200 height=200></iframe>';
		
		$_POST['cmd']=0;
	}
	
	if($_POST['cmd']==3){
		if(nivelUsuario() > 2){
			mysql_query("UPDATE tarjetas_unidad SET estatus='C',obscan='".$_GET['obs']."',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE cve='".$_POST['reg']."'");
			$_POST['cmd']=0;
		}
	}
	
	if($_POST['cmd']==2){
		mysql_query("INSERT tarjetas_unidad SET fecha_viaje='".$_POST['fecha_viaje']."',
		fecha='".fechaLocal()."',operador='".$_POST['operador']."',hora='".horaLocal()."',unidad='".$_POST['unidad']."',
		derrotero='".$_POST['derrotero']."',usuario='".$_POST['cveusuario']."',empresa='".$_POST['empresa']."',estatus='A',
		monto='".$_POST['monto']."'") or die(mysql_error());
		$_POST['reg']=mysql_insert_id();
		
		
		$mensaje="<b>Se genero el Folio de Tarjeta: ".$folio." de la unidad ".$array_unidad[$_POST['unidad']]."</b>";
		$fecha=$_POST['fecha_viaje'];
		$texto =chr(27)."@".'|';
		$texto.=chr(27).'!'.chr(10)."TARJETA";
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."FOLIO: ".$_POST['reg'];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."TTAQUILLERO: ".$array_usuario[$_POST['cveusuario']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."DDEERROTERO: ".$array_derrotero[$_POST['derrotero']];
		$texto.=chr(27).'!'.chr(10).'||';
		$texto.="FECHA:    ".$_POST['fecha_viaje'];
		$texto.='||';
		$texto.=chr(27).'!'.chr(40)."NUM ECO: ".$array_unidad[$_POST['unidad']];
		$texto.='|';
		$texto.=chr(27).'!'.chr(10)."((".$array_cveconductor[$_POST['operador']].')'.$array_nomconductor[$_POST['operador']];
		$texto.='||';
		$texto.=chr(27).'!'.chr(10);
		$texto.="SSALIDA         DESTINO           FIRMA";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.="______________  ________________  ______________";
		$texto.='||';
		$texto.='|';
		$texto.="___________________  ____________________";
		$texto.='|';
		$texto.="OPERADOR             DESPACHADOR";
		/*$texto.='-------------------- VIAJE 1 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
			$texto.='Sauces     ______    __________    _______________||';
			$texto.='-------------------- VIAJE 2 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
			$texto.='Sauces     ______    __________    _______________||';
			$texto.='-------------------- VIAJE 3 ---------------------|';
			$texto.='Lugar       Hora     Firma         Nombre Checador||';
			$texto.='S. IV      ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='Tecamac    ______    __________    _______________||';
			$texto.='S. Zum     ______    __________    _______________||';
		$texto.='Sauces     ______    __________    _______________||';*/
		
		$impresion='<iframe src="http://localhost/impresiongenerallogo.php?logo='.$array_empresalogo[$_POST['empresa']].'&textoimp='.$texto.'&barcode='.sprintf("%02s","45").sprintf("%04s","0555").sprintf("%06s",$_POST['reg']).'" width=200 height=200></iframe>';
		
		//}
		
		$_POST['cmd']=0;
	}
	
	if($_POST['cmd']==1){
		echo '<table>';
		echo '<tr>';
		$nivelUsuario=nivelUsuario();
		if($nivelUsuario>1 && $row['cverec']==0)
		echo '<td><a href="#" onClick="
		if(document.forma.fecha_viaje.value==\'\')
		alert(\'Necesita ingresar la fecha de viaje\');
		//else if(document.forma.fecha_viaje.value>\''.fechaLocal().'\')
		//	alert(\'No puede ingresar salidas de roles posteriores al dia de hoy\');
		else if(document.forma.unidad.value==\'\')
		alert(\'No ha cargado correctamente la unidad\');
		else if(document.forma.operador.value==\'\')
		alert(\'No ha cargado correctamente el operador\');
		else{
		checaTarjeta();
		}
		"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		if($_GET['viene_recaudacion']!=1)
		echo '<td><a href="#" onClick="atcr(\'tarjetas_unidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>';
		else{
			$row['operador'] = $_POST['operador'];
			$row['unidad'] = $_POST['unidad'];
			$rsUni=mysql_query("SELECT cve,estatus,empresa,derrotero,IF(liberada=1,0,monto_cuenta) as monto_cuenta FROM unidades WHERE no_eco='".strtoupper($_POST['unidad'])."'");
			$Uni = mysql_fetch_array($rsUni);
			$row['empresa'] = $Uni['empresa'];
			$row['derrotero'] = $Uni['derrotero'];
			$row['monto'] = $Uni['monto_cuenta'];
		}
		echo '</tr>';
		echo '</table>';
		echo '<br>';
		if($_POST['reg']==0){
			//$row['fecha_viaje']=fechaLocal();
			$row['fecha_viaje']=date( "Y-m-d" , strtotime ( "+1 day" , strtotime(fechaLocal()) ) );
		}
		elseif($_POST['fecha_viaje']!=""){
			$row['fecha_viaje']=$_POST['fecha_viaje'];
		}
		echo '<input type="hidden" name="viene_recaudacion" value="'.$_GET['viene_recaudacion'].'">';
		echo '<table>';
		echo '<tr><td class="tableEnc">Generar Tarjeta</td></tr>';
		echo '</table>';
		echo '<table>';
		//echo '<tr><th align="left">Fecha Viaje</th><td><input type="text" class="readOnly" name="fecha_viaje" id="fecha_viaje" value="'.trim($row['fecha_viaje']).'" size="15" readOnly>';
		echo '<tr><th align="left">Fecha Viaje</th><td><input type="text" class="readOnly" name="fecha_viaje" id="fecha_viaje" value="'.trim($row['fecha_viaje']).'" size="15" readOnly>';
		if($nivelUsuario>2) echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_viaje,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		//echo '<input type="hidden" name="operador" id="operador" size="10" value="'.$row['operador'].'" class="readOnly" readOnly>';
		//echo '<tr><th align="left">Operador</th><td><input type="text" name="credencial" id="credencial" size="10" value="'.$array_cveconductor[$row['operador']].'" onKeyUp="if(event.keyCode==13){ traeCond();}" class="textField">&nbsp;<input type="text" class="readOnly" name="nomcond" id="nomcond" size="50" value="'.$array_nomconductor[$row['operador']].'" readOnly></td></tr>';
		echo '<tr><th align="left">Operador</th><td><select name="operador" id="operador" class="textField"><option value="">Seleccione</option>';
		foreach($array_nomconductor as $k=>$v){
			echo '<option value="'.$k.'"';
			if($row['operador']==$k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Localidad</th><td><select name="localidad" id="localidad" onChange="
		document.forma.unidad.value=\'\';
		document.forma.no_eco.value=\'\';
		document.forma.empresa.value=\'\';
		document.forma.nomempresa.value=\'\';
		document.forma.derrotero.value=\'\';
		document.forma.nomderrotero.value=\'\';">';
		foreach($array_localidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['localidad']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<input type="hidden" name="unidad" id="unidad" size="10" value="'.$row['unidad'].'" class="readOnly" readOnly>';
		echo '<tr><th align="left">Unidad</th><td><input type="text" name="no_eco" id="no_eco" size="10" value="'.$array_unidad[$row['unidad']].'" class="textField" onKeyUp="if(event.keyCode==13){ traeUni();}"></td></tr>';
		echo '<input type="hidden" name="empresa" id="empresa" size="10" value="'.$row['empresa'].'" class="readOnly" readOnly>';
		echo '<tr><th align="left">Empresa</th><td><input type="text" name="nomempresa" id="nomempresa" size="50" value="'.$array_empresa[$row['empresa']].'" class="readOnly" readOnly></td></tr>';
		echo '<input type="hidden" name="derrotero" id="derrotero" size="10" value="'.$row['derrotero'].'" class="readOnly" readOnly>';
		echo '<tr><th align="left">Derrotero</th><td><input type="text" name="nomderrotero" id="nomderrotero" size="50" value="'.$array_derrotero[$row['derrotero']].'" class="readOnly" readOnly></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" size="10" value="'.$row['monto'].'" class="readOnly" readOnly></td></tr>';
		echo '</table>';
		
		echo '<script>
		function traeUni(){
		objeto1=crearObjeto();
		if (objeto1.readyState != 0) {
		alert("Error: El Navegador no soporta AJAX");
		} else {
		objeto1.open("POST","tarjetas_unidad.php",true);
		objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto1.send("ajax=2&localidad="+document.forma.localidad.value+"&empresa="+document.forma.empresa.value+"&no_eco="+document.forma.no_eco.value);
		objeto1.onreadystatechange = function(){
		if (objeto1.readyState==4){
		//alert(objeto1.responseText);
		var opciones2=objeto1.responseText;
		if(opciones2=="0"){
		alert("La unidad no existe");
		document.forma.no_eco.value="";
		document.forma.unidad.value="";
		document.forma.empresa.value="";
		document.forma.nomempresa.value="";
		document.forma.derrotero.value="";
		document.forma.nomderrotero.value="";
		document.forma.monto.value="";
		document.forma.no_eco.focus();
		}
		else{
		var opciones3=objeto1.responseText.split("|");
		if(opciones3[1]=="1"){
		document.forma.unidad.value=opciones3[0];
		document.forma.empresa.value=opciones3[2];
		document.forma.nomempresa.value=opciones3[3];
		document.forma.derrotero.value=opciones3[4];
		document.forma.nomderrotero.value=opciones3[5];
		document.forma.monto.value=opciones3[6];
		}
		else if(opciones3[1]=="2"){
		alert("La unidad esta dada de baja");
		document.forma.no_eco.value="";
		document.forma.unidad.value="";
		document.forma.empresa.value="";
		document.forma.nomempresa.value="";
		document.forma.derrotero.value="";
		document.forma.nomderrotero.value="";
		document.forma.monto.value="";
		document.forma.no_eco.focus();
		}
		else{
		alert("La unidad esta inactiva");
		document.forma.no_eco.value="";
		document.forma.unidad.value="";
		document.forma.derrotero.value="";
		document.forma.nomderrotero.value="";
		document.forma.monto.value="";
		document.forma.no_eco.focus();
		}
		}
		}
		}
		}
		}
		
		function traeCond(){
		objeto1=crearObjeto();
		if (objeto1.readyState != 0) {
		alert("Error: El Navegador no soporta AJAX");
		} else {
		objeto1.open("POST","tarjetas_unidad.php",true);
		objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto1.send("ajax=4&empresa="+document.forma.empresa.value+"&credencial="+document.forma.credencial.value);
		objeto1.onreadystatechange = function(){
		if (objeto1.readyState==4){
		//alert(objeto1.responseText);
		var opciones2=objeto1.responseText.split("|");
		if(opciones2[0]=="0"){
		alert("El operador no existe");
		document.forma.credencial.value="";
		document.forma.nomcond.value="";
		document.forma.operador.value="";
		document.forma.credencial.focus();
		}
		else{
		if(opciones2[2]=="1"){
		document.forma.operador.value=opciones2[0];
		document.forma.nomcond.value=opciones2[1];
		document.forma.credencial.focus();
		}
		else if(opciones2[2]=="2"){
		alert("El operador esta dado de baja");
		document.forma.credencial.value="";
		document.forma.nomcond.value="";
		document.forma.operador.value="";
		document.forma.credencial.focus();
		}
		else{
		alert("El operador esta inactivo");
		document.forma.credencial.value="";
		document.forma.nomcond.value="";
		document.forma.operador.value="";
		document.forma.credencial.focus();
		}
		}
		}
		}
		}
		}
		
		function checaTarjeta(){
		objeto1=crearObjeto();
		if (objeto1.readyState != 0) {
		alert("Error: El Navegador no soporta AJAX");
		} else {
		objeto1.open("POST","tarjetas_unidad.php",true);
		objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		objeto1.send("ajax=3&fecha_viaje="+document.forma.fecha_viaje.value+"&unidad="+document.forma.unidad.value+"&operador="+document.forma.operador.value);
		objeto1.onreadystatechange = function(){
		if (objeto1.readyState==4){
		var opciones2=objeto1.responseText.split("|");
		if(opciones2[0]=="0"){
		alert("La unidad ya se le registro la tarjeta");
		}
		else if(opciones2[0]=="-1"){
		alert("La unidad tiene incidencia en la fecha");
		}
		else if(opciones2[0]=="-2" && "'.intval($nivelUsuario).'"!="3"){
		alert("La unidad tiene tarjetas sin recaudar");
		}
		else if(opciones2[0]=="-3" && "'.intval($nivelUsuario).'"!="3"){
		alert("No se le ha generado la tarjeta del dia "+opciones2[1]);
		}
		else{
		atcr(\'tarjetas_unidad.php\',\'\',\'2\',\''.$_POST['reg'].'\');
		}
		}
		}
		}
		}
		</script>';
	}
	
	if($_POST['cmd']==0){
		if($mensaje!=""){
			echo $mensaje;
			echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		}
		if($_POST['viene_recaudacion']==1){
			echo '<script>setTimeout("window.close()",4000);</script>';
			exit();
		}
		//Busqueda
		echo '<table>';
		echo '<tr>
		<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
		<td><a href="#" onClick="atcr(\'tarjetas_unidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		<!--<td><a href="#" onClick="atcr(\'tarjetas_unidad.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>-->
		</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="textField" size="12" value="">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Clave Operador</td><td><input type="text" name="credencial" id="credencial" class="textField" size="12" value=""></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField" size="5" value=""></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="0">--- Todos ---</option><option value="A" selected>Por pagar</option>
		<option value="P">Pagado</option><option value="S">Suspendido</option><option value="C">Cancelado</option></select></td></tr>';
		echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="all">--- Todos ---</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="all">--- Todos ---</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
		foreach($array_usuario as $k=>$v){
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
	objeto.open("POST","tarjetas_unidad.php",true);
	objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&credencial="+document.getElementById("credencial").value+"&derrotero="+document.getElementById("derrotero").value+"&estatus="+document.getElementById("estatus").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&usuario="+document.getElementById("usuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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


<script >
	$('#operador').select2();
	
</script>

