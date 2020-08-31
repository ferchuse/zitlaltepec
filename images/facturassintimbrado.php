<?
include("main.php");
include("imp_factura.php");
//ARREGLOS

$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_clientes=array();
$res=mysql_db_query($base,"SELECT * FROM clientes WHERE empresa='".$_POST['cveempresa']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_clientes[$row['cve']]=$row['nombre'];
	if(strlen($row['rfc'].$row['homoclave'])!=13 || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['localidad']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
}

function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_db_query($base,"SELECT * FROM empresas WHERE cve='".$_POST['cveempresa']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

//function GenerarPdfSinTimbrar($empresa,$cvefact){
if($_POST['cmd']==101){
	$empresa=$_POST['cveempresa'];
	$cvefact=$_POST['reg'];
	require_once('fpdf153/fpdf.php');
	$pdf = new FPDF('P','mm','LETTER');
	$pdf->AddPage();
	$pdf->SetFont("Arial","",12);
	if(file_exists("logos/logo".$empresa.".jpg")) $pdf->Image("logos/logo".$empresa.".jpg",10,10,100,25);
	$pdf->SetXY(120,10);
	$pdf->MultiCell(77.5,4,"Ricardo Flores Mogón No. 1 Int.3
Col. San Pedro, Texcoco
México C.P. 56150
Cel.: 55 32 79 59 22
E-mail: hgaribay@gmail.com",0,"R");
	$pdf->Image("images/cuadrocircular.jpg",17,38,45,8);
	$pdf->Image("images/cuadrocircular.jpg",157,38,40.5,8);
	$res = mysql_db_query($base,"SELECT * FROM facturassintimbrado WHERE empresa='".$empresa."' AND cve='".$cvefact."'");
	$row = mysql_fetch_array($res);
	$pdf->SetXY(20,40);
	$pdf->Cell(30,4,"R.F.C.: CSI100506J42",0,0,"L");
	$pdf->SetTextColor(255,0,0);
	$pdf->SetXY(160,40);
	$pdf->Cell(30,4,"Factura No. ".$row['cve'],0,0,"L");
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont("Arial","",9);
	$pdf->SetXY(143,52);
	$pdf->Cell(30,4,fecha_letra($row['fecha_factura']),0,0,"L");
	$res1 = mysql_db_query($base,"SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$pdf->SetXY(18,52);
	$pdf->MultiCell(80,4,$row1['nombre']."
".$row1['rfc']."
".$row1['calle'].' '.$row1['numexterior'].' '.$row1['numinterior']."
".$row1['colonia'].' C.P. '.$row1['codigopostal']."
".$row1['localidad'].', '.$row1['estado'].", MEXICO",0,"L");
	$pdf->SetXY(10,92);
	$y=$pdf->GetY();
	$res2 = mysql_db_query($base,"SELECT * FROM facturassintimbradomov WHERE empresa='".$empresa."' AND cvefact='".$cvefact."'");
	while($row2 = mysql_fetch_array($res2)){
		$pdf->SetXY(10,$y);
		$pdf->Cell(30,4,$row2['cantidad'],0,0,"C",0);
		$pdf->MultiCell(90,4,$row2['concepto'],0,"C",0);
		$y2=$pdf->GetY();
		$pdf->SetXY(127.5,$y);
		$pdf->Cell(40,4,$row2['precio'],0,0,"R",0);
		$pdf->Cell(30,4,$row2['importe'],0,0,"R",0);
		$y=$y2;
	}
	$pdf->SetXY(127,$y+5);
	$pdf->Cell(40,4,"Subtotal",0,0,"R",0);
	$pdf->Cell(30,4,$row['subtotal'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(127);
	$pdf->Cell(40,4,"I.V.A.",0,0,"R",0);
	$pdf->Cell(30,4,$row['iva'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(127);
	$pdf->Cell(40,4,"Total",0,0,"R",0);
	$pdf->Cell(30,4,$row['total']+$row['iva_retenido'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(127);
	$pdf->Cell(40,4,"I.V.A. Retenido",0,0,"R",0);
	$pdf->Cell(30,4,$row['iva_retenido'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(127);
	$pdf->Cell(40,4,"Total",0,0,"R",0);
	$pdf->Cell(30,4,$row['total'],0,0,"R",0);
	$pdf->Ln();
	$pdf->SetX(127);
	$pdf->Output();
	exit();
}

if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM facturassintimbrado as a WHERE a.empresa='".$_POST['cveempresa']."' AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
	//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
	if($_POST['cliente']!="all") $select.=" AND a.cliente='".$_POST['cliente']."'";
	if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
	$select.=" ORDER BY a.cve DESC";
	$rsabonos=mysql_db_query($base,$select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		echo '<tr><td bgcolor="#E9F2F8" colspan="12">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Fecha Factura</th><th>Concepto</th>
		<th>Cliente</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>Iva Retenido</th><th>Total</th>
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_db_query($base,"SELECT a.usuario FROM facturassintimbrado as a WHERE empresa='".$_POST['cveempresa']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th></tr>'; 
		$sumacargo=array();
		$x=0;
		while ($Abono=mysql_fetch_array($rsabonos)){	
			rowb();
			$estatus='';
			if($Abono['estatus']=='C'){
				$estatus='(CANCELADO)';
				if($_POST['estatus']!='C'){
					$Abono['subtotal']=0;
					$Abono['iva']=0;
					$Abono['iva_retenido']=0;
					$Abono['total']=0;
				}
				echo '<td align="center">CANCELADO</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'facturassintimbrado.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'facturassintimbrado.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['fecha_factura'].'</td>';
			echo '<td align="center">'.$Abono['obs'].'</td>';
			//echo '<td>'.htmlentities($Abono['cliente']).'</td>';
			echo '<td>'.htmlentities($array_clientes[$Abono['cliente']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido'];
			$sumacargo[3]+=$Abono['iva_retenido'];
			$sumacargo[4]+=$Abono['total'];
		}
		echo '<tr><td bgcolor="#E9F2F8" colspan="5">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		echo '<td bgcolor="#E9F2F8" colspan="1">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	}
	else {
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="sanLR10"><font class="fntN10B"> No se encontraron registros</font></td>
			</tr>	  
			</table>';
	}
	exit();
}


if($_POST['cmd']==3){
	$res = mysql_db_query($base,"SELECT * FROM facturassintimbrado WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		mysql_db_query($base,"UPDATE facturassintimbrado SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	$res = mysql_db_query($base,"SELECT folio_inicial FROM foliosiniciales WHERE empresa='".$_POST['cveempresa']."' AND tipo=1");
	$row = mysql_fetch_array($res);
	$res1 = mysql_db_query($base,"SELECT cve FROM facturassintimbrado WHERE empresa='".$_POST['cveempresa']."'");
	if(mysql_num_rows($res1) > 0){
		mysql_db_query($base,"INSERT facturassintimbrado SET empresa='".$_POST['cveempresa']."',fecha='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',fecha_factura='".$_POST['fecha']."',calleynumero='".$_POST['calleynumero']."',colonia='".$_POST['colonia']."',
		ciudad='".$_POST['ciudad']."',cp='".$_POST['cp']."',rfc='".$_POST['rfc']."',baniva_retenido='".$_POST['banivaretenido']."',usuario='".$_POST['cveusuario']."'");
	}
	else{
		mysql_db_query($base,"INSERT facturassintimbrado SET empresa='".$_POST['cveempresa']."',cve='".$row['folio_inicial']."',fecha='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',fecha_factura='".$_POST['fecha']."',calleynumero='".$_POST['calleynumero']."',colonia='".$_POST['colonia']."',
		ciudad='".$_POST['ciudad']."',cp='".$_POST['cp']."',rfc='".$_POST['rfc']."',baniva_retenido='".$_POST['banivaretenido']."',usuario='".$_POST['cveusuario']."'");
	}
	$cvefact=mysql_insert_id();
	//Agregamos los conceptos
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			$importe_iva=round($_POST['importe'][$k]*$_POST['ivap'][$k]/100,2);
			mysql_db_query($base,"INSERT facturassintimbradomov SET empresa='".$_POST['cveempresa']."',cvefact='$cvefact',cantidad='".$v."',concepto='".$_POST['concepto'][$k]."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."',iva='".$_POST['ivap'][$k]."',importe_iva='$importe_iva'");
			$i++;
		}
	}
	mysql_db_query($base,"UPDATE facturassintimbrado SET subtotal='".$_POST['subtotal']."',iva='".$_POST['iva']."',total='".$_POST['total']."',iva_retenido='".$_POST['iva_retenido']."' WHERE empresa='".$_POST['cveempresa']."' AND cve=".$cvefact);
	$_POST['cmd']=0;
}

top($_SESSION);
	if($_POST['cmd']==1){
		echo '<table><tr>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if($.trim(document.forma.cliente.value)==\'0\'){
				alert(\'Necesita ingresar el cliente\');
				$(\'#panel\').hide();
			}
			else if($.trim(document.forma.total.value)==\'\'){
				alert(\'El total debe de ser mayor a cero\');
				$(\'#panel\').show();
			}
			else{
				atcr(\'facturassintimbrado.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'facturassintimbrado.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td align="left">Fecha</td><td><input type="text" name="fecha" id="fecha"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		/*echo '<tr><td align="left">Cliente</td><td><input type="text" name="cliente" id="cliente"  size="50" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">RFC</td><td><input type="text" name="rfc" id="rfc"  size="15" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Calle y Numero</td><td><input type="text" name="calleynumero" id="calleynumero"  size="50" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Colonia</td><td><input type="text" name="colonia" id="colonia"  size="50" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Ciudad</td><td><input type="text" name="ciudad" id="ciudad"  size="50" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">C.P.</td><td><input type="text" name="cp" id="cp"  size="6" class="textField" value=""></td></tr>';*/
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea class="textField" name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';
		echo '<th>Cantidad</th>';
		echo '<th>Descripcion</th><th>Precio Unitario</th><th>Importe</th></tr>';
		$i=0;
		if($i==0){
			echo '<tr>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td><input type="text" name="concepto['.$i.']" id="concepto'.$i.'" class="textField" size="50" value=""></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '<td align="center"><input type="checkbox" name="ivap['.$i.']" id="ivap'.$i.'" value="16" onClick="sumarproductos()"></td>';
			echo '</tr>';
			$i++;
		}
		echo '<tr id="idsubtotal"><th align="right" colspan="3">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="" readOnly></td></tr>';
		echo '<tr id="idiva"><th align="right" colspan="3">Iva&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="" readOnly></td></tr>';
		echo '<tr id="idtotal1"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="" readOnly></td></tr>';
		echo '<tr id="idiva_ret"><th align="right" colspan="3"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()">Iva Retenido&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="" readOnly></td></tr>';
		echo '<tr id="idtotal"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';		
		echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>
					
			function agregarproducto(){
				var checkeado=\'\';
				if($("#baniva_retenido").is(":checked")){
					checkeado=\'checked\';
				}
				tot=$("#total").val();
				$("#idtotal").remove();
				subtot=$("#subtotal").val();
				$("#idsubtotal").remove();
				iv=$("#iva").val();
				$("#idiva").remove();
				tot1=$("#total1").val();
				$("#idtotal1").remove();
				iva_ret=$("#iva_retenido").val();
				$("#idiva_ret").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\</td>\
				<td><input type="text" name="concepto[\'+num+\']" id="concepto\'+num+\'" class="textField" size="50" value=""></td>\
				<td align="center"><input type="text" class="textField" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				<td align="center"><input type="checkbox" name="ivap[\'+num+\']" id="ivap\'+num+\'" value="16" onClick="sumarproductos()"></td>\
				</tr>\
				<tr id="idsubtotal"><th align="right" colspan="3">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="\'+subtot+\'" readOnly></td></tr>\
				<tr id="idiva"><th align="right" colspan="3">Iva&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="\'+iv+\'" readOnly></td></tr>\
				<tr id="idtotal1"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="\'+tot1+\'" readOnly></td></tr>\
				<tr id="idiva_ret"><th align="right" colspan="3"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Iva Retenido&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="\'+iva_ret+\'" readOnly></td></tr>\
				<tr id="idtotal"><th align="right" colspan="3">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);
					sumar+=(document.getElementById("importe"+i).value/1);
					if(document.getElementById("ivap"+i).checked){
						iv+=document.getElementById("importe"+i).value*0.16;
					}
				}
				document.forma.subtotal.value=sumar.toFixed(2);
				document.forma.iva.value=iv.toFixed(2);
				document.forma.total1.value=(document.forma.subtotal.value/1)+(document.forma.iva.value/1);
				if($("#baniva_retenido").is(":checked")){
					document.forma.iva_retenido.value=iv.toFixed(2);
					document.forma.total.value=sumar.toFixed(2);
				}
				else{
					document.forma.iva_retenido.value=0;
					document.forma.total.value=document.forma.total1.value;
				}
			}
			
			
		  </script>';
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onClick="atcr(\'facturassintimbrado.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>
			  </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente"><option value="all">--- Todos ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
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
			objeto.open("POST","facturassintimbrado.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveempresa="+document.getElementById("cveempresa").value);
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
			buscarRegistros(0,1); //Realizar consulta de todos los registros al iniciar la forma.
	}';
	}
	echo '
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

	</Script>
';

?>