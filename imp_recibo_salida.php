<?
include ("main.php"); 
include('fpdf153/fpdf.php');
include("numlet.php");	
/*** ARREGLOS ***********************************************************/

$rsPlaza=mysql_db_query($base,"SELECT * FROM plazas");
while($Plaza=mysql_fetch_array($rsPlaza)){
	$array_plaza[$Plaza['cve']]=$Plaza['nombre'];
}


$rsUsuario=mysql_db_query($base,"SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['nombre'];
}

$rsBenef=mysql_db_query($base,"SELECT * FROM beneficiarios_salidas WHERE 1");
while($Benef=mysql_fetch_array($rsBenef)){
	$array_benef[$Benef['cve']]=$Benef['nombre'];
}

$rsMotivo=mysql_db_query($base,"SELECT * FROM motivos_salida WHERE 1 ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsPuesto=mysql_db_query($base,"SELECT * FROM puestos");
while($Puesto=mysql_fetch_array($rsPuesto)){
	$array_puesto[$Puesto['cve']]=$Puesto['nombre'];
}

$rsUnidad=mysql_db_query($base,"SELECT * FROM parque");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'].' - '.$Unidad['propietario'];
}

$rsconductor=mysql_db_query($base,"SELECT * FROM conductores");
while($Conductor=mysql_fetch_array($rsconductor)){
	$array_conductor[$Conductor['cve']]=$Conductor['credencial'].' - '.$Conductor['nombre'];
}

$rspersonal=mysql_db_query($base,"SELECT * FROM personal");
while($Personal=mysql_fetch_array($rspersonal)){
	$array_personal[$Personal['cve']]=$Personal['nombre'];
}

//$update= "UPDATE recibos_salidas SET usuimp='".$_SESSION['CveUsuario']."',impreso='1' WHERE plaza='".$_POST['plaza']."' AND cve='".$_POST['reg']."' ";
$update=mysql_db_query($base,$update);
$tamano=array(209.97,148.5);
$rssalida=mysql_db_query($base,"SELECT * FROM recibos_salidas WHERE  cve='".$_POST['reg']."'");
$Salida=mysql_fetch_array($rssalida);
$beneficiario="";
//if($Salida['tipo_beneficiario']==0)
	$beneficiario=$array_benef[$Salida['beneficiario']];
//elseif($Salida['tipo_beneficiario']==1)
//	$beneficiario=$array_unidad[$Salida['beneficiario']];
//elseif($Salida['tipo_beneficiario']==2)
//	$beneficiario=$array_conductor[$Salida['beneficiario']];
//elseif($Salida['tipo_beneficiario']==3)
//	$beneficiario=$array_personal[$Salida['beneficiario']];
$pdf=new FPDF('P','mm','LETTER');
$pdf->AddPage();
//$pdf->Image('images/membrete.JPG',30,3,150,15);
$pdf->SetFont('Arial','B',16);
//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
//$this->Ln();
if($Salida['estatus']=="C"){$Salida['monto']=0;}
$pdf->SetY(23);
$pdf->Cell(95,10,'Recibo de Salida',0,0,'L');
$pdf->Cell(95,10,'Folio: '.$_POST['reg'],0,0,'R');
$pdf->Ln();
$pdf->SetFont('Arial','B',10);
$pdf->Cell(95,5,'',0,0,'L');
$pdf->Cell(95,5,'Bueno por: $ '.number_format($Salida['monto'],2),0,0,'R');
$pdf->Ln();
$y=$pdf->GetY();
$pdf->MultiCell(95,5,'Motivo: '.$array_motivo[$Salida['motivo']],0,'L');
$pdf->SetXY(105,$y);
$pdf->Cell(95,5,'Fecha: '.fecha_letra($Salida['fecha']),0,0,'R');
$pdf->Ln();
if($Salida['estatus']=="A"){
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,6,'Activo',1,0,'C');
	$pdf->SetFont('Arial','B',12);
}
if($Salida['estatus']=="C"){
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(190,6,'CANCELADO',1,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',12);
//	$pdf->Cell(190,6,'('.$Salida['obscan'].')',1,0,'C');

	
}
$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(190,5,"Recibi la cantidad de ".numlet($Salida['monto']),0,"R");
$pdf->Ln();
$pdf->MultiCell(190,5,"Por Concepto de: ".$Salida['concepto'],0,"R");
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','U',12);
$pdf->Cell(60,5,'');
$pdf->MultiCell(70,5,$beneficiario,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',12);
$pdf->Cell(190,5,"Recibi",0,0,'C');
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$rsfirmas=mysql_db_query($base,"SELECT * FROM cargos_admon WHERE firma='Si' AND plaza='".$_POST['plaza']."' AND fecha<='".$Salida['fecha']."' AND (fechaf>='".$Salida['fecha']."' OR fechaf='0000-00-00')");
$numfirmas=mysql_num_rows($rsfirmas);
$ancho=190/$numfirmas;
$array_puestoadmon=array();
$i=0;
$pdf->SetFont('Arial','U',9);
while($Firmas=mysql_fetch_array($rsfirmas)){
	
	$pdf->Cell($ancho,5,$Firmas['nombre'],0,0,'C');
	$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
	$i++;
}
$pdf->Ln();
$pdf->SetFont('Arial','',9);
for($x=0;$x<$i;$x++){
	$pdf->Cell($ancho,5,$array_puestoadmon[$x],0,0,'C');
}
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Arial','',10);
$pdf->Cell(95,5,'Impreso por: '.$array_usuario[$_SESSION['CveUsuario']],0,0,'L');
$pdf->Cell(95,5,'Creado por: '.$array_usuario[$Salida['usuario']],0,0,'R');
$pdf->Output();	



?>