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

//$rsParque=mysql_db_query($base,"SELECT * FROM parque");
$rsParque=mysql_db_query($base,"SELECT * FROM unidades");
while($Parque=mysql_fetch_array($rsParque)){
	$array_unidad[$Parque['cve']]=$Parque['no_eco'];
}

$rsMotivo=mysql_db_query($base,"SELECT * FROM motivos WHERE plaza='".$_POST['plaza']."' ORDER BY nombre");
while($Motivo=mysql_fetch_array($rsMotivo)){
	$array_motivo[$Motivo['cve']]=$Motivo['nombre'];
}

$rsPuesto=mysql_db_query($base,"SELECT * FROM puestos");
while($Puesto=mysql_fetch_array($rsPuesto)){
	$array_puesto[$Puesto['cve']]=$Puesto['nombre'];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM personal");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_personal[$Conductor['cve']]=$Conductor['folio'].' - '.$Conductor['nombre'];
}

$tamano=array(209.97,148.5);
$rsaccidentes=mysql_db_query($base,"SELECT * FROM accidentes WHERE cve='".$_POST['reg']."'");
$Accidente=mysql_fetch_array($rsaccidentes);
$rsconductor=mysql_db_query($base,"SELECT * FROM operadores WHERE cve='".$Accidente['conductor']."'");
$Conductor=mysql_fetch_array($rsconductor);
$pdf=new PDF_MC_Table('P','mm','LEGAL');
$pdf->AddPage();
//$pdf->Image('images/membrete.JPG',30,3,150,15);
$pdf->SetFont('Arial','B',16);
//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
//$this->Ln();
$pdf->SetY(23);
$pdf->Cell(200,10,'REPORTE DE ACCIDENTE',0,0,'C');
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->Cell(60,3,'Folio: '.$Accidente['folio'],0,0,'L');
if($Accidente['unidadesexternas']==1)
	$pdf->Cell(70,3,'Unidad: '.$Accidente['unidadext'],0,0,'C');
elseif($Accidente['tipo']==4)
	$pdf->Cell(70,3,'Unidad: '.$Accidente['unisinregistro'],0,0,'C');
else
	$pdf->Cell(70,3,'No. Económico: '.$array_unidad[$Accidente['unidad']],0,0,'C');
$pdf->Cell(70,3,'Fecha del Accidente: '.$Accidente['fecha_accidente'],0,0,'R');
$pdf->Ln();
$pdf->Ln();

$pdf->Cell(60,3,'Nombre del Operador:',0,0,'C');
$pdf->Cell(70,3,'Lugar donde ocurrió el accidente: ',0,0,'C');
$pdf->Cell(70,3,'Gestor que atendió: ',0,0,'C');
$pdf->Ln();
$pdf->Ln();
$y=$pdf->GetY();
if($Accidente['unidadesexternas']==1)
	$pdf->Cell(60,3,$Accidente['condext'],0,0,'C');
elseif($Accidente['tipo']==4)
	$pdf->MultiCell(60,3,$array_personal[$Accidente['opesinregistro']],0,'C');
else
	$pdf->MultiCell(60,3,$Conductor['credencial'].' - '.$Conductor['nombre'],0,'C');
$y2=$pdf->GetY();
$pdf->SetXY(70,$y);
$pdf->MultiCell(70,3,$Accidente['lugar'],0,"C");
$y3=$pdf->GetY();
$pdf->SetXY(140,$y);
$pdf->MultiCell(70,3,$Accidente['gestor'],0,'C');
$y4=$pdf->GetY();
if($y2>$y3 && $y2>$y4)
	$y5=$y2;
elseif($y3>$y2 && $y3>$y4)
	$y5=$y3;
elseif($y4>$y2 && $y4>$y3)
	$y5=$y4;
else
	$y5=$y3;
	
$pdf->SetY($y5);

$pdf->Line(10,$y5,210,$y5);
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->Cell(200,3,'Descripción del Accidente:',0,0,'L');
$pdf->Ln();
$pdf->Ln();
$pdf->MultiCell(200,5,$Accidente['descripcion'],0,"L");
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$rsfirmas=mysql_db_query($base,"SELECT * FROM cargos_admon WHERE accidente='Si' AND plaza='".$Accidente['plaza']."' AND fecha<='".$Accidente['fecha']."' AND (fechaf>='".$Accidente['fecha']."' OR fechaf='0000-00-00')");
$numfirmas=mysql_num_rows($rsfirmas);
$ancho=200/($numfirmas + 1);
$array_puestoadmon=array();
$i=0;
$pdf->SetFont('Arial','U',10);
$pdf->Cell($ancho,5,$Accidente['gestor'],0,0,'C');
while($Firmas=mysql_fetch_array($rsfirmas)){
	
	$pdf->Cell($ancho,5,$Firmas['nombre'],0,0,'C');
	$array_puestoadmon[$i]=$array_puesto[$Firmas['puesto']];
	$i++;
}
$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->Cell($ancho,5,'Vo. Bo. Gestor',0,0,'C');
$y=$pdf->GetY();
for($x=0;$x<$i;$x++){
	$pdf->SetXY(($ancho*($x+1))+10,$y);
	$pdf->MultiCell($ancho,5,$array_puestoadmon[$x]."
	Autorizó Reparación",0,'C');
	
}

$pdf->Ln();
$pdf->Ln();
$select= " SELECT * FROM cambios_datos_accidente WHERE cve_accidente='".$Accidente['cve']."' and plaza='".$Accidente['plaza']."'
		ORDER BY cve desc";

$rsSalidas=mysql_db_query($base,$select);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(200,5,'Conclusiones',0,0,'C');
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,5,'Folio',1,0,'C');
$pdf->Cell(30,5,'Fecha',1,0,'C');
$pdf->Cell(100,5,'Conclusion',1,0,'C');
$pdf->Cell(30,5,'Usuario',1,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',9);
$pdf->SetWidths(array(30,30,100,30));
	
while($Salidas=mysql_fetch_array($rsSalidas)) {
	$renglon=array();
	$renglon[]=$Salidas['folio'];
	$renglon[]=$Salidas['fecha'];
	$renglon[]=$Salidas['valor'];
	$renglon[]=$array_usuario[$Salidas['usuario']];
	$pdf->Row($renglon);
}
$pdf->Ln();
$pdf->Ln();
$select= " SELECT * FROM salidas_accidentes WHERE accidente='".$Accidente['cve']."' and estatus='1' and plaza='".$Accidente['plaza']."'
		ORDER BY fecha desc";

$rsSalidas=mysql_db_query($base,$select);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(200,5,'SALIDAS',0,0,'C');
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,5,'Folio',1,0,'C');
$pdf->Cell(30,5,'Fecha',1,0,'C');
$pdf->Cell(30,5,'Monto',1,0,'C');
$pdf->Cell(50,5,'Descripcion',1,0,'C');
$pdf->Cell(30,5,'Estatus',1,0,'C');
$pdf->Cell(30,5,'Usuario',1,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',9);
$pdf->SetWidths(array(30,30,30,50,30,30));
$totsalidas=0;	
while($Salidas=mysql_fetch_array($rsSalidas)) {
	$renglon=array();
	$renglon[]=$Salidas['cve'];
	$renglon[]=$Salidas['fecha'];
	$renglon[]=number_format($Salidas['monto'],2);
	$renglon[]=$Salidas['descripcion'];
	$renglon[]=$array_estatus_accidentes[$Salidas['estatus']];
	$renglon[]=$array_usuario[$Salidas['usuario']];
	$totsalidas+=$Salidas['monto'];
	$pdf->Row($renglon);
}
$pdf->Ln();
$pdf->SetFont('Arial','B',9);
$pdf->Cell(60,5,"Total: ",0,0,'R');
$pdf->Cell(30,5,number_format($totsalidas,2),0,0,'C');
$pdf->SetFont('Arial','',9);
$rsFotos=mysql_db_query($base,"SELECT * FROM imagenes_accidentes WHERE accidente='".$_POST['reg']."' ORDER BY cve");
$imgxhoja=4;
while($Foto=mysql_fetch_array($rsFotos)){
	if($imgxhoja==4){
		$pdf->AddPage();
		$pdf->Image('images/membrete.JPG',30,3,150,15);
		$pdf->SetFont('Arial','B',16);
		$pdf->SetY(18);
		$pdf->Cell(200,10,'REPORTE DE ACCIDENTE',0,0,'C');
		$pdf->Image('imgaccidentes/'.$Foto['nombre'].'.jpg',65,30,90,55);
		$imgxhoja=0;
	}
	if($imgxhoja==1){
		$pdf->Image('imgaccidentes/'.$Foto['nombre'].'.jpg',65,90,90,55);
	}
	if($imgxhoja==2){
		$pdf->Image('imgaccidentes/'.$Foto['nombre'].'.jpg',65,150,90,55);
	}
	if($imgxhoja==3){
		$pdf->Image('imgaccidentes/'.$Foto['nombre'].'.jpg',65,210,90,55);
	}
	$imgxhoja++;
}



$pdf->Output();	



?>