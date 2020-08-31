<?php 
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

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$array_estatus_unidad=array(1=>'Alta',2=>'Baja',3=>'Inactivo');
$array_liberada=array(0=>'No Liberada',1=>'Liberada');

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM unidades WHERE 1 ";
		if ($_POST['no_eco']!="") { $select.=" AND no_eco = '".$_POST['no_eco']."'"; }
		if ($_POST['empresa']!="") { $select.=" AND empresa = '".$_POST['empresa']."'"; }
		if ($_POST['localidad']!="") { $select.=" AND localidad = '".$_POST['localidad']."'"; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."'"; }
		if ($_POST['permisionario']!="") { $select.=" AND permisionario = '".$_POST['permisionario']."'"; }
		if ($_POST['tipo_unidad']!="") { $select.=" AND tipo_unidad = '".$_POST['tipo_unidad']."'"; }
		if ($_POST['derrotero']!="") { $select.=" AND derrotero = '".$_POST['derrotero']."'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY no_eco";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="11">'.mysql_num_rows($res).' Registro(s)</td></tr><tr bgcolor="#E9F2F8">';
		if(nivelUsuario()>1){echo '<th>&nbsp;</th>';}echo'<th>No Eco</th><th>Empresa</th><th>Localidad</th><th>Permisionario</th><th>Serie</th><th>Tipo Unidad</th><th>Derrotero</th>';
			if(nivelUsuario()>1){echo'<th>Liberada</th><th>Cuenta</th>';}echo'<th>Estatus</th><th>Fecha Estatus</th></tr>';
			$x=0;
//			echo'*'.$nivelUsuario.'';
			while($row=mysql_fetch_array($res)) {
				rowb();
			if(nivelUsuario()>1){
				echo '<td align="center"><a href="#" onClick="atcr(\'unidades.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				}
				echo '<td align="center">'.utf8_encode($row['no_eco']).'</td>';
				echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_localidad[$row['localidad']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_permisionario[$row['permisionario']]).'</td>';
				echo '<td align="center">'.($row['serie']).'</td>';
				echo '<td align="left">'.utf8_encode($array_tipo_unidad[$row['tipo_unidad']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_derrotero[$row['derrotero']]).'</td>';
				if(nivelUsuario()>1){echo '<td align="center"><select name="liberada_list'.$row['cve'].'" id="liberada_list'.$row['cve'].'">';
						foreach($array_liberada as $k=>$v){
							echo '<option value="'.$k.'"';
						if($row['liberada'] == $k) echo ' selected';
							echo '>'.$v.'</option>';
							}
					echo '</select></br>
				        <input type="button" value="Guardar" onClick="CambioLiberada('.$row['cve'].')"</td>';
				echo '<td align="center"><input type="text"  name="monto_list'.$row['cve'].'" id="monto_list'.$row['cve'].'" value="'.$row['monto_cuenta'].'"></br>
			<input type="button" value="Guardar"  onClick="CambioMonto('.$row['cve'].')"</td>';}
				echo '<td align="left">'.utf8_encode($array_estatus_unidad[$row['estatus']]).'</td>';
				echo '<td align="center">'.utf8_encode($row['fecha_estatus']).'</td>';
				echo '</tr>';
				$x++;
			}
			echo '	
				<tr>
				<td colspan="12" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res = mysql_query("SELECT * FROM unidades WHERE cve != '".$_POST['unidad']."' AND localidad='".$_POST['localidad']."' AND no_eco='".$_POST['no_eco']."'");
	if($row=mysql_fetch_array($res))
		echo '1';
	exit();
}

if($_POST['ajax']==3) {
	
		//Listado de Historial
		$select= " SELECT * FROM historial WHERE cveaux='".$_POST['unidad']."' and menu='".$_POST['cvemenu']."'";
		$rscambios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rscambios);
		if($totalRegistros / $eRegistrosPagina > 1) 
		{
			$eTotalPaginas = $totalRegistros / $eRegistrosPagina;
			if(is_int($eTotalPaginas))
			{$eTotalPaginas--;}
			else
			{$eTotalPaginas = floor($eTotalPaginas);}
		}
		$select .= " ORDER BY cve DESC  LIMIT ".$primerRegistro.",".$eRegistrosPagina;
		$rscambios=mysql_query($select);
		
		if(mysql_num_rows($rscambios)>0) 
		{
		
			echo '<h3 align="center"> Historial de Cambios </h3>';
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Fecha</th><th>Dato</th><th>Valor Nuevo</th><th>Valor Anterior</th><th>Usuario</th>';
			echo '</tr>';
			$i=0;
			while($Cambios=mysql_fetch_array($rscambios)) {
				rowb();
				echo '<td align="center">'.($Cambios['fecha']).'</td>';
				echo '<td align="left">'.htmlentities($Cambios['dato']).'</td>';
				if($Cambios['arreglo']!=""){
					$arreglo=$Cambios['arreglo'];
					$arreglo=$$arreglo;
					echo '<td align="left">'.$arreglo[$Cambios['nuevo']].'</td>';
					echo '<td align="left">'.$arreglo[$Cambios['anterior']].'</td>';
				}else{
					echo '<td align="left">'.$Cambios['nuevo'].'</td>';
					echo '<td align="left">'.$Cambios['anterior'].'</td>';
				}	
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
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
		}
		exit();	
}	

top($_SESSION);

/*** ACTUALIZAR REGISTRO  **************************************************/

if($_POST['ajax']==4){
//	echo'alert("'.$_POST['liberada_list'].$_POST['reg'].'")';	
	$res = mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['liberada']!=intval($_POST['liberada_list'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Liberada',nuevo='".$_POST['liberada_list']."',anterior='".$row['liberada']."',arreglo='array_nosi',usuario='".$_POST['cveusuario']."'");
		}
	
	$update = " UPDATE unidades 
					SET liberada='".$_POST['liberada_list']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);
//		$_POST['cmd']=0;
		exit();

//	header("Location: unidades.php");
}
if($_POST['ajax']==5){
		//echo'alert("'.$_POST['monto_list'].$_POST['reg'].'")';	
	$res = mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['monto_cuenta']!=floatval($_POST['monto_list'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Monto Cuenta',nuevo='".$_POST['monto_list']."',anterior='".$row['monto_cuenta']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
	
	$update = " UPDATE unidades 
					SET monto_cuenta='".$_POST['monto_list']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);

//		$_POST['cmd']=0;
//	header("Location: unidades.php");
}

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$res = mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['no_eco']!=intval($_POST['no_eco'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='No Eco',nuevo='".$_POST['no_eco']."',anterior='".$row['no_eco']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['empresa']!=intval($_POST['empresa'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Empresa',nuevo='".$_POST['empresa']."',anterior='".$row['empresa']."',arreglo='array_empresa',usuario='".$_POST['cveusuario']."'");
		}
		if($row['localidad']!=intval($_POST['localidad'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Localidad',nuevo='".$_POST['localidad']."',anterior='".$row['localidad']."',arreglo='array_localidad',usuario='".$_POST['cveusuario']."'");
		}
		if($row['permisionario']!=intval($_POST['permisionario'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Permisionario',nuevo='".$_POST['permisionario']."',anterior='".$row['permisionario']."',arreglo='array_permisionario',usuario='".$_POST['cveusuario']."'");
		}
		if($row['serie']!=$_POST['serie']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Serie',nuevo='".$_POST['serie']."',anterior='".$row['serie']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['tipo_unidad']!=intval($_POST['tipo_unidad'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo Unidad',nuevo='".$_POST['tipo_unidad']."',anterior='".$row['tipo_unidad']."',arreglo='array_tipo_unidad',usuario='".$_POST['cveusuario']."'");
		}
		if($row['derrotero']!=intval($_POST['derrotero'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Derrotero',nuevo='".$_POST['derrotero']."',anterior='".$row['derrotero']."',arreglo='array_derrotero',usuario='".$_POST['cveusuario']."'");
		}
		if($row['monto_cuenta']!=floatval($_POST['monto_cuenta'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Monto Cuenta',nuevo='".$_POST['monto_cuenta']."',anterior='".$row['monto_cuenta']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['monto_administracion']!=floatval($_POST['monto_administracion'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Monto Administracion',nuevo='".$_POST['monto_administracion']."',anterior='".$row['monto_administracion']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['monto_seguro_interno']!=floatval($_POST['monto_seguro_interno'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Monto Seguro Interno',nuevo='".$_POST['monto_seguro_interno']."',anterior='".$row['monto_seguro_interno']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['fecha_ingreso'] == '0000-00-00') $row['fecha_ingreso']='';
		if($row['fecha_ingreso']!=$_POST['fecha_ingreso']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Fecha Ingreso',nuevo='".$_POST['fecha_ingreso']."',anterior='".$row['fecha_ingreso']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['estatus']!=intval($_POST['estatus'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='array_estatus_unidad',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		if($row['liberada']!=intval($_POST['liberada'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Liberada',nuevo='".$_POST['liberada']."',anterior='".$row['liberada']."',arreglo='array_nosi',usuario='".$_POST['cveusuario']."'");
		}
		//Actualizar el Registro
		$update = " UPDATE unidades 
					SET no_eco='".$_POST['no_eco']."',empresa='".$_POST['empresa']."',localidad='".$_POST['localidad']."',permisionario='".$_POST['permisionario']."',
					serie='".$_POST['serie']."',tipo_unidad='".$_POST['tipo_unidad']."',derrotero='".$_POST['derrotero']."',
					monto_administracion='".$_POST['monto_administracion']."',monto_seguro_interno='".$_POST['monto_seguro_interno']."',
					fecha_ingreso='".$_POST['fecha_ingreso']."',estatus='".$_POST['estatus']."',fecha_estatus='".$_POST['fecha_estatus']."',
					monto_cuenta='".$_POST['monto_cuenta']."',liberada='".$_POST['liberada']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT unidades 
					SET no_eco='".$_POST['no_eco']."',empresa='".$_POST['empresa']."',localidad='".$_POST['localidad']."',permisionario='".$_POST['permisionario']."',
					serie='".$_POST['serie']."',tipo_unidad='".$_POST['tipo_unidad']."',derrotero='".$_POST['derrotero']."',
					monto_administracion='".$_POST['monto_administracion']."',monto_seguro_interno='".$_POST['monto_seguro_interno']."',
					fecha_ingreso='".$_POST['fecha_ingreso']."',estatus='1',fecha_estatus='".fechaLocal()."',
					monto_cuenta = '".$_POST['monto_cuenta']."',liberada='".$_POST['liberada']."'";
		$ejecutar = mysql_query($insert);
		$id = mysql_insert_id();
	}
	
	
	$data = array(
		'function' => 'actualizar_unidades',
        'parametros' => array(
        	'empresa' => $empresagcompufax,
        	'no_eco' => $_POST['no_eco'],
        	'cve' => $id,
        	'estatus' => ($_POST['estatus'] > 0) ? $_POST['estatus'] : 1
        )
     );
 
	
	$options = array('http' => array(
		'method'  => 'POST',
		'content' => http_build_query($data)
	));
	$context  = stream_context_create($options);


	$page = file_get_contents($urlgcompufax, false, $context);

	$data = array(
		'function' => 'actualizar_unidades',
        'parametros' => array(
        	'empresa' => $empresagdatos,
        	'no_eco' => $_POST['no_eco'],
        	'cve' => $id,
        	'estatus' => ($_POST['estatus'] > 0) ? $_POST['estatus'] : 1
        )
     );
 
	
	$options = array('http' => array(
		'method'  => 'POST',
		'content' => http_build_query($data)
	));
	$context  = stream_context_create($options);


	$page = file_get_contents($urlgdatos, false, $context);
	/*if($_POST['borrar_foto']=="S")
		unlink("logos/logo".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"logos/logo".$id.".jpg");
		chmod("logos/logo".$id.".jpg", 0777);
	}*/
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		$select=" SELECT * FROM unidades WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(validar()){ atcr(\'unidades.php\',\'\',\'2\',\''.$row['cve'].'\');} else{$(\'#panel\').hide();}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'unidades.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Unidades</td></tr>';
		echo '</table>';

		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
		if($row['fecha_ingreso']=='0000-00-00') $row['fecha_ingreso']='';
		echo '<tr><th>Fecha Ingreso</th><td><input type="text" name="fecha_ingreso" id="fecha_ingreso" class="readOnly" size="12" value="'.$row['fecha_ingreso'].'"><a href="#" onClick="displayCalendar(document.forms[0].fecha_ingreso,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th>No Eco</th><td><input type="text" name="no_eco" id="no_eco" class="textField" size="10" value="'.$row['no_eco'].'"></td></tr>';
		echo '<tr><th>Empresa</th><td><select name="empresa" id="empresa"><option value="0">Seleccione</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['empresa']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Localidad</th><td><select name="localidad" id="localidad">';
		foreach($array_localidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['localidad']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Serie</th><td><input type="text" name="serie" id="serie" class="textField" size="30" value="'.$row['serie'].'"></td></tr>';
		echo '<tr><th>Permisionario</th><td><select name="permisionario" id="permisionario"><option value="0">Seleccione</option>';
		foreach($array_permisionario as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['permisionario']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Tipo Unidad</th><td><select name="tipo_unidad" id="tipo_unidad"><option value="0">Seleccione</option>';
		foreach($array_tipo_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['tipo_unidad']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Derrotero</th><td><select name="derrotero" id="derrotero"><option value="0">Seleccione</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['derrotero']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Liberada</th><td><input type="checkbox" name="liberada" id="liberada" class="textField" size="15" value="1"'; if($row['liberada']==1) echo ' checked'; echo '></td></tr>';
		echo '<tr><th>Cuenta</th><td><input type="text" name="monto_cuenta" id="monto_cuenta" class="textField" size="15" value="'.$row['monto_cuenta'].'"></td></tr>';
		echo '<tr><th>Administracion</th><td><input type="text" name="monto_administracion" id="monto_administracion" class="textField" size="15" value="'.$row['monto_administracion'].'"></td></tr>';
		echo '<tr><th>Seguro Interno</th><td><input type="text" name="monto_seguro_interno" id="monto_seguro_interno" class="textField" size="15" value="'.$row['monto_seguro_interno'].'"></td></tr>';
		if($_POST['reg']>0){
			echo '<tr><th>Estatus</th><td><select name="estatus" id="estatus">';
			foreach($array_estatus_unidad as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['estatus']) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th>Fecha Estatus</th><td><input type="text" name="fecha_estatus" id="fecha_estatus" class="readOnly" size="12" value="'.$row['fecha_estatus'].'" readOnly></td></tr>';
		}
		echo '</table>';
		/*echo '</td><td valign="top">';
		echo '<table align="right" style="display:none;"><tr><td colspan="2" align="center"><img width="200" height="250" src="logos/logo'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nuevo Logo</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Logo</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';*/
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script>
				function validar(){
					regresar = true;
					if(document.forma.no_eco.value==""){
						regresar = false;
						alert("Necesita ingresar el no eco");
					}
					else if(document.forma.empresa.value=="0"){
						regresar = false;
						alert("Necesita seleccionar la empresa");
					}
					else if(!validarEco()){
						regresar = false;
						alert("El no eco ya existe en la empresa");
					}
					else if(document.forma.serie.value==""){
						regresar = false;
						alert("Necesita ingresar la serie");
					}
					else if(document.forma.permisionario.value=="0"){
						regresar = false;
						alert("Necesita seleccionar el permisionario");
					}
					else if(document.forma.tipo_unidad.value=="0"){
						regresar = false;
						alert("Necesita seleccionar el tipo de unidad");
					}
					else if(document.forma.derrotero.value=="0"){
						regresar = false;
						alert("Necesita seleccionar el derrotero");
					}
					return regresar;
				}

				function validarEco(){
					regresar = true;
					$.ajax({
					  url: "unidades.php",
					  type: "POST",
					  async: false,
					  data: {
						no_eco: document.getElementById("no_eco").value,
						empresa: document.forma.empresa.value,
						localidad: document.forma.localidad.value,
						unidad: '.intval($_POST['reg']).',
						ajax: 2
					  },
						success: function(data) {
							if(data == "1"){
								regresar = false;
							}
						}
					});
					return regresar;
				}
				function buscarRegistros()
				{
					document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","unidades.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&cvemenu="+document.getElementById("cvemenu").value+"&cveusuario="+document.getElementById("cveusuario").value+"&unidad='.$_POST['reg'].'&numeroPagina="+document.getElementById("numeroPagina").value);
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
				}	
				if('.intval($_POST['reg']).'>0)
					buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
			</script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'unidades.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" size="10" class="textField"></td></tr>';	
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todos</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Localidad</td><td><select name="localidad" id="localidad"><option value="">Todos</option>';
		foreach($array_localidad as $k=>$v){
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
			objeto.open("POST","unidades.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&permisionario="+document.getElementById("permisionario").value+"&tipo_unidad="+document.getElementById("tipo_unidad").value+"&derrotero="+document.getElementById("derrotero").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value+"&localidad="+document.getElementById("localidad").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	    
		
		function esnulo(v){
			dato = document.forma.cuenta_list+v.value;
        if(isNaN(dato)){
			alert("es nulo "+dato);

			CambioLiberada(v);
        }else{
			alert(" no es nulo");
			CambioLiberada(v);
        }
    }
		function CambioLiberada(cod){
					regresar = true;
					$.ajax({
					  url: "unidades.php",
					  type: "POST",
					  async: false,
					  data: {
						reg: cod,
						liberada_list: document.getElementById("liberada_list"+cod).value,
						cvemenu:document.forma.cvemenu.value,
						ajax: 4
					  },
						success: function(data) {
							
						}
					});

				}
		function CambioMonto(cod){
					regresar = true;
					$.ajax({
					  url: "unidades.php",
					  type: "POST",
					  async: false,
					  data: {
						reg: cod,
						monto_list: document.getElementById("monto_list"+cod).value,
						cvemenu:document.forma.cvemenu.value,
						ajax: 5
					  },
						success: function(data) {
							
						}
					});

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

