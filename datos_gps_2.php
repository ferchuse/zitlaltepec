<?php 

include ("main.php"); 

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
	
		//Listado de tecnicos y administradores
		$select= " SELECT * FROM datos_alarm WHERE left(time,10) between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'";
		if ($_POST['nombre']!="") { $select.=" AND tired LIKE '%".$_POST['nombre']."%'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY time desc";
		
//		$fecha=$_POST['fecha_ini'];
//		$fecha1= date("Y-m-d",strtotime($fecha."- 1 days")); 
//		echo $fecha1;
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="14">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th width="">tired</th><th>gpstime</th><th>altitude</th><th>direction</th><th>gpslat</th><th>gpslng</th>
				  <th>speed</th><th>recordspeed</th><th>state</th><th>time</th><th>type</th><th>content</th><th>cmdtype</th><th>alarmid</th></tr>';
			while($row=mysql_fetch_array($res)) {
				rowb();
//				echo '<td align="left"><a href="#" onClick="atcr(\'cat_estatus.php\',\'\',1,\''.$row['cve'].'\')"><img src="images/modificar.gif" border="0" title="Editar"></a></td>';
				echo '<td align="left">'.$row[1].'</td>';
				echo '<td align="left">'.$row[2].'</td>';
				echo '<td align="left">'.$row[3].'</td>';
				echo '<td align="left">'.$row[4].'</td>';
				echo '<td align="left">'.$row[5].'</td>';
				echo '<td align="left">'.$row[6].'</td>';
				echo '<td align="left">'.$row[7].'</td>';
				echo '<td align="left">'.$row[8].'</td>';
				echo '<td align="left">'.$row[9].'</td>';
				echo '<td align="left">'.$row[10].'</td>';
				echo '<td align="left">'.$row[11].'</td>';
				echo '<td align="left">'.$row[12].'</td>';
				echo '<td align="left">'.$row[13].'</td>';
				echo '<td align="left">'.$row[14].'</td>';
				
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="14" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
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
		echo '<tr style="display:none"><td>Economico</td><td><input type="text" name="nombre" id="nombre" size="30" class="textField"></td></tr>';	
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
			objeto.open("POST","datos_gps_2.php",true);
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
		
	function Datos(reg){
				
				$.ajax({
				  url: "cat_clientes.php",
				  type: "POST",
				  async: false,
				  data: {
					rfc: document.getElementById("rfc").value,
					cliente: reg,
					plazausuario: document.forma.plazausuario.value,
					ajax: 2
				  },
					success: function(data) {
						if(data == "no"){
							atcr(\'cat_clientes.php\',\'\',2,reg);
						}
						else{
							$(\'#panel\').hide();
							alert("Ya esta dado de alta el rfc");
						}
					}
				});
			}
			
			
			
	</Script>
';
	}
	
bottom();
?>

