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
	$array_accidente_tipo[$Accidente['cve']]=$array_tipo_accidente[$Accidente['tipo']];
}

$rsParque=mysql_db_query($base,"SELECT * FROM unidades");
while($Parque=mysql_fetch_array($rsParque)){
	$array_unidad[$Parque['cve']]=$Parque['no_eco'];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM operadores");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_conductor[$Conductor['cve']]=$Conductor['credencial'].' - '.$Conductor['nombre'];
}

$array_estatussalidas=array("En Proceso","Pagado","Cancelado");
/*** ELIMINAR REGISTRO  **************************************************/

if ($_POST['cmd']==3) {
	$delete= "UPDATE condonacion_accidentes SET estatus='2',fechacan='".fechaLocal()."',usucan='".$_POST['usuario']."',monto='0' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_db_query($base,$delete);
	header("Location: condonacion_accidentes.php");
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE condonacion_accidentes 
						SET monto='".$_POST['monto']."',
						    descripcion='".$_POST['descripcion']."',accidente='".$_POST['accidente']."'
						WHERE AND cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);			
	} else {
			//Insertar el Registro
			$insert = " INSERT condonacion_accidentes
						SET fecha='".$_POST['fecha']."',
						    monto='".$_POST['monto']."',descripcion='".$_POST['descripcion']."',accidente='".$_POST['accidente']."',
							usuario='".$_POST['usuario']."'";
			$ejecutar = mysql_db_query($base,$insert);	
			$resCargo=mysql_db_query($base,"SELECT * FROM cargos_conductores WHERE motivo='6' and folio='".$array_accidente[$_POST['accidente']]."'");
			$saldo=$_POST['monto'];
			while($rowCargo=mysql_fetch_array($resCargo)){
				if($saldo>0){
					if(($rowCargo['cargo']-$rowCargo['abono'])>$saldo){
						mysql_db_query($base,"UPDATE cargos_conductores SET cargo=cargo-".$saldo." WHERE cve='".$rowCargo['cve']."'");
						$saldo-=$saldo;
					}
					elseif(($rowCargo['cargo']-$rowCargo['abono'])<$saldo && $rowCargo['abono']>0){
						mysql_db_query($base,"UPDATE cargos_conductores SET cargo=cargo-".($rowCargo['cargo']-$rowCargo['abono'])." WHERE cve='".$rowCargo['cve']."'");
						$saldo-=($rowCargo['cargo']-$rowCargo['abono']);
					}
					else{
						mysql_db_query($base,"DELETE FROM cargos_conductores WHERE cve='".$rowCargo['cve']."'");
						$saldo-=($rowCargo['cargo']-$rowCargo['abono']);
					}
				}
			}
	}
	header("Location: condonacion_accidentes.php");
	
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de plazas
		$select= " SELECT * FROM condonacion_accidentes WHERE estatus='0' ";
		if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
		$select .= " ORDER BY cve DESC";
		$rssalida=mysql_db_query($base,$select);
		/*$totalRegistros = mysql_num_rows($rssalida);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve DESC LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rssalida=mysql_db_query($base,$select);*/
		
		if(mysql_num_rows($rssalida)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			if($_SESSION['PlazaUsuario']==0) $col=11;
			else $col=10;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rssalida).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Imprimir</th>';
			if($_SESSION['PlazaUsuario']==0) echo '<th>Plaza</th>';
			echo '<th>Folio</th><th>Fecha</th><th>Monto</th><th>Accidente</th><th>Unidad</th><th>Conductor</th><th>Descripcion</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			while($Salida=mysql_fetch_array($rssalida)) {
				rowb();
				//echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.plaza.value=\''.$Salida['plaza'].'\';atcr(\'condonacion_accidentes.php\',\'\',\'1\','.$Salida['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Salida['nombre'].'"></a></td>';
				echo '<td align="center" width="40" nowrap><a href="#" onClick="document.forma.plaza.value=\''.$Salida['plaza'].'\';atcr(\'imp_condonacion_accidentes.php\',\'_bank\',\'1\','.$Salida['cve'].')"><img src="images/b_print.png" border="0" title="Editar '.$Salida['nombre'].'"></a></td>';
				if($_SESSION['PlazaUsuario']==0)
					echo '<td>'.htmlentities($array_plaza[$Salida['plaza']]).'</td>';
				echo '<td align="center">'.$Salida['cve'].'</td>';
				echo '<td align="center">'.$Salida['fecha'].'</td>';
				echo '<td align="center">$ '.number_format($Salida['monto'],2).'</td>';
				echo '<td align="center">'.$array_accidente[$Salida['accidente']].'</td>';
				echo '<td align="center">'.$array_unidad[$array_accidente_uni[$Salida['accidente']]].'</td>';
				echo '<td align="left">'.htmlentities($array_conductor[$array_accidente_ope[$Salida['accidente']]]).'</td>';
				echo '<td align="left">'.$Salida['descripcion'].'</td>';
				echo '<td align="left">'.htmlentities($array_usuario[$Salida['usuario']]).'</td>';
				$total+=$Salida['monto'];
				$i++;
				echo '</tr>';
			}
			if($_SESSION['PlazaUsuario']==0)$col=3;
			else $col=2;
			echo '	
				<tr>
				<td colspan="'.$col.'" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
				<td bgcolor="#E9F2F8" align="right">Total:</td>
				<td bgcolor="#E9F2F8" align="center">$ '.number_format($total,2).'</td>
				<td colspan="5" bgcolor="#E9F2F8">&nbsp;</td>
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

if($_POST['ajax']==2){
	$rsAccidente=mysql_db_query($base,"SELECT * FROM accidentes WHERE estatus='0' ORDER BY folio");
	while($Accidente=mysql_fetch_array($rsAccidente)){
		echo $Accidente['cve'].','.$Accidente['folio'].'|';
	}
	exit();
}	

if($_POST['ajax']==3){
	$resCargo=mysql_db_query($base,"SELECT sum(cargo-abono) FROM cargos_conductores WHERE motivo='6' and folio='".$array_accidente[$_POST['accidente']]."'");
	$rowCargo=mysql_fetch_array($resCargo);
	echo $array_conductor[$array_accidente_ope[$_POST['accidente']]].'|'.$array_unidad[$array_accidente_uni[$_POST['accidente']]].'|'.$array_accidente_tipo[$_POST['accidente']].'|'.round($rowCargo[0],2);
	exit();
}

top($_SESSION);

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM condonacion_accidentes WHERE cve='".$_POST['reg']."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		if($_POST['reg']>0){
			$fecha=$Salida['fecha'];
			$Encabezado = 'Folio No.'.$_POST['reg'];
		}
		else{
			$fecha=fechaLocal();
			$Encabezado = 'Nueva Condonacion';
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if($_SESSION[$archivo[(count($archivo)-1)]]>1 && ($_POST['reg']==0 || $Salida['estatus']==0)){
				echo '<td><a href="#" onClick="
				if(document.forma.monto.value==\'\')
					alert(\'Necesita ingresar el monto\');
				else if(document.forma.accidente.value==\'0\')
					alert(\'Necesita seleccionar un accidente\');
				else if((document.forma.saldo.value/1)<(document.forma.monto.value/1))
					alert(\'El monto no puede ser mayor que el saldo\');
				else if(document.forma.clickguardar.value==\'no\'){
					document.forma.clickguardar.value=\'si\';
					atcr(\'condonacion_accidentes.php\',\'\',\'2\',\''.$Salida['cve'].'\');
				}"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			}
			echo '<td><a href="#" onClick="atcr(\'condonacion_accidentes.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Condonacion de Accidentes</td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar"value="no">';
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly></td></tr>';
		if($_SESSION['PlazaUsuario']==0 && $_POST['reg']==0){
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza" onChange="traeAccidentes(this.value);"><option value="0">---Seleccione una Plaza---</option>';
			$rsPlazas=mysql_db_query($base,"SELECT * FROM plazas ORDER BY nombre");
			while($Plaza=mysql_fetch_array($rsPlazas)){
				echo '<option value="'.$Plaza['cve'].'"';
				if($Salida['plaza']==$Plaza['cve']) echo ' selected';
				echo '>'.$Plaza['nombre'].'</option>';
			}
			echo '</select></td></tr>';
		}
		else if($_POST['reg']>0){
			echo '<tr><th align="left">Plaza</th><td><input type="text" value="'.$array_plaza[$Salida['plaza']].'" class="readOnly" readonly><input type="hidden" name="plaza" id="plaza" value="'.$Salida['plaza'].'"></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '<tr><th align="left">Accidente</th><td><select name="accidente" id="accidente" onChange="traerDatosAccidentes(this.value);"><option value="0">---Seleccione un Accidente---</option>';
		if($_POST['reg']>0){
			$rsMotivo=mysql_db_query($base,"SELECT * FROM accidentes WHERE 1 ORDER BY folio");
			while($Motivo=mysql_fetch_array($rsMotivo)){
				echo '<option value="'.$Motivo['cve'].'"';
				if($Salida['accidente']==$Motivo['cve']) echo ' selected';
				echo '>'.$Motivo['folio'].'</option>';
			}
		}
		else if($_SESSION['PlazaUsuario']>0){
			$rsMotivo=mysql_db_query($base,"SELECT * FROM accidentes WHERE 1 ORDER BY folio");
			while($Motivo=mysql_fetch_array($rsMotivo)){
				echo '<option value="'.$Motivo['cve'].'"';
				echo '>'.$Motivo['folio'].'</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Unidad</th><td><input type="text" id="unidad" name="unidad" value="'.$array_unidad[$array_accidente_uni[$Salida['accidente']]].'" class="readOnly" readonly></td></tr>';
		echo '<tr><th align="left">Conductor</th><td><input type="text" id="conductor" size="50" name="conductor" value="'.$array_conductor[$array_accidente_ope[$Salida['accidente']]].'" class="readOnly" readonly></td></tr>';
		echo '<tr><th align="left">Tipo Accidente</th><td><input type="text" id="tipoA" size="50" name="tipoA" value="'.$array_accidente_tipo[$Salida['accidente']].'" class="readOnly" readonly></td></tr>';
		echo '<tr><th align="left">Saldo</th><td><input type="text" name="saldo" id="saldo" class="readOnly" size="15" value="" readOnly></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="15" value="'.$Salida['monto'].'"></td></tr>';
		echo '<tr><th valign="top" align="left">Descripcion</th><td><textarea name="descripcion" id="descripcion" class="textField" rows="5" cols="50">'.$Salida['descripcion'].'</textarea></td></tr>';
		echo '</table>';
		
		echo '<script language="javascript">';
				
		echo '	function traeAccidentes(plazavalor){
				  if(plazavalor==0){
					document.forma.accidente.options.length=0;
					document.forma.accidente.options[0]= new Option("---Seleccione un Accidente---","0");
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","condonacion_accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=2&plaza="+plazavalor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.accidente.options.length=0;
								document.forma.accidente.options[0]= new Option("---Seleccione un Accidente---","0");
								var opciones2=objeto2.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.accidente.options[i+1]= new Option(datos[1], datos[0]);
								}
							}
						}
					}
				  }
				}
				
				function traerDatosAccidentes(valor){
				  if(valor==0){
					document.forma.conductor.value="";
					document.forma.unidad.value="";
				  }
				  else{
					objeto2=crearObjeto();
					if (objeto2.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto2.open("POST","condonacion_accidentes.php",true);
						objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto2.send("ajax=3&accidente="+valor);
						objeto2.onreadystatechange = function(){
							if (objeto2.readyState==4){
								document.forma.conductor.value="";
								document.forma.unidad.value="";
								var opciones2=objeto2.responseText.split("|");
								document.forma.conductor.value=opciones2[0];
								document.forma.unidad.value=opciones2[1];
								document.forma.tipoA.value=opciones2[2];
								document.forma.saldo.value=opciones2[3];
							}
						}
					}
				  }
				}
			  </script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onClick="atcr(\'condonacion_accidentes.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td><td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
	}
echo '<input type="hidden" name="usuario" value="'.$_SESSION['CveUsuario'].'">';
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
			objeto.open("POST","condonacion_accidentes.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&plaza="+document.getElementById("searchplaza").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

