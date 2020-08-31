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
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

//$rsParque=mysql_db_query($base,"SELECT * FROM parque");
$rsParque=mysql_db_query($base,"SELECT * FROM unidades");
while($Parque=mysql_fetch_array($rsParque)){
	$array_unidad[$Parque['cve']]=$Parque['no_eco'];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM operadores");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_conductor[$Conductor['cve']]=$Conductor['credencial'].' - '.$Conductor['nombre'];
}

$rsConductor=mysql_db_query($base,"SELECT * FROM personal");
while($Conductor=mysql_fetch_array($rsConductor)){
	$array_personal[$Conductor['cve']]=$Conductor['folio'].' - '.$Conductor['nombre'];
}

$fecha1=$_POST['fecha_ini'];
$fecha2=$_POST['fecha_fin'];
$plaza=$_POST['searchplaza'];
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $fecha1,$fecha2,$plaza,$array_plaza;
//			$this->Image('images/membrete.JPG',60,3,150,15);
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$tit='';
			if ($fecha1!="") { $tit.=" del dia ".$fecha1; }
			if ($fecha2!="") { $tit.=" al dia ".$fecha2.""; }
//			if($plaza!="all") $tit.=' de la plaza '.$array_plaza[$plaza];
			$this->MultiCell(270,5,'Reporte de Accidentes '.$tit,0,'C');
			$this->Ln();
			$this->Ln();
			$this->SetFont('Arial','B',9);
			if($plaza=="all")$this->Cell(20,4,'Plaza',0,0,'C',0);
			$this->Cell(15,4,'Folio',0,0,'C',0);
			$this->Cell(20,4,'Fecha',0,0,'C',0);
			$this->Cell(25,4,'Fecha Accidente',0,0,'C',0);
			$this->Cell(20,4,'Unidad',0,0,'C',0);
			$this->Cell(50,4,'Conductor',0,0,'C',0);
			$this->Cell(20,4,'Costo',0,0,'C',0);
			$this->Cell(20,4,'A Cuenta',0,0,'C',0);
			$this->Cell(20,4,'Saldo',0,0,'C',0);
			$this->Cell(30,4,'Monto a Pagar',0,0,'C',0);
			$this->Cell(25,4,'Estatus',0,0,'C',0);
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
$select= " SELECT * FROM accidentes WHERE 1 ";
if ($_POST['fecha_ini']!="") { $select.=" AND fecha>='".$_POST['fecha_ini']."'"; }
if ($_POST['fecha_fin']!="") { $select.=" AND fecha<='".$_POST['fecha_fin']."'"; }
if ($_POST['unidad']!="all") { $select.=" AND unidad='".$_POST['unidad']."'"; }
if ($_POST['conductor']!="all") { $select.=" AND conductor='".$_POST['conductor']."'"; }
if ($_POST['estatus']!="all") { $select.=" AND estatus='".$_POST['estatus']."'"; }
//if ($_POST['searchplaza']!="all") { $select.=" AND plaza='".$_POST['searchplaza']."'"; }
$select .= " ORDER BY folio desc";
$rsAccidente=mysql_db_query($base,$select);
$pdf->SetFont('Arial','',9);
if($_POST['searchplaza']=="all")
	$pdf->SetWidths(array(20,15,20,25,20,50,20,20,20,30,25));
else
	$pdf->SetWidths(array(15,20,25,20,50,20,20,20,30,25));
	
while($Accidente=mysql_fetch_array($rsAccidente)) {
	$renglon=array();
	if($_POST['searchplaza']=="all") $renglon[]=$array_plaza[$Accidente['plaza']];
	$renglon[]=$Accidente['folio'];
	$renglon[]=$Accidente['fecha'];
	$renglon[]=$Accidente['fecha_accidente'];
	if($Accidente['unidadesexternas']==1){
		$renglon[]=$Accidente['unidadext'];
		$renglon[]=$Accidente['conddext'];
}else{
	if($Accidente['tipo']==4){
		$renglon[]=$Accidente['unisinregistro'];
		$renglon[]=$array_personal[$Accidente['opesinregistro']];
	}
	else{
		$renglon[]=$array_unidad[$Accidente['unidad']];
		$renglon[]=$array_conductor[$Accidente['conductor']];
	}
	}
	$renglon[]=number_format($Accidente['costo'],2);
	$renglon[]=number_format(0,2);
	$renglon[]=number_format(0,2);
	$renglon[]=number_format($Accidente['costo']*($Accidente['porcentaje']/100) ,2);
	$renglon[]=$array_estatus_accidentes[$Accidente['estatus']];
	$pdf->Row($renglon);
}

$pdf->Output();	



?>