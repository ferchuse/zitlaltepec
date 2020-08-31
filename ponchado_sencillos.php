<?php
include("main.php");
$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_taquilla=array();
$res=mysql_query("SELECT * FROM taquillas_sencillos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_taquilla[$row['cve']]=$row['nombre'];
}

$array_empresas=array();
$res=mysql_query("SELECT * FROM empresas_sencillos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_empresas[$row['cve']]=$row['nombre'];
}

$res=mysql_query("SELECT * FROM costo_boletos_sencillos ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_costo[$row['cve']]=$row['nombre'];
}


if($_POST['ajax']==1){
	$nivelUsuario = nivelUsuario();
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Folio</th>';
	echo '<th>Fecha</th><th>Hora</th><th>Empresas</th><th>Boleto</th><th>Importe</th><th>Usuario</th>
	</tr>'; 
	$x=0;
	$select = "SELECT * FROM ponchado_sencillos WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
	if($_POST['empresa'] > 0){
		$select .= " AND empresa='".$_POST['empresa']."'";
	}
	$select .= " ORDER BY cve DESC";

	$res=mysql_query($select) or die(mysql_error());
	$totales=array();
	while($row=mysql_fetch_array($res)){
		rowb();
		$factor=1;
		echo '<td align="center">';
		if($row['estatus']=='C'){
			echo 'CANCELADO';
			$factor=0;
		}
		else{
			if($nivelUsuario > 2){
				echo '<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro?\')) cancelar(\''.$row['cve'].'\')"><img src="images/validono.gif" border="0" title="Cancelar"></a>&nbsp;&nbsp;';
			}
			else{
				echo '<a href="#" onClick="atcr(\'ponchado_sencillos.php\',\'_blank\',101,'.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir"></a>';
			}
		}
		echo '</td>';
		echo '<td align="center">'.$row['cve'].'</td>';
		echo '<td align="center">'.$row['fecha'].'</td>';
		echo '<td align="center">'.$row['hora'].'</td>';
		echo '<td align="center">'.utf8_encode($array_empresas[$row['empresa']]).'</td>';
		$c=0;
		echo '<td align="right">'.number_format($row['cantidad']*$factor,0).'</td>';
		$totales[$c]+=round(1*$factor,2);$c++;
		echo '<td align="right">'.number_format($row['monto']*$factor,2).'</td>';
		$totales[$c]+=round($row['monto']*$factor,2);$c++;
		echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
		echo '</tr>';
		$x++;
	}
	echo '<tr bgcolor="#E9F2F8"><th colspan="5" align="left">'.$x.' Registro(s)</th>';
	foreach($totales as $total)
		echo '<th align="right">'.number_format($total,2).'</th>';
	echo '<th>&nbsp;</th></tr>';
	echo '</table>';
	exit();
}

if($_POST['ajax']==2){
	$taq = intval(substr($_POST['boleto'],1,2));
	$costo = intval(substr($_POST['boleto'],3,4));
	$folio = intval(substr($_POST['boleto'],7,7));
	$resultado = array('error' => 0, 'mensaje' => '', 'html' => '');
	$res = mysql_query("SELECT *, DATEDIFF(CURDATE(), fecha) as dias FROM boletos_sencillos WHERE taquilla = '$taq' AND folio='$folio'");
	if($row = mysql_fetch_array($res)){
		if($row['folio_recaudacion'] > 0){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto ya se poncho';
		}
		elseif($row['estatus']==1){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto esta cancelado';
		}
		elseif($row['dias']>5){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto '.$boleto['folio'].' esta caducado';
		}
		else{
			$resultado['html'] .= rowb(false);
			$resultado['html'] .= '<td align="center">';
			$resultado['html'] .= '<a href="#" class="aboletos" onClick="quitar_boleto($(this))" taquilla="'.$row['taquilla'].'" folio="'.$row['folio'].'" monto="'.$row['monto'].'"><img src="images/validono.gif" border="0" title="Quitar"></a>';
			$resultado['html'] .= '</td>';
			$resultado['html'] .= '<td align="left">'.utf8_encode($array_taquilla[$row['taquilla']]).'</td>';
			$resultado['html'] .= '<td align="center">'.$row['folio'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['fecha'].'</td>';
			$resultado['html'] .= '<td align="center">'.$row['hora'].'</td>';
			$resultado['html'] .= '<td align="center">'.utf8_encode($array_costo[$row['costo']]).'</td>';
			$resultado['html'] .= '<td align="right">'.number_format($row['monto'],2).'</td>';
			$resultado['html'] .= '</tr>';
		}
	}
	else{
		$resultado['error'] = 1;
		$resultado['mensaje'] = 'No se encontro el boleto';
	}
	echo json_encode($resultado);
	exit();
}

if($_POST['ajax']==3){
	mysql_query("UPDATE ponchado_sencillos SET estatus='C',fechacan='NOW()',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['cveponchado']."'");
	mysql_query("UPDATE boletos_sencillos SET folio_recaudacion=0, fecha_recaudacion='0000-00-00', tipo_recaudacion=0 WHERE folio_recaudacion='".$_POST['cveponchado']."' AND tipo_recaudacion=0");
	exit();
}

if($_POST['ajax']==4){
	$resultado = array('error' => 0, 'mensaje' => '');
	$boletos = json_decode($_POST['boletos'], true);
	foreach($boletos as $boleto){
		$res = mysql_query("SELECT *, DATEDIFF(CURDATE(), fecha) as dias FROM boletos_sencillos WHERE taquilla = '".$boleto['taquilla']."' AND folio='".$boleto['folio']."'");
		$row = mysql_fetch_array($res);
		if($row['folio_recaudacion'] > 0){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'El boleto '.$boleto['folio'].' ya se poncho'."\n";
		}
		elseif($row['dias']>5){
			$resultado['error'] = 1;
			$resultado['mensaje'] = 'El boleto '.$boleto['folio'].' esta caducado';
		}
		elseif($row['estatus']==1){
			$resultado['error'] = 1;
			$resultado['mensaje'] .= 'El boleto '.$boleto['folio'].' esta cancelado'."\n";
		}
	}
	echo json_encode($resultado);
	exit();
}

top($_SESSION);

if($_POST['cmd']==2){
	$res=mysql_query("SELECT * FROM ponchado_sencillos ORDER BY cve DESC");
	$row = mysql_fetch_array($res);
	if($row['estatus']=='C' || $_POST['empresa'] != $row['empresa'] || $_POST['monto'] != $row['monto'] || $_POST['cantidad'] == $row['cantidad']){
		mysql_query("INSERT ponchado_sencillos SET fecha='".$_POST['fecha']."',hora=CURTIME(), empresa='".$_POST['empresa']."', cantidad='".$_POST['cantidad']."', monto='".$_POST['monto']."', usuario='".$_POST['cveusuario']."', estatus='A', boletos='".addslashes($_POST['boletos'])."'");
		$folio_recaudacion = mysql_insert_id();
		$boletos = json_decode($_POST['boletos'], true);
		foreach($boletos as $boleto){
			mysql_query("UPDATE boletos_sencillos SET folio_recaudacion='$folio_recaudacion', fecha_recaudacion='".$_POST['fecha']."', tipo_recaudacion=0 WHERE taquilla = '".$boleto['taquilla']."' AND folio='".$boleto['folio']."'");
		}
	}
	$_POST['cmd']=0;
}


if($_POST['cmd']==1){

	echo '<table>';
	echo '
		<tr>';
	if(nivelUsuario()>1)
		echo '<td><a href="#" onClick="$(\'#panel\').show();if(validarboletos()){atcr(\'ponchado_sencillos.php\',\'\',\'2\',\''.$row['cve'].'\');}"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
	echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'ponchado_sencillos.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<br>';
	
	//Formulario 
	echo '<table>';
	echo '<tr><td class="tableEnc">Ponchado de Boletos sin Guia</td></tr>';
	echo '</table>';

	//Formulario 
	//echo '<table width="100%"><tr><td>';
	echo '<table>';
	echo '<tr><th align="left">Fecha</th><td><input type="text" class="readOnly" name="fecha" id="fecha" value="'.fechaLocal().'" size="15" readOnly>';
	if(nivelUsuario()>2){
		echo'&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>';
	}
	echo'</td></tr>';
	echo '<tr><th align="left">Empresa</th><td><select name="empresa" id="empresa">';
	echo '<option value="0">Seleccione</option>';
	foreach($array_empresas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th>Cantidad</th><td><input type="text" class="readOnly" name="cantidad" id="cantidad" value="" size="15" readOnly></td></tr>';
	echo '<tr><th>Importe</th><td><input type="text" class="readOnly" name="monto" id="monto" value="" size="15" readOnly></td></tr>';
	echo '<tr><th>Boleto</th><td><input type="text" class="textField" id="boleto" onpaste="return false" autocomplete="off" value="" onKeyPress="
		if(document.getElementById(\'capturado\').value == 0){
			setTimeout(\'limpiarboleto()\',2000);
			document.getElementById(\'capturado\').value = 1;
		}
		if(event.keyCode==13){
			agregarBoleto();
		}"></td></tr>';
	echo '</table>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tablaboletos">';
	echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
	echo '<th>Taquilla</th><th>Folio</th>';
	echo '<th>Fecha</th><th>Hora</th><th>Boleto</th><th>Costo</th><th>Usuario</th>
	<th>Folio Recaudacion</th><th>Fecha Recaudacion</th>
	</tr></table>'; 
	echo '<textarea style="display:none;" name="boletos" id="boletos"></textarea>';
	echo '<input type="hidden" id="capturado" value="0">';

	echo '<script>
			document.getElementById(\'boleto\').focus();
			function limpiarboleto(){
				document.getElementById(\'capturado\').value = 0;
				document.getElementById(\'boleto\').value = "";
				document.getElementById(\'boleto\').focus();
			}

			function agregarBoleto(){
				$.ajax({
				  url: "ponchado_sencillos.php",
				  type: "POST",
				  async: false,
				  dataType: "json",
				  data: {
					boleto: document.getElementById("boleto").value,
					ajax: 2
				  },
					success: function(data) {
						if(data.error == 1){
							alert(data.mensaje);
						}
						else{
							$("#tablaboletos").append(data.html);
						}

						document.getElementById(\'capturado\').value = 0;
						document.getElementById(\'boleto\').value = "";
						document.getElementById(\'boleto\').focus();
						calcular();
					}
				});
			}

			function validarboletos(){
				boletos = [];
				$(".aboletos").each(function(){
					campo = $(this);
					boleto = {};
					boleto.taquilla = campo.attr("taquilla");
					boleto.folio = campo.attr("folio");
					boleto.monto = campo.attr("monto");
					boletos.push(boleto);
				});
				document.forma.boletos.value = JSON.stringify(boletos);
				regresar = true;
				$.ajax({
				  url: "ponchado_sencillos.php",
				  type: "POST",
				  async: false,
				  dataType: "json",
				  data: {
					boletos: document.getElementById("boletos").value,
					ajax: 4
				  },
					success: function(data) {
						if(data.error == 1){
							alert(data.mensaje);
							regresar = false;
							$("#panel").hide();
						}
					}
				});
				return regresar;
			}

			function quitar_boleto(aref){
				aref.parents("tr:first").remove();
				calcular();
				document.getElementById(\'boleto\').focus();
			}

			function calcular(){
				cantidad=0;
				monto = 0;
				$(".aboletos").each(function(){
					monto += $(this).attr("monto")/1;
					cantidad++;
				});
				document.forma.cantidad.value=cantidad.toFixed(0);
				document.forma.monto.value=monto.toFixed(2);
			}
			</script>';
}


if($_POST['cmd']==0){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
			<td><a href="#" onClick="atcr(\'ponchado_sencillos.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="0">--- Todos ---</option>';
	foreach($array_empresas as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="Resultados">';
	echo '</div>';

echo '
<Script language="javascript">

	function buscarRegistros()
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ponchado_sencillos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&empresa="+document.getElementById("empresa").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}

	function cancelar(cveponchado)
	{
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","ponchado_sencillos.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=3&cveponchado="+cveponchado+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{buscarRegistros();}
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
}
bottom();

?>