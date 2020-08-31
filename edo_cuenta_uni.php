<?php 
include ("main.php"); 
/*** ARREGLOS ***********************************************************/

$res=mysql_query("SELECT * FROM empresas ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_empresa[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM permisionarios ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_permisionario[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM tipos_unidad ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_tipo_unidad[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM derroteros ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_derrotero[$row['cve']] = $row['nombre'];
}
$res=mysql_query("SELECT * FROM cat_cargos_unidades ORDER BY nombre");
while($row = mysql_fetch_array($res)){
	$array_cargos[$row['cve']] = $row['nombre'];
}
$array_estatus_unidad=array(1=>'Alta',2=>'Baja',3=>'Inactivo');

$res=mysql_query("SELECT * FROM motivos_condonacion ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_motivoscondo[$row['cve']]=$row['nombre'];
}

$rsUnidad=mysql_query("SELECT * FROM unidades");
while($Unidad=mysql_fetch_array($rsUnidad)){
	$array_unidad[$Unidad['cve']]=$Unidad['no_eco'];
}


if($_POST['cmd']==100){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST;
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$this->MultiCell(275,5,'Estado de Cuenta de Unidades del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,'C');
			$this->MultiCell(275,5,$tit,0,'C');
			$this->Ln();
			$this->SetFont('Arial','B',9);
			$this->Cell(30,4,'No Eco',0,0,'C',0);
			$this->Cell(60,4,'Permisionario',0,0,'C',0);
			$this->Cell(25,4,'Estatus',0,0,'C',0);
			if($_POST['tipo']!=1) $this->Cell(25,4,'Saldo Anterior',0,0,'C',0);
			$this->Cell(25,4,'Cargo',0,0,'C',0);
			$this->Cell(25,4,'Abono',0,0,'C',0);
			$this->Cell(25,4,'Saldo',0,0,'C',0);
			$this->Cell(40,4,'Firma',0,0,'C',0);
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
	if($_POST['tipo']!=1){
		$pdf->SetWidths(array(30,60,25,25,25,25,25,40));
		$pdf->SetAligns(array('C','L','C','R','R','R','R','C'));
	}
	else{
		$pdf->SetWidths(array(30,60,25,25,25,25,40));
		$pdf->SetAligns(array('C','L','C','R','R','R','C'));	
	}
	for($i=0;$i<count($_POST['unidades']);$i++){
		$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['unidades'][$i]."'");
		$row=mysql_fetch_array($res);
		$cargo=0;$abono=0;$saldoanterior=0;
		if($_POST['tipo']!=1) $saldoanterior=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
		$renglon=array();
		$cargo=saldo_unidad($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
		$abono=saldo_unidad($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
		$renglon[]=$row['no_eco'];
		$renglon[]=$array_permisionario[$row['permisionario']];
		$renglon[]=$array_estatus_unidad[$row['estatus']];
		if($_POST['tipo']!=1) $renglon[]=number_format($saldoanterior,2);
		$renglon[]=number_format($cargo,2);
		$renglon[]=number_format($abono,2);
		$renglon[]=number_format($saldoanterior+$abono-$cargo,2);
		$renglon[]="                                                          ";
		$pdf->Row($renglon);
		if($_POST['tipo']!=1) $total[0]+=round($saldoanterior,2);
		$total[1]+=round($cargo,2);
		$total[2]+=round($abono,2);
		$total[3]+=round($saldoanterior+$abono-$cargo,2);
	}
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(90,4,'',0,0,'C',0);
	$pdf->Cell(25,4,'Totales',0,0,'R',0);
	foreach($total as $v){
		$pdf->Cell(25,4,number_format($v,2),0,0,'R',0);
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
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$this->MultiCell(190,5,'Estado de Cuenta de Unidades del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,'C');
			$this->MultiCell(190,5,$tit,0,'C');
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
	$pdf=new FPDF2('P','mm','LETTER');
	$pdf->AliasNbPages();
	$total=array();
	$i=0;
	$pdf->SetWidths(array(20,50,20,20,20,70));
	$pdf->SetAligns(array('C','L','R','R','R','L'));
	for($i=0;$i<count($_POST['unidades']);$i++){
		$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['unidades'][$i]."'");
		$row=mysql_fetch_array($res);
		$pdf->AddPage('P');
		$pdf->SetFont('Arial','',9);
		$pdf->SetFont('Arial','B',16);
		$pdf->MultiCell(200,5,'Estado de Cuenta de la Unidad '.$row['no_eco'],0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(20,4,'Fecha',0,0,'C',0);
		$pdf->Cell(50,4,'Motivo',0,0,'C',0);
		$pdf->Cell(20,4,'Cargo',0,0,'C',0);
		$pdf->Cell(20,4,'Abono',0,0,'C',0);
		$pdf->Cell(20,4,'Saldo',0,0,'C',0);
		$pdf->Cell(70,4,'Observaciones',0,0,'C',0);
		$pdf->Ln();
		$fecha=$_POST['fecha_ini'];
		$sumacargo=$sumaabono=$saldo=0;
		$x=0;
		if($_POST['tipo']!=1){
			$saldo=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$renglon=array();
			$renglon[]=$fecha;
			$renglon[]="Saldo Anterior";
			$renglon[]="";
			$renglon[]="";
			$renglon[]=number_format($saldo,2);
			$observaciones="";
			$renglon[]=$observaciones;
			$pdf->Row($renglon);
			$x++;
		}
		for($j=1;$fecha<=$_POST['fecha_fin'];$j++){
			$res1=mysql_query("SELECT * FROM cargos_unidades WHERE fecha='$fecha' AND unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['cargo'];
				$saldo-=$row1['cargo'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]=$array_cargos[$row1['motivo']];
				$renglon[]=number_format($row1['cargo'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones="";
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT * FROM condonacion_tarjetas_unidad WHERE fecha='$fecha' AND estatus!='C' AND unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				//$sumacargo-=$row1['monto'];
				//$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Condonacion de Tarjeta # '.$row1['cve'].' '.$array_motivoscondo[$row1['motivo']];
				$renglon[]=0.00;
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_unidad WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['monto']>0){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Recaudacion # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
				if($row1['abono_adeudo_operador']>0){
					$sumaabono+=$row1['abono_adeudo_operador'];
					$saldo+=$row1['abono_adeudo_operador'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Abono adeudo por Recaudacion # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['abono_adeudo_operador'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, concepto FROM recaudacion_autobus WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Recaudacion Autobuses # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones="";
					$renglon[]=$row1['concepto'];
					$pdf->Row($renglon);
					$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['abono_adeudo_operador']>0){
					$sumaabono+=$row1['abono_adeudo_operador'];
					$saldo+=$row1['abono_adeudo_operador'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Abono adeudo por Recaudacion de Operador # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['abono_adeudo_operador'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM abono_general_unidad WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Abono General de Unidad # '.$row1['folio'];
				$renglon[]=0.00;
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM deuda_operador_abono WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Abono Deuda Operador # '.$row1['folio'];
				$renglon[]=0.00;
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT a.*,a.cve as folio, b.monto, a.concepto FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion='$fecha' AND b.unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['monto'];
				$saldo-=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Vale de Utilidad # '.$row1['folio'];
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['concepto'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			//cargosvvariables
		$res1=mysql_query("SELECT *, cve as folio FROM cargos_variables_unidadeszitla WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND sta!='C'");
		while($row1=mysql_fetch_array($res1)){
			/*$sumacargo+=$row1['total'];
			$saldo-=$row1['total'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Cargo Variable # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['total'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
			echo '</tr>';*/
			$sumacargo+=$row1['total'];
				$saldo-=$row1['total'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Cargo Variable # '.$row1['folio'];
				$renglon[]=number_format($row1['total'],2);
				$renglon[]=00.0;
				$renglon[]=number_format($saldo,2);
				$observaciones="";
				$renglon[]=utf8_encode($row1['concepto']);
				$pdf->Row($renglon);
			$x++;
		}
			$res1=mysql_query("SELECT *,cve as folio, monto, unidad_origen, unidad_destino FROM traspaso_unidades WHERE fecha_aplicacion='$fecha' AND (unidad_origen='".$row['cve']."' OR unidad_destino='".$row['cve']."') AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['unidad_destino'] == $row['cve']){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Traspaso entre Unidades # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones="Unidad origen ".$array_unidad[$row1['unidad_origen']];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
				elseif($row1['unidad_origen'] == $row['cve']){
					$sumacargo+=$row1['monto'];
					$saldo-=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Traspaso entre Unidades # '.$row1['folio'];
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=0.00;
					$renglon[]=number_format($saldo,2);
					$observaciones="Unidad destino ".$array_unidad[$row1['unidad_destino']];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM devolucion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['monto'];
				$saldo-=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Devolucion al operador # '.$row1['folio'];
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$fecha=date( "Y-m-d" , strtotime ( "+ ".$j." day" , strtotime($_POST['fecha_ini']) ) );
		}
		$renglon=array();
		$renglon[]=$x." Registro(s)";
		$renglon[]="Total:";
		$renglon[]=number_format($sumacargo,2);
		$renglon[]=number_format($sumaabono,2);
		$renglon[]=number_format($saldo,2);
		$observaciones="";
		$renglon[]=$observaciones;
		$pdf->Row($renglon);
	}
	$pdf->Output();	
	exit();
}

if($_POST['cmd']==102){
	 header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=cuenta.xls");
header("Pragma: no-cache");
header("Expires: 0");

	$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['reg']."'") or die(mysql_error());
	$row=mysql_fetch_array($res);
	echo '<table>';
	echo '<tr><td class="tableEnc">Estado de Cuenta de la Unidad '.$row['no_eco'].', Permisionario: '.$array_permisionario[$row['permisionario']].'<br>
	'.$array_estatus_unidad[$row['estatus']].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="1" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolo="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$sumacargo=$sumaabono=$saldo=0;
	$x=0;
	$fecha=$_POST['fecha_ini'];
	if($_POST['tipo']!=1){
		$saldo=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
		//rowb();
		echo'<tr>';
		echo '<td align=center>&nbsp;'.$fecha.'</td>';
		echo '<td align=left>&nbsp;Saldo Anterior</td>';
		echo '<td align="right">'.number_format(0,2).'</td>';
		echo '<td align="right">'.number_format(0,2).'</td>';
		echo '<td align="right">'.number_format($saldo,2).'</td>';
		echo '<td align=left>&nbsp;</td>';
		echo '</tr>';
		$x++;
	}
	for($i=1;$fecha<=$_POST['fecha_fin'];$i++){
		$res1=mysql_query("SELECT * FROM cargos_unidades WHERE fecha='$fecha' AND unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['cargo'];
			$saldo-=$row1['cargo'];
		//	rowb();
		echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>'.$array_cargos[$row1['motivo']].'</td>';
			echo '<td align="right">'.number_format($row1['cargo'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT * FROM condonacion_tarjetas_unidad WHERE fecha='$fecha' AND estatus!='C' AND unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			//$sumacargo-=$row1['monto'];
			//$saldo+=$row1['monto'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Condonacion de Tarjeta # '.$row1['cve'].', Motivo: '.$array_motivoscondo[$row1['motivo']].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT cve as folio, monto, abono_adeudo_operador FROM recaudacion_unidad WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			if($row1['monto'] > 0){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				//rowb();
				echo'<tr>';
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Recaudacion # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>&nbsp;</td>';
				echo '</tr>';
				$x++;
			}
			if($row1['abono_adeudo_operador'] > 0){
				$sumaabono+=$row1['abono_adeudo_operador'];
				$saldo+=$row1['abono_adeudo_operador'];
				//rowb();
				echo'<tr>';
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Abono a deudo por Recaudacion # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['abono_adeudo_operador'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>&nbsp;</td>';
				echo '</tr>';
				$x++;
			}
		}
		$res1=mysql_query("SELECT cve as folio, monto, concepto FROM recaudacion_autobus WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				//rowb();
				echo'<tr>';
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Recaudacion Autobuses # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row1['concepto'].'&nbsp;</td>';
				echo '</tr>';
				$x++;
		}
		$res1=mysql_query("SELECT cve as folio, monto, abono_adeudo_operador FROM recaudacion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			if($row1['abono_adeudo_operador'] > 0){
				$sumaabono+=$row1['abono_adeudo_operador'];
				$saldo+=$row1['abono_adeudo_operador'];
				//rowb();
				echo'<tr>';
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Abono a deudo por Recaudacion de Operador # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['abono_adeudo_operador'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>&nbsp;</td>';
				echo '</tr>';
				$x++;
			}
		}
		$res1=mysql_query("SELECT cve as folio, monto, obs FROM abono_general_unidad WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumaabono+=$row1['monto'];
			$saldo+=$row1['monto'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Abono General de Unidad # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT cve as folio, monto, obs FROM deuda_operador_abono WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumaabono+=$row1['monto'];
			$saldo+=$row1['monto'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Abono Deuda Operador # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT cve as folio, monto, unidad_origen, unidad_destino FROM traspaso_unidades WHERE fecha_aplicacion='$fecha' AND (unidad_origen='".$row['cve']."' OR unidad_destino='".$row['cve']."') AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['unidad_destino'] == $row['cve']){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					//rowb();
					echo'<tr>';
					echo '<td align=center>&nbsp;'.$fecha.'</td>';
					echo '<td align=left>Traspaso entre Unidades #  # '.$row1['folio'].'</td>';
					echo '<td align="right">'.number_format(0,2).'</td>';
					echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
					echo '<td align="right">'.number_format($saldo,2).'</td>';
					echo '<td align=left>'."Unidad origen ".$array_unidad[$row1['unidad_origen']].'&nbsp;</td>';
					echo '</tr>';
					$x++;
				}
				elseif($row1['unidad_origen'] == $row['cve']){
					$sumacargo+=$row1['monto'];
					$saldo-=$row1['monto'];
					echo '<td align=center>&nbsp;'.$fecha.'</td>';
					echo '<td align=left>Traspaso entre Unidades #  # '.$row1['folio'].'</td>';
					echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
					echo '<td align="right">'.number_format(0,2).'</td>';
					echo '<td align="right">'.number_format($saldo,2).'</td>';
					echo '<td align=left>'."Unidad destino ".$array_unidad[$row1['unidad_destino']].'&nbsp;</td>';
					echo '</tr>';
					$x++;
					$x++;
				}
			}
		$res1=mysql_query("SELECT a.cve as folio, b.monto, a.concepto FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion='$fecha' AND b.unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['monto'];
			$saldo-=$row1['monto'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Vale de Utilidad # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		//cargosvvariables
		$res1=mysql_query("SELECT *, cve as folio FROM cargos_variables_unidadeszitla WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND sta!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['total'];
			$saldo-=$row1['total'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Cargo Variable # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['total'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT cve as folio, monto, obs FROM devolucion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['monto'];
			$saldo-=$row1['monto'];
			//rowb();
			echo'<tr>';
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Devolucion de Operador # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.$row1['obs'].'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}

		$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($_POST['fecha_ini']) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumacargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumaabono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	exit();
}

if($_POST['cmd']==103){
	include('fpdf153/fpdf.php');
	include("numlet.php");	
	class FPDF2 extends PDF_MC_Table {
		function Header(){
			global $_POST;
			$this->SetFont('Arial','B',16);
			//$this->Cell(190,10,'Autobuses Rapidos del Valle de Mexico',0,0,'C');
			//$this->Ln();
			$this->SetY(23);
			$this->MultiCell(190,5,'Estado de Cuenta de Unidades del '.$_POST['fecha_ini'].' al '.$_POST['fecha_fin'],0,'C');
			$this->MultiCell(190,5,$tit,0,'C');
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
	$pdf=new FPDF2('P','mm','LETTER');
	$pdf->AliasNbPages();
	$total=array();
	$i=0;
	$pdf->SetWidths(array(20,50,20,20,20,70));
	$pdf->SetAligns(array('C','L','R','R','R','L'));
//	for($i=0;$i<count($_POST['unidades']);$i++){
	//$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['unidades'][$i]."'");
		$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['reg']."'");
		$row=mysql_fetch_array($res);
		$pdf->AddPage('P');
		$pdf->SetFont('Arial','',9);
		$pdf->SetFont('Arial','B',16);
		$pdf->MultiCell(200,5,'Estado de Cuenta de la Unidad '.$row['no_eco'],0,'C');
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(20,4,'Fecha',0,0,'C',0);
		$pdf->Cell(50,4,'Motivo',0,0,'C',0);
		$pdf->Cell(20,4,'Cargo',0,0,'C',0);
		$pdf->Cell(20,4,'Abono',0,0,'C',0);
		$pdf->Cell(20,4,'Saldo',0,0,'C',0);
		$pdf->Cell(70,4,'Observaciones',0,0,'C',0);
		$pdf->Ln();
		$fecha=$_POST['fecha_ini'];
		$sumacargo=$sumaabono=$saldo=0;
		$x=0;
		if($_POST['tipo']!=1){
			$saldo=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
			$renglon=array();
			$renglon[]=$fecha;
			$renglon[]="Saldo Anterior";
			$renglon[]="";
			$renglon[]="";
			$renglon[]=number_format($saldo,2);
			$observaciones="";
			$renglon[]=$observaciones;
			$pdf->Row($renglon);
			$x++;
		}
		for($j=1;$fecha<=$_POST['fecha_fin'];$j++){
			$res1=mysql_query("SELECT * FROM cargos_unidades WHERE fecha='$fecha' AND unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['cargo'];
				$saldo-=$row1['cargo'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]=$array_cargos[$row1['motivo']];
				$renglon[]=number_format($row1['cargo'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones="";
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT * FROM condonacion_tarjetas_unidad WHERE fecha='$fecha' AND estatus!='C' AND unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				//$sumacargo-=$row1['monto'];
				//$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Condonacion de Tarjeta # '.$row1['cve'].' '.$array_motivoscondo[$row1['motivo']];
				$renglon[]=0.00;
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_unidad WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['monto']>0){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Recaudacion # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
				if($row1['abono_adeudo_operador']>0){
					$sumaabono+=$row1['abono_adeudo_operador'];
					$saldo+=$row1['abono_adeudo_operador'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Abono adeudo por Recaudacion # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['abono_adeudo_operador'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, concepto FROM recaudacion_autobus WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Recaudacion Autobuses # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones="";
					$renglon[]=$row1['concepto'];
					$pdf->Row($renglon);
					$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['abono_adeudo_operador']>0){
					$sumaabono+=$row1['abono_adeudo_operador'];
					$saldo+=$row1['abono_adeudo_operador'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Abono adeudo por Recaudacion de Operador # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['abono_adeudo_operador'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones=$row1['obs'];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM abono_general_unidad WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Abono General de Unidad # '.$row1['folio'];
				$renglon[]=0.00;
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM deuda_operador_abono WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Abono Deuda Operador # '.$row1['folio'];
				$renglon[]=0.00;
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$res1=mysql_query("SELECT a.*,a.cve as folio, b.monto, a.concepto FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion='$fecha' AND b.unidad='".$row['cve']."'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['monto'];
				$saldo-=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Vale de Utilidad # '.$row1['folio'];
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['concepto'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			//cargosvvariables
		$res1=mysql_query("SELECT *, cve as folio FROM cargos_variables_unidadeszitla WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND sta!='C'");
		while($row1=mysql_fetch_array($res1)){
			/*$sumacargo+=$row1['total'];
			$saldo-=$row1['total'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Cargo Variable # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['total'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
			echo '</tr>';*/
			$sumacargo+=$row1['total'];
				$saldo-=$row1['total'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Cargo Variable # '.$row1['folio'];
				$renglon[]=number_format($row1['total'],2);
				$renglon[]=00.0;
				$renglon[]=number_format($saldo,2);
				$observaciones="";
				$renglon[]=utf8_encode($row1['concepto']);
				$pdf->Row($renglon);
			$x++;
		}
			$res1=mysql_query("SELECT *,cve as folio, monto, unidad_origen, unidad_destino FROM traspaso_unidades WHERE fecha_aplicacion='$fecha' AND (unidad_origen='".$row['cve']."' OR unidad_destino='".$row['cve']."') AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['unidad_destino'] == $row['cve']){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Traspaso entre Unidades # '.$row1['folio'];
					$renglon[]=0.00;
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=number_format($saldo,2);
					$observaciones="Unidad origen ".$array_unidad[$row1['unidad_origen']];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
				elseif($row1['unidad_origen'] == $row['cve']){
					$sumacargo+=$row1['monto'];
					$saldo-=$row1['monto'];
					$renglon=array();
					$renglon[]=$fecha;
					$renglon[]='Traspaso entre Unidades # '.$row1['folio'];
					$renglon[]=number_format($row1['monto'],2);
					$renglon[]=0.00;
					$renglon[]=number_format($saldo,2);
					$observaciones="Unidad destino ".$array_unidad[$row1['unidad_destino']];
					$renglon[]=$observaciones;
					$pdf->Row($renglon);
					$x++;
				}
			}
			$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM devolucion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				$sumacargo+=$row1['monto'];
				$saldo-=$row1['monto'];
				$renglon=array();
				$renglon[]=$fecha;
				$renglon[]='Devolucion al operador # '.$row1['folio'];
				$renglon[]=number_format($row1['monto'],2);
				$renglon[]=0.00;
				$renglon[]=number_format($saldo,2);
				$observaciones=$row1['obs'];
				$renglon[]=$observaciones;
				$pdf->Row($renglon);
				$x++;
			}
			$fecha=date( "Y-m-d" , strtotime ( "+ ".$j." day" , strtotime($_POST['fecha_ini']) ) );
		}
		$renglon=array();
		$renglon[]=$x." Registro(s)";
		$renglon[]="Total:";
		$renglon[]=number_format($sumacargo,2);
		$renglon[]=number_format($sumaabono,2);
		$renglon[]=number_format($saldo,2);
		$observaciones="";
		$renglon[]=$observaciones;
		$pdf->Row($renglon);
//}	
	$pdf->Output();	
	exit();
}

/*** CONSULTA AJAX  **************************************************/

if($_POST['ajax']==1) {
		//Listado de Parque
		$select= " SELECT * FROM unidades WHERE 1 ";
		if ($_POST['no_eco']!="") { $select.=" AND no_eco = '".$_POST['no_eco']."'"; }
		if ($_POST['empresa']!="") { $select.=" AND empresa = '".$_POST['empresa']."'"; }
		if ($_POST['estatus']!="") { $select.=" AND estatus = '".$_POST['estatus']."'"; }
		if ($_POST['permisionario']!="") { $select.=" AND permisionario = '".$_POST['permisionario']."'"; }
		if ($_POST['tipo_unidad']!="") { $select.=" AND tipo_unidad = '".$_POST['tipo_unidad']."'"; }
		if ($_POST['derrotero']!="") { $select.=" AND derrotero = '".$_POST['derrotero']."'"; }
		$res=mysql_query($select);
		$totalRegistros = mysql_num_rows($res);
		$select .= " ORDER BY no_eco";
		$res=mysql_query($select);
		
		if(mysql_num_rows($res)>0) 
		{
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="" id="tabla1">';
			echo '<thead></tr>';
			echo '<tr bgcolor="#E9F2F8"><th><input type="checkbox" value="1" name="seltodos" onClick="
			if(this.checked)
				$(\'.seleccion\').attr(\'checked\',\'checked\');
			else
				$(\'.seleccion\').removeAttr(\'checked\');"></th>';
			echo '<th>No.Eco</th>
			<th>Permisionario</th>
			<th>Estatus</th><th>Tipo Vehiculo</th>
			';
			if($_POST['tipo']!=1)
				echo '<th>Saldo Anterior</th>';
			echo '<th>Cargos</th><th>Abonos</th><th>Saldo</th>
			<th>&nbsp;</th>
			<th>Administracion</th>
			<th>Seguro Interno</th>
			<th>Cargos Variables</th>
			<th>Saldo del Periodo</th>';
			echo '</tr></thead><tbody>';
			$total=array();
			$i=0;
			while($row=mysql_fetch_array($res)) {
				$cargo=0;$abono=0;$saldoanterior=0;$saldoactual=0;
				if($_POST['tipo']!=1)
					$saldoanterior=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$cargo=saldo_unidad($row['cve'],2,1,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$abono=saldo_unidad($row['cve'],2,2,$_POST['fecha_ini'],$_POST['fecha_fin']);
				$saldoactual=$saldoanterior+($abono-$cargo);
				if($_POST['mostrar']==0 || ($_POST['mostrar']==1 && $saldoactual>=0) || ($_POST['mostrar']==2 && $saldoactual<0)){
					rowb();
					$no_eco=$row['no_eco'];
					echo '<td align="center"><input type="checkbox" class="seleccion" name="unidades[]" value="'.$row['cve'].'"></td>';
					echo '<td align="center">'.$no_eco.'</td>';
					echo '<td align="left">'.utf8_encode(trim($array_permisionario[$row['permisionario']])).'</td>';
					echo '<td align="center">'.$array_estatus_unidad[$row['estatus']].'</td>';
					echo '<td align="center">'.utf8_encode($array_tipo_unidad[$row['tipo_unidad']]).'</td>';
					if($_POST['tipo']!=1) echo '<td align="right">'.number_format($saldoanterior,2).'</td>';
					echo '<td align="right">'.number_format($cargo,2).'</td>';
					echo '<td align="right">'.number_format($abono,2).'</td>';
					echo '<td align="right">'.number_format($saldoactual,2).'</td>';
					echo '<td align="center"><a href="#" onClick="atcr(\'\',\'\',1,\''.$row['cve'].'\');"><img src="images/b_search.png" border="0"></a></td>';
					echo '<td align="right">'.number_format($row['monto_administracion'],2).'</td>';
					echo '<td align="right">'.number_format($row['monto_seguro_interno'],2).'</td>';
					$res1 = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '".$row['cve']."' and sta='A' and fecha between '".$_POST['fecha_ini']."' and '".$_POST['fecha_fin']."'") or die(mysql_error());
					$row1 = mysql_fetch_array($res1);
					echo '<td align="right">'.number_format($row1[0],2).'</td>';
					$saldo_p=($abono)-($cargo)-($row1[0]);
					echo '<td align="right">'.number_format($saldo_p,2).'</td>';
					
					if($_POST['tipo']!=1) $total[0]+=round($saldoanterior,2);
					$total[1]+=round($cargo,2);
					$total[2]+=round($abono,2);
					$total[3]+=round($saldoactual,2);
					$total[4]+="";
					$total[5]+=round($row['monto_administracion'],2);
					$total[6]+=round($row['monto_seguro_interno'],2);
					$total[7]+=round($row[0],2);
					$total[8]+=round($saldo_p,2);
					
					$i++;
					echo '</tr>';
				}
			}
			
			echo '	</tbody>
				<tr bgcolor="#E9F2F8">
				<td colspan="4" >';menunavegacion(); echo '</td>
				<td>Totales:</td>';
			foreach($total as $k=>$v){
				if($k!=4){
				echo '<td align="right">'.number_format($v,2).'</td>';
				}else{echo'<td>&nbsp;</td>';}
			}
			echo '
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
	$res=mysql_query("SELECT * FROM unidades WHERE cve='".$_POST['unidad']."'");
	$row=mysql_fetch_array($res);
	echo '<table>';
	echo '<tr><td class="tableEnc">Estado de Cuenta de la Unidad '.$row['no_eco'].', Permisionario: '.$array_permisionario[$row['permisionario']].'<br>
	'.$array_estatus_unidad[$row['estatus']].'</td></tr>';
	echo '</table>';
	echo '<br>';
	echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
	echo '<tr bgcolor="#E9F2F8">';
	echo '<th>Fecha</th><th>Motivo</th><th>Cargo</th><th>Abono</th><th>Saldo</th><th>Observaciones</th>';
	echo '</tr>';
	$sumacargo=$sumaabono=$saldo=0;
	$x=0;
	$fecha=$_POST['fecha_ini'];
	if($_POST['tipo']!=1){
		$saldo=saldo_unidad($row['cve'],1,0,$_POST['fecha_ini'],$_POST['fecha_fin']);
		rowb();
		echo '<td align=center>&nbsp;'.$fecha.'</td>';
		echo '<td align=left>&nbsp;Saldo Anterior</td>';
		echo '<td align="right">'.number_format(0,2).'</td>';
		echo '<td align="right">'.number_format(0,2).'</td>';
		echo '<td align="right">'.number_format($saldo,2).'</td>';
		echo '<td align=left>&nbsp;</td>';
		echo '</tr>';
		$x++;
	}
	for($i=1;$fecha<=$_POST['fecha_fin'];$i++){
		$res1=mysql_query("SELECT * FROM cargos_unidades WHERE fecha='$fecha' AND unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['cargo'];
			$saldo-=$row1['cargo'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>'.$array_cargos[$row1['motivo']].'</td>';
			echo '<td align="right">'.number_format($row1['cargo'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT * FROM condonacion_tarjetas_unidad WHERE fecha='$fecha' AND estatus!='C' AND unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			//$sumacargo-=$row1['monto'];
			//$saldo+=$row1['monto'];
			rowb();
			/*			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Condonacion de Tarjeta # '.$row1['cve'].', Motivo: '.$array_motivoscondo[$row1['motivo']].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.number_format($row1['monto'],2).'&nbsp;</td>';
			echo '</tr>';*/
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Condonacion de Tarjeta # '.$row1['cve'].', Motivo: '.$array_motivoscondo[$row1['motivo']].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.number_format($row1['monto'],2).'&nbsp;</td>';
			echo '<td align="right">'.$row1['obs'].'</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_unidad WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			if($row1['monto'] != 0){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Recaudacion # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row1['obs'].'</td>';
				echo '</tr>';
				$x++;
			}
			if($row1['abono_adeudo_operador'] != 0){
				$sumaabono+=$row1['abono_adeudo_operador'];
				$saldo+=$row1['abono_adeudo_operador'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Abono a deudo por Recaudacion # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['abono_adeudo_operador'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row1['obs'].'</td>';
				echo '</tr>';
				$x++;
			}
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, concepto FROM recaudacion_autobus WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
				$sumaabono+=$row1['monto'];
				$saldo+=$row1['monto'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Recaudacion Autobuses # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
				echo '</tr>';
				$x++;
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, abono_adeudo_operador FROM recaudacion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			if($row1['abono_adeudo_operador'] != 0){
				$sumaabono+=$row1['abono_adeudo_operador'];
				$saldo+=$row1['abono_adeudo_operador'];
				rowb();
				echo '<td align=center>&nbsp;'.$fecha.'</td>';
				echo '<td align=left>Abono a deudo por Recaudacion de Operador # '.$row1['folio'].'</td>';
				echo '<td align="right">'.number_format(0,2).'</td>';
				echo '<td align="right">'.number_format($row1['abono_adeudo_operador'],2).'</td>';
				echo '<td align="right">'.number_format($saldo,2).'</td>';
				echo '<td align=left>'.$row1['obs'].'</td>';
				echo '</tr>';
				$x++;
			}
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM abono_general_unidad WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumaabono+=$row1['monto'];
			$saldo+=$row1['monto'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Abono General de Unidad # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM deuda_operador_abono WHERE fecha_aplicacion='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumaabono+=$row1['monto'];
			$saldo+=$row1['monto'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Abono Deuda Operador # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}
		$res1=mysql_query("SELECT *,cve as folio, monto, unidad_origen, unidad_destino FROM traspaso_unidades WHERE fecha_aplicacion='$fecha' AND (unidad_origen='".$row['cve']."' OR unidad_destino='".$row['cve']."') AND estatus!='C'");
			while($row1=mysql_fetch_array($res1)){
				if($row1['unidad_destino'] == $row['cve']){
					$sumaabono+=$row1['monto'];
					$saldo+=$row1['monto'];
					rowb();
					echo '<td align=center>&nbsp;'.$fecha.'</td>';
					echo '<td align=left>Traspaso entre Unidades #  # '.$row1['folio'].'</td>';
					echo '<td align="right">'.number_format(0,2).'</td>';
					echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
					echo '<td align="right">'.number_format($saldo,2).'</td>';
					echo '<td align=left>'."Unidad origen ".$array_unidad[$row1['unidad_origen']].'&nbsp;</td>';
					echo '</tr>';
					$x++;
				}
				elseif($row1['unidad_origen'] == $row['cve']){
					$sumacargo+=$row1['monto'];
					$saldo-=$row1['monto'];
					echo '<td align=center>&nbsp;'.$fecha.'</td>';
					echo '<td align=left>Traspaso entre Unidades #  # '.$row1['folio'].'</td>';
					echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
					echo '<td align="right">'.number_format(0,2).'</td>';
					echo '<td align="right">'.number_format($saldo,2).'</td>';
					echo '<td align=left>'."Unidad destino ".$array_unidad[$row1['unidad_destino']].'&nbsp;</td>';
					echo '</tr>';
					$x++;
					$x++;
				}
			}
		$res1=mysql_query("SELECT a.*,a.cve as folio, b.monto, a.concepto FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion='$fecha' AND b.unidad='".$row['cve']."'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['monto'];
			$saldo-=$row1['monto'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Vale de Utilidad # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.$row1['concepto'].'</td>';
			echo '</tr>';
			$x++;
		}
		//cargosvvariables
		$res1=mysql_query("SELECT *, cve as folio FROM cargos_variables_unidadeszitla WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND sta!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['total'];
			$saldo-=$row1['total'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Cargo Variable # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['total'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['concepto']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}

		$res1=mysql_query("SELECT *,cve as folio, monto, obs FROM devolucion_operador WHERE fecha='$fecha' AND unidad='".$row['cve']."' AND estatus!='C'");
		while($row1=mysql_fetch_array($res1)){
			$sumacargo+=$row1['monto'];
			$saldo-=$row1['monto'];
			rowb();
			echo '<td align=center>&nbsp;'.$fecha.'</td>';
			echo '<td align=left>Devolucion de Operador # '.$row1['folio'].'</td>';
			echo '<td align="right">'.number_format($row1['monto'],2).'</td>';
			echo '<td align="right">'.number_format(0,2).'</td>';
			echo '<td align="right">'.number_format($saldo,2).'</td>';
			echo '<td align=left>'.utf8_encode($row1['obs']).'&nbsp;</td>';
			echo '</tr>';
			$x++;
		}

		$fecha=date( "Y-m-d" , strtotime ( "+ ".$i." day" , strtotime($_POST['fecha_ini']) ) );
	}
	echo '	
			<tr>
			<td colspan="2" bgcolor="#E9F2F8">'.$x.' Registro(s)</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumacargo,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($sumaabono,2).'</td>
			<td bgcolor="#E9F2F8" align="right">'.number_format($saldo,2).'</td>
			<td colspan="2" bgcolor="#E9F2F8">&nbsp;</td>
			</tr>';
	echo '</table>';
	exit();
}
	
top($_SESSION);

if($_POST['cmd']==1){
	echo '<table>';
	echo '<tr>
			<td><a href="#" onclick="buscar_cargos(\''.$_POST['reg'].'\');"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar Cargos</a></td>
			<td><a href="#" onclick="atcr(\'edo_cuenta_uni.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>
			<td><a href="#" onClick="atcr(\'\',\'_blank\',\'102\','.$_POST['reg'].')"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Excell</a></td>
			<td><a href="#" onclick="atcr(\'\',\'_blank\',\'103\','.$_POST['reg'].')"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a></td>
		</tr>';
	echo '</table>';
	echo '<table>';
	echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" value="'.$_POST['fecha_ini'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" value="'.$_POST['fecha_fin'].'" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
	echo '<tr><th>Tipo</th><td><select name="tipo" id="tipo"><option value="0">Normal</option><option value="1"';
	if($_POST['tipo']==1) echo ' selected';
	echo '>Saldo del Periodo</option></select></td></tr>';
	echo '</table>';
	echo '<br>';

	//Listado
	echo '<div id="idCargos">';
	echo '</div>';
	echo '
	<script>
	function buscar_cargos(unidad)
	{
		document.getElementById("idCargos").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","edo_cuenta_uni.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=2&unidad="+unidad+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&cveusuario="+document.getElementById("cveusuario").value+"&tipo="+document.getElementById("tipo").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("idCargos").innerHTML = objeto.responseText;}
			}
		}
	}
	buscar_cargos(\''.$_POST['reg'].'\'); //Realizar consulta de todos los registros al iniciar la forma.

	</script>';
}

/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		if(date("d")<="10") $fecha_ini=date("Y-m").'-01';
		elseif(date("d")<="20") $fecha_ini=date("Y-m").'-11';
		else $fecha_ini=date("Y-m").'-21';
		$fecha_ini=date("Y-m").'-01';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros();"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Busca</a>&nbsp;&nbsp;</td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_uni.php\',\'_blank\',\'100\',\'0\');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir</a></td>
				<td><a href="#" onclick="atcr(\'edo_cuenta_uni.php\',\'_blank\',\'101\',\'0\');"><img src="images/b_print.png" border="0">&nbsp;&nbsp;Imprimir Detallados</a></td>
				</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td>Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini" class="readOnly" size="12" value="'.$fecha_ini.'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin" class="readOnly" size="12" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td>No Eco</td><td><input type="text" name="no_eco" id="no_eco" size="10" class="textField"></td></tr>';	
		echo '<tr><td>Empresa</td><td><select name="empresa" id="empresa"><option value="">Todos</option>';
		foreach($array_empresa as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Permisionario</td><td><select name="permisionario" id="permisionario"><option value="">Todos</option>';
		foreach($array_permisionario as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo Unidad</td><td><select name="tipo_unidad" id="tipo_unidad"><option value="">Todos</option>';
		foreach($array_tipo_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Derrotero</td><td><select name="derrotero" id="derrotero"><option value="">Todos</option>';
		foreach($array_derrotero as $k=>$v){
			echo '<option value="'.$k.'"';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Estatus</td><td><select name="estatus" id="estatus"><option value="">Todos</option>';
		foreach($array_estatus_unidad as $k=>$v){
			echo '<option value="'.$k.'"';
			if($k==1) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>Tipo</th><td><select name="tipo" id="tipo"><option value="0">Normal</option><option value="1">Saldo del Periodo</option></select></td></tr>';
		echo '<tr><td>Mostrar</td><td><select name="mostrar" id="mostrar"><option value="0">Todos</option>
		<option value="1">Saldo Positivo</option><option value="2">Saldo Negativo</option>
		</select></td></tr>';
		echo '</table>';
		echo '<br>';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
		echo '<script language="javascript">
			//Funcion para navegacion de Registros. 20 por pagina.
			function moverPagina(x) {
				document.getElementById("numeroPagina").value = x;
				buscarRegistros();
			} </script>';


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
			objeto.open("POST","edo_cuenta_uni.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&mostrar="+document.getElementById("mostrar").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&no_eco="+document.getElementById("no_eco").value+"&empresa="+document.getElementById("empresa").value+"&permisionario="+document.getElementById("permisionario").value+"&tipo_unidad="+document.getElementById("tipo_unidad").value+"&derrotero="+document.getElementById("derrotero").value+"&estatus="+document.getElementById("estatus").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&tipo="+document.getElementById("tipo").value);
			objeto.onreadystatechange = function()
			{
				if (objeto.readyState==4)
				{document.getElementById("Resultados").innerHTML = objeto.responseText;}
			}
		}
		document.getElementById("numeroPagina").value = "0"; //Se reestablece la variable para que las busquedas por criterio no se afecten.
	}
	
	
			buscarRegistros(); //Realizar consulta de todos los registros al iniciar la forma.
	
	
	</Script>
';
	}
	
bottom();



?>

