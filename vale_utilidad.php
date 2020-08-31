<?php 

include ("main.php"); 

/*** ARREGLOS ***********************************************************/



$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}
$res=mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_permisionario[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM cat_autoriza ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_autoriza[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_empresa[$row['cve']] = $row['nombre'];
}
$rsBenef=mysql_db_query($base,"SELECT * FROM unidades WHERE 1");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_unidad[$Benef['cve']]=$Benef['no_eco'].$Benef['letra'];
	$array_propietario[$Benef['cve']]=$array_permisionario[$Benef['permisionario']];
	$array_empresauni[$Benef['cve']]=$array_empresa[$Benef['empresa']];
	$array_localidaduni[$Benef['cve']]=$Benef['localidad'];
	$array_uni[$Benef['cve']]=$Benef['no_eco'].' -'.$array_empresa[$Benef['empresa']];
}

$rsBenef=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas ORDER BY nombre");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_benef[$Benef['cve']]=$Benef['nombre'];
}
$rsBenef=mysql_db_query($base,"SELECT * FROM cat_estatus ORDER BY nombre");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_estatus2[$Benef['cve']]=$Benef['nombre'];
}

$array_estatusvales=array('A'=>"Pagado",'C'=>"Cancelado");

if($_POST['cmd']==101){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
	if($_POST['reg']>0){
		$_POST['rec']=array($_POST['reg']);
	}
	foreach($_POST['rec'] as $cver){
		$select=" SELECT * FROM vale_utilidad WHERE cve='".$cver."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		$pdf->AddPage();
//		$pdf->Image('images/membrete.JPG',30,3,150,15);
$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
		$pdf->SetFont('Arial','B',14);
		$pdf->Ln(10);
		$pdf->Cell(95,10,'Vale de Utilidad',0,0,'L');
		$pdf->Cell(95,10,'Folio: '.$cver,0,0,'R');
		$pdf->SetFont('Arial','B',9);
		$pdf->Ln();
		$pdf->Cell(95,3,'Referencia Bancaria: '.$Salida['referencia'],0,0,'L');
		$pdf->Cell(95,3,'Fecha Apl: '.$Salida['fecha_aplicacion'],0,0,'R');
		$pdf->Ln();
		$pdf->Cell(180,3,'Unidades',0,0,'C');
		$pdf->Ln();
		$propietario="";
		if($Salida['tipo_beneficiario']==0)
		{$propietario=$array_benef[$Salida['beneficiario']];}
		else{
			$propietario=$array_propietario[$Salida['beneficiario']];
		}
		$res1=mysql_db_query($base,"SELECT * FROM vale_utilidad_detalle WHERE vale='".$cver."' ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			$pdf->Cell(15,3,"");
			$pdf->Cell(30,3,$array_unidad[$row1['unidad']]);
			$pdf->Cell(100,3,$array_propietario[$row1['unidad']]);
			$pdf->Cell(30,3,number_format($row1['monto'],2),0,0,'R');
			$pdf->Ln();
		}
		$pdf->Cell(95,3,'',0,0,'L');
		$pdf->Cell(95,3,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
		$pdf->Ln();
		$pdf->MultiCell(190,3,numlet($Salida['monto']),0,0,'R');
		$pdf->MultiCell(190,3,'Concepto: '.$Salida['concepto'],0,0,'R');
		$pdf->Ln();
		$pdf->Ln();
		if($Salida['tipo_beneficiario']==0){
		$propietario=$array_benef[$Salida['beneficiario']];}
		else{
		$propietario=$array_uni[$Salida['beneficiario']];}
		$pdf->MultiCell(190,3,'____________________________________
		Beneficiario
		'.$propietario,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(92.5,4,$array_autoriza[$Salida['autoriza']],'B',0,'C');
		$pdf->Cell(5,4,'');
		$pdf->Cell(92.5,4,' ','B',0,'C');
		$pdf->Ln();
		$pdf->Cell(95,4,'Autoriza',0,0,'C');
		$pdf->Cell(95,4,'Entrega',0,0,'C');
		$pdf->Ln();
		/*$pdf->Cell(95,3,'Autoriza',0,0,'C');
		$pdf->Cell(95,3,'Entrega',0,0,'C');
		$pdf->Ln();
		$pdf->Cell(95,3,'ROSA ELIA GUADALUPE RODRIGUEZ ADVINCULA',0,0,'C');
		$pdf->Cell(95,3,$array_nomusuario[$Salida['usuario']],0,0,'C');*/
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(95,3,'Fecha Elaboracion: '.$Salida['fecha'],0,0,'L');
		
		
		//$pdf->AddPage();
		//$pdf->Image('images/membrete.JPG',30,3,150,15);
		
		
		$pdf->SetXY(10,153);
$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
		$pdf->SetFont('Arial','B',14);
		$pdf->Ln(10);
		$pdf->Cell(95,10,'Vale de Utilidad de Autobuses     -   COPIA',0,0,'L');
		$pdf->Cell(95,10,'Folio: '.$cver,0,0,'R');
		$pdf->SetFont('Arial','B',9);
		$pdf->Ln();
		$pdf->Cell(95,3,'Referencia Bancaria: '.$Salida['referencia'],0,0,'L');
		$pdf->Cell(95,3,'Fecha Apl: '.$Salida['fecha_aplicacion'],0,0,'R');
		$pdf->Ln();
		$pdf->Cell(180,3,'Unidades',0,0,'C');
		$pdf->Ln();
		$propietario="";
		if($Salida['tipo_beneficiario']==0)
			$propietario=$array_benef[$Salida['beneficiario']];
		else
			$propietario=$array_propietario[$Salida['beneficiario']];
		$res1=mysql_db_query($base,"SELECT * FROM vale_utilidad_detalle WHERE vale='".$cver."' ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			$pdf->Cell(15,3,"");
			$pdf->Cell(30,3,$array_unidad[$row1['unidad']]);
			$pdf->Cell(100,3,$array_propietario[$row1['unidad']]);
			$pdf->Cell(30,3,number_format($row1['monto'],2),0,0,'R');
			$pdf->Ln();
		}
		$pdf->Cell(95,3,'',0,0,'L');
		$pdf->Cell(95,3,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
		$pdf->Ln();
		$pdf->MultiCell(190,3,numlet($Salida['monto']),0,0,'R');
		$pdf->MultiCell(190,3,'Concepto: '.$Salida['concepto'],0,0,'R');
		$pdf->Ln();
		$pdf->Ln();
		if($Salida['tipo_beneficiario']==0){
		$propietario=$array_benef[$Salida['beneficiario']];}
		else{
		$propietario=$array_uni[$Salida['beneficiario']];}
		$pdf->MultiCell(190,3,'____________________________________
		Beneficiario
		'.$propietario,0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(92.5,4,$array_autoriza[$Salida['autoriza']],'B',0,'C');
		$pdf->Cell(5,4,'');
		$pdf->Cell(92.5,4,' ','B',0,'C');
		$pdf->Ln();
		/*$pdf->Cell(95,3,'Autoriza',0,0,'C');
		$pdf->Cell(95,3,'Entrega',0,0,'C');
		$pdf->Ln();
		$pdf->Cell(95,3,'ROSA ELIA GUADALUPE RODRIGUEZ ADVINCULA',0,0,'C');
		$pdf->Cell(95,3,$array_nomusuario[$Salida['usuario']],0,0,'C');*/
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(95,3,'Fecha Elaboracion: '.$Salida['fecha'],0,0,'L');
		
	}
	$pdf->Output();
	exit();
}

if($_POST['cmd']==111){
	echo '<html><body><p align="center">';
	$select=" SELECT * FROM vale_utilidad WHERE cve='".$_POST['reg']."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		$fecha=$Salida['fecha'];
		$fecha_aplicacion=$Salida['fecha_aplicacion'];
		$Encabezado = 'Folio No.'.$_POST['reg'];
		//Formulario 
		echo '<table>';
		echo '<tr><td><img src="images/membrete.JPG" width="850px" heigth="200px"></td></tr>';
		echo '<tr><td align="center"><h1>Vale de Utilidad</h1></td></tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha_aplicacion" id="fecha_aplicacion" class="readOnly" size="15" value="'.$fecha_aplicacion.'" readOnly>';
		echo '</td></tr>';
		echo '<tr><th align="left">Referencia Bancaria</th><td><input type="text" name="referencia" id="referencia" class="readOnly" size="30" value="'.$Salida['referencia'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Unidades</th><td><table id="tabla1"><tr><th>No. Eco.</th><th>Empresa</th><th>Saldo</th><th>Monto</th></tr>';
		$cantuni=0;
		$res1=mysql_db_query($base,"SELECT * FROM vale_utilidad_detalle WHERE vale='".$_POST['reg']."' ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td><input type="text" name="eco'.$cantuni.'" class="readOnly" value="'.$array_unidad[$row1['unidad']].'" size="15" readOnly><input type="hidden" name="uni'.$cantuni.'" value="'.$row1['unidad'].'"></td>
			<td><input type="text" name="pro'.$cantuni.'" class="readOnly" size="50" value="'.$array_propietario[$row1['unidad']].'" readOnly></td>
			<td><input type="text" name="sal'.$cantuni.'" class="readOnly" size="20" value="'.$row1['saldo'].'" readOnly></td>
			<td><input type="text" name="monto'.$cantuni.'" value="'.$row1['monto'].'" class="readOnly" size="20" readOnly></td></tr>';
			$cantuni++;
		}
		echo '</table></td></tr>';
		echo '<input type="hidden" name="cantuni" value="'.$cantuni.'">';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="15" value="'.$Salida['monto'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Tipo de Beneficiario</th><td>';
		if($_POST['reg']==0 || $Salida['tipo_beneficiario']==0) echo ' Externo';
		if($Salida['tipo_beneficiario']==1) echo ' Unidades';
		echo '</td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario" class="textField">';
			if($Salida['tipo_beneficiario']==0){
				$res1=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas WHERE cve='".$Salida['beneficiario']."' ORDER BY nombre");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['nombre'].'</option>';
				}
			}
			elseif($Salida['tipo_beneficiario']==1){
				$res1=mysql_db_query($base,"SELECT * FROM unidades WHERE cve='".$Salida['beneficiario']."' ORDER BY no_eco");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['no_eco'].' '.$array_permisionario[$row1['permisionario']].'</option>';
				}
			}
		echo '</select></td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="readOnly" rows="5" cols="50" readOnly>'.$Salida['concepto'].'</textarea></td></tr>';
		echo '<tr><th align="left">Fecha Elaboracion</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly></td></tr>';
		echo '</table>';
		echo '<br><br<br>';
		echo '<table>';
		echo '<tr><td><img src="images/membrete.JPG" width="850px" heigth="200px"></td></tr>';
		echo '<tr><td align="center"><h1>Vale de Utilidad<br>COPIA</h1></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha_aplicacion" id="fecha_aplicacion" class="readOnly" size="15" value="'.$fecha_aplicacion.'" readOnly>';
		echo '</td></tr>';
		echo '<tr><th align="left">Referencia Bancaria</th><td><input type="text" name="referencia" id="referencia" class="readOnly" size="30" value="'.$Salida['referencia'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Unidades</th><td><table id="tabla1"><tr><th>No. Eco.</th><th>Propietario</th><th>Saldo</th><th>Monto</th></tr>';
		$cantuni=0;
		$res1=mysql_db_query($base,"SELECT * FROM vale_utilidad_detalle WHERE vale='".$_POST['reg']."' ORDER BY cve");
		while($row1=mysql_fetch_array($res1)){
			echo '<tr><td><input type="text" name="eco'.$cantuni.'" class="readOnly" value="'.$array_unidad[$row1['unidad']].'" size="15" readOnly><input type="hidden" name="uni'.$cantuni.'" value="'.$row1['unidad'].'"></td>
			<td><input type="text" name="pro'.$cantuni.'" class="readOnly" size="50" value="'.$array_propietario[$row1['unidad']].'" readOnly></td>
			<td><input type="text" name="sal'.$cantuni.'" class="readOnly" size="20" value="'.$row1['saldo'].'" readOnly></td>
			<td><input type="text" name="monto'.$cantuni.'" value="'.$row1['monto'].'" class="readOnly" size="20" readOnly></td></tr>';
			$cantuni++;
		}
		echo '</table></td></tr>';
		echo '<input type="hidden" name="cantuni" value="'.$cantuni.'">';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="15" value="'.$Salida['monto'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Tipo de Beneficiario</th><td>';
		if($_POST['reg']==0 || $Salida['tipo_beneficiario']==0) echo ' Externo';
		if($Salida['tipo_beneficiario']==1) echo ' Unidades';
		echo '</td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario" class="textField">';
			if($Salida['tipo_beneficiario']==0){
				$res1=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas WHERE cve='".$Salida['beneficiario']."' ORDER BY nombre");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['nombre'].'</option>';
				}
			}
			elseif($Salida['tipo_beneficiario']==1){
				$res1=mysql_db_query($base,"SELECT * FROM unidades WHERE cve='".$Salida['beneficiario']."' ORDER BY no_eco");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['no_eco'].' '.$array_permisionario[$row1['permisionario']].'</option>';
				}
			}
		echo '</select></td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="readOnly" rows="5" cols="50" readOnly>'.$Salida['concepto'].'</textarea></td></tr>';
		echo '<tr><th align="left">Fecha Elaboracion</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly></td></tr>';
		echo '</table>';
	echo '</p></body></html>';
	echo '<script>window.print();</script>';
	exit();	
}

if($_POST['cmd']==100){
	echo '<html><body><p align="center"><h1>Vale de Utilidades</th>';
	$filtroeco="";
		if($_POST['no_eco']!="") $filtroeco=" INNER JOIN unidades AS c ON (c.cve=b.unidad AND c.no_eco='".$_POST['no_eco']."')";
		$select= " SELECT a.* FROM vale_utilidad as a INNER JOIN vale_utilidad_detalle as b on a.cve=b.vale $filtroeco WHERE 1";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		$select .= " AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ";
		if($_POST['fecha_apli_ini'] and $_POST['fecha_apli_fin']){$select.=" AND a.fecha_aplicacion BETWEEN '".$_POST['fecha_apli_ini']."' AND '".$_POST['fecha_apli_fin']."'";}
		$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
		$rssalida=mysql_db_query($base,$select) or die(mysql_error());
		//if(mysql_num_rows($rssalida)>0) 
		//{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$col=9;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rssalida).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8">';
			echo '<th>Folio</th><th>Fecha</th><th>Fecha Aplicacion</th><th>Monto</th><th>Beneficiario</th><th>Referencia</th><th>Estatus</th><th>Usuario</th>';
			echo '</tr>';
			$total=0;
			$i=0;
			while($Salida=mysql_fetch_array($rssalida)) {
				rowb();
				echo '<td align="center">'.$Salida['cve'].'</td>';
				echo '<td align="center">'.$Salida['fecha'].'</td>';
				echo '<td align="center">'.$Salida['fecha_aplicacion'].'</td>';
				echo '<td align="center">$ '.number_format($Salida['monto'],2).'</td>';
				if($Salida['tipo_beneficiario']==0)
					echo '<td align="left">'.htmlentities($array_benef[$Salida['beneficiario']]).'</td>';
				else
					echo '<td align="left">'.htmlentities($array_propietario[$Salida['beneficiario']]).'</td>';
				echo '<td align="center">'.$Salida['referencia'].'</td>';
				echo '<td align="center">'.$array_estatusvales[$Salida['estatus']].'</td>';
				echo '<td align="left">'.htmlentities($array_usuario[$Salida['usuario']]).'</td>';
				$total+=$Salida['monto'];
				$i++;
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="2" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
				<td bgcolor="#E9F2F8" align="right">Total:</td>
				<td bgcolor="#E9F2F8" align="center">$ '.number_format($total,2).'</td>
				<td colspan="4" bgcolor="#E9F2F8" colspan="5">&nbsp;</td>
				</tr>
			</table>';
	echo '</p></body></html>';
	echo '<script>window.print();</script>';
	exit();	
}

if ($_POST['cmd']==3) {
	$delete= "UPDATE vale_utilidad SET estatus='C',fechacan=NOW(),usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_db_query($base,$delete);
	$_POST['cmd']=0;
}
/*** ACTUALIZAR REGISTRO  **************************************************/
/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$filtroeco="";
		if($_POST['no_eco']!="") $filtroeco=" INNER JOIN unidades AS c ON (c.cve=b.unidad AND c.no_eco='".$_POST['no_eco']."')";
		$select= " SELECT a.* FROM vale_utilidad as a INNER JOIN vale_utilidad_detalle as b on (b.vale=a.cve) $filtroeco WHERE 1";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		if ($_POST['referencia']!="") { $select.=" AND a.referencia='".$_POST['referencia']."'"; }
		if($_POST['tipo_benef']!="all") $select.=" AND a.tipo_beneficiario='".$_POST['tipo_benef']."'";
		if($_POST['beneficiario']!="all") $select.=" AND a.beneficiario='".$_POST['beneficiario']."'";
		$select .= " AND a.fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."' ";
		if($_POST['fecha_apli_ini'] and $_POST['fecha_apli_fin']){$select.=" AND a.fecha_aplicacion BETWEEN '".$_POST['fecha_apli_ini']."' AND '".$_POST['fecha_apli_fin']."'";}
		$select.=" GROUP BY a.cve ORDER BY a.cve DESC";
		$rssalida=mysql_db_query($base,$select) or die(mysql_error());
		//if(mysql_num_rows($rssalida)>0) 
		//{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			$col=13;
			echo '<tr><td bgcolor="#E9F2F8" colspan="'.$col.'">'.mysql_num_rows($rssalida).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>Cancelar</th><th>Editar</th><th>Imprimir<br><input type="checkbox" onClick="if(this.checked) $(\'.chkimp\').attr(\'checked\',\'checked\'); else $(\'.chkimp\').removeAttr(\'checked\');"></th>';
			echo '<th>Folio</th><th>Fecha</th><th>Fecha Aplicacion</th><th>Monto</th><th>Beneficiario</th><th>Referencia</th><th>Estatus</th><th>Estado</th><th>Usuario<br>
			<select name="usu2" id="usu2" onChange="document.forma.usu.value=this.value;buscarRegistros()">
			<option value="all">--- Todos ---</option>';
			$res=mysql_db_query($base,"SELECT usuario FROM vale_utilidad  GROUP BY usuario");
			while($row=mysql_fetch_array($res)){
				echo '<option value="'.$row['usuario'].'"';
				if($row['usuario']==$_POST['usu']) echo ' selected';
				echo '>'.$array_usuario[$row['usuario']].'</option>';
			}
			echo '</select></th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			$i=0;
			$nivelUsuario=nivelUsuario();
			while($Salida=mysql_fetch_array($rssalida)) {
				rowb();
				if($Salida['estatus']!='C'){
					if($nivelUsuario>2){
						echo '<td align="center" width="40" nowrap><a href="#" onClick="if(confirm(\'Esta seguro de cancelar este folio?\')){atcr(\'vale_utilidad.php\',\'\',\'3\',\''.$Salida['cve'].'\'); }"><img src="images/validono.gif" border="0"></a></td>';
						echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'vale_utilidad.php\',\'\',\'1\','.$Salida['cve'].')"><img src="images/modificar.gif" border="0" title="Editar '.$Salida['nombre'].'"></a></td>';
					}
					else{
						echo '<td>&nbsp;</td>';
						echo '<td>&nbsp;</td>';
					}
					//echo '<td align="center" width="40" nowrap><a href="#" onClick="atcr(\'vale_utilidad.php\',\'_blank\',\'101\','.$Salida['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$Salida['nombre'].'"></a></td>';
					echo '<td align="center" width="40" nowrap><input type="checkbox" name="rec[]" value="'.$Salida['cve'].'" class="chkimp"></td>';
				}
				else{
					echo '<td colspan="3">&nbsp;</td>';
					$Salida['monto'] = 0;
				}
				echo '<td align="center">'.$Salida['cve'].'</td>';
				echo '<td align="center">'.$Salida['fecha'].' '.$Salida['hora'].'</td>';
				echo '<td align="center">'.$Salida['fecha_aplicacion'].'</td>';
				echo '<td align="center">$ '.number_format($Salida['monto'],2).'</td>';
				if($Salida['tipo_beneficiario']==0)
//					echo '<td align="left">*'.utf8_decode(htmlentities($array_benef[$Salida['beneficiario']])).'</td>';
				echo '<td align="left">'.utf8_encode($array_benef[$Salida['beneficiario']]).'</td>';
				else
					echo '<td align="left">'.utf8_decode($array_unidad[$Salida['beneficiario']]).' - '.utf8_decode(htmlentities($array_propietario[$Salida['beneficiario']])).'</td>';
				echo '<td align="center">'.$Salida['referencia'].'</td>';
				echo '<td align="center">'.$array_estatusvales[$Salida['estatus']].'</td>';
				echo '<td align="center">'.$array_estatus2[$Salida['estatus2']].'</td>';
				echo '<td align="left">'.htmlentities($array_usuario[$Salida['usuario']]).'</td>';
				$total+=$Salida['monto'];
				$i++;
				echo '</tr>';
			}
			echo '	
				<tr>
				<td colspan="5" bgcolor="#E9F2F8">'.$i.' Registro(s)</td>
				<td bgcolor="#E9F2F8" align="right">Total:</td>
				<td bgcolor="#E9F2F8" align="center">$ '.number_format($total,2).'</td>
				<td colspan="5" bgcolor="#E9F2F8" colspan="6">&nbsp;</td>
				</tr>
			</table>';
			
		exit();	
}

if($_POST['ajax']==3){
	if($_POST['tipo_beneficiario']==0){
		$res1=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas ORDER BY nombre");
		while ($row1=mysql_fetch_array($res1)) {
			echo $row1['cve'].','.$row1['nombre'].'|';
		}
	}
	elseif($_POST['tipo_beneficiario']==1){
		$res1=mysql_db_query($base,"SELECT * FROM unidades ORDER BY no_eco");
		while ($row1=mysql_fetch_array($res1)) {
			echo $row1['cve'].','.$row1['no_eco'].' '.utf8_encode($array_propietario[$row1['empresa']]).'|';
		}
	}
	exit();
}	

if($_POST['ajax']==5){
	$rsUni=mysql_db_query($base,"SELECT cve,empresa FROM unidades WHERE no_eco='".strtoupper($_POST['eco'])."' AND localidad='".$_POST['localidad']."' AND estatus='1'");
	if($Uni=mysql_fetch_array($rsUni)){
		$_POST['unidad']=$Uni['cve'];
		
		$saldo=saldo_unidad($_POST['unidad']);
		$prorratear="";
		echo $_POST['unidad'].'|'.utf8_encode($array_propietario[$_POST['unidad']]).'|'.round($saldo,2).'|'.$prorratear;
	}
	else{
		echo "0";
	}
	exit();
}

top($_SESSION);
if ($_POST['cmd']==2) {

	if($_POST['reg']) {
			//Actualizar el Registro
			$update = " UPDATE vale_utilidad
						SET monto='".$_POST['monto']."',referencia='".$_POST['referencia']."',
						    tipo_beneficiario='".$_POST['tipo_beneficiario']."',concepto='".$_POST['concepto']."',
						    beneficiario='".$_POST['beneficiario']."',autoriza='".$_POST['autoriza']."'
							fecha_aplicacion='".$_POST['fecha_aplicacion']."',estatus2='".$_POST['estatus2']."'
						WHERE cve='".$_POST['reg']."' " ;
			$ejecutar = mysql_db_query($base,$update);			
			mysql_db_query($base,"DELETE FROM vale_utilidad_detalle WHERE vale='".$_POST['reg']."'");
	} else {
			//Insertar el Registro
			$insert = " INSERT vale_utilidad
						SET monto='".$_POST['monto']."',referencia='".$_POST['referencia']."',
						    tipo_beneficiario='".$_POST['tipo_beneficiario']."',concepto='".$_POST['concepto']."',
						    beneficiario='".$_POST['beneficiario']."',
							fecha_aplicacion='".$_POST['fecha_aplicacion']."',estatus='A',estatus2='1',autoriza='".$_POST['autoriza']."',
							usuario='".$_POST['cveusuario']."',fecha=CURDATE(),hora=CURTIME()";
			$ejecutar = mysql_db_query($base,$insert);
			$_POST['reg']=mysql_insert_id();
	}
	for($i=0;$i<$_POST['cantuni'];$i++){
		if($_POST['uni'.$i]!="" && $_POST['monto'.$i]>0){
			mysql_db_query($base,"INSERT vale_utilidad_detalle SET vale='".$_POST['reg']."',unidad='".$_POST['uni'.$i]."',monto='".$_POST['monto'.$i]."',saldo='".$_POST['sal'.$i]."'");
		}
	}
	$_POST['cmd']=0;
	
}

/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM vale_utilidad WHERE cve='".$_POST['reg']."' ";
		$rssalida=mysql_db_query($base,$select);
		$Salida=mysql_fetch_array($rssalida);
		if($_POST['reg']>0){
			$fecha=$Salida['fecha'];
			$fecha_aplicacion=$Salida['fecha_aplicacion'];
			$Encabezado = 'Folio No.'.$_POST['reg'];
		}
		else{
			$fecha=fechaLocal();
			$fecha_aplicacion=fechaLocal();
			$Encabezado = 'Nuevo Vale de Utilidad';
		}
		//Menu
		echo '<table>';
		$cmd=2;
		echo '
			<tr>';
			$nivelUsuario=nivelUsuario();
			if($nivelUsuario>1 && $_POST['reg']==0){
				echo '<td><a href="#" onClick="$(\'#panel\').show();
				if(document.forma.monto.value==\'\'){
					$(\'#panel\').hide();
					alert(\'Necesita ingresar el monto\');
				}
				else if((document.forma.monto.value/1)<=0){
					$(\'#panel\').hide();
					alert(\'El monto debe ser mayor a 0\');
				}
				else if(document.forma.referencia.value==\'\'){
					$(\'#panel\').hide();
					alert(\'Debe de capturar la referencia\');
				}
				else if(document.forma.beneficiario.value==\'0\'){
					$(\'#panel\').hide();
					alert(\'Debe de seleccionar el beneficiario\');
				}
				else if(document.forma.autoriza.value==\'0\'){
					alert(\'Necesita seleccionar quien autoriza\');
					$(\'#panel\').hide();
				}
				else{
					atcr(\'vale_utilidad.php\',\'\',\'2\',\''.$Salida['cve'].'\');
				}"
				><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			}
			if($_POST['reg']>0 && $Salida['estatus']!='C' && $nivelUsuario>1){
				echo '<td><a href="#" onClick="if(confirm(\'Esta seguro de cancelar este folio?\')){ atcr(\'vale_utilidad.php\',\'\',\'3\',\''.$Salida['cve'].'\'); }"><img src="images/validono.gif" border="0">&nbsp;Cancelar</a></td><td>&nbsp;</td>';
			}
			echo '<td><a href="#" onClick="atcr(\'vale_utilidad.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion Vale de Utilidad</td></tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		if($_POST['reg']>0){if($nivelUsuario>2){
		echo '<tr align="left"><th>Estatus</th><td><select name="estatus2">';
				foreach($array_estatus2 as $k=>$v){				
					echo '<option value="'.$k.'"';if($Salida['estatus2']==$k){ echo ' selected';}
					echo '>'.$v.'</option>';
					
				}
		echo '</select>';
		echo '</td></tr> ';
		}else{echo'<input type="hidden" name="estatus2" value="'.$Salida['estatus2'].'"';}}
		echo '<tr><th align="left">Fecha</th><td><input type="text" name="fecha" id="fecha" class="readOnly" size="15" value="'.$fecha.'" readonly></td></tr>';
		echo '<tr><th align="left">Fecha Aplicacion</th><td><input type="text" name="fecha_aplicacion" id="fecha_aplicacion" class="readOnly" size="15" value="'.$fecha_aplicacion.'" readOnly>';
		echo '&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true,\'\',\'traeSaldos\')"><img src="images/calendario.gif" border="0"></a>';
		echo '</td></tr>';
		echo '<tr><th align="left">Referencia Bancaria</th><td><input type="text" name="referencia" id="referencia" class="textField" size="30" value="'.$Salida['referencia'].'"></td></tr>';
		
		echo '<input type="hidden" name="bandera" id="bandera" value="0">';
		echo '<tr><th align="left">Unidades<br>
		<a href="#" onClick="addUni();">Agregar Unidad</a></th><td>
		<table id="tabla1"><tr><th>Localidad</th><th>No. Eco.</th><th>Propietario</th><th>Saldo</th><th>Monto</th></tr>';
		$cantuni=0;
		if($_POST['reg']==0){
			echo '<tr>
			<td><select name="localidad0" onChange="limpiarUni(0)">';
				foreach($array_localidad as $k=>$v){
					echo '<option value="'.$k.'"';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>
			<td><input type="text" name="eco0" class="textField" value="" size="15" onKeyUp="if(event.keyCode==13){ traeUni(0);}"><input type="hidden" name="uni0" value=""></td>
			<td><input type="text" name="pro0" class="readOnly" size="50" value="" readOnly></td>
			<td><input type="text" name="sal0" class="readOnly" size="20" value="" readOnly></td>
			<td><input type="text" name="monto0" value"" class="textField" size="20" onKeyUp="validamonto(0);calcula();"></td></tr>';
			$cantuni++;
		}
		else{
			$res1=mysql_db_query($base,"SELECT * FROM vale_utilidad_detalle WHERE vale='".$_POST['reg']."' ORDER BY cve");
			while($row1=mysql_fetch_array($res1)){
				echo '<tr>
				<td><select name="localidad'.$cantuni.'" onChange="limpiarUni('.$cantuni.')">';
				foreach($array_localidad as $k=>$v){
					echo '<option value="'.$k.'"';
					if($array_localidaduni[$row1['unidad']]==$k) echo ' selected';
					echo '>'.$v.'</option>';
				}
				echo '</select></td>
				<td><input type="text" name="eco'.$cantuni.'" class="textField" value="'.$array_unidad[$row1['unidad']].'" size="15" onKeyUp="if(event.keyCode==13){ traeUni('.$cantuni.');}"><input type="hidden" name="uni'.$cantuni.'" value="'.$row1['unidad'].'"></td>
				<td><input type="text" name="pro'.$cantuni.'" class="readOnly" size="50" value="'.$array_propietario[$row1['unidad']].'" readOnly></td>
				<td><input type="text" name="sal'.$cantuni.'" class="readOnly" size="20" value="'.$row1['saldo'].'" readOnly></td>
				<td><input type="text" name="monto'.$cantuni.'" value="'.$row1['monto'].'" class="textField" size="20" onKeyUp="validamonto('.$cantuni.');calcula();"></td></tr>';
				$cantuni++;
			}
		}
		echo '</table></td></tr>';
		echo '<input type="hidden" name="cantuni" value="'.$cantuni.'">';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="readOnly" size="15" value="'.$Salida['monto'].'" readOnly></td></tr>';
		echo '<tr><th align="left">Tipo de Beneficiario</th><td><input type="radio" name="tipo_beneficiario" value="0" onClick="traeBenef();"';
		if($_POST['reg']==0 || $Salida['tipo_beneficiario']==0) echo ' checked';
		echo '>Externo&nbsp;
		<input type="radio" name="tipo_beneficiario" value="1" onClick="traeBenef();"';
		if($Salida['tipo_beneficiario']==1) echo ' checked';
		echo '>Unidades&nbsp;
		&nbsp;</td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario" class="textField">';
		echo '<option value="0">--- Seleccione un Beneficiario ---</option>';
		if($_POST['reg']==0){
			$res1=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas ORDER BY nombre");
			while ($row1=mysql_fetch_array($res1)) {
				echo '<option value="'.$row1['cve'].'"';
				echo '>'.$row1['nombre'].'</option>';
			}
		}
		else{
			if($Salida['tipo_beneficiario']==0){
				$res1=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas ORDER BY nombre");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['nombre'].' '.$row1['apaterno'].' '.$row1['amaterno'].'</option>';
				}
			}
			elseif($Salida['tipo_beneficiario']==1){
				$res1=mysql_db_query($base,"SELECT * FROM unidades ORDER BY no_eco");
				while ($row1=mysql_fetch_array($res1)) {
					echo '<option value="'.$row1['cve'].'"';
					if ($Salida["beneficiario"]==$row1['cve']) echo ' selected';
					echo '>'.$row1['no_eco'].' '.$array_empresa[$row1['empresa']].'</option>';
				}
			}
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Autoriza</th><td><select name="autoriza" id="autoriza" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_autoriza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="textField" rows="5" cols="50">'.$Salida['concepto'].'</textarea></td></tr>';
		echo '</table>';
		echo '<script language="javascript">
				function traeSaldo(){
					if(document.forma.no_eco.value==0){
						document.forma.saldo.value=0;
					}
					else{
						objeto1=crearObjeto();
						if (objeto1.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto1.open("POST","vale_utilidad.php",true);
							objeto1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto1.send("ajax=5&no_eco="+document.forma.no_eco.value);
							objeto1.onreadystatechange = function(){
								if (objeto1.readyState==4){
									datos=objeto1.responseText.split("|");
									if(datos[0]=="no"){
										alert("La unidad no existe");
									}
									else{
										document.forma.saldo.value=datos[0];
										document.forma.unidad.value=datos[1];
										document.forma.propietario.value=datos[2];
									}
								}
							}
						}
					}
				}
				
				function traeBenef(){
					objeto=crearObjeto();
					if (objeto.readyState != 0) {
						alert("Error: El Navegador no soporta AJAX");
					} else {
						if(document.forma.tipo_beneficiario[0].checked==true) tipo_benef=0;
						else if(document.forma.tipo_beneficiario[1].checked==true) tipo_benef=1;
						else if(document.forma.tipo_beneficiario[2].checked==true) tipo_benef=2;
						else if(document.forma.tipo_beneficiario[3].checked==true) tipo_benef=3;
						objeto.open("POST","vale_utilidad.php",true);
						objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
						objeto.send("ajax=3&tipo_beneficiario="+tipo_benef);
						objeto.onreadystatechange = function()
						{
							if (objeto.readyState==4){
								document.forma.beneficiario.options.length=0;
								document.forma.beneficiario.options[0]= new Option("---Seleccione un Beneficiario---","0");
								var opciones2=objeto.responseText.split("|");
								for (i = 0; i < opciones2.length-1; i++){
									datos=opciones2[i].split(",");
									document.forma.beneficiario.options[i+1]= new Option(datos[1], datos[0]);
								}
							}
						}
					}
				}
				
				function validamonto(ren){
					if(document.forma["uni"+ren].value==""){
						alert("No se ha cargado la unidad correctamente");
						document.forma["monto"+ren].value="";
					}
					/*else if((document.forma["monto"+ren].value/1)>(document.forma["sal"+ren].value/1) && '.intval($_SESSION[$archivo[(count($archivo)-1)]]).'<3){
						alert(\'El saldo es menor al monto\');
						document.forma["monto"+ren].value=document.forma["sal"+ren].value;
					}
					else if((document.forma["monto"+ren].value/1)>(document.forma["sal"+ren].value/1) && '.intval($_SESSION[$archivo[(count($archivo)-1)]]).'==3 && document.forma.bandera.value=="0"){
						if(!confirm(\'El saldo es menor al monto, desea continuar?\')){
							document.forma["monto"+ren].value=document.forma["sal"+ren].value;
						}
						else{
							document.forma.bandera.value="1";
						}
					}*/
					/*else if((document.forma["monto"+ren].value/1)>(document.forma["sal"+ren].value/1)){
						alert(\'El saldo es menor al monto\');
						document.forma["monto"+ren].value=document.forma["sal"+ren].value;
					}*/
				}
				
				function calcula(){
					total=0;
					for(i=0;i<(document.forma.cantuni.value/1);i++){
						total+=(document.forma["monto"+i].value/1);
					}
					document.forma.monto.value=total.toFixed(2);
				}
				
				function traeSaldos(ren){
					renglon = ren || 0;
					if(ren<(document.forma.cantuni.value/1)){
						traeUni(ren,1);
					}
				}
				
				function traeUni(ren,tip){
					tipo = tip || 0;
					if(document.forma["eco"+ren].value==""){
						document.forma["eco"+ren].focus();
						document.forma["uni"+ren].value="";
						document.forma["pro"+ren].value="";
						document.forma["sal"+ren].value="";
						//document.forma["monto"+ren].value="";
					}
					else{
						objeto2=crearObjeto();
						if (objeto2.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto2.open("POST","vale_utilidad.php",true);
							objeto2.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto2.send("ajax=5&localidad="+document.forma["localidad"+ren].value+"&eco="+document.forma["eco"+ren].value+"&ren="+ren);
							objeto2.onreadystatechange = function()
							{
								if (objeto2.readyState==4){
									if(objeto2.responseText=="0"){
										alert("Error en Unidad");
										document.forma["eco"+ren].focus();
										document.forma["uni"+ren].value="";
										document.forma["pro"+ren].value="";
										document.forma["sal"+ren].value="";
										//document.forma["monto"+ren].value="";
									}
									else{
										datos=objeto2.responseText.split("|");
										document.forma["uni"+ren].value=datos[0];
										document.forma["pro"+ren].value=datos[1];
										document.forma["sal"+ren].value=datos[2];
										//document.forma["monto"+ren].value="";
										document.forma["monto"+ren].focus();
									}
									calcula();
									if(tipo==1) traeSaldos(ren+1);
								}
							}
						}
					}
				}

				function limpiarUni(ren){
					document.forma["eco"+ren].value="";
					document.forma["uni"+ren].value="";
					document.forma["pro"+ren].value="";
					document.forma["sal"+ren].value="";
					document.forma["monto"+ren].value="";
				}
				
				function addUni(){
					var tblBody = document.getElementById("tabla1").getElementsByTagName("TBODY")[0];
					var lastRow = tblBody.rows.length;
					var iteration = document.forma["cantuni"].value;
					var newRow = tblBody.insertRow(lastRow);
					var newCell0 = newRow.insertCell(0);
					newCell0.innerHTML = \'<select name="localidad\'+iteration+\'" onChange="limpiarUni(\'+iteration+\');}">';
					foreach($array_localidad as $k=>$v){
						echo '<option value="'.$k.'">'.$v.'</option>';
					}
					echo '</select>\';
					var newCell1 = newRow.insertCell(1);
					newCell1.innerHTML = \'<input type="text" name="eco\'+iteration+\'" size="15" value="" class="textField" onKeyUp="if(event.keyCode==13){ traeUni(\'+iteration+\');}"><input type="hidden" name="uni\'+iteration+\'" value="">\';
					var newCell2 = newRow.insertCell(2);
					newCell2.innerHTML = \'<input type="text" name="pro\'+iteration+\'" value="" size="50" class="readOnly" readOnly>\';
					var newCell3 = newRow.insertCell(3);
					newCell3.innerHTML = \'<input type="text" name="sal\'+iteration+\'" value="" size="20" class="readOnly" readOnly>\';
					var newCell4 = newRow.insertCell(4);
					newCell4.innerHTML = \'<input type="text" name="monto\'+iteration+\'" size="20" value="" class="textField" onKeyUp="validamonto(\'+iteration+\');calcula();">\';
					document.forma["eco"+iteration].focus();
					iteration++;
					document.forma["cantuni"].value=iteration;
				}
								
			  </script>';
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'vale_utilidad.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'vale_utilidad.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
				<td><a href="#" onClick="if($(\'.chkimp\').is(\':checked\')){atcr(\'vale_utilidad.php\',\'_blank\',\'101\',\'0\');}"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir Vales</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Aplicacion Inicial</td><td><input type="text" name="fecha_apli_ini" id="fecha_apli_ini" class="readOnly" size="12" value="" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_apli_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Aplicacion Final</td><td><input type="text" name="fecha_apli_fin" id="fecha_apli_fin" class="readOnly" size="12" value="" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_apli_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No. Eco.</td><td><input type="text" class="textField" name="no_eco" id="no_eco" value="" size="10"></td></tr>';
		echo '<tr><td>Referencia</td><td><input type="text" class="textField" name="referencia" id="referencia" value="" size="10"></td></tr>';
		echo '<tr><td>Tipo de Beneficiario</td><td>
		<select name="tipo_benef" id="tipo_benef" onChange="traeBenef()">
		<option value="all" selected>--- Todos ---</option>
		<option value="0">Externos</option>
		<option value="1">Unidades</option>
		</select></td></tr>';
		echo '<tr><td>Beneficiario</td><td><select name="beneficiario" id="beneficiario" class="textField">';
		echo '<option value="all">--- Todos ---</option>';
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		echo '<script>
				function traeBenef(){
					if(document.forma.tipo_benef.value=="all"){
						document.forma.beneficiario.options.length=0;
						document.forma.beneficiario.options[0]= new Option("--- Todos ---","all");
					}
					else{
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
							alert("Error: El Navegador no soporta AJAX");
						} else {
							objeto.open("POST","vale_utilidad.php",true);
							objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
							objeto.send("ajax=3&tipo_beneficiario="+document.forma.tipo_benef.value);
							objeto.onreadystatechange = function()
							{
								if (objeto.readyState==4){
									document.forma.beneficiario.options.length=0;
									document.forma.beneficiario.options[0]= new Option("--- Todos ---","all");
									var opciones2=objeto.responseText.split("|");
									for (i = 0; i < opciones2.length-1; i++){
										datos=opciones2[i].split(",");
										document.forma.beneficiario.options[i+1]= new Option(datos[1], datos[0]);
									}
								}
							}
						}
					}
				}
			</script>';
	}
bottom();



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
			objeto.open("POST","vale_utilidad.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&tipo_benef="+document.forma.tipo_benef.value+"&beneficiario="+document.getElementById("beneficiario").value+"&no_eco="+document.getElementById("no_eco").value+"&referencia="+document.getElementById("referencia").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&fecha_apli_ini="+document.getElementById("fecha_apli_ini").value+"&fecha_apli_fin="+document.getElementById("fecha_apli_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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

?>

