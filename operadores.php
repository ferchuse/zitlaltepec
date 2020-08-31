<?php 

include ("main.php"); 
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_empresa[$row['cve']] = $row['nombre'];
}
$array_estatus_operador=array(1=>'Alta',2=>'Baja',3=>'Inactivo');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM operadores WHERE 1 ";
		if ($_POST['credencial']!="") { $select.=" AND cve = '".$_POST['credencial']."'"; }
		if ($_POST['empresa']!="") { $select.=" AND empresa = '".$_POST['empresa']."'"; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."'"; }
		if ($_POST['nombre']!="") { $select.=" AND nombre like '%".$_POST['nombre']."%'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Credencial</th><th>Nombre</th><th>Empresa</th><th>RFC</th><th>Direccion</th><th>Estatus</th><th>Fecha Estatus</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center"><a href="#" onClick="atcr(\'operadores.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="center">'.htmlentities($row['cve']).'</td>';
				echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';
				echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';
				echo '<td align="center">'.utf8_encode($row['rfc']).'</td>';
				echo '<td align="left">'.utf8_encode($row['direccion']).'</td>';
				echo '<td align="left">'.utf8_encode($array_estatus_operador[$row['estatus']]).'</td>';
				echo '<td align="center">'.utf8_encode($row['fecha_estatus']).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="8" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res = mysql_query("SELECT * FROM operadores WHERE cve != '".$_POST['operador']."' AND rfc='".$_POST['rfc']."'");
	if($row=mysql_fetch_array($res))
		echo '1';
	exit();
}
top($_SESSION);

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']>0) {
		$res = mysql_query("SELECT * FROM operadores WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['empresa']!=intval($_POST['empresa'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Empresa',nuevo='".$_POST['empresa']."',anterior='".$row['empresa']."',arreglo='array_empresa',usuario='".$_POST['cveusuario']."'");
		}
		if($row['rfc']!=$_POST['rfc']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='RFC',nuevo='".$_POST['rfc']."',anterior='".$row['rfc']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['direccion']!=$_POST['direccion']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Direccion',nuevo='".$_POST['direccion']."',anterior='".$row['direccion']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['fecha_ingreso'] == '0000-00-00') $row['fecha_ingreso']='';
		if($row['fecha_ingreso']!=$_POST['fecha_ingreso']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Fecha Ingreso',nuevo='".$_POST['fecha_ingreso']."',anterior='".$row['fecha_ingreso']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['estatus']!=intval($_POST['estatus'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='array_estatus_unidad',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		if($row['tag']!=intval($_POST['tag'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='TAG',nuevo='".$array_nosi[intval($_POST['estatus'])]."',anterior='".$array_nosi[$row['estatus']]."',arreglo='array_estatus_unidad',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		if($row['foliotag']!=$_POST['foliotag']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Folio TAG',nuevo='".$_POST['foliotag']."',anterior='".$row['foliotag']."',arreglo='array_estatus_unidad',usuario='".$_POST['cveusuario']."'");
			$_POST['fecha_sta'] = fechaLocal();
		}
		//Actualizar el Registro
		$update = " UPDATE operadores 
					SET nombre='".$_POST['nombre']."',empresa='".$_POST['empresa']."',rfc='".$_POST['rfc']."',
					direccion='".$_POST['direccion']."',
					fecha_ingreso='".$_POST['fecha_ingreso']."',estatus='".$_POST['estatus']."',fecha_estatus='".$_POST['fecha_estatus']."',tag='".$_POST['tag']."',foliotag='".$_POST['foliotag']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT operadores 
					SET nombre='".$_POST['nombre']."',empresa='".$_POST['empresa']."',rfc='".$_POST['rfc']."',
					direccion='".$_POST['direccion']."',
					fecha_ingreso='".$_POST['fecha_ingreso']."',estatus='1',fecha_estatus='".fechaLocal()."',tag='".$_POST['tag']."',foliotag='".$_POST['foliotag']."'";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$id = mysql_insert_id();
	}
	
	$data = array(
		'function' => 'actualizar_operadores',
        'parametros' => array(
        	'empresa' => $empresagcompufax,
        	'clave' => $id,
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
	
	if($_POST['borrar_foto']=="S")
		unlink("fotos/foto".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"fotos/foto".$id.".jpg");
		chmod("fotos/foto".$id.".jpg", 0777);
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		$select=" SELECT * FROM operadores WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		if($_POST['reg']==0) $row['cve']='Nuevo';
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();if(validar()){ atcr(\'operadores.php\',\'\',\'2\',\''.$_POST['reg'].'\');} else{$(\'#panel\').hide();}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'operadores.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Operadores</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table width="100%"><tr><td>';
		echo '<table>';
		if($row['fecha_ingreso']=='0000-00-00') $row['fecha_ingreso']='';
		echo '<tr><th>Fecha Ingreso</th><td><input type="text" name="fecha_ingreso" id="fecha_ingreso" class="readOnly" size="12" value="'.$row['fecha_ingreso'].'"><a href="#" onClick="displayCalendar(document.forms[0].fecha_ingreso,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th>Credencial</th><td><input type="text" name="credencial" id="credencial" class="readOnly" size="10" value="'.$row['cve'].'" readOnly></td></tr>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th>Empresa</th><td><select name="empresa" id="empresa"><option value="0">Seleccione</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==$row['empresa']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>RFC</th><td><input type="text" name="rfc" id="rfc" class="textField" size="20" value="'.$row['rfc'].'"></td></tr>';
		echo '<tr><th>Direccion</th><td><input type="text" name="direccion" id="direccion" class="textField" size="100" value="'.$row['direccion'].'"></td></tr>';
		echo '<tr><th>TAG</th><td><input type="checkbox" name="tag" id="tag" value="1" onClick="if(this.checked) $(\'#trtag\').show(); else $(\'#trtag\').hide();"';
		if($row['tag']==1) echo ' checked';
		echo '></td></tr>';
		echo '<tr';
		if($row['tag']!=1) echo ' style="display:none;"';
		echo '><th>Folio TAG</th><td><input type="text" name="foliotag" id="foliotag" class="textField" size="30" value="'.$row['foliotag'].'"></td></tr>';
		if($_POST['reg']>0){
			echo '<tr><th>Estatus</th><td><select name="estatus" id="estatus">';
			foreach($array_estatus_operador as $k=>$v){
				echo '<option value="'.$k.'"';
				if($k==$row['estatus']) echo ' selected';
				echo '>'.$v.'</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><th>Fecha Estatus</th><td><input type="text" name="fecha_estatus" id="fecha_estatus" class="readOnly" size="12" value="'.$row['fecha_estatus'].'" readOnly></td></tr>';
		}
		echo '</table>';
		echo '</td><td valign="top">';
		echo '<table align="right" style="display:none;"><tr><td colspan="2" align="center"><img width="200" height="250" src="fotos/foto'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nuevo Logo</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Logo</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';

		echo '<script>
				function validar(){
					regresar = true;
					if(document.forma.nombre.value==""){
						regresar = false;
						alert("Necesita ingresar el nombre");
					}
					else if(document.forma.empresa.value=="0"){
						regresar = false;
						alert("Necesita seleccionar la empresa");
					}
					else if(!validarRFC()){
						regresar = false;
						alert("El no eco ya existe en la empresa");
					}
					return regresar;
				}

				function validarRFC(){
					if(document.getElementById("rfc").value=="")
						return true;
					regresar = true;
					$.ajax({
					  url: "operadores.php",
					  type: "POST",
					  async: false,
					  data: {
						rfc: document.getElementById("rfc").value,
						empresa: document.forma.empresa.value,
						operador: '.intval($_POST['reg']).',
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
			</script>';
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'operadores.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Credencial</td><td><input type="text" name="credencial" id="credencial" size="10" class="textField"></td></tr>';	
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="50" class="textField"></td></tr>';	
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todos</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
		foreach($array_estatus_operador as $k=>$v){
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
			objeto.open("POST","operadores.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&credencial="+document.getElementById("credencial").value+"&empresa="+document.getElementById("empresa").value+"&nombre="+document.getElementById("nombre").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
	
	    buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	</Script>
';
	}
	
bottom();
?>

