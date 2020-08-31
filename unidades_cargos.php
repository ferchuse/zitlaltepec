<?php 
  function lastday($m,$y) { 
      $month = $m;
      $year = $y;
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };

  /** Actual month first day **/
  function firstday() {
      $month = $m;
      $year = $y;
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
include ("main.php"); 
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_empresa[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_permisionario[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM tipos_unidad ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_tipo_unidad[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM derroteros ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_derrotero[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM cat_cargos_unidades ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_cargos[$row['cve']] = $row['nombre'];
}
$array_estatus_unidad=array(1=>'Alta',2=>'Baja',3=>'Inactivo');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$array_cargos_monto = array();
		$res=mysql_query("SELECT * FROM cargos_unidades WHERE LEFT(fecha,7) = '".$_POST['mes']."'");
		while($row = mysql_fetch_array($res)){
			$array_cargos_monto[$row['unidad']][$row['motivo']] = $row['cargo'];
		}
		
		$ro=explode('-',$_POST['mes']);
		$primer=firstday($ro[1],$ro[0]);
		$last=lastday($ro[1],$ro[0]);
		$_POST['fecha_ini']=$primer;
		$_POST['fecha_fin']=$last;
		
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM unidades WHERE 1 ";
		if ($_POST['no_eco']!="") { $select.=" AND no_eco = '".$_POST['no_eco']."'"; }
		if ($_POST['empresa']!="") { $select.=" AND empresa = '".$_POST['empresa']."'"; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."'"; }
		if ($_POST['permisionario']!="") { $select.=" AND permisionario = '".$_POST['permisionario']."'"; }
		if ($_POST['tipo_unidad']!="") { $select.=" AND tipo_unidad = '".$_POST['tipo_unidad']."'"; }
		if ($_POST['derrotero']!="") { $select.=" AND derrotero = '".$_POST['derrotero']."'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY no_eco";
		$res=mysql_query($select);
		$nivelUsuario=nivelUsuario();
		$array_totales=array(0,0);
		if(mysql_num_rows($res)>0) 
		{
			echo '<input type="hidden" name="mesedicion" id="mesedicion" value="'.$_POST['mes'].'">';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="9">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>No Eco</th><th>Empresa</th><th>Permisionario</th><th>Estatus</th><th>Derrotero</th><th>Gastos de Administracion</th><th>Seguro Interno</th>';
			if(nivelUsuario()>2){echo'<th>Saldo</th>';}
			echo'</tr>';
			while($row=mysql_fetch_array($res)) {
				$cargo=0;$abono=0;$saldoanterior=0;$saldoactual=0;
				/*$saldoanterior=saldo_unidad_2($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$cargo=saldo_unidad_2($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$abono=saldo_unidad_2($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$saldoactual=$saldoanterior+($abono-$cargo);*/
				$saldoanterior=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$cargo=saldo_unidad($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$abono=saldo_unidad($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$saldoactual=$saldoanterior+($abono-$cargo);

				
				rowb();
				echo '<td align="center">'.utf8_encode($row['no_eco']).'</td>';
				echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_permisionario[$row['permisionario']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_estatus_unidad[$row['estatus']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
				if($nivelUsuario > 1){
					echo '<td align="center"><input type="text" class="textField" id="cargo_1_'.$row['cve'].'" size="10" value="'.$array_cargos_monto[$row['cve']][1].'" onKeyUp="if(event.keyCode==13){ guardarCargo(1,'.$row['cve'].');}"></td>';
					echo '<td align="center"><input type="text" class="textField" id="cargo_2_'.$row['cve'].'" size="10" value="'.$array_cargos_monto[$row['cve']][2].'" onKeyUp="if(event.keyCode==13){ guardarCargo(2,'.$row['cve'].');}"></td>';
				}
				else{
					echo '<td align="right">'.number_format($array_cargos_monto[$row['cve']][1]).'</td>';
					echo '<td align="right">'.number_format($array_cargos_monto[$row['cve']][2]).'</td>';
				}
				if(nivelUsuario()>2){
					echo '<td align="right">'.number_format($saldoactual,2).'</td>';
					}
				
				$array_totales[0]+=$array_cargos_monto[$row['cve']][1];
				$array_totales[1]+=$array_cargos_monto[$row['cve']][2];
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">';menunavegacion();echo '</td>';
			foreach($array_totales as $v) echo '<td align="right" bgcolor="#E9F2F8">'.number_format($v,2).'</td>';
			echo '
				<td bgcolor="#E9F2F8"></td></tr>
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

if($_POST['ajax']==2){
	$res = mysql_query("SELECT * FROM cargos_unidades WHERE unidad = '".$_POST['unidad']."' AND motivo = '".$_POST['cargo']."' AND LEFT(fecha,7) = '".$_POST['mes']."'") or die(mysql_error()."SELECT * FROM cargos_unidades WHERE unidad = '".$_POST['unidad']."' AND motivo = '".$_POST['cargo']."' AND LEFT(fecha,7) = '".$_POST['mes']."'");
	if($row = mysql_fetch_array($res)){
		if($row['cargo']!=$_POST['monto']){
			mysql_query("UPDATE cargos_unidades SET cargo = '".$_POST['monto']."' WHERE cve='".$row['cve']."'");
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['unidad']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='".$array_cargos[$_POST['cargo']]." ".$_POST['mes']."',nuevo='".$_POST['monto']."',anterior='".$row['cargo']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
	}
	else{
		if($_POST['monto']>0){
			$fecha=$_POST['mes'].'-01';
			$res = mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['unidad']."'");
			$row = mysql_fetch_array($res);
			if($row['estatus']==1 && $row['fecha_estatus']>$fecha) $fecha=$row['fecha_estatus'];
			mysql_query("INSERT cargos_unidades SET fecha='$fecha',motivo='".$_POST['cargo']."',unidad='".$_POST['unidad']."',cargo = '".$_POST['monto']."'");
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['unidad']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='".$array_cargos[$_POST['cargo']]." ".$_POST['mes']."',nuevo='".$_POST['monto']."',anterior='0',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
	}
	exit();
}
top($_SESSION);


/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<!--<td><a href="#" onClick="atcr(\'unidades.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Mes</td><td><select name="mes" id="mes">';
		$res = mysql_query("SELECT LEFT(fecha,7) FROM cargos_unidades GROUP BY LEFT(fecha,7) ORDER BY LEFT(fecha,7) DESC");
		while($row = mysql_fetch_array($res)){
			echo '<option value="'.$row[0].'"';
			echo '>'.$row[0].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" size="10" class="textField"></td></tr>';	
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todos</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Permisionario</td><td><select name="permisionario" id="permisionario"><option value="">Todos</option>';
		foreach($array_permisionario as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo Unidad</td><td><select name="tipo_unidad" id="tipo_unidad"><option value="">Todos</option>';
		foreach($array_tipo_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="">Todos</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
		foreach($array_estatus_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';		

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
			objeto.open("POST","unidades_cargos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mes="+document.getElementById("mes").value+"&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&permisionario="+document.getElementById("permisionario").value+"&tipo_unidad="+document.getElementById("tipo_unidad").value+"&derrotero="+document.getElementById("derrotero").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function guardarCargo(cargo, unidad){
		if(confirm("Esta seguro de cambiar el importe?")){
			objeto=crearObjeto();
			if (objeto.readyState != 0) {
				alert("Error: El Navegador no soporta AJAX");
			} else {
				objeto.open("POST","unidades_cargos.php",true);
				objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
				objeto.send("ajax=2&monto="+document.getElementById("cargo_"+cargo+"_"+unidad).value+"&mes="+document.getElementById("mesedicion").value+"&unidad="+unidad+"&cargo="+cargo+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
				objeto.onreadystatechange = function()
				{
					if (objeto.readyState==4)
					{buscarRegistros();}
				}
			}
		}
	}
	
	//Funcion para navegacion de Registros. 20 por pagina.
	function moverPagina(x) {
		document.getElementById("numeroPagina").value = x;
		buscarRegistros();
	}	
	
	    buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	</Script>
';
	}
	
bottom();
?>

