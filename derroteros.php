<?php 

include ("main.php"); 

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM derroteros WHERE 1 ";
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY nombre";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="6">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Nombre</th><th>Mutualidad</th><!--<th>Cuenta</th>--><th>Tarjeta</th><th>Seguro Interno</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center"></a><a href="#" onClick="atcr(\'derroteros.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';
				/*echo '<td align="right">'.number_format($row['monto'],2).'</td>';*/
				echo '<td align="right">'.number_format($row['mutualidad'],2).'</td>';
				echo '<td align="right">'.number_format($row['cargo_1'],2).'</td>';
				echo '<td align="right">'.number_format($row['cargo_2'],2).'</td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="6" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$res = mysql_query("SELECT * FROM derroteros WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		$cveusu=$_POST['reg'];
		if($row['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['mutualidad']!=$_POST['mutualidad']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='mutualidad',nuevo='".$_POST['mutualidad']."',anterior='".$row['mutualidad']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		/*if($row['monto']!=floatval($_POST['monto'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Cuenta',nuevo='".$_POST['monto']."',anterior='".$row['monto']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}*/
		if($row['cargo_1']!=floatval($_POST['cargo_1'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tarjeta',nuevo='".$_POST['cargo_1']."',anterior='".$row['cargo_1']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['cargo_2']!=floatval($_POST['cargo_2'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Seguro Interno',nuevo='".$_POST['cargo_2']."',anterior='".$row['cargo_2']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['estatus']!=floatval($_POST['estatus'])){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='".$_POST['estatus']."',anterior='".$row['estatus']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		//Actualizar el Registro
		$update = " UPDATE derroteros 
					SET nombre='".$_POST['nombre']."',mutualidad='".$_POST['mutualidad']."',monto='".$_POST['monto']."',cargo_1='".$_POST['cargo_1']."',cargo_2='".$_POST['cargo_2']."',estatus='".$_POST['estatus']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT derroteros 
					SET nombre='".$_POST['nombre']."',mutualidad='".$_POST['mutualidad']."',monto='".$_POST['monto']."',cargo_1='".$_POST['cargo_1']."',cargo_2='".$_POST['cargo_2']."',estatus='1'";
		$ejecutar = mysql_query($insert);
		$id = mysql_insert_id();
	}
	
	
	
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
		$select=" SELECT * FROM derroteros WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'derroteros.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'derroteros.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Derroteros</td></tr>';
		echo '</table>';

		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th>Estatus</th><td><select name="estatus" id="estatus" class="textField"><option value="">---Seleccione---</option>';
			foreach ($array_estatus_personal as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($k==$row['estatus']){echo'selected';}echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		//echo '<tr><th>Cuenta</th><td><input type="text" name="monto" id="monto" class="textField" size="15" value="'.$row['monto'].'"></td></tr>';
		echo '<tr><th>Tarjeta</th><td><input type="text" name="cargo_1" id="cargo_1" class="textField" size="15" value="'.$row['cargo_1'].'"></td></tr>';
		echo '<tr><th>Seguro Interno</th><td><input type="text" name="cargo_2" id="cargo_2" class="textField" size="15" value="'.$row['cargo_2'].'"></td></tr>';
		echo '<tr><th>mutualidad</th><td><input type="text" name="mutualidad" id="mutualidad" class="textField" size="15" value="'.$row['mutualidad'].'"></td></tr>';
		echo '</table>';
		/*echo '</td><td valign="top">';
		echo '<table align="right" style="display:none;"><tr><td colspan="2" align="center"><img width="200" height="250" src="logos/logo'.$_POST['reg'].'.jpg?'.date('h:i:s').'" border="1"></td></tr>';
		echo '<tr><th>Nuevo Logo</th><td><input type="file" name="foto" id="foto"></td></tr>';
		echo '<tr><th>Borrar Logo</th><td><input type="checkbox" name="borrar_foto" id="borrar_foto" value="S"></td></tr></table>';
		echo '</td></tr></table>';*/
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'derroteros.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField"></td></tr>';
		echo '<tr ><td>Estatus</td><td><select name="estatus" id="estatus" class="textField">';
		foreach ($array_estatus_personal as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
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
			objeto.open("POST","derroteros.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&estatus="+document.getElementById("estatus").value);
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

