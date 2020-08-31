<?php
include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$rsMotivos=mysql_query("SELECT * FROM cat_cargos_unidadeszitla WHERE tipo='V'");
while($Motivo=mysql_fetch_array($rsMotivos)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsconductor=mysql_query("SELECT * FROM unidades");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_parque[$Conductor['cve']]=$Conductor['no_eco'];
}

$sta_var=array('A'=>"ACTIVO",'D'=>"DEVUELTO",'C'=>"CANCELADO",'P'=>"PARADO");	

if($_POST['cmd']==5){
	mysql_query("UPDATE cargos_variables_unidadeszitla SET fecha_fin='$fecha_fin',sta='A',obs=CONCAT(obs,' ,','Reanudado ".fechaLocal()." por motivo ".$_GET['obs']."') WHERE cve='".$_POST['reg']."'");
	header("Location: cargos_variables_uni.php");
}
if($_POST['cmd']==4){
	mysql_query("UPDATE cargos_variables_unidadeszitla SET sta='P',obs=CONCAT(obs,' ,','Detenido ".fechaLocal()." por motivo ".$_GET['obs']."') WHERE cve='".$_POST['reg']."'");
	header("Location: cargos_variables_uni.php");
}
if($_POST['cmd']==3){

	for($i=0;$i<count($_POST['sel']);$i++){
//			echo'alert("'.$_POST['sel'][$i].'")';
		mysql_query("UPDATE cargos_variables_unidadeszitla SET sta='C',obs=CONCAT(obs,', ','Cancelado ".fechaLocal()." por motivo ".$_GET['obs']."') WHERE cve='".$_POST['sel'][$i]."'");
	}
//	mysql_query("UPDATE cargos_variables_unidadeszitla SET sta='C',obs=CONCAT(obs,', ','Cancelado ".fechaLocal()." por motivo ".$_GET['obs']."') WHERE cve='".$_POST['reg']."'");

	header("Location: cargos_variables_uni.php");
}

if($_POST['cmd']==2){
	$rsVariable=mysql_query("SELECT IFNULL(MAX(folio)+1,1) FROM cargos_variables_unidadeszitla WHERE plaza='".$_POST['plaza']."'");
	$Variable=mysql_fetch_array($rsVariable);
	$folio=$Variable[0];
	for ($i=0;$i<count($_POST['sel']);$i++){
		$insert="INSERT INTO cargos_variables_unidadeszitla (plaza,folio,fecha,fecha_ini,total,motivo,usuario,concepto,unidad,deposito_inicial) 
		values ('".$_POST['plaza']."','$folio','".fechaLocal()."','".$_POST['fecha_ini']."','".$_POST['montototal']."',
				'".$_POST['motivo']."','".$_SESSION['CveUsuario']."','".$_POST['concepto']."','".$_POST['sel'][$i]."','".$_POST['deposito_inicial']."')";
		mysql_query($insert);
		$variable=mysql_insert_id();
		$folio++;
	}
	header("Location: cargos_variables_uni.php");
}

if($_POST['ajax']==1){
	$filtro="";
	if(trim($_POST['nom'])!="") $filtro=" AND b.no_eco='".($_POST['nom'])."'";
	$select= " SELECT a.* FROM cargos_variables_unidadeszitla as a 
			INNER JOIN unidades as b ON (b.cve=a.unidad $filtro)
			WHERE a.fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' ";
//	if ($_POST['plaza']!="all") { $select.=" AND a.plaza='".$_POST['plaza']."'"; }
	if ($_POST['motivo']!="all") { $select.=" AND a.motivo='".$_POST['motivo']."'"; }
	if ($_POST['estatus']!="all") { $select.=" AND a.sta='".$_POST['estatus']."'"; }
	$select.=" ORDER BY a.folio DESC";
	//echo $select;
	$rsCargos=mysql_query($select);
	if(mysql_num_rows($rsCargos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		if($_SESSION['PlazaUsuario']==0) $col=7;
		else $col=6;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsCargos).' Registro(s)</td>
			<td align="right" bgcolor="#E9F2F8"><span id="dep1">'.number_format($deposito_inicial,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="tot1">'.number_format($total,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="abo1">'.number_format($abono,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="sal1">'.number_format($saldototal,2).'</span></td></tr>';
		echo '<tr bgcolor="#E9F2F8">';
		if($_POST['plaza']=="all"){ 
			echo '<th>&nbsp;</th>';
		}
		else{
			echo '<th><input type="checkbox" name="marcatodos" id="marcartodos" onclick="marcar();" value="">&nbsp;Marcar&nbsp;</th>';
		}
		if($_SESSION['PlazaUsuario']==0) 
			echo '<th>Plaza</th>';
		
		echo '<th>Folio</th><th>Motivo</th><th>Fecha</th><th>Fecha Inicial</th><th>Unidad</th><th>Deposito Inicial</th><th>Total</th><th>Abono</th><th>Saldo Total</th>';
		echo '</tr>';
		$i=0;
		$x=0;
		$deposito_inicial=0;
		$total=0;
		$saldototal=0;
		$abono=0;
		while($Cargos=mysql_fetch_array($rsCargos)) {
			rowb();
			if($_POST['plaza']=="all"){
				echo '<td>&nbsp;</td>';
			}
			else{
				echo '<td align="center"><input type="checkbox" name="sel[]" id="sel2'.$x.'" value="'.$Cargos['cve'].'"></td>';
				$x++;
			}
			$estatus="";
			if($Cargos['sta']=="D") $estatus="&nbsp;(Devuelto)";
			elseif($Cargos['sta']=="C") $estatus="&nbsp;(Cancelado)";
			if($_SESSION['PlazaUsuario']==0)
				echo '<td>'.htmlentities($array_plaza[$Cargos['plaza']]).'</td>';
			echo '<td align="center"><a href="#" onClick="document.forma.plaza.value=\''.$Cargos['plaza'].'\';atcr(\'cargos_variables_uni.php\',\'\',10,\''.$Cargos['cve'].'\')">'.$Cargos['folio'].$estatus.'</a></td>';
			echo '<td align="left">'.$array_motivo[$Cargos['motivo']].'</td>';
			echo '<td align="center">'.$Cargos['fecha'].'</td>';
			echo '<td align="center">'.$Cargos['fecha_ini'].'</td>';
			echo '<td align="left">'.$array_parque[$Cargos['unidad']].'</td>';
			if($Cargos['sta']=="C"){
				$Cargos['deposito_inicial']=0;
				$Cargos['total']=0;
			}
			error_reporting(0);
			echo '<td align="right">'.number_format($Cargos['deposito_inicial'],2).'</td>';
			$rsCargo=mysql_query("SELECT sum(monto) as abonos 
			FROM recaudacionmovunizitla 
			WHERE fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."' and motivo='".$Cargos['motivo']."' AND variable='".$Cargos['cve']."' GROUP BY unidad");
			$Cargo=mysql_fetch_array($rsCargo);
			echo '<td align="right">'.number_format(($Cargos['total']),2).'</td>';
			echo '<td align="right">'.number_format(($Cargo['abonos']),2).'</td>';
			echo '<td align="right">'.number_format(($Cargos['total']-$Cargo['abonos']),2).'</td>';
			echo '</tr>';
			$i++;
			$deposito_inicial+=$Cargos['deposito_inicial'];
			$total+=$Cargos['total'];
			$abono+=$Cargo['abonos'];
			$saldototal+=($Cargos['total']-$Cargo['abonos']);
		}
		echo '	
			<tr>
			<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
			<td align="right" bgcolor="#E9F2F8"><span id="dep2">'.number_format($deposito_inicial,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="tot2">'.number_format($total,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="abo2">'.number_format($abono,2).'</span></td>
			<td align="right" bgcolor="#E9F2F8"><span id="sal2">'.number_format($saldototal,2).'</span></td>
			</tr>
		</table>';
		echo '<input type="hidden" name="numsels" id="numsels" value="'.$x.'">';
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

if($_POST['ajax']==2){
	$rsConductor=mysql_query("SELECT * FROM cat_cargos_unidadeszitla WHERE plaza='".$_POST['plaza']."' AND tipo='V' ORDER BY nombre");
	while($Conductor=mysql_fetch_array($rsConductor)){
		echo $Conductor['cve'].','.$Conductor['nombre'].'|';
	}
	exit();
}

if($_POST['ajax']==3){
		$select= " SELECT * FROM unidades WHERE 1 ";
		if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
		if ($_POST['no_eco']!="") { $select.=" AND no_eco='".$_POST['no_eco']."'"; }
	//	if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
		if ($_POST['fecha_ini']!="") { $select.=" AND fecha_sta>='".$_POST['fecha_ini']."'"; }
		if ($_POST['fecha_fin']!="") { $select.=" AND fecha_sta<='".$_POST['fecha_fin']."'"; }
		if ($_POST['fecha_fin']!="") { $select.=" AND fecha_sta<='".$_POST['fecha_fin']."'"; }
		if ($_POST['localidad']!="all") { $select.=" AND localidad='".$_POST['localidad']."'"; }
		$select.=" ORDER BY no_eco";
		$rsconductores=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsconductores);
		
		if(mysql_num_rows($rsconductores)>0) 
		{
			echo '<table border="0" cellpadding="4" cellspacing="1" class="">';
			if($_SESSION['PlazaUsuario']==0) $col=5;
			else $col=4;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rsconductores).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			if($_POST['plaza']=="all"){ 
				echo '<th>&nbsp;</th>';
			}
			else{
				echo '<th><input type="checkbox" name="marcatodos" id="marcartodos" onclick="marcar();" value="">&nbsp;Marcar&nbsp;</th>';
			}
			if($_SESSION['PlazaUsuario']==0) 
				echo '<th>Plaza</th>';
			echo '<th>Unidad</th><th>Estatus</th><th>Localidad</th>';
			echo '</tr>';
			$i=0;
			$x=0;
			$cargos=0;
			$abonos=0;
			$saldo_favor=0;
			while($Conductor=mysql_fetch_array($rsconductores)) {
				rowb();
			
				if($_POST['plaza']=="all"){
					echo '<td>&nbsp;</td>';
				}
				else if($Conductor['estatus']==1){
					echo '<td align="center"><input type="checkbox" name="sel[]" id="sel2'.$x.'" value="'.$Conductor['cve'].'"></td>';
					$x++;
				}
				else{
					echo '<td>&nbsp;</td>';
				}
				if($_SESSION['PlazaUsuario']==0)
					echo '<td>'.htmlentities($array_plaza[$Conductor['plaza']]).'</td>';
				echo '<td align="left">'.$Conductor['no_eco'].'</td>';
				echo '<td align="center">'.$array_estatus_unidad[$Conductor['estatus']].'</td>';
				echo '<td align="center">'.$array_localidad[$Conductor['localidad']].'</td>';
			}
			if($_SESSION['PlazaUsuario']==0) $col=5;
			else $col=4;
			echo '	
				<tr>
				<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
				</tr>
			</table>';
			echo '<input type="hidden" name="numsels" id="numsels" value="'.$x.'">';
		}
		else {
			echo '
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
				</tr>	  
				</table>';
		}
		exit();	
}

top($_SESSION);

if($_POST['cmd']==10){
	$rsCargos=mysql_query("SELECT * FROM cargos_variables_unidadeszitla WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['reg']."'");
	$Cargos=mysql_fetch_array($rsCargos);
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
//	if($_SESSION[$archivo[(count($archivo)-1)]]>1)
	if(nivelUsuario()>1){		
		echo '<td><a href="#" onclick="
				if(document.forma.sta.value!=\'A\')
					alert(\'El Cargo ya no esta Activo\');
				else if(confirm(\'¿Esta seguro de cancelar el cargo?\')){
					resp=prompt(\'Observacion:\');
					atcr(\'cargos_variables_uni.php?obs=\'+resp,\'\',3,\''.$_POST['reg'].'\');
				}
				"><img src="images/validono.gif" border="0">&nbsp;&nbsp;Cancelar</a></td>';
		if($Cargos['sta']=='A' && $Cargos['fecha_fin']>fechaLocal()){
			echo '<td><a href="#" onclick="
				if(confirm(\'¿Esta seguro de detener el cargo?\')){
					resp=prompt(\'Observacion:\');
					atcr(\'cargos_variables_uni.php?obs=\'+resp,\'\',4,\''.$_POST['reg'].'\');
				}
			"><img src="images/validono.gif" border="0">&nbsp;&nbsp;Detener Cargos</a></td>';
		}
		if($Cargos['sta']=='P'){
			echo '<td><a href="#" onclick="
				if(confirm(\'¿Esta seguro de reanudar el cargo?\')){
					resp=prompt(\'Observacion:\');
					atcr(\'cargos_variables_uni.php?obs=\'+resp,\'\',5,\''.$_POST['reg'].'\');
				}
			"><img src="images/validono.gif" border="0">&nbsp;&nbsp;Reanudar Cargos</a></td>';
		}
	}
	echo '
		  </tr>';
	echo '</table>';

	
	if(mysql_num_rows($rsCargos)>1){
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo Global por Convenio Folio# '.$_POST['reg'].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th colspan=2>Aplicar Cargo a Unidades</th></tr>';
		while($Cargos=mysql_fetch_array($rsCargos)){
			rowb();
			echo '<td align=center>'.$array_parque[$Cargos['unidad']].'</td>';
			echo '</tr>';
			$fecha=$Cargos['fecha'];
			$fecha_ini=$Cargos['fecha_ini'];
			$montototal=$Cargos['total'];
			$dias=$Cargos['dias'];
			$diario=$Cargos['diario'];
			$fecha_fin=$Cargos['fecha_fin'];
			$motivo=$Cargos['motivo'];
			$concepto=$Cargos['concepto'];
			$i++;
		}
		echo '<tr><th>'.$i.' Registros</th></tr>';
		echo '</table><br>';
		echo '<table>';
		echo '<tr><th align="left">Monto Total de la Deuda:</th><td>'.number_format($i*$montototal,2).'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$fecha.'</td></tr>';
		echo '<tr><th align="left">Fecha Inicial</th><td>'.$fecha_ini.'</td></tr>';
		echo '<tr><th align="left">Monto Total: $</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Dias: </th><td>'.$dias.'</td></tr>';
		echo '<tr><th align="left">Cargo Diario: $</th><td>'.number_format($diario,2).'</td></tr>';
		//echo '<tr><th align="left">Fecha Final: </th><td>'.$fecha_fin.'</td></tr>';
		echo '<tr><th align="left">Motivo:</th><td>'.$array_motivo[$motivo].'</td></tr>';
		echo '<tr><th align="left">Concepto:</th><td>'.$concepto.'<br></td></tr>';
		echo '</table>';
	}
	else{
		
		$fecha=$Cargos['fecha'];
		$fecha_ini=$Cargos['fecha_ini'];
		$montototal=$Cargos['total'];
		$dias=$Cargos['dias'];
		$diario=$Cargos['diario'];
		$fecha_fin=$Cargos['fecha_fin'];
		$motivo=$Cargos['motivo'];
		$concepto=$Cargos['concepto'];
		$deposito_inicial=$Cargos['deposito_inicial'];
		$obs=$Cargos['obs'];
		$sta=$Cargos['sta'];
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo por Convenio Folio# '.$Cargos['folio'].' de la Unidad '.$array_conductor[$Cargos['conductor']].'</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th align="left">Deposito Inicial $</th><td>'.number_format($deposito_inicial,2).'</td></tr>';
		echo '<tr><th align="left">Fecha</th><td>'.$fecha.'</td></tr>';
		echo '<tr><th align="left">Fecha Inicial</th><td>'.$fecha_ini.'</td></tr>';
		echo '<tr><th align="left">Monto Total de la Diferencia de la Deuda:</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Monto Total: $</th><td>'.number_format($montototal,2).'</td></tr>';
		echo '<tr><th align="left">Cargo Diario: $</th><td>'.number_format($diario,2).'</td></tr>';
		echo '<tr><th align="left">Dias: </th><td>'.$dias.'</td></tr>';
		//echo '<tr><th align="left">Fecha Final: </th><td>'.$fecha_fin.'</td></tr>';
		echo '<tr><th align="left">Motivo:</th><td>'.$array_motivo[$motivo].'</td></tr>';
		echo '<tr><th align="left">Concepto:</th><td>'.$concepto.'<br></td></tr>';
		echo '<tr><th align="left">Observaciones:</th><td>'.$obs.'<br></td></tr>';
		echo '</table><input type="hidden" name="sta" value="'.$sta.'">';
	}
}

if($_POST['cmd']==1){
	if($_POST['reg']==0){
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar&nbsp;&nbsp;</a></td>
				<td><a href="#" onclick="validar_seleccion();"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Cargo Administrativo</a></td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($array_estatus_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td><td></td><td>&nbsp;</td></tr>';
		echo '<tr><td>Localidad</td><td><select name="localidad" id="localidad">
		<option value="all">Todos</option>';
		foreach($array_localidad as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '
		</td></tr>'; 
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" class="textField"></td></tr>'; 
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Conductores">';
		echo '</div>';
		
		echo '
		<Script language="javascript">

			function buscarRegistros()
			{
				document.getElementById("Conductores").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
				objeto=crearObjeto();
				if (objeto.readyState != 0) {
					alert("Error: El Navegador no soporta AJAX");
				} else {
					objeto.open("POST","cargos_variables_uni.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=3&localidad="+document.getElementById("localidad").value+"&estatus="+document.getElementById("estatus").value+"&no_eco="+document.getElementById("no_eco").value+"&plaza="+document.getElementById("searchplaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
					objeto.onreadystatechange = function()
					{
						if (objeto.readyState==4)
						{document.getElementById("Conductores").innerHTML = objeto.responseText;}
					}
				}
				document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
			}
			
			function marcar()
			{
				if(document.forma.marcatodos.checked)
			 	{
					for (i=0;i<(document.forma.numsels.value/1);i++) 
						document.getElementById(\'sel2\'+i).checked =true;
					document.forma.marcatodos.checked=true;		
					document.forma.marcatodos.value=1;	
				}		
				
				if(document.forma.marcatodos.checked==false)
			 	{
					for (i=0;i<(document.forma.numsels.value/1);i++) 
						document.getElementById(\'sel2\'+i).checked =false;
					document.forma.marcatodos.checked=false;		
					document.forma.marcatodos.value=0;	
				}		
			}
			
			function validar_seleccion()
			{
				sels=0;
				for (i=0;i<(document.forma.numsels.value/1);i++){
					if(document.getElementById(\'sel2\'+i).checked==true)
						sels++;
				}
				if(sels==0)
					alert("Necesita seleccionar un conductor");
				else
					atcr("cargos_variables_uni.php","",1,"1");
			}
		
			window.onload = function () {
				buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
			}
	
		</Script>
		';

	}
	if($_POST['reg']>0){
		echo '<table><tr>';
//		if($_SESSION[$archivo[(count($archivo)-1)]]>1)
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="
			if(document.forma.fecha_ini.value==\'\')
				alert(\'Necesita seleccionar una fecha inicial\');
			else if(document.forma.fecha_ini.value<\''.fechaLocal().'\')
				alert(\'La fecha inicial no puede ser menor al dia de hoy\');
			else if((document.forma.montototal.value/1)<1 && (document.forma.deposito_inicial.value/1)<1)
				alert(\'Necesita ingresar el monto total no puede ser menor a 1\');
			else if(document.forma.motivo.value==\'\')
				alert(\'Necesita seleccionar el motivo\');
			else
				atcr(\'cargos_variables_uni.php\',\'\',2,\'0\');
				this.disabled=true;
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',1,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td class="tableEnc">Cargo Administrativo</td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><th colspan=2>Aplicar Cargo a Unidades</th></tr>';
		for ($i=0;$i<count($_POST['sel']);$i++){
			rowb();
			echo '<td align=left>'.$array_parque[$_POST['sel'][$i]].'</td>';
			echo '<input type="hidden" name="sel[]" id="sel2'.$x.'" value="'.$_POST['sel'][$i].'">';
			echo '</tr>';
		}
		$rsConductor=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['sel'][0]."'");
		$Conductor=mysql_fetch_array($rsConductor);
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$Conductor['plaza'].'">';
		echo '<input type="hidden" name="numconductores" id="numconductores" value="'.count($_POST['sel']).'">';
		$class="textField";
		$tipo="";
		if(count($_POST['sel'])>1){
			$class="readOnly";
			$tipo="readonly";
		}
		echo '<tr><th>'.$i.' Registros</th></tr>';
		echo '</table><br>';
		echo '<table>';
		echo '<tr><th align="left">Fecha</th><td>'.fechaLocal().'</td></tr>';
		echo '<tr><th align="left">Monto Total del Cargo:</th><td><input type="text" class="textField" name="montototaldeuda" id="montototaldeuda" value="" onblur="calcula();"></td></tr>';
		echo '<tr><th align="left">Deposito Inicial:</th><td><input type="text" class="'.$class.'" name="deposito_inicial" id="deposito_inicial" value="" onblur="calcula();" '.$tipo.'> <small>Solo se habilita cuando se genera el cargo a solo una Unidad</th></td></tr>';
		echo '<tr><th align="left">Diferencia $</th><td><input  type="text" class="readOnly" name="montototal" id="montototal" value="" readonly></td></tr>';
		echo '<input  type="hidden" class="textField" id="diario" name="diario" value="">';
//		echo '<tr><th align="left">Cargo Diario $</th><td><input  type="hidden" class="textField" id="diario" name="diario" value=""></td></tr>';
		echo '<tr><th align="left">Fecha Inicial</th><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" value="'.fechaLocal().'" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo" class="textField"><option value="">--Seleccione--</option>';
		$result=mysql_query("SELECT * FROM cat_cargos_unidadeszitla WHERE plaza='".$_SESSION['PlazaUsuario']."' AND tipo='V' ORDER BY nombre");
		while($rowx=mysql_fetch_array($result)){
			echo '<option value="'.$rowx['cve'].'">'.$rowx['nombre'].'</option>';
		}	
		echo '</select></td></tr>';
		echo '<tr><th align="left">Concepto:</th><td><textarea cols=60 rows=4 name="concepto" class="textField"></textarea><br></td></tr>';
		echo '</table>';
		echo '<script>
				function calcula(){
					var t4=0;
					t4=(document.forma.montototaldeuda.value/1)-(document.forma.deposito_inicial.value/1);
					document.forma.montototal.value=t4;
				}
				
			 </script>';
	}

}


if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a></td><td>&nbsp;</td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',1,\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;&nbsp;Nuevo Cargo Administrativo</a></td>
				<td><a href="#" onclick="atcr(\'cargos_variables_uni.php\',\'\',3,\'0\');"><img src="images/validono.gif" border="0">&nbsp;&nbsp;Cancelar</a></td>
				<!--<td><a href="#" onclick="atcr(\'imp_cargos_variables_uni.php\',\'_blank\',\'0\',\'\');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a>&nbsp;&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField" onChange="traeMotivos(this.value);"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
        echo '<tr><td>Fecha Inicial </td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
        echo '<tr><td>Fecha Final </td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.fechaLocal().'" class="textField" size="12">&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" name="nombre" id="nombre" class="textField"></td></tr>'; 
		echo '<tr><td align="left">Motivo</td><td><select name="motivo" id="motivo" class="textField"><option value="all">--Todos--</option>';
		if($_SESSION['PlazaUsuario']>0){
			$result=mysql_query("SELECT * FROM cat_cargos_unidadeszitla WHERE tipo='V' AND plaza='".$_SESSION['PlazaUsuario']."' ORDER BY nombre");
			while($rowx=mysql_fetch_array($result)){
				echo '<option value="'.$rowx['cve'].'">'.$rowx['nombre'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus" class="textField"><option value="all">---Todos---</option>';
		foreach($sta_var as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="plaza" id="plaza" value="">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}


echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cargos_variables_uni.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("searchplaza").value+"&nom="+document.getElementById("nombre").value+"&motivo="+document.getElementById("motivo").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					document.getElementById("Resultados").innerHTML = objeto.responseText;
					document.getElementById("dep1").innerHTML = document.getElementById("dep2").innerHTML;
					document.getElementById("tot1").innerHTML = document.getElementById("tot2").innerHTML;
					document.getElementById("abo1").innerHTML = document.getElementById("abo2").innerHTML;
					document.getElementById("sal1").innerHTML = document.getElementById("sal2").innerHTML;
				}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function traeMotivos(plazavalor)
	{
	  if(plazavalor=="all"){
		document.forma.motivo.options.length=0;
		document.forma.motivo.options[0]= new Option("---Todos---","all");
	  }
	  else{
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","cargos_variables_uni.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&plaza="+plazavalor);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4){
					document.forma.motivo.options.length=0;
					document.forma.motivo.options[0]= new Option("---Todos---","all");
					var opciones2=objeto.responseText.split("|");
					for (i = 0; i < opciones2.length-1; i++){
						datos=opciones2[i].split(",");
						document.forma.motivo.options[i+1]= new Option(datos[1], datos[0]);
					}
				}
			}
		}
	  }
	}
	
	function marcar()
	{
		if(document.forma.marcatodos.checked)
	 	{
			for (i=0;i<(document.forma.numsels.value/1);i++) 
				document.getElementById(\'sel2\'+i).checked =true;
			document.forma.marcatodos.checked=true;		
			document.forma.marcatodos.value=1;	
		}		
		
		if(document.forma.marcatodos.checked==false)
	 	{
			for (i=0;i<(document.forma.numsels.value/1);i++) 
				document.getElementById(\'sel2\'+i).checked =false;
			document.forma.marcatodos.checked=false;		
			document.forma.marcatodos.value=0;	
		}		
	}
	
	function validar_seleccion()
	{
		sels=0;
		for (i=0;i<(document.forma.numsels.value/1);i++){
			if(document.getElementById(\'sel2\'+i).checked==true)
				sels++;
		}
		if(sels==0)
			alert("Necesita seleccionar un cargo");
		else
			atcr("imp_cargos_variables_uni.php","",0,"0");
	}
		
	';	
	/*if($_POST['cmd']<1){
	echo '
	window.onload = function () {
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}*/
	echo '
	
	</Script>
';
}
bottom();

?>