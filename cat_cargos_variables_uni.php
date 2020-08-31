<?php
include ("main.php"); 

/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_query("SELECT * FROM plazas ORDER BY nombre");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$tipo_variable=array("G"=>"Garantia","R"=>"Recuperacion");
if($_POST['cmd']==2){
	if ($_POST['reg']>0){ 
		$sSQL="UPDATE cat_cargos_unidadeszitla
				SET nombre='".$_POST['nombre']."',tipo_variable='".$_POST['tipo_variable']."',orden='99'
				WHERE cve='".$_POST['reg']."'";
		mysql_query($sSQL);
	}
	else{
		$sSQL="INSERT cat_cargos_unidadeszitla
				SET nombre='".$_POST['nombre']."',plaza='".$_POST['plaza']."',tipo='V',tipo_variable='".$_POST['tipo_variable']."',orden='99'";
		mysql_query($sSQL);
	}
	
	header("Location: cat_cargos_variables_uni.php");
}

if($_POST['ajax']==1){
	$select= " SELECT * FROM cat_cargos_unidadeszitla WHERE tipo='V' ";
	if ($_POST['plaza']!="all") { $select.=" AND plaza='".$_POST['plaza']."'"; }
	$select.=" ORDER BY nombre";
	$rscargos=mysql_query($select);
	if(mysql_num_rows($rscargos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="4">'.mysql_num_rows($rscargos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>Editar</th>';
		if($_SESSION['PlazaUsuario']==0) echo '<th>Plaza</th>';
		echo '<th>Nombre</th><th>Tipo</th>';
		echo '</tr>';
		$i=0;
		while($Cargo=mysql_fetch_array($rscargos)) {
			rowb();
			echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'cat_cargos_variables_uni.php\',\'\',\'1\','.$Cargo['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Parque['nombre'].'"></a></td>';
			
			if($_SESSION['PlazaUsuario']==0)
				echo '<td>'.htmlentities($array_plaza[$Cargo['plaza']]).'</td>';
			
			echo '<td align="left">'.$Cargo['nombre'].'</td>';
			echo '<td align=center>'.$tipo_variable[$Cargo["tipo_variable"]].'</td>';	
			echo '</tr>';
			$i++;
		}
		
		echo '	
			<tr>
			<td colspan="4" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
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

if($_POST['cmd']==1){
	$select=" SELECT * FROM cat_cargos_unidadeszitla WHERE cve='".$_POST['reg']."' ";
	$rscargos=mysql_query($select);
	$Cargo=mysql_fetch_array($rscargos);
	
		echo '<table>';
		echo '
			<tr>';
			//if($_SESSION[$archivo[(count($archivo)-1)]]>1)
			if(nivelUsuario()>1){
			
				echo '<td><a id="guarda" href="#" onClick="
				if(document.forma.plaza.value==\'0\')
					alert(\'Necesita seleccionar la plaza\');
				else if(document.forma.nombre.value==\'\')
					alert(\'Necesita ingresar el nombre\');
				else 
					checkSubmit();
					atcr(\'cat_cargos_variables_uni.php\',\'\',\'2\',\''.$Cargo['cve'].'\');"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			
			}
		
			echo '<td><a href="#" onClick="atcr(\'cat_cargos_variables_uni.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Catálogo de Cargos Variables de Unidades</td></tr>';
		echo '</table>';
		
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0 && $_POST['reg']==0){
			echo '<tr><th align="left">Plaza</th><td><select name="plaza" id="plaza"><option value="0">---Seleccione una Plaza---</option>';
			$rsParque=mysql_query("SELECT * FROM plazaslusa ORDER BY nombre");
			while($Plaza=mysql_fetch_array($rsParque)){
				echo '<option value="'.$Plaza['cve'].'"';
				if($Parque['plaza']==$Plaza['cve']) echo ' selected';
				echo '>'.$Plaza['nombre'].'</option>';
			}
			echo '</select></td></tr>';
		}
		elseif($_POST['reg']>0){
			echo '<tr><th align="left">Plaza</th><td><input type="hidden" name="plaza" id="plaza" value="'.$Cargo['plaza'].'"><b>'.$array_plaza[$Cargo['plaza']].'</b></td></tr>';
		}
		else{
			echo '<input type="hidden" name="plaza" id="plaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		if($_POST['reg']>0)
			echo '<tr><th align="left">Nombre</th><td colspan=3><input type="text" name="nombre" id="nombre" size="50" class="readOnly" value="'.$Cargo["nombre"].'" readonly></td>';
		else
			echo '<tr><th align="left">Nombre</th><td colspan=3><input type="text" name="nombre" id="nombre" size="50" class="textField" value="'.$Cargo["nombre"].'"></td>';
		
		echo '<tr><th align="left">Tipo</th><td colspan="3"><input type="radio" name="tipo_variable" value="R" class="textField"';
		if($Cargo['tipo_variable']!="G") echo ' checked';
		echo '>Recuperacion&nbsp;&nbsp;<input type="radio" name="tipo_variable" value="G" class="textField"';
		if($Cargo['tipo_variable']=="G") echo ' checked';
		echo '>Garantia</td></tr>';
		//echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha"  size="15" value="'.$Cargo['fecha'].'" class="readOnly" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '</table>';
		echo'<Script language="javascript">
			    function checkSubmit() {
			$(\'#guarda\').hide();
          }
			 </script>';
}

if($_POST['cmd']<1){
	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onClick="atcr(\'cat_cargos_variables_uni.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField" onChange="buscarRegistros();"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select></td><td></td><td>&nbsp;</td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}

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
			objeto.open("POST","cat_cargos_variables_uni.php",true);
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
	
	';	
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