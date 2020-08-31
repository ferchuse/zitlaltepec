<?php
include("main.php");
  function lastday() { 
      $month = date('m');
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
  };

  /** Actual month first day **/
  function firstday() {
      $month = date('m');
      $year = date('Y');
      return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
  }
$array_empresa=array();
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_empresa[$row['cve']]=$row['nombre'];
	$array_empresalogo[$row['cve']]=$row['logo'];
}

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios order by nombre");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}
$rsMotivo=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_recaudacion[$Motivo['cve']]=$Motivo['nombre'];
}
//
$rsMotivo=mysql_query("SELECT * FROM tipo_sector ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_tipo_sector[$Motivo['cve']]=utf8_encode($Motivo['nombre']);
}
$rsMotivo=mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_permisionarios[$Motivo['cve']]=$Motivo['nombre'];
}
//19-01-2017
$rsBenef=mysql_query("SELECT * FROM beneficiarios_salidas ORDER BY nombre");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_beneficiario[$Benef['cve']]=$Benef['nombre'];
}
$res=mysql_db_query($base,"SELECT * FROM taquillas ORDER BY nombre");

//$denominaciones=array(1000,500,200,100,50,20,10,5,2,1,0.50,0.20,0.10,0.05,"Documentos","Boletos","Cheques");
$denominaciones=array(1000,500,200,100,50,20,10,5,2,1,0.50,0.20,0.10,0.05);
$array_cargo = array(1=>'Administracion', 2=>'Seguro Interno', 3=>'Mutualidad', 4=>'Prorrata',5=>'Seguridad', 6=>'Fianza');

if($_POST['cmd']==100){
	$filename = "Edo De Cuenta de Caja.xls";
        header("Content-type: application/octet-stream");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"$filename\"\n");
		
		echo '<table width="100%" border="0" cellpadding="" cellspacing="" class="">';
		echo'<tr><td colspan="11" align="center" style="font-size:28px">Estado de Cuenta de Caja</td></tr>';
		echo'<tr><td style="font-size:18px" align="left" colspan="6">Periodo del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'].'</td></tr>
			<tr><td>&nbsp;</td></tr>';
		
		echo'</table>';
		echo '<table><tr><td><table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolr="#E9F2F8"><th width="">Concepto</th><th>Fecha</th><th width="">Sector</th><th>Abono</th><th>Cargo</th><th>Saldo</th>';
		echo '</tr>';
		$sumacargo=$sumaabono=$saldo=0;
		$fecha=$_POST['fecha_ini'];
		$tcargos=0;
		$toabonos=0;
		for($i=1;$fecha<=$_POST['fecha_fin'];$i++){
			$ss="select * from desglosedinero where fecha ='".$fecha."' ";
			$result=mysql_db_query($base,$ss)or die(mysql_error());
			while($row=mysql_fetch_array($result)){
			
			$ss1="select if(tipo not in(1,2,20),sum(cant*if(tipo not in(1,2,20),denomin,0)),0) as efectivo,sum(cant*if(tipo in(2,20),denomin,0)) as descuento from desglosedineromov where cvedesg='".$row['cve']."' and tipo not in(1)";
			$result1=mysql_db_query($base,$ss1)or die(mysql_error());
			$row1=mysql_fetch_array($result1);
			$sumaabono+=$total;
			
		
			echo '<tr><td align=left>Desglose #'.$row['cve'].'</td>';
			echo '<td align=center>'.$row['fecha'].'</td>';
			//echo '<td align="left">'.$array_cargo[$row['cargo']].'</td>';
			echo '<td align="left">'.$array_tipo_sector[$row['sector']].'</td>';
			
			$total=$row1['efectivo'] + $row1['descuento'];
			if($row["estatus"]=="C"){
			$total=0;
			}
			echo '<td align="right">'.number_format($total,2).'</td>';
			$saldo+=$total;
			$toabonos=$toabonos + $total;
			echo '<td align="right"></td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '</tr>';
			$x++;
			}
			
			$res1=mysql_query("SELECT * FROM recibos_salidas WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
			$sumaabono+=$total;
			$saldo-=$row1['monto'];
			
			echo '<tr><td align=left>Recibo de Salida #'.$row1['cve'].'</td>';
			echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
//			echo '<td align="left">'.$array_cargo[$row1['cargo']].'</td>';
			echo '<td align="left">'.$array_tipo_sector[$row['sector']].'</td>';
			
			echo '<td align="right"></td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			$tcargos=$tcargos + $row1['monto'];	
			echo '<td align="right">'.number_format($saldo,2).'</td>';

			echo '</tr>';
			$x++;
			}

			$res1=mysql_query("SELECT * FROM vale_utilidad WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$total;
				$saldo-=$row1['monto'];
				echo '<tr><td align=left>Vale de utilidad #'.$row1['cve'].'</td>';
				echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
				echo '<td align="left">&nbsp;</td>';
				
				echo '<td align="right"></td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				$tcargos=$tcargos + $row1['monto'];	
				echo '<td align="right">'.number_format($saldo,2).'</td>';

				echo '</tr>';
				$x++;
			}


			$res1=mysql_query("SELECT * FROM devolucion_fianza WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$total;
				$saldo-=$row1['monto'];
				echo '<tr><td align=left>Traspaso de Fianza #'.$row1['cve'].'</td>';
				echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
				echo '<td align="left">&nbsp;</td>';
				
				echo '<td align="right"></td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				$tcargos=$tcargos + $row1['monto'];	
				echo '<td align="right">'.number_format($saldo,2).'</td>';

				echo '</tr>';
				$x++;
			}
			
			
	

			$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($_POST['fecha_ini']) ) );
		}
		

		
		echo '<tr bgcor="#E9F2F8"><th colspan=3 align="right">Total</th><th align="right">'.number_format($toabonos,2).'</th><th align="right">'.number_format($tcargos,2).'</th><th align="right">'.number_format(($toabonos-$tcargos),2).'</th></tr>';
		echo '</table></td><td>&nbsp;</td><td>';
		/*echo'<table border="1" width="100%"><tr><th>Seccion</th><th>Abonos</th><th>Cargos</th><th>Saldo</th></tr>';
			foreach($array_cargo as $k=>$v){
			$abonos=0;
			$cargos=0;
			$ss="select * from desglosedinero where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and  cargo ='".$k."' ";
			$result=mysql_db_query($base,$ss)or die(mysql_error());
			while($row=mysql_fetch_array($result)){
			$ss1="select if(tipo not in(1,2,20),sum(cant*if(tipo not in(1,2,20),denomin,0)),0) as efectivo,sum(cant*if(tipo in(2,20),denomin,0)) as descuento from desglosedineromov where cvedesg='".$row['cve']."' and tipo not in(1)";
			$result1=mysql_db_query($base,$ss1)or die(mysql_error());
			$row1=mysql_fetch_array($result1);
			$total=$row1['efectivo'] + $row1['descuento'];
			if($row["estatus"]=="C"){
			$total=0;
			}
			$abonos=$abonos + $total;
			}
			
			$res1=mysql_query("SELECT sum(monto) as cargos FROM recibos_salidas WHERE fecha_aplicacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and  cargo ='".$k."' AND estatus!='C'");
			$row1=mysql_fetch_array($res1);
			$cargos=$row1['cargos'];
			echo'<tr>
			<td>'.$v.'</td><td>'.number_format($abonos,2).'</td><td>'.number_format($cargos,2).'</td><td>'.number_format(($abonos-$cargos),2).'</td>
			</tr>';
			}
		echo'</table>';*/
		echo'</td></table>';
		exit();
}


if($_POST['ajax']==1)
{

		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr bgcolor="#E9F2F8"><th width="">Concepto</th><th>Fecha</th><th width="">Sector</th><th>Abono</th><th>Cargo</th><th>Saldo</th>';
		echo '</tr>';
		$sumacargo=$sumaabono=$saldo=0;
		$fecha=$_POST['fecha_ini'];
		$tcargos=0;
		$toabonos=0;
		for($i=1;$fecha<=$_POST['fecha_fin'];$i++){
			$ss="select * from desglosedinero where fecha ='".$fecha."' ";
			$result=mysql_db_query($base,$ss)or die(mysql_error());
			while($row=mysql_fetch_array($result)){
			
			$ss1="select if(tipo not in(1,2,20),sum(cant*if(tipo not in(1,2,20),denomin,0)),0) as efectivo,sum(cant*if(tipo in(2,20),denomin,0)) as descuento from desglosedineromov where cvedesg='".$row['cve']."' and tipo not in(1)";
			$result1=mysql_db_query($base,$ss1)or die(mysql_error());
			$row1=mysql_fetch_array($result1);
			$sumaabono+=$total;
			
			rowb();
			echo '<td align=left>Desglose #'.$row['cve'].'</td>';
			echo '<td align=center>'.$row['fecha'].'</td>';
//echo '<td align="left">'.$array_cargo[$row['cargo']].'</td>';
			echo '<td align="left">'.$array_tipo_sector[$row['sector']].'</td>';
			
			$total=$row1['efectivo'] + $row1['descuento'];
			if($row["estatus"]=="C"){
				$total=0;
			}
			echo '<td align="right">'.number_format($total,2).'</td>';
			$saldo+=$total;
			$toabonos=$toabonos + $total;
			echo '<td align="right"></td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '</tr>';
			$x++;
			}
			
			$res1=mysql_query("SELECT * FROM recibos_salidas WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$total;
				$saldo-=$row1['monto'];
				rowb();
				echo '<td align=left>Recibo de Salida #'.$row1['cve'].'</td>';
				echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
				//echo '<td align="left">'.$array_cargo[$row1['cargo']].'</td>';
				echo '<td align="left">'.$array_tipo_sector[$row['sector']].'</td>';
				
				echo '<td align="right"></td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				$tcargos=$tcargos + $row1['monto'];	
				echo '<td align="right">'.number_format($saldo,2).'</td>';

				echo '</tr>';
				$x++;
			}
			
			
			$res1=mysql_query("SELECT * FROM vale_utilidad WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$total;
				$saldo-=$row1['monto'];
				rowb();
				echo '<td align=left>Vale de utilidad #'.$row1['cve'].'</td>';
				echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
				echo '<td align="left">&nbsp;</td>';
				
				echo '<td align="right"></td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				$tcargos=$tcargos + $row1['monto'];	
				echo '<td align="right">'.number_format($saldo,2).'</td>';

				echo '</tr>';
				$x++;
			}

			$res1=mysql_query("SELECT * FROM devolucion_fianza WHERE fecha_aplicacion='".$fecha."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$total;
				$saldo-=$row1['monto'];
				rowb();
				echo '<td align=left>Traspaso de Fianza #'.$row1['cve'].'</td>';
				echo '<td align=center>'.$row1['fecha_aplicacion'].'</td>';
				echo '<td align="left">&nbsp;</td>';
				
				echo '<td align="right"></td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				$tcargos=$tcargos + $row1['monto'];	
				echo '<td align="right">'.number_format($saldo,2).'</td>';

				echo '</tr>';
				$x++;
			}
	

			$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($_POST['fecha_ini']) ) );
		}
		

		
		echo '<tr bgcolor="#E9F2F8"><th colspan=3 align="right">Total</th><th align="right">'.number_format($toabonos,2).'</th><th align="right">'.number_format($tcargos,2).'</th><th align="right">'.number_format(($toabonos-$tcargos),2).'</th></tr>';
		echo '</table>';
		
		echo '|';
		/*echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo'<table border="1"><tr><th>Seccion</th><th>Abonos</th><th>Cargos</th><th>Saldo</th></tr>';
			foreach($array_cargo as $k=>$v){
			$abonos=0;
			$cargos=0;
			$ss="select * from desglosedinero where fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and  cargo ='".$k."' ";
			$result=mysql_db_query($base,$ss)or die(mysql_error());
			while($row=mysql_fetch_array($result)){
			$ss1="select if(tipo not in(1,2,20),sum(cant*if(tipo not in(1,2,20),denomin,0)),0) as efectivo,sum(cant*if(tipo in(2,20),denomin,0)) as descuento from desglosedineromov where cvedesg='".$row['cve']."' and tipo not in(1)";
			$result1=mysql_db_query($base,$ss1)or die(mysql_error());
			$row1=mysql_fetch_array($result1);
			$total=$row1['efectivo'] + $row1['descuento'];
			if($row["estatus"]=="C"){
			$total=0;
			}
			$abonos=$abonos + $total;
			}
			
			$res1=mysql_query("SELECT sum(monto) as cargos FROM recibos_salidas WHERE fecha_aplicacion BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' and  cargo ='".$k."' AND estatus!='C'");
			$row1=mysql_fetch_array($res1);
			$cargos=$row1['cargos'];
			echo'<tr>
			<td>'.$v.'</td><td>'.number_format($abonos,2).'</td><td>'.number_format($cargos,2).'</td><td>'.number_format(($abonos-$cargos),2).'</td>
			</tr>';
			}
		echo'</table>';*/
	
	exit();
}


top($_SESSION);

	if ($_POST['cmd']<1) {
		if(trim($impresion)!="") echo '<div style="visibility:hidden;position:absolute">'.$impresion.'</div>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;
				<a href="#" onClick="atcr(\'\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0" title="Imprimir">Imprimir</a></td>';
		echo '
			  </tr>';
		echo '</table>';
		echo '<table width="" border="0"><tr><td valign="top"><table>';
		if($_SESSION['PlazaUsuario']==0){
			echo '<tr><td>Plaza</td><td><select name="searchplaza" id="searchplaza" class="textField"><option value="all">---Todas---</option>';
			foreach($array_plaza as $k=>$v){
				echo '<option value="'.$k.'">'.$v.'</option>';
			}
			echo '</select>&nbsp;&nbsp;</td></tr>';
		}
		else{
			echo '<input type="hidden" name="searchplaza" id="searchplaza" value="'.$_SESSION['PlazaUsuario'].'">';
		}
		//echo '<td>No. Economico</td><td><input type="text" size="5" class="textField" name="no_eco" id="no_eco"></td></t>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.firstday().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		
/*		echo '<!--<tr><td>Seccion</td><td><select name="seccion" id="seccion" class="textField"><option value="">---Seleccione---</option>';
		foreach ($array_cargo as $k=>$v) { 
	    echo '<option value="'.$k.'"';echo'>'.$v.'</option>';
		}
		echo '</select></td></tr>-->';*/
			
		echo '</table></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td><td align="center" name="Resultados1" id="Resultados1"></td></tr></table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script language="javascript">
		$("#usuarios").multipleSelect({
		width: 500
			});	
				function buscarRegistros()
				{
					document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						objeto.open("POST","edo_cuenta_cajageneral.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=1&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4){

							datos = objeto.responseText.split("|");
							document.getElementById("Resultados").innerHTML = datos[0];
							document.getElementById("Resultados1").innerHTML = datos[1];
							
							}
						}
					}
					document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
				}
				
				window.onload = function () {
					buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
				}
			</script>';
	}
	
	echo '<script>
			function validanumero(campo) {
				var ValidChars = "0123456789.";
				var cadena=campo.value;
				var cadenares="";
				var digito;
				for(i=0;i<cadena.length;i++) {
					digito=cadena.charAt(i);
					if (ValidChars.indexOf(digito) != -1)
						cadenares+=""+digito;
				}
				campo.value=cadenares;
			}
		</script>';


bottom();

?>
