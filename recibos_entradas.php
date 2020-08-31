<?php 

include ("main.php"); 

$rsdepa=mysql_query("SELECT * FROM recaudaciones ORDER BY nombre");
while($depa=mysql_fetch_array($rsdepa)){
	$array_recaudacion[$depa['cve']]=$depa['nombre'];
}

$rsBenef=mysql_query("SELECT * FROM beneficiarios_salidas ORDER BY nombre");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_beneficiario[$Benef['cve']]=$Benef['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM motivos_salida ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsMotivo=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_empresa[$Motivo['cve']]=$Motivo['nombre'];
}

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
	$array_nomusuario[$Usuario['cve']]=$Usuario['nombre'];
}
$rsBenef=mysql_db_query($base,"SELECT * FROM cat_estatus ORDER BY nombre");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_estatus2[$Benef['cve']]=$Benef['nombre'];
}

$array_cargo = array(1=>'Administracion', 2=>'Seguro Interno', 3=>'Mutualidad', 4=>'Prorrata',5=>'Seguridad', 6=>'Fianza', 7=>'Otros Ingresos',8=>'Tag',9=>'Bases');

$res=mysql_query("SELECT recaudacion FROM usuario_recaudacion WHERE usuario='".$_POST['cveusuario']."' AND fecha=CURDATE()");
$row=mysql_fetch_array($res);
$recaudacion_usuario=$_SESSION['RecUsuario'];

if($_POST['cmd']==100){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
	$rssalida=mysql_query("SELECT * FROM recibos_entradas WHERE cve='".$_POST['reg']."'");
	$Salida=mysql_fetch_array($rssalida);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(95,10,'Recibo de Entrada',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->SetFont('Arial','B',10);
	$pdf->Ln();
	//$pdf->Cell(95,4,'Recaudacion: '.$array_recaudacion[$Salida['recaudacion']],0,0,'L');
	$pdf->Cell(95,4,'Fecha Aplicacion: '.$Salida['fecha_aplicacion'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,4,'Seccion: '.$array_cargo[$Salida['cargo']]);
	$pdf->Cell(95,4,'Fecha: '.$Salida['fecha'],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,4,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Empresa: '.$array_empresa[$Salida['empresa']],0,0,'L');
	$pdf->Ln();


	
	$pdf->MultiCell(95,4,numlet($Salida['monto']),0,0,'R');
	$pdf->MultiCell(190,4,'Concepto: '.$Salida['concepto'],0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->MultiCell(190,4,'____________________________________
	Beneficiario
	'.$array_beneficiario[$Salida['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	/*$rsfirmas=mysql_query("SELECT * FROM ".$pre."cargos_admon WHERE firma='Si' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	if($numfirmas==0){
		$pdf->Cell(0,4,"NO HAY ADMINISTRATIVOS",0,0,'C');
	}
	else{
		$ancho=190/$numfirmas;
		$array_puestoadmon=array();
		$i=0;
		$pdf->SetFont('Arial','U',10);
		while($Firmas=mysql_fetch_array($rsfirmas)){
			
			$pdf->Cell($ancho,4,$Firmas['nombre'],0,0,'C');
			$array_puestoadmon[$i]=$Firmas['puesto'];
			$i++;
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		for($x=0;$x<$i;$x++){
			$pdf->Cell($ancho,4,$array_puestoadmon[$x],0,0,'C');
		}
	}*/
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,'Autoriza',0,0,'C');
	$pdf->Cell(95,4,'Entrega',0,0,'C');
	$pdf->Ln();
	$pdf->SetXY(10,135);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(95,10,'Recibo de Entrada   -  COPIA',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
	$pdf->SetFont('Arial','B',10);
	$pdf->Ln();
	//$pdf->Cell(95,4,'Recaudacion: '.$array_recaudacion[$Salida['recaudacion']],0,0,'L');
	$pdf->Cell(95,4,'Fecha Aplicacion: '.$Salida['fecha_aplicacion'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,4,'Seccion: '.$array_cargo[$Salida['cargo']]);
	$pdf->Cell(95,4,'Fecha: '.$Salida['fecha'],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,4,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Empresa: '.$array_empresa[$Salida['empresa']],0,0,'L');
	$pdf->Ln();


	
	$pdf->MultiCell(95,4,numlet($Salida['monto']),0,0,'R');
	$pdf->MultiCell(190,4,'Concepto: '.$Salida['concepto'],0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->MultiCell(190,4,'____________________________________
	Beneficiario
	'.$array_beneficiario[$Salida['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	/*$rsfirmas=mysql_query("SELECT * FROM ".$pre."cargos_admon WHERE firma='Si' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	if($numfirmas==0){
		$pdf->Cell(0,4,"NO HAY ADMINISTRATIVOS",0,0,'C');
	}
	else{
		$ancho=190/$numfirmas;
		$array_puestoadmon=array();
		$i=0;
		$pdf->SetFont('Arial','U',10);
		while($Firmas=mysql_fetch_array($rsfirmas)){
			
			$pdf->Cell($ancho,4,$Firmas['nombre'],0,0,'C');
			$array_puestoadmon[$i]=$Firmas['puesto'];
			$i++;
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		for($x=0;$x<$i;$x++){
			$pdf->Cell($ancho,4,$array_puestoadmon[$x],0,0,'C');
		}
	}*/
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,'Autoriza',0,0,'C');
	$pdf->Cell(95,4,'Entrega',0,0,'C');
	$pdf->Ln();
	$pdf->Output();
	exit();
}

if($_POST['cmd']==102){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	$pdf=new FPDF('P','mm','LETTER');
		for($i=0;$i<count($_POST['sel']);$i++){
	$rssalida=mysql_query("SELECT * FROM recibos_entradas WHERE cve='".$_POST['sel'][$i]."'");
	$Salida=mysql_fetch_array($rssalida);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',18);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(95,10,'Recibo de Entrada',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['sel'][$i],0,0,'R');
	$pdf->SetFont('Arial','B',10);
	$pdf->Ln();
	//$pdf->Cell(95,4,'Recaudacion: '.$array_recaudacion[$Salida['recaudacion']],0,0,'L');
	$pdf->Cell(95,4,'Fecha Aplicacion: '.$Salida['fecha_aplicacion'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,4,'Seccion: '.$array_cargo[$Salida['cargo']]);
	$pdf->Cell(95,4,'Fecha: '.$Salida['fecha'],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,4,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Empresa: '.$array_empresa[$Salida['empresa']],0,0,'L');
	$pdf->Ln();


	
	$pdf->MultiCell(95,4,numlet($Salida['monto']),0,0,'R');
	$pdf->MultiCell(190,4,'Concepto: '.$Salida['concepto'],0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->MultiCell(190,4,'____________________________________
	Beneficiario
	'.$array_beneficiario[$Salida['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	/*$rsfirmas=mysql_query("SELECT * FROM ".$pre."cargos_admon WHERE firma='Si' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	if($numfirmas==0){
		$pdf->Cell(0,4,"NO HAY ADMINISTRATIVOS",0,0,'C');
	}
	else{
		$ancho=190/$numfirmas;
		$array_puestoadmon=array();
		$i=0;
		$pdf->SetFont('Arial','U',10);
		while($Firmas=mysql_fetch_array($rsfirmas)){
			
			$pdf->Cell($ancho,4,$Firmas['nombre'],0,0,'C');
			$array_puestoadmon[$i]=$Firmas['puesto'];
			$i++;
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		for($x=0;$x<$i;$x++){
			$pdf->Cell($ancho,4,$array_puestoadmon[$x],0,0,'C');
		}
	}*/
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,'Autoriza',0,0,'C');
	$pdf->Cell(95,4,'Entrega',0,0,'C');
	$pdf->Ln();
	$pdf->SetXY(10,135);
	$pdf->Cell(190,10,'Grupo Zitlaltepec',0,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(95,10,'Recibo de Salida   -  COPIA',0,0,'L');
	$pdf->Cell(95,10,'Folio: '.$_POST['sel'][$i],0,0,'R');
	$pdf->SetFont('Arial','B',10);
	$pdf->Ln();
	//$pdf->Cell(95,4,'Recaudacion: '.$array_recaudacion[$Salida['recaudacion']],0,0,'L');
	$pdf->Cell(95,4,'Fecha Aplicacion: '.$Salida['fecha_aplicacion'],0,0,'L');
	$pdf->Ln();
	$pdf->Cell(95,4,'Seccion: '.$array_cargo[$Salida['cargo']]);
	$pdf->Cell(95,4,'Fecha: '.$Salida['fecha'],0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Motivo: '.$array_motivo[$Salida['motivo']],0,0,'L');
	$pdf->Cell(95,4,'Monto: $'.number_format($Salida['monto'],2),0,0,'R');
	$pdf->Ln();
	$pdf->Cell(95,4,'Empresa: '.$array_empresa[$Salida['empresa']],0,0,'L');
	$pdf->Ln();


	
	$pdf->MultiCell(95,4,numlet($Salida['monto']),0,0,'R');
	$pdf->MultiCell(190,4,'Concepto: '.$Salida['concepto'],0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->MultiCell(190,4,'____________________________________
	Beneficiario
	'.$array_beneficiario[$Salida['beneficiario']],0,'C');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	/*$rsfirmas=mysql_query("SELECT * FROM ".$pre."cargos_admon WHERE firma='Si' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
	$numfirmas=mysql_num_rows($rsfirmas);
	if($numfirmas==0){
		$pdf->Cell(0,4,"NO HAY ADMINISTRATIVOS",0,0,'C');
	}
	else{
		$ancho=190/$numfirmas;
		$array_puestoadmon=array();
		$i=0;
		$pdf->SetFont('Arial','U',10);
		while($Firmas=mysql_fetch_array($rsfirmas)){
			
			$pdf->Cell($ancho,4,$Firmas['nombre'],0,0,'C');
			$array_puestoadmon[$i]=$Firmas['puesto'];
			$i++;
		}
		$pdf->Ln();
		$pdf->SetFont('Arial','',10);
		for($x=0;$x<$i;$x++){
			$pdf->Cell($ancho,4,$array_puestoadmon[$x],0,0,'C');
		}
	}*/
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Cell(95,4,'________________________________________',0,0,'C');
	$pdf->Ln();
	$pdf->Cell(95,4,'Autoriza',0,0,'C');
	$pdf->Cell(95,4,'Entrega',0,0,'C');
	$pdf->Ln();
	}
	$pdf->Output();
	exit();
}

if($_POST['cmd']==101){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST;
			$this->SetFont('Arial','B',16);
			$this->SetY(23);
			$this->MultiCell(275,5,'Listado de Recibos de Entrada',0,'C');
			$this->Ln();
			$this->SetFont('Arial','B',9);
			$this->Cell(20,4,'Folio',0,0,'C',0);
			$this->Cell(20,4,'Fecha',0,0,'C',0);
			$this->Cell(20,4,'Fec. Apl.',0,0,'C',0);
			$this->Cell(20,4,'Seccion',0,0,'C',0);
			$this->Cell(50,4,'Beneficiario',0,0,'C',0);
			$this->Cell(30,4,'Motivo',0,0,'C',0);
			$this->Cell(25,4,'Monto',0,0,'C',0);
			$this->Cell(60,4,'Concepto',0,0,'C',0);
			$this->Cell(20,4,'Usuario',0,0,'C',0);
			$this->Ln();		
		}
		//Pie de página
		function Footer(){
			//Posición: a 1,5 cm del final
			$this->SetY(-15);
			//Arial bold 12
			$this->SetFont('Arial','B',11);
			//Número de página
			$this->Cell(0,10,'Página '.$this->PageNo().' de {nb}',0,0,'C');
		}
	}
	$pdf=new FPDF2('L','mm','LETTER');
	$pdf->AliasNbPages();
	$pdf->AddPage('L');
	$pdf->SetFont('Arial','',9);
	$total=array();
	$i=0;
	$pdf->SetWidths(array(20,20,20,20,50,30,25,60,20));
	$pdf->SetAligns(array('C','C','C','L','L','L','R','L','L'));
	$suma=0;
	for($i=0;$i<count($_POST['sel']);$i++){
		$res=mysql_query("SELECT * FROM recibos_entradas WHERE cve='".$_POST['sel'][$i]."'");
		$row=mysql_fetch_array($res);
		$renglon=array();
		if($row['estatus']=='C'){
			$row['cve'].='(C)';
			$row['monto']=0;
		}
		$renglon[]=$row['cve'];
		$renglon[]=$row['fecha'].' '.$row['hora'];
		$renglon[]=$row['fecha_aplicacion'];
		$renglon[]=$array_cargo[$row['cargo']];
		$renglon[]=$array_beneficiario[$row['beneficiario']];
		$renglon[]=$array_motivo[$row['motivo']];
		$renglon[]=number_format($row['monto'],2);
		$renglon[]=$row['concepto'];
		$renglon[]=$array_usuario[$row['usuario']];
		$pdf->Row($renglon);
		$suma=$suma+$row['monto'];
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(60,5,"Total:",0,0,'R');
	$pdf->Cell(25,5,number_format($suma,2),0,0,'R');
	$pdf->Output();
	exit();
}


/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		$nivelUsuario = nivelUsuario();
		//Listado de plazas
		$select= " SELECT * FROM recibos_entradas WHERE fecha BETWEEN '".$_POST['fecha_ini']."' AND '".$_POST['fecha_fin']."'";
		if ($_POST['motivo']!="all") { $select.=" AND motivo='".$_POST['motivo']."' "; }
		if ($_POST['cargo']!="all") { $select.=" AND cargo='".$_POST['cargo']."' "; }
		if ($_POST['beneficiario']!="all") { $select.=" AND beneficiario='".$_POST['beneficiario']."' "; }
		if ($_POST['empresa']!="all") { $select.=" AND empresa='".$_POST['empresa']."' "; }
		if($_POST['usuario']!='') $select.=" AND usuario='".$_POST['usuario']."'";
		$select.=" ORDER BY cve DESC";
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
			echo '<tr><td bgcolor="#E9F2F8" colspan="13">'.mysql_num_rows($res).' Registro(s)</td></tr>';
			echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th><td><input type="checkbox" name="selall" value="1" onClick="
			if(this.checked) $(\'.seleccionar\').attr(\'checked\',\'checked\');
			else $(\'.seleccionar\').removeAttr(\'checked\');"></th><th>Folio</th><th>Fecha</th><th>Fecha Aplicacion</th>
			<th>Seccion</th><th>Empresa</th><th>Beneficiario</th><th>Motivo</th><th>Monto</th><th>Concepto</th><th>Estatus</th><th>Usuario</th>';
			echo '</tr>';//<th>P.Costo</th><th>P.Venta</th>
			$total=0;
			while($row=mysql_fetch_array($res)) {
				rowb();
				if($row['estatus']!='C'){
					echo '<td align="center" width="40" nowrap>
					<a href="#" onClick="atcr(\'\',\'_blank\',\'100\','.$row['cve'].')"><img src="images/b_print.png" border="0" title="Imprimir '.$row['cve'].'"></a>';
					if($nivelUsuario > 2)
						echo '&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar el registro?\')) atcr(\'\',\'\',\'3\','.$row['cve'].')"><img src="images/validono.gif" border="0" title="Cancelar '.$row['cve'].'"></a></td>';
				}
				else{
					echo '<td align="center" width="40" nowrap>Cancelado</td>';
					$row['monto']=0;
				}
				echo '<td align="center"><input type="checkbox" name="sel[]" class="seleccionar" value="'.$row['cve'].'"></td>';
				echo '<td align="center">'.$row['cve'].'</td>';
				echo '<td align="center">'.$row['fecha'].' '.$row['hora'].'</td>';
				echo '<td align="center">'.$row['fecha_aplicacion'].'</td>';
				echo '<td align="left">'.utf8_encode($array_cargo[$row['cargo']]).'</td>';
				echo '<td align="left">'.utf8_encode($array_empresa[$row['empresa']]).'</td>';				
				echo '<td align="left">'.utf8_encode($array_beneficiario[$row['beneficiario']]).'</td>';				
				echo '<td align="left">'.utf8_encode($array_motivo[$row['motivo']]).'</td>';
				echo '<td align="right">'.number_format($row['monto'],2).'</td>';
				echo '<td align="left">'.utf8_encode($row['concepto']).'</td>';
				echo '<td align="center">';if($row["estatus"]!="C" and nivelUsuario()>2 and $row["estatus2"]=="1" ){echo'<a href="#" onClick="atcr(\'recibos_entradas.php\',\'\',4,'.$row['cve'].')"><img src="images/validosi.gif" border="0"></a>';}echo''.$array_estatus2[$row["estatus2"]].'</td>';
				echo '<td align="left">'.$array_usuario[$row['usuario']].'</td>';
				echo '</tr>';
				$total+=$row['monto'];
			}
			echo '	
				<tr>
				<td colspan="9" bgcolor="#E9F2F8">';menunavegacion();echo '</td>
				<td align="right" bgcolor="#E9F2F8">'.number_format($total,2).'</td>
				<td colspan="3" bgcolor="#E9F2F8">&nbsp;</td>
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
if ($_POST['cmd']==4) {
	$delete= "UPDATE recibos_entradas SET estatus2='2' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	$_POST['cmd']=0;
}


if ($_POST['cmd']==3) {
	$delete= "UPDATE recibos_entradas SET estatus='C',fechacan='".fechaLocal()." ".horaLocal()."',usucan='".$_POST['cveusuario']."' WHERE cve='".$_POST['reg']."' ";
	$ejecutar=mysql_query($delete);
	$_POST['cmd']=0;
}

/*** ACTUALIZAR REGISTRO  **************************************************/

if ($_POST['cmd']==2) {
		$insert = " INSERT recibos_entradas 
					SET monto='".$_POST['monto']."',beneficiario='".$_POST['beneficiario']."',concepto='".$_POST['concepto']."',estatus='A',estatus2='1',
						usuario='".$_POST['cveusuario']."',fecha='".fechaLocal()."',hora='".horaLocal()."',motivo='".$_POST['motivo']."',
						recaudacion='".$_POST['recaudacion']."',empresa='".$_POST['empresa']."',fecha_aplicacion='".$_POST['fecha_aplicacion']."',
						cargo='".$_POST['cargo']."'";
		$ejecutar = mysql_query($insert) or die(mysql_error());

	$_POST['cmd']=0;
	
}


/*** EDICION  **************************************************/

	if ($_POST['cmd']==1) {
		
		$select=" SELECT * FROM recibos_entradas WHERE cve='".$_POST['reg']."' ";
		$res=mysql_query($select);
		$row=mysql_fetch_array($res);
		if($_POST['reg']>0){
			$fecha=$row['fecha'];
			$Encabezado = 'Folio No.'.$_POST['reg'];
		}
		else{
			$fecha=fechaLocal();
			$fechapag=fechaLocal();
			$Encabezado = 'Nuevo Recibo de Entrada';
		}
		//Menu
		echo '<table>';
		echo '
			<tr>';
			if(nivelUsuario()>1)
				echo '<td id="btnguardar"><a href="#" onClick="
				$(\'#panel\').show();
				if(document.forma.monto.value==\'\'){
					alert(\'Necesita ingresar el monto\');
					$(\'#panel\').hide();
				}
				else if((document.forma.monto.value/1)<=0){
					alert(\'El monto debe ser mayor a 0\');
					$(\'#panel\').hide();
				}
				else if(document.forma.empresa.value==\'0\'){
					alert(\'Necesita seleccionar la empresa\');
					$(\'#panel\').hide();
				}
				else if(document.forma.motivo.value==\'0\'){
					alert(\'Necesita seleccionar el motivo\');
					$(\'#panel\').hide();
				}
				else if(document.forma.cargo.value==\'0\'){
					alert(\'Necesita seleccionar la seccion\');
					$(\'#panel\').hide();
				}
				else if(document.forma.beneficiario.value==\'0\'){
					alert(\'Necesita seleccionar un beneficiario\');
					$(\'#panel\').hide();
				}
				else
					atcr(\'recibos_entradas.php\',\'\',\'2\',\''.$row['cve'].'\');"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="$(\'#panel\').show();atcr(\'recibos_entradas.php\',\'\',\'0\',\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;Volver</a></td><td>&nbsp;</td>
			</tr>';
		echo '</table>';
		echo '<br>';
		
		//Formulario 
		echo '<table>';
		echo '<tr><td class="tableEnc">Edicion de Recibos de Entrada</td></tr>';
		echo '</table>';
		
		echo '<table>';
		echo '<tr><th align="left">'.$Encabezado.'</th><td>&nbsp;</td></tr>';
		echo '<tr><td>Fecha Aplicacion</td><td><input type="text" name="fecha_aplicacion" id="fecha_aplicacion" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_aplicacion,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><th align="left">Seccion</th><td><select name="cargo" id="cargo">';
		echo '<option value="0">Seleccione</option>';
		foreach($array_cargo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		
		echo '<tr><th align="left">Empresa</th><td><select name="empresa" id="empresa" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Motivo</th><td><select name="motivo" id="motivo" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_motivo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Beneficiario</th><td><select name="beneficiario" id="beneficiario" class="textField"><option value="0">---Seleccione---</option>';
		foreach($array_beneficiario as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th align="left">Monto</th><td><input type="text" name="monto" id="monto" class="textField" size="15" value="'.$row['monto'].'"></td></tr>';
		echo '<tr><th valign="top" align="left">Concepto</th><td><textarea name="concepto" id="concepto" class="textField" rows="5" cols="50">'.$row['concepto'].'</textarea></td></tr>';
		echo '</table>';	
		
	}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0"></a>&nbsp;&nbsp;Buscar</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'recibos_entradas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0"></a>&nbsp;Nuevo</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'recibos_entradas.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimir</td><td>&nbsp;</td>
				<td><a href="#" onClick="atcr(\'recibos_entradas.php\',\'_blank\',\'102\',\'0\');"><img src="images/b_print.png" border="0"></a>&nbsp;Imprimi por Recibos</td><td>&nbsp;</td>
			 </tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa" class="textField"><option value="all">---Todos---</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Motivo</td><td><select name="motivo" id="motivo" class="textField"><option value="all">---Todos---</option>';
		foreach($array_motivo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Beneficiario</td><td><select name="beneficiario" id="beneficiario" class="textField"><option value="all">---Todos---</option>';
		foreach($array_beneficiario as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Seccion</td><td><select name="cargo" id="cargo">';
		echo '<option value="all">--- Todos ---</option>';
		foreach($array_cargo as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Usuario</td><td><select name="usuario" id="usuario"><option value="">--- Todos ---</option>';
	foreach($array_usuario as $k=>$v){
		echo '<option value="'.$k.'">'.$v.'</option>';
	}
	echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';

		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
	
bottom();



/*** RUTINAS JS **************************************************/
echo '
<Script language="javascript">

	function buscarRegistros()
	{
		benef=document.forma.beneficiario.value;
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recibos_entradas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&empresa="+document.getElementById("empresa").value+"&cargo="+document.getElementById("cargo").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&motivo="+document.getElementById("motivo").value+"&beneficiario="+benef+"&numeroPagina="+document.getElementById("numeroPagina").value+"&usuario="+document.getElementById("usuario").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	function guardaTaq(taqpag,salida)
	{
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","recibos_salidas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&taqpag="+taqpag+"&salida="+salida);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{
					alert("Se guardar la taquilla");
				}
			}
		}
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

