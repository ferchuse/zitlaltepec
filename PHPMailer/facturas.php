<?php
require('vendor/autoload.php');
use Mailgun\Mailgun;
include("main.php");
include("imp_factura.php");

//ARREGLOS

$rsUsuario=mysql_query("SELECT * FROM usuarios");
while($Usuario=mysql_fetch_array($rsUsuario)){
	$array_usuario[$Usuario['cve']]=$Usuario['usuario'];
}

$array_sucursales=array();
$res = mysql_query("SELECT * FROM sucursales WHERE empresa = '".$_POST['cveempresa']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)) $array_sucursales[$row['cve']] = $row['nombre'];

$array_grupo_clientes=array();
$res = mysql_query("SELECT * FROM grupo_clientes WHERE empresa = '".$_POST['cveempresa']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	$array_grupo_clientes[$row['cve']] = $row['nombre'];
}

$array_clientes=array();
$array_clientes_sucursal = array();
if($_POST['grupo_clientec']>0 && $_POST['cmd']==1)
	$res=mysql_query("SELECT * FROM clientes WHERE empresa='".$_POST['cveempresa']."' AND grupo='".$_POST['grupo_clientec']."' AND estatus!=1 ORDER BY nombre");
elseif($_POST['cmd'] == 1)
	$res=mysql_query("SELECT * FROM clientes WHERE empresa='".$_POST['cveempresa']."' AND estatus!=1 ORDER BY nombre");
else
	$res=mysql_query("SELECT * FROM clientes WHERE empresa='".$_POST['cveempresa']."' ORDER BY nombre");
while($row=mysql_fetch_array($res)){
	if(trim($row['descripcion'])!='') $row['nombre'].=' ('.trim($row['descripcion']).')';
	$array_clientes[$row['cve']]=$row['nombre'];
	if($row['rfc']=="" || $row['nombre']=="" || $row['calle']=="" || $row['numexterior']=="" || $row['colonia']=="" || $row['municipio']=="" || $row['codigopostal']=="")
		$array_colorcliente[$row['cve']] = "#FF0000";
	else
		$array_colorcliente[$row['cve']] = "#000000";
	$array_clientes_sucursal[$row['cve']] = array('sucursal' => $row['sucursal'], 'nomsucursal' => $array_sucursales[$row['sucursal']]);
}

$array_tipo_poliza = array(1=>'Poliza de Contado', 2=>"Poliza de Provision");

function mestexto($fec){
	global $array_meses;
	$datos=explode("-",$fec);
	return $array_meses[intval($datos[1])].' '.$datos[0];
}
//$array_tipocliente=array("Propietario","Cliente Externo","Mostrador");

$resempresa = mysql_query("SELECT * FROM empresas WHERE cve='".$_POST['cveempresa']."'");
$rowempresa = mysql_fetch_array($resempresa);

$abono=0;

if($_POST['cmd']==16){
	$zip = new ZipArchive();
	$fecha=date('Y_m_d_H_i_s');
	if($zip->open("cfdi/zipcfdis".$fecha.".zip",ZipArchive::CREATE)){
		foreach($_POST['checksf'] as $cvefact){
			$res = mysql_query("SELECT * FROM facturas WHERE empresa='".$_POST['cveempresa']."' AND cve='".$cvefact."'");
			$row = mysql_fetch_array($res);
			if($row['estatus']=='C')
				$zip->addFile("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf","Factura_".$_POST['cveempresa']."_".$cvefact.".pdf");
			else
				$zip->addFile("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf","Factura_".$_POST['cveempresa']."_".$cvefact.".pdf");
			$zip->addFile("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml","Factura_".$_POST['cveempresa']."_".$cvefact.".xml");
		}
		$zip->close(); 
	    if(file_exists("cfdi/zipcfdis".$fecha.".zip")){ 
	        header('Content-type: "application/zip"'); 
	        header('Content-Disposition: attachment; filename="zipcfdis'.$fecha.'.zip"'); 
	        readfile("cfdi/zipcfdis".$fecha.".zip"); 
	         
	        unlink("cfdi/zipcfdis".$fecha.".zip"); 
	    } 
	    else{
			echo '<h1>Ocurrio un problema al cerrar el archivo favor de intentarlo de nuevo</h1>';
		}
	}
	else{
		echo '<h1>Ocurrio un problema al generar el archivo favor de intentarlo de nuevo</h1>';
	}
	exit();
}

if($_POST['cmd']==101){
	generaFacturaPdf($_POST['cveempresa'],$_POST['reg'],1);
	exit();
}

if($_POST['cmd']==103){
	unlink($_POST['reg']);
	echo '<script>window.close();</script>';
	exit();
}

if($_POST['cmd']==10){
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=Facturas.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	$filtro="";
	if($_POST['grupo_cliente'] != "") $select.=" INNER JOIN clientes b ON b.cve = a.cliente";
	$select.=" WHERE a.empresa='".$_POST['cveempresa']."'";
	if($_POST['folio'] != ''){
		$select.=" AND a.cve='".$_POST['folio']."'";
	}
	else{
		$select.=" AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
		if($_POST['cliente']!="") $select.=" AND a.cliente IN (".$_POST['cliente'].")";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		if ($_POST['grupo_cliente']!="all") { $select.=" AND b.grupo='".$_POST['grupo_cliente']."'"; }
		if ($_POST['estatus_pago']!="all") { $select.=" AND a.estatus_pago='".$_POST['estatus_pago']."'"; }
		if ($_POST['sucursal']!="all") { $select.=" AND a.sucursal='".$_POST['sucursal']."'"; }
		if ($_POST['tipo_poliza']!="all") { $select.=" AND a.tipo_poliza='".$_POST['tipo_poliza']."'"; }
		if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
		elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
		$select.=" ORDER BY a.cve DESC";
	}
	$rsabonos=mysql_query($select) or die(mysql_error());
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=14;
		if($rowempresa['maneja_pagado']==1) $c++;
		if($rowempresa['maneja_sucursal']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		echo '<th>Folio</th><th>Fecha</th><th>Concepto</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>';
		if($rowempresa['maneja_pagado']==1){
			echo '<th>Fecha Pago</th>';
		}
		if($rowempresa['maneja_sucursal']==1){
			echo '<th>Sucursal</th>';
		}
		echo '<th>Tipo Poliza</th>';
		echo '
		<th>Usuario</th></tr>'; 
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
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO</td>';
			}
			elseif($Abono['respuesta1']==""){
				echo '<td align="center" width="40" nowrap>&nbsp;</td>';
			}
			else{
				echo '<td align="center" width="40" nowrap>&nbsp;</td>';
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['obs'].'</td>';
			echo '<td>'.htmlentities($array_clientes[$Abono['cliente']]).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			if($rowempresa['maneja_pagado']==1){
				if($Abono['estatus_pago']==1)
					echo '<td align="center">'.$Abono['fecha_pago'].'</td>';
				else
					echo '<td align="center">&nbsp;</td>';
			}
			if($rowempresa['maneja_sucursal']==1){
				echo '<td align="center">'.$array_sucursales[$Abono['sucursal']].'</td>';
			}
			echo '<td align="center">'.$array_tipo_poliza[$Abono['tipo_poliza']].'</td>';
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido'];
			$sumacargo[3]+=$Abono['isr_retenido'];
			$sumacargo[4]+=$Abono['iva_retenido'];
			$sumacargo[5]+=$Abono['total'];
		}
		$c=5;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		$c=2;
		if($rowempresa['maneja_pagado']==1) $c++;
		if($rowempresa['maneja_sucursal']==1) $c++;
		echo '<td bgcolor="#E9F2F8" colspan="'.$c.'">&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	
	exit();
}
if($_POST['ajax']==100)
{
	$respuesta=array();
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	if($rowempresa['idempresacontabilidad']>0)
	{
		$select="select * from facturas where empresa='{$_POST['cveempresa']}' and cve='{$_POST['cve']}'";
		$rsfactura=mysql_query($select) or die(mysql_error());
		$rowfactura=mysql_fetch_array($rsfactura);
		
		//Tomar la informacion para crear la poliza
		if($rowfactura['tipo_poliza']==1)
			$tipopoliza=2; //Poliza de Ingreso
		else
			$tipopoliza=1; //Poliza de Diario
		
		$idtipomovimiento=2; //Generado por Sistema Externo
		$referencia=$rowfactura['cve'];
		$fecha=$rowfactura['fecha'];
		$concepto='VENTA SEGUN FACTURA FOLIO '.$rowfactura['cve'];
		$conceptos=array();
		$comprobantes=array();
		$pagos=array();
		
		//$comprobantes[]=array('uuid'=>$rowfactura['uuid'],'rfc'=>$row['rfcreceptor'],'importe' => $row['total'],'moneda' =>$row['moneda'], 'tipocambio' =>$row['tipocambio']);
		if($rowfactura['tipo_poliza']==1)
		{
			//Tomar la cuenta de banco de la empresa
			$conceptos[]=array('cuenta'=>$rowempresa['cuentabanco'],'concepto'=>$concepto,'importe'=>$rowfactura['total'],'tipo'=>0);
		}
		else
		{
			//Tomar la cuenta contable del Cliente
			$select="select cuentacontable from clientes where cve='{$rowfactura['cliente']}'";
			$rscliente=mysql_query($select) or die(mysql_error());
			$rowcliente=mysql_fetch_array($rscliente);
			$conceptos[]=array('cuenta'=>$rowcliente['cuentacontable'],'concepto'=>$concepto,'importe'=>$rowfactura['total'],'tipo'=>0);
		}
		
		//Tomar la cuenta de ingresos por venta
		if($rowfactura['sucursal']==0)
			$ctaingreso=$rowempresa['cuentaingresos'];
		else
		{
			$select="select cuentacontable from sucursales where cve='{$rowfactura['sucursal']}'";
			$rssucursal=mysql_query($select) or die(mysql_error());
			$rowsucursal=mysql_fetch_array($rssucursal);
			$ctaingreso=$rowsucursal['cuentacontable'];
		}
		$imping=$rowfactura['subtotal'];
		$conceptos[]=array('cuenta'=>$ctaingreso,'concepto'=>$concepto,'importe'=>$imping,'tipo'=>1);
		
		if($rowfactura['tipo_poliza']==1)
			$ctaiva=$rowempresa['cuentaiva'];
		else
			$ctaiva=$rowempresa['cuentaivaxcobrar'];
		$impiva=$rowfactura['iva'];
		$conceptos[]=array('cuenta'=>$ctaiva,'concepto'=>$concepto,'importe'=>$impiva,'tipo'=>1);
		/*
		if($row['tasaretensioniva']==4)
			$catretiva=$idretiva4;
		else
			$catretiva=0;
		
		$impretiva=$row['retensioniva'];
		if($impretiva>0)
			$conceptos[]=array('idcuenta'=>$catretiva,'importe'=>$impretiva,'tipo'=>'C');
		
		if($row['tasaretensionisr']==4)
			$catretisr=$idretisr4;
		
		if($row['tasaretensionisr']==10)
			$catretisr=$idretisr10;
		
		$impretisr=$row['retensionisr'];
		if($impretisr>0)
			$conceptos[]=array('idcuenta'=>$catretisr,'importe'=>$impretisr,'tipo'=>'C');
		*/
		$poliza=array('fecha' => $fecha, 'concepto' => $concepto, 'referencia' => $referencia, 'tipoMovimiento' => $idtipomovimiento, 'tipoPoliza' => $tipopoliza, 'asientos' => $conceptos, 'comprobantesNacional' => $comprobantes, 'comprobantesExtranjero' => array(), 'pagosCheque' => array(), 'pagosTransferencia' => array(), 'pagosOtro' => array());
		//Generar la poliza
		require_once('nusoap/nusoap.php');
		$oSoapClient = new nusoap_client("http://sucontabilidad.net/wscontabilidad.php?wsdl", true);			
		$err = $oSoapClient->getError();
		if($err!="")
			$respuesta['mensaje']='error:'.$err;
		else
		{
			$oSoapClient->timeout = 300;
			$oSoapClient->response_timeout = 300;
			$respuestaws = $oSoapClient->call("RegistrarPoliza", array ('usr'=>$rowempresa['usuariocontabilidad'],'pwd'=>$rowempresa['passwordcontabilidad'],'empresa'=>$rowempresa['idempresacontabilidad'],'poliza'=>$poliza));
			if ($oSoapClient->fault)
			{
				$respuesta['mensaje']='<p><b>Fault: ';
				ob_start();
				print_r($respuestaws);
				$straux=ob_get_clean();
				$respuesta['mensaje'].=$straux.'</b></p>';
			}
			else
			{
				$err = $oSoapClient->getError();
				if ($err)
					$respuesta['mensaje']='<p><b>Error: ' . $err . '</b></p>';
				else
				{
					if($respuestaws['resultado'])
					{
						$idpoliza=$respuestaws['id'];
						$respuesta['folio']=$respuestaws['mensaje'];
						$respuesta['resultado']=true;
						//Actualizar el registro
						$query="update facturas set idpoliza='$idpoliza',foliopoliza='{$respuesta['folio']}' where empresa='{$_POST['cveempresa']}' and cve='{$_POST['cve']}'";
						mysql_query($query);
					}
					else
						$respuesta['mensaje']=$respuestaws['mensaje'];
				}
			}
		}
	}
	else
		$respuesta['mensaje']='La empresa no tiene configurado el enlase con SuContabilidad';
	echo json_encode($respuesta);
	exit();
}
if($_POST['ajax']==1){
	$filtro="";
	$select= " SELECT a.* FROM facturas as a";
	if($_POST['grupo_cliente'] != "") $select.=" INNER JOIN clientes b ON b.cve = a.cliente";
	$select.=" WHERE a.empresa='".$_POST['cveempresa']."'";
	if($_POST['folio'] != ''){
		$select.=" AND a.cve='".$_POST['folio']."'";
	}
	else{
		$select.=" AND a.fecha>='".$_POST['fecha_ini']."' AND a.fecha<='".$_POST['fecha_fin']."' ";
		//if($_POST['tipo']!="all") $select.=" AND a.tipo='".$_POST['tipo']."'";
		if($_POST['cliente']!="") $select.=" AND a.cliente IN (".$_POST['cliente'].")";
		if ($_POST['usu']!="all") { $select.=" AND a.usuario='".$_POST['usu']."'"; }
		if ($_POST['grupo_cliente']!="all") { $select.=" AND b.grupo='".$_POST['grupo_cliente']."'"; }
		if ($_POST['estatus_pago']!="all") { $select.=" AND a.estatus_pago='".$_POST['estatus_pago']."'"; }
		if ($_POST['sucursal']!="all") { $select.=" AND a.sucursal='".$_POST['sucursal']."'"; }
		if ($_POST['tipo_poliza']!="all") { $select.=" AND a.tipo_poliza='".$_POST['tipo_poliza']."'"; }
		if($_POST['estatus']==1) $select.=" AND a.estatus!='C'";
		elseif($_POST['estatus']==2) $select.=" AND a.estatus='C'";
		$select.=" ORDER BY a.cve DESC";
	}
	$rsabonos=mysql_query($select) or die(mysql_error());
	if(mysql_num_rows($rsabonos)>0) {
		echo '<table width="100%" border="0" cellpadding="4" cellspacing="1" class="">';
		$c=15;
		if($_POST['cveusuario']==1) $c++;
		if($rowempresa['maneja_pagado']==1) $c+=2;
		if($rowempresa['maneja_sucursal']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.mysql_num_rows($rsabonos).' Registro(s)</td></tr>';
		echo '<tr bgcolor="#E9F2F8"><th>&nbsp;</th>';
		if($_POST['cveusuario']==1){
			echo '<th><input type="checkbox" name="selt" value="1" onClick="if(this.checked) $(\'.checks\').attr(\'checked\',\'checked\'); else $(\'.checks\').removeAttr(\'checked\');"></th>';
		}
		if($rowempresa['maneja_pagado']==1){
			echo '<th>Pagar<br><input type="checkbox" name="seltp" value="1" onClick="if(this.checked) $(\'.checksp\').attr(\'checked\',\'checked\'); else $(\'.checksp\').removeAttr(\'checked\');"></th>';
		}
		echo '<th>Folio</th><th>Fecha</th><th>Concepto</th>
		<th>Cliente</th><th>Tipo Pago</th><th>Subtotal</th>
		<th>Iva</th><th>Total</th><th>Retencion I.S.R.</th><th>Retencion I.V.A.</th><th>Total</th>';
		if($rowempresa['maneja_pagado']==1){
			echo '<th>Fecha Pago</th>';
		}
		if($rowempresa['maneja_sucursal']==1){
			echo '<th>Sucursal</th>';
		}
		echo '
		<th>Usuario<br>';
		echo '<select name="usuario" onchange="document.forma.usu.value=this.value;buscarRegistros();"><option value="all">---Todos---</option>';
		$res1=mysql_query("SELECT a.usuario FROM facturas as a WHERE empresa='".$_POST['cveempresa']."' GROUP BY a.usuario ORDER BY a.usuario");
		while($row1=mysql_fetch_array($res1)){
			echo '<option value="'.$row1['usuario'].'"';
			if($row1['usuario']==$_POST['usu']) echo ' selected';
			echo '>'.$array_usuario[$row1['usuario']].'</option>';
		}
		echo '</select></th><th>Tipo Poliza</th><th>Poliza</th></tr>'; 
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
					$Abono['total']=0;
					$Abono['iva_retenido']=0;
				}
				echo '<td align="center">CANCELADO<br>';
				if(file_exists('cfdi/comprobantes/facturac_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cfdi/comprobantes/facturac_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'cfdi/comprobantes/facturac_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				echo '</td>';
				if($_POST['cveusuario']==1){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
				if($rowempresa['maneja_pagado']==1){
					echo '<td>&nbsp;</td>';
				}
			}
			elseif($Abono['respuesta1']==""){
				echo '<td align="center" width="40" nowrap>';
				echo '<a href="#" onClick="if(confirm(\'Esta seguro que desea timbrar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'5\',\''.$Abono['cve'].'\');}"><img src="images/validosi.gif" border="0" title="Timbrar '.$Abono['folio'].'"></a>';
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				echo '</td>';
				if($_POST['cveusuario']==1){
					echo '<td>&nbsp;</td>';
				}
				if($rowempresa['maneja_pagado']==1){
					echo '<td>&nbsp;</td>';
				}
			}
			else{
				echo '<td align="center" width="40" nowrap>';
				//<a href="#" onClick="atcr(\'cfdi/comprobantes/cfdi_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'\',\'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				if(file_exists('cfdi/comprobantes/factura_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf')){
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'cfdi/comprobantes/factura_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\',\'_blank\',\'0\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
					if($_POST['cveusuario']==1){
						echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'103\',\'cfdi/comprobantes/factura_'.$_POST['cveempresa'].'_'.$Abono['cve'].'.pdf\');"><img src="images/basura.gif" border="0" title="Borrar PDF '.$Abono['folio'].'"></a>';
					}
				}
				else{
					echo '&nbsp;&nbsp;<a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'101\',\''.$Abono['cve'].'\');"><img src="images/b_print.png" border="0" title="Imprimir '.$Abono['folio'].'"></a>';
				}
				echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de cancelar?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'3\',\''.$Abono['cve'].'\');}"><img src="images/validono.gif" border="0" title="Cancelar '.$Abono['folio'].'"></a>';
				if($rowempresa['maneja_pagado']==1){
					if($Abono['estatus_pago']==1 && nivelUsuario()>2){
						echo '&nbsp;&nbsp;<a href="#" onClick="if(confirm(\'Esta seguro de regresar a proceso?\')){$(\'#panel\').show();atcr(\'facturas.php\',\'\',\'13\',\''.$Abono['cve'].'\');}"><img src="images/cerrar.gif" border="0" title="Regresar a Proceso '.$Abono['folio'].'"></a>';
					}
				}
				echo '</td>';
				if($_POST['cveusuario']==1){
					echo '<td><input type="checkbox" class="checks" name="checksf[]" value="'.$Abono['cve'].'"></td>';
				}
				if($rowempresa['maneja_pagado']==1){
					if($Abono['estatus_pago']==1){
						echo '<td>Pagado</td>';
					}
					else{
						echo '<td align="center"><input type="checkbox" class="checksp" name="checksp[]" value="'.$Abono['cve'].'"></td>';
					}
				}
			}
			echo '<td align="center">'.$Abono['cve'].'</td>';
			echo '<td align="center">'.$Abono['fecha'].' '.$Abono['hora'].'</td>';
			echo '<td align="center">'.$Abono['obs'].'</td>';
			echo '<td>'.htmlentities($array_clientes[$Abono['cliente']]).'</td>';
			echo '<td>'.htmlentities($array_tipo_pago[$Abono['tipo_pago']]).'</td>';
			echo '<td align="right">'.number_format($Abono['subtotal'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total']+$Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['isr_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['iva_retenido'],2).'</td>';
			echo '<td align="right">'.number_format($Abono['total'],2).'</td>';
			if($rowempresa['maneja_pagado']==1){
				if($Abono['estatus_pago']==1)
					echo '<td align="center">'.$Abono['fecha_pago'].'</td>';
				else
					echo '<td align="center">&nbsp;</td>';
			}
			if($rowempresa['maneja_sucursal']==1){
				echo '<td align="center">'.$array_sucursales[$Abono['sucursal']].'</td>';
			}
			echo '<td align="center">'.$array_usuario[$Abono['usuario']].'</td>';
			echo '<td align="center">'.$array_tipo_poliza[$Abono['tipo_poliza']].'</td>';
			if($Abono['idpoliza']>0)
				echo '<td align="center">'.$Abono['foliopoliza'].'</td>';
			else
				echo '<td align="center" id="poliza'.$Abono['cve'].'"><a href="#" onClick="GeneraPoliza('.$Abono['cve'].')">Generar</a></td>';
			echo '</tr>';
			$x++;
			$sumacargo[0]+=$Abono['subtotal'];
			$sumacargo[1]+=$Abono['iva'];
			$sumacargo[2]+=$Abono['total']+$Abono['iva_retenido'];
			$sumacargo[3]+=$Abono['isr_retenido'];
			$sumacargo[4]+=$Abono['iva_retenido'];
			$sumacargo[5]+=$Abono['total'];
		}
		$c=5;
		if($_POST['cveusuario']==1) $c++;
		if($rowempresa['maneja_pagado']==1) $c++;
		echo '<tr><td bgcolor="#E9F2F8" colspan="'.$c.'">'.$x.' Registro(s)</td>';
		echo '<td bgcolor="#E9F2F8" align="right">&nbsp;Total</td>';
		foreach($sumacargo as $k=>$v){
			echo '<td bgcolor="#E9F2F8" align="right">&nbsp;'.number_format($v,2).'</td>';
		}
		$c=3;
		if($rowempresa['maneja_pagado']==1) $c++;
		if($rowempresa['maneja_sucursal']==1) $c++;
		echo '<td bgcolor="#E9F2F8" colspan="'.$c.'">&nbsp;</td>';
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

if($_POST['cmd']==6){
	require_once("PHPMailer/class.phpmailer.php");
	require_once("PHPMailer/class.smtp.php");
	foreach($_POST['checksf'] as $cvefact){
		$res = mysql_query("SELECT * FROM facturas WHERE empresa='".$_POST['cveempresa']."' AND cve='".$cvefact."'");
		$row = mysql_fetch_array($res);
		$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
		$row1 = mysql_fetch_array($res1);
		$row1['cve']=0;
		$emailenvio = $row1['email'];

		$mgClient = new Mailgun('key-21c746b361efffee28fa9f560769805f');
		$domain = "hoyfactura.com";

		$attachments = array();
		if($row['estatus']=='C')
			$attachments[] = "cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf";
		else
			$attachments[] = array("Factura ".$cvefact.".pdf","cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf");
		$attachments[] = "cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml";
		if(trim($emailenvio)!=""){
			$result = $mgClient->sendMessage($domain, array(
			    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
			    'to'      => trim($emailenvio),
			    'subject' => "Factura ".$cvefact,
			    'text'    => "Factura ".$cvefact,
			), array(
			    'attachment' => $attachments
			));
		}
		if($rowempresa['email']!=""){
			$result = $mgClient->sendMessage($domain, array(
			    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
			    'to'      => trim($rowempresa['email']),
			    'subject' => "Factura ".$cvefact,
			    'text'    => "Factura ".$cvefact,
			), array(
			    'attachment' => $attachments
			));
		}
		
		/*$mail = new PHPMailer();
		//$mail->Host = "localhost";

		$mail->Host = 'smtp.mailgun.org';   
		$mail->Port = 465;
		$mail->Username = 'postmaster@hoyfactura.com';   
		$mail->Password ='aeb15fb2b02a52be6664316e28982520';
		$mail->From = "hoyfactura@hoyfactura.com";
		$mail->FromName = "MiFactura";
		$mail->Subject = "Factura ".$cvefact;
		$mail->Body = "Factura ".$cvefact;
		//$mail->AddAddress(trim($emailenvio));
		$correos = explode(",",trim($emailenvio));
		foreach($correos as $correo)
			$mail->AddAddress(trim($correo));
		if($row['estatus']=='C')
			$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
		else
			$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
		$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
		$mail->Send();
		if($rowempresa['email']!=""){
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->Host = 'smtp.mailgun.org';   
			$mail->Port = 2525;
			$mail->Username = 'postmaster@hoyfactura.com';   
			$mail->Password ='aeb15fb2b02a52be6664316e28982520';
			$mail->From = "hoyfactura@hoyfactura.com";
			$mail->FromName = "MiFactura";
			$mail->Subject = "Factura ".$cvefact;
			$mail->Body = "Factura ".$cvefact;
			$correos = explode(",",trim($rowempresa['email']));
			foreach($correos as $correo)
				$mail->AddAddress(trim($correo));
			if($row['estatus']=='C')
				$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
			else
				$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
			$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
			$mail->Send();
		}*/
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==5){
	require_once("phpmailer/class.phpmailer.php");
	
	$res = mysql_query("SELECT * FROM facturas WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	
	$cvefact=$_POST['reg'];
	$documento=array();
	require_once("nusoap/nusoap.php");
	//Generamos la Factura
	$documento['serie']='';
	$documento['folio']=$cvefact;
	$documento['fecha']=$row['fecha'].' '.$row['hora'];
	$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
	$documento['idtipodocumento']=1;
	$documento['observaciones']=$row['obs'];
	$documento['metodopago']=$array_tipo_pago[$row['tipo_pago']];
	$res1 = mysql_query("SELECT * FROM clientes WHERE cve='".$row['cliente']."'");
	$row1 = mysql_fetch_array($res1);
	$row1['cve']=0;
	$emailenvio = $row1['email'];
	$documento['receptor']['codigo']=$row1['cve'];
	$documento['receptor']['rfc']=$row1['rfc'].$row1['homoclave'];
	$documento['receptor']['nombre']=$row1['nombre'];
	$documento['receptor']['calle']=$row1['calle'];
	$documento['receptor']['num_ext']=$row1['numexterior'];
	$documento['receptor']['num_int']=$row1['numinterior'];
	$documento['receptor']['colonia']=$row1['colonia'];
	$documento['receptor']['localidad']=$row1['localidad'];
	$documento['receptor']['municipio']=$row1['municipio'];
	$documento['receptor']['estado']=$row1['estado'];
	$documento['receptor']['pais']='MEXICO';
	$documento['receptor']['codigopostal']=$row1['codigopostal'];
	//Agregamos los conceptos
	$res2 = mysql_query("SELECT * FROM facturasmov WHERE empresa='".$_POST['cveempresa']."' AND cvefact='".$cvefact."'");
	
	$i=0;
	while($row2 = mysql_fetch_array($res2)){
		$documento['conceptos'][$i]['cantidad']=$row2['cantidad'];
		$documento['conceptos'][$i]['unidad']=$row2['unidad'];
		$documento['conceptos'][$i]['descripcion']=iconv('UTF-8','ISO-8859-1',$row2['concepto']);
		$documento['conceptos'][$i]['valorUnitario']=$row2['precio'];
		$documento['conceptos'][$i]['importe']=$row2['importe'];
		$documento['conceptos'][$i]['importe_iva']=$row2['importe_iva'];
		$i++;
	}
	$documento['subtotal']=$row['subtotal'];
	$documento['descuento']=0;
	//Traslados
	#IVA
	if($row['iva']>0){
		$documento['tasaivatrasladado']=16;
		$documento['ivatrasladado']=$row['iva'];  //Solo 200 grava iva
	}
	if($row['iva_retenido'] > 0){
		$documento['ivaretenido']=$row['iva_retenido'];  
	}
	if($row['isr_retenido'] > 0){
		$documento['isrretenido']=$row['isr_retenido'];  
	}
	
	//total
	$documento['total']=$row['total'];
	//Moneda
	$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;
	
	//print_r($documento);
	$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
	$err = $oSoapClient->getError();
	if($err!="")
		echo "error1:".$err;
	else{
		//print_r($documento);
		$oSoapClient->timeout = 300;
		$oSoapClient->response_timeout = 300;
		$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
		if ($oSoapClient->fault) {
			echo '<p><b>Fault: ';
			print_r($respuesta);
			echo '</b></p>';
			echo '<p><b>Request: <br>';
			echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
			echo '<p><b>Response: <br>';
			echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
			echo '<p><b>Debug: <br>';
			echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
		}
		else{
			$err = $oSoapClient->getError();
			if ($err){
				echo '<p><b>Error: ' . $err . '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
			}
			else{
				if($respuesta['resultado']){
					mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
					sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
					sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
					fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
					WHERE empresa='".$_POST['cveempresa']."' AND cve=".$cvefact);
					generaFacturaPdf($_POST['cveempresa'],$cvefact);
					//Tomar la informacion de Retorno
					$dir="cfdi/comprobantes/";
					//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
					//el zip siempre se deja fuera
					$dir2="cfdi/";
					//Leer el Archivo Zip
					$fileresult=$respuesta['archivos'];
					$strzipresponse=base64_decode($fileresult);
					$filename='cfdi_'.$_POST['cveempresa'].'_'.$cvefact;
					file_put_contents($dir2.$filename.'.zip', $strzipresponse);
					$zip = new ZipArchive;
					if ($zip->open($dir2.$filename.'.zip') === TRUE){
						$strxml=$zip->getFromName('xml.xml');
						file_put_contents($dir.$filename.'.xml', $strxml);
						$strpdf=$zip->getFromName('formato.pdf');
						file_put_contents($dir.$filename.'.pdf', $strpdf);
						$zip->close();	
						generaFacturaPdf($_POST['cveempresa'],$cvefact);
						if($emailenvio!=""){
							$mail = new PHPMailer();
							$mail->Host = 'smtp.mailgun.org';   
							$mail->Port = 465;
							$mail->Username = 'postmaster@hoyfactura.com';   
							$mail->Password ='aeb15fb2b02a52be6664316e28982520';
							$mail->From = "hoyfactura@hoyfactura.com";
							$mail->FromName = "MiFactura";
							$mail->Subject = "Factura ".$cvefact;
							$mail->Body = "Factura ".$cvefact;
							//$mail->AddAddress(trim($emailenvio));
							$correos = explode(",",trim($emailenvio));
							foreach($correos as $correo)
								$mail->AddAddress(trim($correo));
							$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
							$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
							$mail->Send();
						}
						if($rowempresa['email']!=""){
							$mail = new PHPMailer();
							$mail->Host = 'smtp.mailgun.org';   
							$mail->Port = 465;
							$mail->Username = 'postmaster@hoyfactura.com';   
							$mail->Password ='aeb15fb2b02a52be6664316e28982520';
							$mail->From = "hoyfactura@hoyfactura.com";
							$mail->FromName = "MiFactura";
							$mail->Subject = "Factura ".$cvefact;
							$mail->Body = "Factura ".$cvefact;
							$correos = explode(",",trim($rowempresa['email']));
							foreach($correos as $correo)
								$mail->AddAddress(trim($correo));
							$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
							$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
							$mail->Send();
						}
						/*$mgClient = new Mailgun('key-21c746b361efffee28fa9f560769805f');
						$domain = "hoyfactura.com";

						$attachments = array();
						$attachments[] = "cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf";
						$attachments[] = "cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml";
						if(trim($emailenvio)!=""){
							$result = $mgClient->sendMessage($domain, array(
							    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
							    'to'      => trim($emailenvio),
							    'subject' => "Factura ".$cvefact,
							    'text'    => "Factura ".$cvefact,
							), array(
							    'attachment' => $attachments
							));
						}
						if($rowempresa['email']!=""){
							$result = $mgClient->sendMessage($domain, array(
							    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
							    'to'      => trim($rowempresa['email']),
							    'subject' => "Factura ".$cvefact,
							    'text'    => "Factura ".$cvefact,
							), array(
							    'attachment' => $attachments
							));
						}*/
					}
					else 
						$strmsg='Error al descomprimir el archivo';
				}
				else
					$strmsg=$respuesta['mensaje'];
				//print_r($respuesta);	
				echo $strmsg;
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==3){
	require_once("phpmailer/class.phpmailer.php");
	$res = mysql_query("SELECT * FROM facturas WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
	$row = mysql_fetch_array($res);
	if($row['estatus']!='C'){
		$cvefact=$row['cve'];
		if($row['respuesta1']!=""){
			require_once("nusoap/nusoap.php");
			$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
			$err = $oSoapClient->getError();
			if($err!="")
				echo "error1:".$err;
			else{
				//print_r($documento);
				$oSoapClient->timeout = 300;
				$oSoapClient->response_timeout = 300;
				$respuesta = $oSoapClient->call("cancelar", array ('id' => $rowempresa['idplaza'],'rfcemisor' =>$rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'uuid' => $row['respuesta1'], 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
				if ($oSoapClient->fault) {
					echo '<p><b>Fault: ';
					print_r($respuesta);
					echo '</b></p>';
					echo '<p><b>Request: <br>';
					echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Response: <br>';
					echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
					echo '<p><b>Debug: <br>';
					echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
				}
				else{
					$err = $oSoapClient->getError();
					if ($err){
						echo '<p><b>Error: ' . $err . '</b></p>';
						echo '<p><b>Request: <br>';
						echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Response: <br>';
						echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
						echo '<p><b>Debug: <br>';
						echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
					}
					else{
						if($respuesta['resultado']){
							mysql_query("UPDATE facturas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."',respuesta2='".$respuesta['mensaje']."' WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
							generaFacturaPdf($_POST['cveempresa'],$cvefact);
							if($emailenvio!=""){
								$mail = new PHPMailer();
								$mail->Host = 'smtp.mailgun.org';   
								$mail->Port = 465;
								$mail->Username = 'postmaster@hoyfactura.com';   
								$mail->Password ='aeb15fb2b02a52be6664316e28982520';
								$mail->From = "hoyfactura@hoyfactura.com";
								$mail->FromName = "MiFactura";
								$mail->Subject = "Cancelacion Factura ".$cvefact;
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($emailenvio));
								$correos = explode(",",trim($emailenvio));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}	
							if($rowempresa['email']!=""){
								$mail = new PHPMailer();
								$mail->Host = 'smtp.mailgun.org';   
								$mail->Port = 465;
								$mail->Username = 'postmaster@hoyfactura.com';   
								$mail->Password ='aeb15fb2b02a52be6664316e28982520';
								$mail->From = "hoyfactura@hoyfactura.com";
								$mail->FromName = "MiFactura";
								$mail->Subject = "Cancelacion Factura ".$cvefact;
								$mail->Body = "Cancelacion Factura ".$cvefact;
								//$mail->AddAddress(trim($rowempresa['email']));
								$correos = explode(",",trim($rowempresa['email']));
								foreach($correos as $correo)
									$mail->AddAddress(trim($correo));
								$mail->AddAttachment("cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
								$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
								$mail->Send();
							}
							/*$mgClient = new Mailgun('key-21c746b361efffee28fa9f560769805f');
							$domain = "hoyfactura.com";

							$attachments = array();
							$attachments[] = "cfdi/comprobantes/facturac_".$_POST['cveempresa']."_".$cvefact.".pdf";
							$attachments[] = "cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml";
							if(trim($emailenvio)!=""){
								$result = $mgClient->sendMessage($domain, array(
								    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
								    'to'      => trim($emailenvio),
								    'subject' => "Factura ".$cvefact,
								    'text'    => "Factura ".$cvefact,
								), array(
								    'attachment' => $attachments
								));
							}
							if($rowempresa['email']!=""){
								$result = $mgClient->sendMessage($domain, array(
								    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
								    'to'      => trim($rowempresa['email']),
								    'subject' => "Factura ".$cvefact,
								    'text'    => "Factura ".$cvefact,
								), array(
								    'attachment' => $attachments
								));
							}*/
						}
						else
							$strmsg=$respuesta['mensaje'];
						//print_r($respuesta);	
						echo $strmsg;
					}
				}
			}
		}
		else{
			mysql_query("UPDATE facturas SET estatus='C',usucan='".$_POST['cveusuario']."',fechacan='".fechaLocal()." ".horaLocal()."' WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
			generaFacturaPdf($_POST['cveempresa'],$cvefact);
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==2){
	require_once("phpmailer/class.phpmailer.php");
	$res = mysql_query("SELECT folio_inicial FROM foliosiniciales WHERE empresa='".$_POST['cveempresa']."' AND tipo=0 AND tipodocumento=1");
	$row = mysql_fetch_array($res);
	$res1 = mysql_query("SELECT cve FROM facturas WHERE empresa='".$_POST['cveempresa']."'");
	if(mysql_num_rows($res1) > 0){
		mysql_query("INSERT facturas SET empresa='".$_POST['cveempresa']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',tipo_poliza='".$_POST['tipo_poliza']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',sucursal='".$_POST['sucursal']."'") or die("INSERT facturas SET empresa='".$_POST['cveempresa']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."'".mysql_error());
	}
	else{
		mysql_query("INSERT facturas SET empresa='".$_POST['cveempresa']."',cve='".$row['folio_inicial']."',fecha='".$_POST['fecha']."',fecha_creacion='".fechaLocal()."',hora='".horaLocal()."',obs='".$_POST['obs']."',
		cliente='".$_POST['cliente']."',tipo_pago='".$_POST['tipo_pago']."',usuario='".$_POST['cveusuario']."',baniva_retenido='".$_POST['baniva_retenido']."',banisr_retenido='".$_POST['banisr_retenido']."',
		carta_porte='".$_POST['carta_porte']."',load_cliente='".$_POST['load']."',nombre_cliente='".$_POST['nombre_cliente']."',direccion_cliente='".$_POST['direccion_cliente']."',tipo_poliza='".$_POST['tipo_poliza']."',
		tipopago_cliente='".$_POST['tipopago_cliente']."',banco_cliente='".$_POST['banco_cliente']."',cuenta_cliente='".$_POST['cuenta_cliente']."',tipo_factura='".$_POST['tipo_factura']."',sucursal='".$_POST['sucursal']."'") or die(mysql_error());
	}
	$cvefact=mysql_insert_id();
	$documento=array();
	require_once("nusoap/nusoap.php");
	//Generamos la Factura
	$documento['serie']='';
	$documento['folio']=$cvefact;
	$documento['fecha']=$_POST['fecha'].' '.horaLocal();
	$documento['formapago']='PAGO EN UNA SOLA EXHIBICION';
	$documento['idtipodocumento']=1;
	$documento['observaciones']=$_POST['obs'];
	$documento['metodopago']=$array_tipo_pago[$_POST['tipo_pago']];
	$res = mysql_query("SELECT * FROM clientes WHERE cve='".$_POST['cliente']."'");
	$row = mysql_fetch_array($res);
	$emailenvio = $row['email'];
	$row['cve']=0;
	$documento['receptor']['codigo']=$row['cve'];
	$documento['receptor']['rfc']=$row['rfc'];
	$documento['receptor']['nombre']=$row['nombre'];
	$documento['receptor']['calle']=$row['calle'];
	$documento['receptor']['num_ext']=$row['numexterior'];
	$documento['receptor']['num_int']=$row['numinterior'];
	$documento['receptor']['colonia']=$row['colonia'];
	$documento['receptor']['localidad']=$row['localidad'];
	$documento['receptor']['municipio']=$row['municipio'];
	$documento['receptor']['estado']=$row['estado'];
	$documento['receptor']['pais']='MEXICO';
	$documento['receptor']['codigopostal']=$row['codigopostal'];
	//Agregamos los conceptos
	$i=0;
	foreach($_POST['cant'] as $k=>$v){
		if($v>0){
			if(trim($_POST['unidad'][$k])=="") $_POST['unidad'][$k] = "NO APLICA";
			$importe_iva=round($_POST['importe'][$k]*$_POST['ivap'][$k]/100,2);
			mysql_query("INSERT facturasmov SET empresa='".$_POST['cveempresa']."',cvefact='$cvefact',cantidad='".$v."',concepto='".$_POST['concepto'][$k]."',
			precio='".$_POST['precio'][$k]."',importe='".$_POST['importe'][$k]."',iva='".$_POST['ivap'][$k]."',importe_iva='$importe_iva',unidad='".$_POST['unidad'][$k]."'");
			$documento['conceptos'][$i]['cantidad']=$v;
			$documento['conceptos'][$i]['unidad']=$_POST['unidad'][$k];
			$documento['conceptos'][$i]['descripcion']=$_POST['concepto'][$k];
			$documento['conceptos'][$i]['valorUnitario']=$_POST['precio'][$k];
			$documento['conceptos'][$i]['importe']=$_POST['importe'][$k];
			$documento['conceptos'][$i]['importe_iva']=$importe_iva;
			$i++;
		}
	}
	mysql_query("UPDATE facturas SET subtotal='".$_POST['subtotal']."',iva='".$_POST['iva']."',total='".$_POST['total']."',
	isr_retenido='".$_POST['isr_retenido']."',por_isr_retenido='".$_POST['por_isr_retenido']."',
	iva_retenido='".$_POST['iva_retenido']."',por_iva_retenido='".$_POST['por_iva_retenido']."' WHERE empresa='".$_POST['cveempresa']."' AND cve=".$cvefact);
	$documento['subtotal']=$_POST['subtotal'];
	$documento['descuento']=0;
	//Traslados
	#IVA
	if($_POST['iva']>0){
		$documento['tasaivatrasladado']=16;
		$documento['ivatrasladado']=$_POST['iva'];  //Solo 200 grava iva
	}
	if($_POST['iva_retenido'] > 0){
		$documento['ivaretenido']=$_POST['iva_retenido'];  
	}
	if($_POST['isr_retenido'] > 0){
		$documento['isrretenido']=$_POST['isr_retenido'];  
	}
	//total
	$documento['total']=$_POST['total'];
	//Moneda
	$documento['moneda']     = 1; //1=pesos, 2=Dolar, 3=Euro
	$documento['tipocambio'] = 1;
	
	//print_r($documento);
	$oSoapClient = new nusoap_client("http://compuredes.mx/webservices/wscfdi2012.php?wsdl", true);			
	$err = $oSoapClient->getError();
	if($err!="")
		echo "error1:".$err;
	else{
		//print_r($documento);
		$oSoapClient->timeout = 300;
		$oSoapClient->response_timeout = 300;
		$respuesta = $oSoapClient->call("generar", array ('id' => $rowempresa['idplaza'],'rfcemisor' => $rowempresa['rfc'],'idcertificado' => $rowempresa['idcertificado'],'documento' => $documento, 'usuario' => $rowempresa['usuario'],'password' => $rowempresa['pass']));
		if ($oSoapClient->fault) {
			echo '<p><b>Fault: ';
			print_r($respuesta);
			echo '</b></p>';
			echo '<p><b>Request: <br>';
			echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
			echo '<p><b>Response: <br>';
			echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
			echo '<p><b>Debug: <br>';
			echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
		}
		else{
			$err = $oSoapClient->getError();
			if ($err){
				echo '<p><b>Error: ' . $err . '</b></p>';
				echo '<p><b>Request: <br>';
				echo htmlspecialchars($oSoapClient->request, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Response: <br>';
				echo htmlspecialchars($oSoapClient->response, ENT_QUOTES) . '</b></p>';
				echo '<p><b>Debug: <br>';
				echo htmlspecialchars($oSoapClient->debug_str, ENT_QUOTES) . '</b></p>';
			}
			else{
				if($respuesta['resultado']){
					mysql_query("UPDATE facturas SET respuesta1='".$respuesta['uuid']."',seriecertificado='".$respuesta['seriecertificado']."',
					sellodocumento='".$respuesta['sellodocumento']."',uuid='".$respuesta['uuid']."',seriecertificadosat='".$respuesta['seriecertificadosat']."',
					sellotimbre='".$respuesta['sellotimbre']."',cadenaoriginal='".$respuesta['cadenaoriginal']."',
					fechatimbre='".substr($respuesta['fechatimbre'],0,10)." ".substr($respuesta['fechatimbre'],-8)."'
					WHERE empresa='".$_POST['cveempresa']."' AND cve=".$cvefact);
					//Tomar la informacion de Retorno
					$dir="cfdi/comprobantes/";
					//$dir=dirname(realpath(getcwd()))."/solucionesfe_facturacion/cfdi/comprobantes/";
					//el zip siempre se deja fuera
					$dir2="cfdi/";
					//Leer el Archivo Zip
					$fileresult=$respuesta['archivos'];
					$strzipresponse=base64_decode($fileresult);
					$filename='cfdi_'.$_POST['cveempresa'].'_'.$cvefact;
					file_put_contents($dir2.$filename.'.zip', $strzipresponse);
					$zip = new ZipArchive;
					if ($zip->open($dir2.$filename.'.zip') === TRUE){
						$strxml=$zip->getFromName('xml.xml');
						file_put_contents($dir.$filename.'.xml', $strxml);
						$strpdf=$zip->getFromName('formato.pdf');
						file_put_contents($dir.$filename.'.pdf', $strpdf);
						$zip->close();		
						generaFacturaPdf($_POST['cveempresa'],$cvefact);
						if($emailenvio!=""){
							$mail = new PHPMailer();
							$mail->Host = 'smtp.mailgun.org';   
							$mail->Port = 465;
							$mail->Username = 'postmaster@hoyfactura.com';   
							$mail->Password ='aeb15fb2b02a52be6664316e28982520';
							$mail->From = "hoyfactura@hoyfactura.com";
							$mail->FromName = "MiFactura";
							$mail->Subject = "Factura ".$cvefact;
							$mail->Body = "Factura ".$cvefact;
							//$mail->AddAddress(trim($emailenvio));
							$correos = explode(",",trim($emailenvio));
							foreach($correos as $correo)
								$mail->AddAddress(trim($correo));
							$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
							$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
							$mail->Send();
						}	
						if($rowempresa['email']!=""){
							$mail = new PHPMailer();
							$mail->Host = 'smtp.mailgun.org';   
							$mail->Port = 465;
							$mail->Username = 'postmaster@hoyfactura.com';   
							$mail->Password ='aeb15fb2b02a52be6664316e28982520';
							$mail->From = "hoyfactura@hoyfactura.com";
							$mail->FromName = "MiFactura";
							$mail->Subject = "Factura ".$cvefact;
							$mail->Body = "Factura ".$cvefact;
							//$mail->AddAddress(trim($rowempresa['email']));
							$correos = explode(",",trim($rowempresa['email']));
							foreach($correos as $correo)
								$mail->AddAddress(trim($correo));
							$mail->AddAttachment("cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf", "Factura ".$cvefact.".pdf");
							$mail->AddAttachment("cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml", "Factura ".$cvefact.".xml");
							$mail->Send();
						}	
						/*$mgClient = new Mailgun('key-21c746b361efffee28fa9f560769805f');
						$domain = "hoyfactura.com";

						$attachments = array();
						$attachments[] = "cfdi/comprobantes/factura_".$_POST['cveempresa']."_".$cvefact.".pdf";
						$attachments[] = "cfdi/comprobantes/cfdi_".$_POST['cveempresa']."_".$cvefact.".xml";
						if(trim($emailenvio)!=""){
							$result = $mgClient->sendMessage($domain, array(
							    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
							    'to'      => trim($emailenvio),
							    'subject' => "Factura ".$cvefact,
							    'text'    => "Factura ".$cvefact,
							), array(
							    'attachment' => $attachments
							));
						}
						if($rowempresa['email']!=""){
							$result = $mgClient->sendMessage($domain, array(
							    'from'    => 'MiFactura <hoyfactura@hoyfactura.com>',
							    'to'      => trim($rowempresa['email']),
							    'subject' => "Factura ".$cvefact,
							    'text'    => "Factura ".$cvefact,
							), array(
							    'attachment' => $attachments
							));
						}*/
					}
					else 
						$strmsg='Error al descomprimir el archivo';
				}
				else
					$strmsg=$respuesta['mensaje'];
				//print_r($respuesta);	
				echo $strmsg;
			}
		}
	}
	$_POST['cmd']=0;
}

if($_POST['cmd']==12){
	foreach($_POST['checksp'] as $cvefact){
		$res = mysql_query("UPDATE facturas SET estatus_pago=1, usuario_pago='".$_POST['cveusuario']."',fecha_pago='".$_POST['reg']."',fechahora_pago='".fechaLocal()." ".horaLocal()."' WHERE empresa='".$_POST['cveempresa']."' AND cve='".$cvefact."'");
	}
	$_POST['cmd']=0;
}


if($_POST['cmd']==13){
	$res = mysql_query("UPDATE facturas SET estatus_pago=0, usuario_pago='0',fecha_pago='0000-00-00',fechahora_pago='0000-00-00 00:00:00' WHERE empresa='".$_POST['cveempresa']."' AND cve='".$_POST['reg']."'");
	$_POST['cmd']=0;
}

top($_SESSION);
	$res = mysql_query("SELECT por_iva_retenido, mod_iva_retenido, por_isr_retenido, mod_isr_retenido FROM empresas WHERE cve='".$_POST['cveempresa']."'");
	$row = mysql_fetch_array($res);
	$por_iva_retenido = $row['por_iva_retenido'];
	$bloquearivaret = " readOnly";
	$claseivaret = "readOnly";
	if($row['mod_iva_retenido'] == 1){
		$bloquearivaret = "";
		$claseivaret = "textField";
	}
	$por_isr_retenido = $row['por_isr_retenido'];
	$bloquearisrret = " readOnly";
	$claseisrret = "readOnly";
	if($row['mod_isr_retenido'] == 1){
		$bloquearisrret = "";
		$claseisrret = "textField";
	}
	if($_POST['cmd']==1){
		echo '<table><tr>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="$(\'#panel\').show();
			if(document.forma.cliente.value==\'0\'){
				alert(\'Necesita seleccionar el cliente\');
				$(\'#panel\').hide();
			}
			else if($.trim(document.forma.total.value)==\'\'){
				alert(\'El total debe de ser mayor a cero\');
				$(\'#panel\').hide();
			}
			else if(document.forma.tipo_poliza.value==\'0\'){
				alert(\'Necesita seleccionar el tipo de poliza\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.load.value)==\'\'){
				alert(\'Necesita ingresar el load\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.nombre_cliente.value)==\'\'){
				alert(\'Necesita ingresar el nombre del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.direccion_cliente.value)==\'\'){
				alert(\'Necesita ingresar la direccion del cliente\');
				$(\'#panel\').hide();
			}
			else if(document.forma.carta_porte.checked == true && $.trim(document.forma.tipopago_cliente.value)==\'\'){
				alert(\'Necesita seleccionar el tipo de pago de la carta porte\');
				$(\'#panel\').hide();
			}
			else{
				atcr(\'facturas.php\',\'\',2,\'0\');
			}
			"><img src="images/guardar.gif" border="0">&nbsp;Guardar</a></td><td>&nbsp;</td>';
		}
		echo '<td><a href="#" onclick="$(\'#panel\').show();atcr(\'facturas.php\',\'\',0,\'0\');"><img src="images/flecha-izquierda.gif" border="0">&nbsp;&nbsp;Regresar</a></td>';
		echo '</tr></table>';
		echo '<br>';
		echo '<table>';
		echo '<tr><td align="left">Fecha</td><td><input type="text" name="fecha" id="fecha"  size="15" class="readOnly" value="'.fechaLocal().'" readOnly>&nbsp;&nbsp;<!--<a href="#" onClick="displayCalendar(document.forms[0].fecha,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a>--></td></tr>';
		$fecha_rec=date( "Y-m-d" , strtotime ( "-1 day" , strtotime(fechaLocal()) ) );
		echo '<tr><td align="left">Tipo</td><td><select name="tipo_factura" id="tipo_factura"><option value="0">Factura</option><option value="1">Honorarios</option></select></td></tr>';
		echo '<tr><td align="left">Grupo Clientes</td><td><select name="grupo_clientec" id="grupo_clientec" onChange="atcr(\'facturas.php\',\'\',1,0);"><option value="0">Todos</option>';
		foreach($array_grupo_clientes as $k=>$v){
			echo '<option value="'.$k.'"';
			if($_POST['grupo_clientec'] == $k) echo ' selected';
			echo '>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select name="cliente" id="cliente" onChange="seleccioneSucursal()"><option value="0" sucursal="0" nomsucursal="">--- Seleccione ---</option>';
		foreach($array_clientes as $k=>$v){
			echo '<option value="'.$k.'" style="color: '.$array_colorcliente[$k].';"';
			if($array_colorcliente[$k] == "#FF0000") echo ' disabled';
			echo ' sucursal="'.$array_clientes_sucursal[$k]['sucursal'].'" nomsucursal="'.$array_clientes_sucursal[$k]['nomsucursal'].'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($rowempresa['maneja_sucursal'] != 1) echo ' style="display:none;"';
		echo '>';
		echo '<td>Sucursal</td><td><input type="hidden" name="sucursal" id="sucursal" value="">
		<input type="text" class="readOnly" value="" id="nomsucursal" size="50"></td>';
		echo '</tr>';
		echo '<tr><td>Tipo de Pago</td><td><select name="tipo_pago" id="tipo_pago">';
		foreach($array_tipo_pago as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>Tipo de Poliza</td><td><select name="tipo_poliza" id="tipo_poliza"><option value="0">Seleccione</option>';
		foreach($array_tipo_poliza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		/*echo '<tr><td align="left">Mes</td><td><select name="mes" id="mes"><option value="0">Seleccione</option>';
		$res = mysql_query("SELECT LEFT(fechaapl,7) FROM depositos WHERE estatus!='C' AND fechaapl>'0000-00-00' GROUP BY LEFT(fechaapl,7) ORDER BY LEFT(fechaapl,7) DESC");
		while($row=mysql_fetch_array($res)){
			$dat=explode("-",$row[0]);
			echo '<option value="'.$row[0].'">'.$array_meses[intval($dat[1])].' '.$dat[0].'</option>';
		}
		echo '</select></td></tr>';*/
		echo '<tr';
		if($rowempresa['carta_porte']!=1){
			echo ' style="display:none;"';
		}
		echo '><td>Carta Porte</th><td><input type="checkbox" id="carta_porte" name="carta_porte" value="1" onClick="
			if(this.checked){ 
				$(\'.rcarta_porte\').show(); 
			}
			else{ 
				$(\'.rcarta_porte\').hide();
			}"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Load</td><td><input type="text" class="textField" name="load" id="load" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Nombre del Cliente</td><td><input type="text" class="textField" name="nombre_cliente" id="nombre_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Direccion del Cliente</td><td><input type="text" class="textField" name="direccion_cliente" id="direccion_cliente" value="" size="100"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Tipo de Pago</td><td><input type="text" class="textField" name="tipopago_cliente" id="tipopago_cliente" value="" size="50"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Banco</td><td><input type="text" class="textField" name="banco_cliente" id="banco_cliente" value="" size="30"></td></tr>';
		echo '<tr class="rcarta_porte" style="display:none;"><td>Cuenta</td><td><input type="text" class="textField" name="cuenta_cliente" id="cuenta_cliente" value="" size="30"></td></tr>';
		echo '<tr><td>Observaciones</td><td><textarea class="textField" name="obs" id="obs" cols="30" rows="3"></textarea></td></tr>';
		echo '</table>';
		echo '<input type="hidden" name="clickguardar" id="clickguardar" value="no">';
		echo '<table id="tablaproductos"><tr>';
		echo '<th>Cantidad</th><th>Unidad</th>';
		echo '<th>Descripcion</th><th>Precio Unitario</th><th>Importe</th><th>IVA</th></tr>';
		$i=0;
		if($i==0){
			echo '<tr>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="cant['.$i.']" id="cant'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td><input type="text" name="unidad['.$i.']" id="unidad'.$i.'" class="textField" size="20" value=""></td>';
			echo '<td><input type="text" name="concepto['.$i.']" id="concepto'.$i.'" class="textField" size="50" value=""></td>';
			echo '<td align="center"><input type="text" class="textField" size="10" name="precio['.$i.']" id="precio'.$i.'" value=""  onKeyUp="sumarproductos()"></td>';
			echo '<td align="center"><input type="text" class="readOnly" size="10" name="importe['.$i.']" id="importe'.$i.'" value="" readOnly></td>';
			echo '<td align="center"><input type="checkbox" name="ivap['.$i.']" id="ivap'.$i.'" value="16" onClick="sumarproductos()" checked></td>';
			echo '</tr>';
			$i++;
		}
		echo '<tr id="idsubtotal"><th align="right" colspan="4">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="" readOnly></td></tr>';
		echo '<tr id="idiva"><th align="right" colspan="4">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="" readOnly></td></tr>';
		echo '<tr id="idtotal1"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="" readOnly></td></tr>';
		echo '<tr id="idisr_ret"><th align="right" colspan="4"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()">Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="'.$por_isr_retenido.'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>';
		echo '<tr id="idiva_ret"><th align="right" colspan="4"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()">Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="'.$por_iva_retenido.'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>';
		echo '<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="" readOnly></td></tr>';
		echo '</table>';		
		echo '<input type="button" value="Agregar" onClick="agregarproducto()" class="textField">';
		echo '<input type="hidden" name="cantprod" value="'.$i.'">';
		echo '<script>

			function seleccioneSucursal(){
				option = $("#cliente").find(\'option[value="\'+document.forma.cliente.value+\'"]\');
				$("#sucursal").val(option.attr("sucursal"));
				$("#nomsucursal").val(option.attr("nomsucursal"));
			}
					
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
				piva_ret=$("#por_iva_retenido").val();
				$("#idiva_ret").remove();
				isr_ret=$("#isr_retenido").val();
				pisr_ret=$("#por_isr_retenido").val();
				$("#idisr_ret").remove();
				num=document.forma.cantprod.value;
				$("#tablaproductos").append(\'<tr>\
				<td align="center"><input type="text" class="textField" size="10" name="cant[\'+num+\']" id="cant\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\</td>\
				<td><input type="text" name="unidad[\'+num+\']" id="unidad\'+num+\'" class="textField" size="20" value=""></td>\
				<td><input type="text" name="concepto[\'+num+\']" id="concepto\'+num+\'" class="textField" size="50" value=""></td>\
				<td align="center"><input type="text" class="textField" size="10" name="precio[\'+num+\']" id="precio\'+num+\'" value=""  onKeyUp="sumarproductos()"></td>\
				<td align="center"><input type="text" class="readOnly" size="10" name="importe[\'+num+\']" id="importe\'+num+\'" value="" readOnly></td>\
				<td align="center"><input type="checkbox" name="ivap[\'+num+\']" id="ivap\'+num+\'" value="16" onClick="sumarproductos()" checked></td>\
				</tr>\
				<tr id="idsubtotal"><th align="right" colspan="4">Subtotal&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="subtotal" id="subtotal" value="\'+subtot+\'" readOnly></td></tr>\
				<tr id="idiva"><th align="right" colspan="4">Iva 16%&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva" id="iva" value="\'+iv+\'" readOnly></td></tr>\
				<tr id="idtotal1"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total1" id="total1" value="\'+tot1+\'" readOnly></td></tr>\
				<tr id="idisr_ret"><th align="right" colspan="4"><input type="checkbox" name="banisr_retenido" id="banisr_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.S.R.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="isr_retenido" id="isr_retenido" value="\'+isr_ret+\'" readOnly></td><td><input type="text" class="'.$claseisrret.'" size="5" name="por_isr_retenido" id="por_isr_retenido" value="\'+pisr_ret+\'" onKeyUp="sumarproductos()" '.$bloquearisrret.'>%</td></tr>\
				<tr id="idiva_ret"><th align="right" colspan="4"><input type="checkbox" name="baniva_retenido" id="baniva_retenido" value="1" onClick="sumarproductos()" \'+checkeado+\'>Retencion I.V.A.&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="iva_retenido" id="iva_retenido" value="\'+iva_ret+\'" readOnly></td><td><input type="text" class="'.$claseivaret.'" size="5" name="por_iva_retenido" id="por_iva_retenido" value="\'+piva_ret+\'" onKeyUp="sumarproductos()" '.$bloquearivaret.'>%</td></tr>\
				<tr id="idtotal"><th align="right" colspan="4">Total&nbsp;&nbsp;<td align="center"><input type="text" class="readOnly" size="10" name="total" id="total" value="\'+tot+\'" readOnly></td></tr>\');
				num++;
				document.forma.cantprod.value=num;
			}
			
			function sumarproductos(){
				var sumar=0;
				var iv=0;
				var iv_ret=0;
				var is_ret=0;
				for(i=0;i<(document.forma.cantprod.value/1);i++){
					impo=(document.getElementById("cant"+i).value/1)*(document.getElementById("precio"+i).value/1);
					document.getElementById("importe"+i).value=impo.toFixed(2);
					sumar+=(document.getElementById("importe"+i).value/1);
					is_ret+=document.getElementById("importe"+i).value*document.forma.por_isr_retenido.value/100;
					if(document.getElementById("ivap"+i).checked){
						iv+=document.getElementById("importe"+i).value*0.16;
						iv_ret+=document.getElementById("importe"+i).value*document.forma.por_iva_retenido.value/100;
					}
				}
				document.forma.subtotal.value=sumar.toFixed(2);
				document.forma.iva.value=iv.toFixed(2);
				document.forma.total1.value=(document.forma.subtotal.value/1)+(document.forma.iva.value/1);
				if($("#banisr_retenido").is(":checked")){
					document.forma.isr_retenido.value=is_ret.toFixed(2);
				}
				else{
					document.forma.isr_retenido.value=0;
				}
				if($("#baniva_retenido").is(":checked")){
					document.forma.iva_retenido.value=iv_ret.toFixed(2);
				}
				else{
					document.forma.iva_retenido.value=0;
				}
				
				tot=(document.forma.subtotal.value/1)+(document.forma.iva.value/1)-(document.forma.isr_retenido.value/1)-(document.forma.iva_retenido.value/1);
				document.forma.total.value=tot.toFixed(2);
			}
			
			
		  </script>';
	}

	/*** PAGINA PRINCIPAL **************************************************/

	if ($_POST['cmd']<1) {
		echo '<div id="dialog" style="display:none">
			<table>
			<tr><td class="tableEnc">Pagar</td></tr>
			</table>
			<table width="100%">
				<tr><td>Fecha de Pago</td><td><input type="text" id="dfecha_pago" value="" class="readOnly" size="12" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.getElementById(\'dfecha_pago\'),\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>
	
			</table>
			</div>';
		//Busqueda
		echo '<table>';
		echo '<tr>
				<td><a href="#" onclick="buscarRegistros(0,1);"><img src="images/buscar.gif" border="0">&nbsp;&nbsp;Buscar</a>&nbsp;&nbsp;</td>
				<td><a href="#" onClick="atcr(\'facturas.php\',\'\',\'1\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Nuevo</a></td><td>&nbsp;</td>
				<td><a href="#" onClick="document.forma.cliente.value=$(\'#clientes\').multipleSelect(\'getSelects\');atcr(\'facturas.php\',\'\',\'10\',\'0\');"><img src="images/b_print.png" border="0">&nbsp;Excel</a></td><td>&nbsp;</td>';
		if(nivelUsuario()>1){
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'\',\'6\',\'0\');"><img src="images/nuevo.gif" border="0">&nbsp;Reenviar Archivos</a></td><td>&nbsp;</td>';
			echo '<td><a href="#" onClick="atcr(\'facturas.php\',\'_blank\',\'16\',\'0\');"><img src="images/zip_grande.png" border="0" width="15px" height="15px" title="Descargar">&nbsp;Descargar Archivos</a></td><td>&nbsp;</td>';
			if($rowempresa['maneja_pagado']==1){
				echo '<td><a href="#" onClick="pagarFacturas()"><img src="images/finalizar.gif" border="0">&nbsp;Pagar</a></td><td>&nbsp;</td>';
			}
		}
		echo '</tr>';
		echo '</table>';
		echo '<table>';
		echo '<tr><td align="left">Folio</td><td><input type="text" name="folio" id="folio"  size="15" class="textField" value=""></td></tr>';
		echo '<tr><td align="left">Fecha Inicial</td><td><input type="text" name="fecha_ini" id="fecha_ini"  size="15" class="readOnly" value="'.substr(fechaLocal(),0,8).'01" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_ini,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Fecha Final</td><td><input type="text" name="fecha_fin" id="fecha_fin"  size="15" class="readOnly" value="'.fechaLocal().'" readonly>&nbsp;<a href="#" onClick="displayCalendar(document.forms[0].fecha_fin,\'yyyy-mm-dd\',this,true)"><img src="images/calendario.gif" border="0"></a></td></tr>';
		echo '<tr><td align="left">Cliente</td><td><select multiple="multiple" name="clientes" id="clientes">';
		foreach($array_clientes as $k=>$v){
			echo '<option class="cexternos" value="'.$k.'" style="color: '.$array_colorcliente[$k].';" selected>'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Estatus</td><td><select name="estatus" id="estatus"><option value="0">Todos</option><option value="1">Activos</option>
		<option value="2">Cancelado</option></select></td></tr>';
		if($rowempresa['maneja_pagado']==1){
			echo '<tr><td align="left">Estatus Pago</td><td><select name="estatus_pago" id="estatus_pago"><option value="all" selected>Todos</option><option value="0">En Proceso</option>
			<option value="1">Pagado</option></select></td></tr>';
		}
		else{
			echo '<input type="hidden" name="estatus_pago" id="estatus_pago" value="all">';
		}
		echo '<tr><td align="left">Grupo de Cliente</th><td><select name="grupo_cliente" id="grupo_cliente"><option value="all">Todos</option>';
		foreach($array_grupo_clientes as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr';
		if($rowempresa['maneja_sucursal'] != 1) echo ' style="display:none;"';
		echo '><td align="left">Sucursal</th><td><select name="sucursal" id="sucursal"><option value="all">Todas</option>';
		foreach($array_sucursales as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="left">Tipo Poliza</td><td><select name="tipo_poliza" id="tipo_poliza"><option value="all">--- Todos ---</option>';
		foreach($array_tipo_poliza as $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
		}
		echo '</select></td></tr>';
		echo '</table>';
		echo '<br>';
		echo '<input type="hidden" name="usu" id="usu" value="all">';
		echo '<input type="hidden" name="cliente" id="cliente" value="">';
		//Listado
		echo '<div id="Resultados">';
		echo '</div>';
	}
bottom();
echo '
<Script language="javascript">
	
	$("#clientes").multipleSelect({
		width: 500
	});	

	function buscarRegistros()
	{
		document.forma.cliente.value=$("#clientes").multipleSelect("getSelects");
		document.getElementById("Resultados").innerHTML = "<img src=\'images/ajaxtrabajando.gif\' border=\'0\' align=\'absmiddle\'> Espere un momento, buscando registros...";
		objeto=crearObjeto();
		if (objeto.readyState != 0) {
			alert("Error: El Navegador no soporta AJAX");
		} else {
			objeto.open("POST","facturas.php",true);
			objeto.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			objeto.send("ajax=1&folio="+document.getElementById("folio").value+"&grupo_cliente="+document.getElementById("grupo_cliente").value+"&tipo_poliza="+document.getElementById("tipo_poliza").value+"&sucursal="+document.getElementById("sucursal").value+"&estatus_pago="+document.getElementById("estatus_pago").value+"&estatus="+document.getElementById("estatus").value+"&cliente="+document.getElementById("cliente").value+"&fecha_ini="+document.getElementById("fecha_ini").value+"&fecha_fin="+document.getElementById("fecha_fin").value+"&usu="+document.getElementById("usu").value+"&numeroPagina="+document.getElementById("numeroPagina").value+"&cveempresa="+document.getElementById("cveempresa").value+"&cveusuario="+document.getElementById("cveusuario").value+"&cvemenu="+document.getElementById("cvemenu").value);
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
	}
	function GeneraPoliza(cve)
	{
		$.post("facturas.php",{ajax:100,cveempresa:$("#cveempresa").val(),cve:cve},function(result)
		{
			if(result.resultado)
				$("#poliza"+cve).html(result.folio);
			else
				alert(result.mensaje);
		},"json");
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
	
	function pagarFacturas(){
		if($(".checksp").is(":checked"))
			$("#dialog").dialog("open");
		else
			alert("Tiene que seleccionar al menos una factura");
	}
	
	$("#dialog").dialog({ 
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 300,
			height: 150,
			autoResize: true,
			position: "center",
			beforeClose: function( event, ui ) {
				document.getElementById("dfecha_pago").value="";
			},
			buttons: {
				"Aceptar": function(){ 
					if(document.getElementById("dfecha_pago").value==""){
						alert("Necesita seleccionar la fecha de pago");
					}
					else{
						atcr("facturas.php","",12,document.getElementById("dfecha_pago").value);
					}
				},
				"Cerrar": function(){ 
					document.getElementById("dfecha_pago").value="";
					$(this).dialog("close"); 
				}
			},
		}); 

	</Script>
';

?>