<?php 

include ("main.php"); 

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de datos
		$select= " SELECT * FROM datos WHERE left(fecha_registro,10)>='".$_POST['fecha_ini']."' AND left(fecha_registro,10)<='".$_POST['fecha_fin']."' ";
		if ($_POST['nombre']!="") { $select.=" AND economic LIKE '%".$_POST['nombre']."%'"; }
//		echo $select;
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve desc";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="6">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">
			<!--<th>ID</th>
			<th>Tired</th>-->
			<th>Inicial</th>
			<th>Final</th>
			<th>Economico</th>
			<th>Kilometros</th>
			</tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
//				echo '<td align="center"><a href="#" onClick="atcr(\'datos_gps.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
//				echo '<td align="center">'.utf8_encode($row[1]).'</td>';
//				echo '<td align="center">'.utf8_encode($row[2]).'</td>';
				echo '<td align="center">'.utf8_encode($row[4]).'</td>';
				echo '<td align="center">'.utf8_encode($row[5]).'</td>';
				echo '<td align="center">'.utf8_encode($row[6]).'</td>';
				echo '<td align="center">'.utf8_encode($row[3]).'</td>';
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
/*
if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$res = mysql_query("SELECT * FROM taquillas WHERE cve='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$row['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		//Actualizar el Registro
		$update = " UPDATE taquillas 
					SET nombre='".$_POST['nombre']."',tipo_impresora='".$_POST['tipo_impresora']."',nombre_impresora='".addslashes($_POST['nombre_impresora'])."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT taquillas 
					SET nombre='".$_POST['nombre']."',tipo_impresora='".$_POST['tipo_impresora']."',nombre_impresora='".addslashes($_POST['nombre_impresora'])."'";
		$ejecutar = mysql_query($insert);
		$id = mysql_insert_id();
	}
	
	
	
	/*if($_POST['borrar_foto']=="S")
		unlink("logos/logo".$id.".jpg");
	if(is_uploaded_file ($_FILES['foto']['tmp_name'])){
		$arch = $_FILES['foto']['tmp_name'];
		copy($arch,"logos/logo".$id.".jpg");
		chmod("logos/logo".$id.".jpg", 0777);
	}
	$_POST['cmd']=0;
	
}
*/
/*** EDICION  **************************************************/


/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>';
			echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
        	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
				echo'<!--<td><a href="#" onClick="atcr(\'taquil.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>-->
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr ><td>Economico</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField"></td></tr>';	
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
			objeto.open("POST","datos_gps.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&nombre="+document.getElementById("nombre").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
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

