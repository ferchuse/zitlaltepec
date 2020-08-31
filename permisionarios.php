<?php 

include ("main.php"); 
$rsPlaza=mysql_db_query($base,"SELECT * FROM bancos");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_bancos[$Plaza['cve']]=$Plaza['nombre'];
}
	if ($_POST['cmd']==100) {
require_once('dompdf/dompdf_config.inc.php');
		$html='<html><head>
      <style type="text/css">
	                    top  lado      ladoiz
		 @page{ margin: 5in 0.5in 1px 0.5in;}
		</style>
		 </head><body>';		
		
		
		$select=" SELECT * FROM permisionarios WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:27px">';
		$html.= '
			<tr>';
		$html.= '<td align="center">Grupo Zitlaltepec</td>
			</tr>';
		$html.= '</table>';
		$html.= '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" style="font-size:22px">';
		$html.= '
			<tr>';
		$html.= '<td>Datos del Permisionario:   '.$row['nombre'].'</td>
			</tr>';
		$html.= '</table>';
		$html.= '<br>';
		
		//Formulario 
//		$html.= '<table>';
//		$html.= '<tr><td class="tableEnc">Edicion Permisionarios</td></tr>';
//		$html.= '</table>';

		//Formulario 
		//$html.= '<table width="100%"><tr><td>';
		$html.= '<table>';
		$html.= '<tr align="left"><th>Nombre</th><td>:  '.$row['nombre'].'</td></tr>';
		$html.= '<tr align="left"><th>Telefono</th><td>:  '.$row['telefono'].'</td></tr>';
		$html.= '<tr align="left"><th>Celular</th><td>:  '.$row['celular'].'</td></tr>';
		$html.= '<tr align="left"><th>Telefono de Casa</th><td>:  '.$row['tel_casa'].'</td></tr>';
		$html.= '<tr align="left"><th>Correo Electronico</th><td>:  '.$row['mail'].'</td></tr>';
		$html.= '<tr align="left"><th>Cuenta</th><td>:  '.$row['cuenta'].'</td></tr>';
		$html.= '<tr align="left"><th>Banco</th><td>:  '.$array_bancos[$row['banco']].'</td></tr>';
		$html.= '</table></body></html>';

		 	$mipdf= new DOMPDF();
//	$mipdf->margin: "0";
	//$mipdf->set_paper("A4", "portrait");
	$mipdf->set_paper("A4", "portrait");
    
//    $mipdf->set_margin("Legal", "landscape");
//	$mipdf->set_paper("Legal", "landscape");
	$mipdf->load_html($html);
	$mipdf->render();
	$mipdf ->stream();
exit();
	}
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM permisionarios WHERE 1 ";
		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY nombre";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="6">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Nombre</th><th>Celular</th><th>Banco</th><th>Cuenta</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				echo '<td align="center">
				<a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\''.$row['cve'].'\')"><img src="images/b_print.png" border="0" title=""></a>
				<a href="#" onClick="atcr(\'permisionarios.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="left">'.utf8_encode($row['nombre']).'</td>';
				echo '<td align="left">'.utf8_encode($row['celular']).'</td>';
				echo '<td align="left">'.utf8_encode($array_bancos[$row['banco']]).'</td>';
				echo '<td align="left">'.utf8_encode($row['cuenta']).'</td>';
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
		$res = mysql_query("SELECT * FROM permisionarios WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['telefono']!=$_POST['telefono']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Telefono',nuevo='".$_POST['telefono']."',anterior='".$row['telefono']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['celular']!=$_POST['celular']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Celular',nuevo='".$_POST['celular']."',anterior='".$row['celular']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['tel_casa']!=$_POST['tel_casa']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Telefono Casa',nuevo='".$_POST['tel_casa']."',anterior='".$row['tel_casa']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['mail']!=$_POST['mail']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Email',nuevo='".$_POST['mail']."',anterior='".$row['mail']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['cuenta']!=$_POST['cuenta']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Cuenta',nuevo='".$_POST['cuenta']."',anterior='".$row['cuenta']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($row['banco']!=$_POST['banco']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Banco',nuevo='".$_POST['banco']."',anterior='".$row['banco']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		//Actualizar el Registro
		$update = " UPDATE permisionarios 
					SET nombre='".$_POST['nombre']."',telefono='".$_POST['telefono']."',celular='".$_POST['celular']."',tel_casa='".$_POST['tel_casa']."',
					mail='".$_POST['mail']."',cuenta='".$_POST['cuenta']."',banco='".$_POST['banco']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT permisionarios 
					SET nombre='".$_POST['nombre']."',telefono='".$_POST['telefono']."',celular='".$_POST['celular']."',tel_casa='".$_POST['tel_casa']."',
					mail='".$_POST['mail']."',cuenta='".$_POST['cuenta']."',banco='".$_POST['banco']."'";
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
		$select=" SELECT * FROM permisionarios WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'permisionarios.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'permisionarios.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Permisionarios</td></tr>';
		echo '</table>';

		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
		echo '<tr><th  align="left">Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		echo '<tr><th align="left">Telefono</th><td><input type="text" name="telefono" id="telefono" class="textField" size="" value="'.$row['telefono'].'"></td></tr>';
		echo '<tr><th align="left">Celular</th><td><input type="text" name="celular" id="celular" class="textField" size="" value="'.$row['celular'].'"></td></tr>';
		echo '<tr><th align="left">Telefono de Casa</th><td><input type="text" name="tel_casa" id="tel_casa" class="textField" size="" value="'.$row['tel_casa'].'"></td></tr>';
		echo '<tr><th align="left">Correo Electronico</th><td><input type="text" name="mail" id="mail" class="textField" size="" value="'.$row['mail'].'"></td></tr>';
		echo '<tr><th align="left">Cuenta</th><td><input type="text" name="cuenta" id="cuenta" class="textField" size="" value="'.$row['cuenta'].'"></td></tr>';
		echo '<tr><th align="left">Banco</td><td><select name="banco" id="banco" class="textField"><option value="">---Seleccione---</option>';
			foreach ($array_bancos as $k=>$v) { 
	    echo '<option value="'.$k.'"';if($row['banco']){echo'selected';}echo'>'.$v.'</option>';
			}
		echo '</select></th></tr>';
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
				<td><a href="#" onClick="atcr(\'permisionarios.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField"></td></tr>';	
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
			objeto.open("POST","permisionarios.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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

