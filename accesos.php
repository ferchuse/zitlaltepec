<?php 

include ("main.php"); 

$array_recaudacion=array();
$res=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_recaudacion[$row['cve']]=$row['nombre'];
}
$array_tipo_taquilla=array(1=>'Administrador', 2=>'Vendedor', 3=>'Administrador Taquilla sin Guia', 4=>'Vendedor Taquilla sin Guia');
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		if($_SESSION['CveUsuario']>1){
			$select= " SELECT * FROM usuarios WHERE estatus!='I' AND cve>'1'";
		}
		else{
			$select= " SELECT * FROM usuarios WHERE estatus!='I'";
		}
		if ($_POST['nom']!="") { $select.=" AND nombre LIKE '%".$_POST['nom']."%' "; }
		if ($_POST['usuario']!="") { $select.=" AND usuario = '".$_POST['usuario']."' "; }
		$rsusuarios=mysql_query($select);
		$totalRegistros = mysql_num_rows($rsusuarios);
		$select .= " ORDER BY nombre";
		$rsusuarios=mysql_query($select);
		
		if(mysql_num_rows($rsusuarios)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="4">'.mysql_num_rows($rsusuarios).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Accesos</th><th>Nombre</th><th>Usuario</th><th>Borrar</th></tr>';
			while($Usuario=mysql_fetch_array($rsusuarios)) {
				rowb();
				if($Usuario['cve']==1 && $_SESSION['CveUsuario']!=1)
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				else
					echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'\',\'\',\'1\','.$Usuario['cve'].')"><img src="images/key.png" border="0" title="Editar '.$Usuario['nombre'].'"></a></td>';
				$extra="";
				if($Usuario['estatus']=="I")
					$extra=" (INACTIVO)";
				echo '<td title="'.$title.'">'.htmlentities(utf8_encode($Usuario['nombre'])).$extra.'</td>';
				echo '<td>'.htmlentities($Usuario['usuario']).'</td>';
				if($Usuario['cve']==1)
					echo '<td align="center" width="40" nowrap>&nbsp;</td>';
				else
					echo '<td align="center" width="40" nowrap><a href="#" onClick="borrar('.$Usuario['cve'].')"><img src="images/basura.gif" border="0" title="Borrar '.$Usuario['nombre'].'"></a></td>';
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="4" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
	$res=mysql_query("SELECT * FROM usuarios WHERE usuario='".$_POST['usuario']."' AND estatus!='I' AND cve!='".$_POST['cveusu']."'");
	if(mysql_num_rows($res)>0){
		echo "1";
	}
	else{
		echo "0";
	}
	exit();
}

top($_SESSION);

/*** ELIMINAR REGISTRO  **************************************************/

if ($_POST['cmd']==3) {
	$delete= "UPDATE usuarios SET estatus='I' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Estatus',nuevo='I',anterior='A',arreglo='',usuario='".$_POST['cveusuario']."'");
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
	if($_POST['reg']) {
		$select=" SELECT * FROM usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		if($Usuario['nombre']!=$_POST['nombre']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Nombre',nuevo='".$_POST['nombre']."',anterior='".$Usuario['nombre']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['password']!=$_POST['password']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Password',nuevo='".$_POST['password']."',anterior='".$Usuario['password']."',arreglo='',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['recaudacion']!=$_POST['recaudacion']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Recaudacion',nuevo='".$_POST['recaudacion']."',anterior='".$Usuario['recaudacion']."',arreglo='array_recaudacion',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['permisionario']!=$_POST['permisionario']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Permisionario',nuevo='".$_POST['permisionario']."',anterior='".$Usuario['permisionario']."',arreglo='array_recaudacion',usuario='".$_POST['cveusuario']."'");
		}
		if($Usuario['tipo_taquilla']!=$_POST['tipo_taquilla']){
			mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
			dato='Tipo Usuario Taquilla',nuevo='".$_POST['tipo_taquilla']."',anterior='".$Usuario['tipo_taquilla']."',arreglo='array_tipo_taquilla',usuario='".$_POST['cveusuario']."'");
		}
		
		if($_POST['plazausuario']>0){
			$res = mysql_query("SELECT * FROM menu WHERE cve>1 AND modulo > 0 ORDER BY cve");
			while($row = mysql_fetch_array($res)){
				$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$_POST['reg']."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
				$row1=mysql_fetch_array($res1);
				if($row1['acceso']!=$_POST['acceso'.$row['cve']]){
					mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$_POST['reg']."',fecha='".fechaLocal()." ".horaLocal()."',
					dato='".$row['cve']."',nuevo='".$_POST['acceso'.$row['cve']]."',anterior='".$row1['acceso']."',arreglo='',usuario='".$_POST['cveusuario']."'");
				}
			}
		}
		
		
		//Actualizar el Registro
			$update = " UPDATE usuarios 
					SET nombre='".$_POST['nombre']."',password='".$_POST['password']."',cerrar_sistema='".$_POST['cerrar_sistema']."',
					plaza='".$_POST['plaza']."',autoriza_vales='".$_POST['autoriza_vales']."',tipo='".$_POST['tipo']."',verificentro='".$_POST['verificentro']."',
					chat='".$_POST['chat']."',ide='".$_POST['ide']."',categoria='".$_POST['categoria']."',validar_huella='".$_POST['validar_huella']."',
					recaudacion='".$_POST['recaudacion']."',permisionario='".$_POST['permisionario']."',tipo_taquilla='".$_POST['tipo_taquilla']."'
					WHERE cve='".$_POST['reg']."' " ;
		$ejecutar = mysql_query($update);
		$cveusu=$_POST['reg'];
	} else {
		//Insertar el Registro
		$insert = " INSERT INTO usuarios (nombre,usuario,password,plaza,cerrar_sistema,estatus,autoriza_vales,tipo,verificentro,chat,ide,categoria,recaudacion,permisionario,tipo_taquilla)
					VALUES 
					( '".$_POST['nombre']."','".$_POST['usuario']."','".$_POST['password']."','".$_POST['plaza']."','".$_POST['cerrar_sistema']."','A',
					'".$_POST['autoriza_vales']."','".$_POST['tipo']."','".$_POST['verificentro']."','".$_POST['chat']."','".$_POST['ide']."','".$_POST['categoria']."',
					'".$_POST['recaudacion']."','".$_POST['permisionario']."','".$_POST['tipo_taquilla']."')";
		$ejecutar = mysql_query($insert) or die(mysql_error());
		$cveusu=mysql_insert_id();
		mysql_query("INSERT historial SET menu='".$_POST['cvemenu']."',cveaux='".$cveusu."',fecha='".fechaLocal()." ".horaLocal()."',
		dato='Estatus',nuevo='A',anterior='',arreglo='',usuario='".$_POST['cveusuario']."'");
	}
	
	if($_POST['plazausuario']>0){
	
		$res = mysql_query("SELECT * FROM menu WHERE modulo > 0 ORDER BY cve");
		while($row = mysql_fetch_array($res)){
			$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$cveusu."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
			if($row1=mysql_fetch_array($res1)){
				mysql_query("UPDATE usuario_accesos SET acceso='".$_POST['acceso'.$row['cve']]."' WHERE cve='".$row1['cve']."'");
			}
			else{
				mysql_query("INSERT usuario_accesos SET usuario='".$cveusu."',menu='".$row['cve']."',acceso='".$_POST['acceso'.$row['cve']]."',plaza='".$_POST['plazausuario']."'");
			}
		}
	}
	$_POST['cmd']=0;
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM usuarios WHERE cve='".$_POST['reg']."' ";
		$rssuario=mysql_query($select);
		$Usuario=mysql_fetch_array($rssuario);
		$array1=explode(",",$Usuario['accesos']);
		for($i=0;$i<count($array1)-1;$i++){
			$array2=explode("-",$array1[$i]);
			$accesos[$array2[0]]=$array2[1];
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
		if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();validarUsuario('.$_POST['reg'].');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		echo '
			<td><a href="#" onClick="$(\'#panel\').show();atcr(\'accesos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Permisos</td></tr>';
		echo '</table>';

		//Formulario 
		echo '<table>';
		
		echo '<tr><th>Nombre</th><td><input type="text" name="nombre" id="nombre" value="'.$Usuario['nombre'].'" size="40" class="textField"></td></tr>';
		if($_POST['reg']>0 && $_POST['cveusuario']!=1){
			echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="readOnly" readOnly></td></tr>';
		}
		else{
			echo '<tr><th>Usuario</th><td><input autocomplete="off" type="text" name="usuario" id="usuario" value="'.$Usuario['usuario'].'" class="textField"></td></tr>';
		}
		echo '<tr><th>Password</th><td><input autocomplete="off" type="password" name="password" id="password" value="'.$Usuario['password'].'" class="textField"></td></tr>';
		if($Usuario['cve']==1 && $_SESSION['CveUsuario']==1){
			echo '<tr><th>Cerrar Sistema</th><td><input type="checkbox" name="cerrar_sistema" value="S"';
			if($Usuario['cerrar_sistema']=='S') echo ' checked';
			echo '></td></tr>';
		}
		echo '<tr><th>Recaudacion</th><td><select name="recaudacion" id="recaudacion"><option value="0">Seleccione</option>';
		foreach($array_recaudacion as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['recaudacion']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Permisionario</th><td><select name="permisionario" id="permisionario"><option value="0">Seleccione</option>';
		$res = mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
		while($row = mysql_fetch_array($res))
		{
			echo '<option value="'.$row['cve'].'"';
			if($row['cve']==$Usuario['permisionario']) echo ' selected';
			echo '>'.$row['nombre'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Tipo Usuario Taquilla</th><td><select name="tipo_taquilla" id="tipo_taquilla"><option value="0">Seleccione</option>';
		foreach($array_tipo_taquilla as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['tipo_taquilla']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th>Plaza</th><td><select name="plaza" id="plaza">';
		foreach($array_plaza as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['plaza']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th>Tipo</th><td><select name="tipo" id="tipo" onChange="mostrar_tipo()">';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['tipo']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;" class="tipo_3"><th>Verificentro</th><td><select name="verificentro" id="verificentro"><option value="0">Seleccione</option>';
		foreach($array_verificentros as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['verificentro']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th>Categoria</th><td><select name="categoria" id="categoria">';
		foreach($array_tipo as $k=>$v)
		{
			echo '<option value="'.$k.'"';
			if($k==$Usuario['categoria']) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr style="display:none;"><th>Chat</th><td><input type="checkbox" name="chat" value="1"';
		if($Usuario['chat']=='1') echo ' checked';
		echo '></td></tr>';
		echo '<tr style="display:none;"><th>IDE</th><td><input type="text" name="ide" id="ide" value="'.$Usuario['ide'].'" size="50" class="textField"></td></tr>';
		if($_POST['cveusuario']==1 && $Usuario['cve']==1){
			echo '<tr style="display:none;"><th>Validar Huella</th><td><input type="checkbox" name="validar_huella" value="1"';
			if($Usuario['validar_huella']=='1') echo ' checked';
			echo '></td></tr>';
		}
		else{
			echo '<input type="hidden" name="validar_huella" value="'.$Usuario['validar_huella'].'">';
		}
		echo '</table>';
		if($_POST['plazausuario']>0){
			echo '<table width="70%">';		
			echo '<tr><th colspan="5" align="left"><br>Accesos</th></tr>';
			foreach($array_modulos as $k=>$v){
				//if($_POST['cveusuario']==1 || $k<99){
					echo '<tr><th colspan="5" align="left"><hr></th></tr>';
					echo '<tr><th colspan="5" align="left">'.$v.'</th></tr>';
					echo '<tr><th>Modulo</th><th>Sin Acceso</th><th>Lectura</th><th>Escritura</th><th>Supervisor</th></tr>';
					$res = mysql_query("SELECT * FROM menu WHERE modulo='$k' ORDER BY orden");
					while($row = mysql_fetch_array($res)){
						$res1=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$_POST['reg']."' AND menu='".$row['cve']."' AND plaza='".$_POST['plazausuario']."'");
						$row1=mysql_fetch_array($res1);
						rowb();
						echo '<td>'.$row['nombre'].'</td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="0"';
						if(intval($row1['acceso'])<1) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="1"';
						if(intval($row1['acceso'])==1) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="2"';
						if(intval($row1['acceso'])==2) echo ' checked'; 
						echo '></td>';
						echo '<td align="center"><input type="radio" name="acceso'.$row['cve'].'" value="3"';
						if(intval($row1['acceso'])==3) echo ' checked';
						echo '></td>';
					}
				//}
			}
		}
		echo '</table>';
		
		if($_POST['reg']==0){
			echo '<script>
			window.onload = function () {
				document.forma.usuario.value="";
				document.forma.password.value="";
			}
			</script>';
		}
		echo '<script>
				function mostrar_tipo(){
					$(".tipo_1").hide();
					$(".tipo_2").hide();
					$(".tipo_3").hide();
					$(".tipo_"+document.forma.tipo.value).show();
				}

				function validarUsuario(reg)
				{
					if(document.forma.tipo.value=="3" && document.forma.verificentro.value=="0"){
						$("#panel").hide();
						alert("Necesita seleccionar un verificentro");
					}
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","accesos.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=2&cveusu="+reg+"&usuario="+document.getElementById("usuario").value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4)
								{
									if(objeto.responseText=="1"){
										$("#panel").hide();
										alert("El usuario ya esta registrado");
									}
									else{
										atcr("accesos.php","",2,reg);
									}
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
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'accesos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><td>Nombre</td><td><input type="text" name="nom" id="nom" size="30" class="textField"></td></tr>';	
		echo '<tr><td>Usuario</td><td><input type="text" name="usuario" id="usuario" size="15" class="textField"></td></tr>';	
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
					objeto.open("POST","accesos.php",true);
					objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
					objeto.send("ajax=1&usuario="+document.getElementById("usuario").value+"&nom="+document.getElementById("nom").value+"&numeroPagina="+document.getElementById("numeroPagina").value);
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
			</Script>';
	}
	
bottom();





?>

