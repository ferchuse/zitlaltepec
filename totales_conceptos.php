<?php 

include ("main.php"); 
$array_cargo = array(1=>'Administracion', 2=>'Seguro Interno', 3=>'Mutualidad', 4=>'Prorrata',5=>'Seguridad', 6=>'Fianza');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select=" SELECT * FROM totales_conceptos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ";
//		if ($_POST['nombre']!="") { $select.=" AND nombre LIKE '%".$_POST['nombre']."%'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY cve desc";
		$res=mysql_query($select);
//		echo''.$select.'';
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="8">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><th>Fecha</th>';
			foreach($array_cargo as $k=>$v){
				echo'<th>'.$v.'</th>';
			}
			echo'</tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
				
				if($row['estatus']=="C"){
					echo'<td>Cancelado</br>'.$row['fecha_can'].' - '.$row['fecha_can'].'</td> <td></td>';
					foreach($array_cargo as $k=>$v){echo'<td align="right">'.number_format(0,2).'</td>';}
				}else{echo '<td align="center"><a href="#" onClick="atcr(\'totales_conceptos.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a>
					  <a href="#" onClick="atcr(\'totales_conceptos.php\',\'\',3,'.$row['cve'].')"><img src="images/validono.gif" border="0"></a></td>';
					  echo '<td align="left">'.$row['fecha'].'</td>';
					$sel=" SELECT * FROM totales_conceptos_mov WHERE aux='".$row['cve']."' ";
					$re=mysql_query($sel);
				while($row1=mysql_fetch_array($re)){
					$array_total[$row1['opc']]=$array_total[$row1['opc']] + $row1['valor'];
				echo '<td align="right">'.number_format($row1['valor'],2).'</td>';
				}
				}
				
				echo '</tr>';
			}
			echo '	
				<tr bgcolor="#E9F2F8">
				<td colspan="" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right">Total</td>
				<td align="right">'.number_format($array_total[1],2).'</td>
				<td align="right">'.number_format($array_total[2],2).'</td>
				<td align="right">'.number_format($array_total[3],2).'</td>
				<td align="right">'.number_format($array_total[4],2).'</td>
				<td align="right">'.number_format($array_total[5],2).'</td>
				<td align="right">'.number_format($array_total[6],2).'</td>
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
if ($_POST['cmd']==3) {
	$delete= "UPDATE totales_conceptos SET estatus='C',usu_can='".$_POST['cveusuario']."',fecha_can='".fechaLocal()."',hora_can='".horaLocal()."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	$_POST['cmd']=0;
}

if ($_POST['cmd']==2) {
	if($_POST['reg']>0) {
		
	foreach($array_cargo as $k=>$v){	
		$res = mysql_query("SELECT * FROM totales_conceptos_mov WHERE aux='".$_POST['reg']."'");
		$row = mysql_fetch_array($res);
		if($row['opc']==$k and $row['valor']!=$_POST["opc_".$k.""]){
			mysql_query("INSERT totales_conceptos_hist SET cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='".$k."',nuevo='".$_POST["opc_".$k.""]."',anterior='".$row['valor']."',arreglo='array_cargo',usuario='".$_POST['cveusuario']."'") or die(mysql_error());
		
			$update = " UPDATE totales_conceptos_mov 
					SET valor='".$_POST["opc_".$k.""]."'
					WHERE aux='".$_POST['reg']."' and opc='".$k."' " ;
			$ejecutar = mysql_query($update);
		}
		
	
	}
		/*//Actualizar el Registro
		$update = " UPDATE beneficiarios_salidas 
					SET nombre='".$_POST['nombre']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);*/			
		$id=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT totales_conceptos 
					SET fecha='".fechaLocal()."',hora='".horaLocal()."',usuario='".$_POST['cveusuario']."', estatus='A',fecha_can='".fechaLocal()."',hora_can='".horaLocal()."',usu_can='".$_POST['cveusuario']."'";
		$ejecutar = mysql_query($insert);
		$id = mysql_insert_id();
		foreach($array_cargo as $k=>$v){
			$insert = " INSERT totales_conceptos_mov 
					SET aux='".$id."',opc='".$k."',valor='".$_POST["opc_".$k.""]."'";
			$ejecutar = mysql_query($insert);
		
		}
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
		$select=" SELECT * FROM totales_conceptos WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'totales_conceptos.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'totales_conceptos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion de Total de Conceptos</td></tr>';
		echo '</table>';

		//Formulario 
		//echo '<table width="100%"><tr><td>';
		echo '<table>';
//		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" class="textField" size="100" value="'.$row['nombre'].'"></td></tr>';
		if($_POST['reg']>0){
			$select=" SELECT * FROM totales_conceptos_mov WHERE aux='".$_POST['reg']."' ";
					$res=mysql_query($select);
				while($row1=mysql_fetch_array($res)){
					echo '<tr><th>'.$array_cargo[$row1['opc']].'</th><td><input type="text" name="opc_'.$row1['opc'].'" id="opc_'.$row1['opc'].'" class="textField" size="" value="'.number_format($row1['valor'],2).'"></td></tr>';
				}
		}else {
			foreach($array_cargo as $k=>$v){
					echo '<tr><th>'.$v.'</th><td><input type="text" name="opc_'.$k.'" id="opc_'.$k.'" class="textField" size="" value="'.number_format($row['nombre'][$k],2).'"></td></tr>';
			
		}
		}
		
		
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
				<td><a href="#" onClick="atcr(\'totales_conceptos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr style="display:none"><td>Nombre</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField"></td></tr>';	
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
			objeto.open("POST","totales_conceptos.php",true);
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

