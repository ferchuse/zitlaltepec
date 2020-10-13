<?php
	require_once('subs/cnx_db.php');
	include("parametros-navegacion.php");
	
	global $base,$PHP_SELF,$cveempresanomina;
	/*Validamos solicitud de login a este sitio*/
	if (!isset($_SESSION)) {
		session_start();
	}
	
	function getRealIP()
	{
		global $_SERVER;
		if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
			
			// los proxys van a�adiendo al final de esta cabecera
			// las direcciones ip que van "ocultando". Para localizar la ip real
			// del usuario se comienza a mirar por el principio hasta encontrar
			// una direcci�n ip que no sea del rango privado. En caso de no
			// encontrarse ninguna se toma como valor el REMOTE_ADDR
			
			$entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
			
			reset($entries);
			while (list(, $entry) = each($entries))
			{
				$entry = trim($entry);
				if ( preg_match("/^([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/", $entry, $ip_list) )
				{
					// http://www.faqs.org/rfcs/rfc1918.html
					$private_ip = array(
					'/^0\\./',
					'/^127\\.0\\.0\\.1/',
					'/^192\\.168\\..*/',
					'/^172\\.((1[6-9])|(2[0-9])|(3[0-1]))\\..*/',
					'/^10\\..*/');
					
					$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
					
					if ($client_ip != $found_ip)
					{
						$client_ip = $found_ip;
						break;
					}
				}
			}
		}
		else
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		}
		
		return $client_ip;
		
	}
	
	if(!$_SESSION['CveUsuario'] && !$_SESSION['NomUsuario'] && !isset($_POST['loginUser']) && !isset($_POST['loginPassword'])) {
		
		header("Location: index2.php");
		exit();
		
	}
	
	if($_SESSION['CveUsuario']!=1 && $_POST['loginUser']!="root"){
		$rsCerrado=mysql_query("SELECT * FROM usuarios WHERE cve='1'") or die(mysql_error());
		$Cerrado=mysql_fetch_array($rsCerrado);
		if($Cerrado['cerrar_sistema']=='S'){
			echo '<script>window.location="index2.php";</script>';
		}
	}
	$archivo=explode("/",$_SERVER["PHP_SELF"]);
	global $archivo,$reg_sistema;
	
	$empresagcompufax=2;
	$urlgcompufax = 'http://gcompufax.com/recepcion_datos.php';
	
	$empresagdatos=3;
	$urlgdatos = 'http://gdatos.com/nuevo/recepcion_datos.php';
	
	$array_tipokardex=array("Compra","Vale","Venta Mostrador","Traspaso Almacen");
	
	$array_meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	$array_dias=array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado");
	$array_modulos=array(1=>"Catalogos", 2=>"Parque Vehicular",3=>"Operadores",4=>'Recaudacion Unidades',
	6=>'Recaudacion Operadores',9=>'Recaudacion Pachuca',5=>'Movimientos',7=>'Accidentes',8=>'Taquilla',10=>'Taquilla sin Guia', 13=>"Gps",12=>"Servicios",16=>"Monitoreo",99=>"Administracion");
	//
	$empresanomina = 999999;
	$array_nosi=array('NO','SI');
	$array_estatus_personal = array(1=>"Alta",2=>"Baja",3=>"Inactivo");
	$array_diasmes=array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	$array_forma_pago=array("PAGO EN UNA SOLA EXHIBICION");
	$array_tipo_pago=array(0=>"EFECTIVO",2=>"TRANSFERENCIA",3=>"DEPOSITO",4=>"NO ESPECIFICADO",5=>"CHEQUE DENOMINATIVO",6=>"NO APLICA",7=>"CREDITO");//,1=>"CHEQUE"
	$array_tipo_nomina=array(1=>"Semanal",2=>"Decenal",3=>"Quincenal",4=>"Mensual");
	$array_tipo_accidente=array("Accidente","Especial","Robo, Conflicto y Dentencion","Liberacion","Sin registro","Mixto");
	$array_estatus_accidentes=array("Pendiente","Liquidado","Sin costo para la empresa","Recuperacion de Terceros");
	$array_documentos=array(1=>"Factura",2=>"Nota");
	$array_localidad=array(0=>'Normal',1=>'Pachuca');
	$array_estatus_unidad=array(1=>'Alta',2=>'Baja',3=>'Inactivo');
	// Si existen las variables POST  usuario y password viene de login
	if (isset($_POST['loginUser']) && isset($_POST['loginPassword'])) {
		//Como se supone venimos de ventana de login o sesion expirada, eliminamos cualquier rastro de sesion anterior
		// Unset all of the session variables.
		$_SESSION = array();
		// Finally, destroy the session.
		session_destroy();
		$loginUsername=$_POST['loginUser'];
		$password=$_POST['loginPassword'];
		$redirectLoginSuccess = "inicio.php";
		$redirectLoginFailed = "index2.php?ErrLogUs=true";
		//Hacemos uso de la funcion GetSQLValueString para evitar la inyeccion de SQL
		//$LoginRS_query = sprintf("SELECT * FROM usuarios WHERE usuario LIKE BINARY %s AND password LIKE BINARY %s", Se le quito validacion de distincion de mayusculas y minusculas
		$LoginRS_query = sprintf("SELECT * FROM usuarios WHERE usuario = %s AND password = %s AND estatus='A'",
		GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
		
		//echo $LoginRS_query;
		//exit();
		$LoginRS = mysql_query($LoginRS_query) or die(mysql_error());
		
		$loginFoundUser = mysql_num_rows($LoginRS);
		
		if ($loginFoundUser) {
			
			$Usuario=mysql_fetch_array($LoginRS);
			
			if($Usuario['cve']!=1){
				$rsCerrado=mysql_query("SELECT * FROM usuarios WHERE cve='1'");
				$Cerrado=mysql_fetch_array($rsCerrado);
				if($Cerrado['cerrar_sistema']=='S'){
					echo '<script>window.location="index2.php";</script>';
				}
			}
			$ip=getRealIP();
			$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 hour" , strtotime(date("Y-m-d H:i:s")) ) );
			mysql_query("INSERT registros_sistema SET usuario='".$Usuario['cve']."',entrada='".$fechahora."',ip='$ip'");
			$reg_sistema=mysql_insert_id();
			
			//Creamos la sesion
			
			session_start();		
			
			
			
			//Creamos las variables de sesion del usuario en cuestion
			
			$_SESSION['CveUsuario'] = $Usuario['cve'];
			
			$_SESSION['NomUsuario'] = $Usuario['nombre'];
			
			$_SESSION['PlazaUsuario'] = $Usuario['plaza'];
			
			$_SESSION['TipoUsuario'] = $Usuario['tipo'];
			
			$_SESSION['RecUsuario'] = $Usuario['recaudacion'];
			
			$_SESSION['NickUsuario'] = $Usuario['usuario'];
			
			$_SESSION['reg_sistema'] = $reg_sistema;
			if($Usuario['permisionario'] > 0)
			header("Location: edo_cuenta_uni_permisionario.php");
			else
			header("Location: " . $redirectLoginSuccess );
			
			} else {
			
			header("Location: " . $redirectLoginFailed);
			
			
		}
		
	}
	
	$_POST['plazausuario'] = 1;
	
	if(intval($_POST['cveusuario'])==0){
		$_POST['cveusuario']=$_SESSION['CveUsuario'];
		$_POST['cvemenu']=2;
		$_POST['cveregistro']=$_SESSION['reg_sistema'];
	}
	
	if($_POST['cveusuario']==1 || $_SESSION['TipoUsuario']==1)
	$res = mysql_query("SELECT a.cve,a.numero FROM plazas a WHERE a.estatus!='I' ORDER BY a.nombre");
	else
	$res = mysql_query("SELECT a.cve,a.numero FROM plazas a INNER JOIN usuario_accesos b ON a.cve=b.plaza AND b.usuario='".$_POST['cveusuario']."' AND b.acceso>0 WHERE a.estatus!='I' ORDER BY a.nombre");
	while($row = mysql_fetch_array($res)){
		$array_plaza[$row['cve']]=$row['numero'];
	}
	
	function top($SESSION,$enter=0,$nomenu=false) {
		
		
		
		global $base,$PHP_SELF,$array_modulos,$_POST,$cveempresanomina;
		
		
		
		//$url=split("/",$PHP_SELF);
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		
		
		
		$menuRS=mysql_query("SELECT * FROM menu WHERE cve='".$_POST['cvemenu']."'");
		
		while($Menu=mysql_fetch_array($menuRS)) {
			
			$menuEncabezado=$Menu['nombre'];
			
		}
		
		echo '
		
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>:: Grupo Zitlaltepec ::</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
		<style>
		.colorrojo { color: #FF0000 } 
		.panel {
		background:#DFE6EF;
		top:0px;
		left:0px;
		display:none;
		position:absolute;
		filter:alpha(opacity=40);
		opacity:.4;
        }
		</style>
		<script src="js/rutinas.js"></script>
		<link href="js/multiple-select.css" rel="stylesheet"/>
		<link rel="stylesheet" type="text/css" href="css/ui.css" />
		<!--link rel="stylesheet" type="text/css" href="jchat/estilos.css" /-->
		<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.8.23.custom.min.js" type="text/javascript"></script>
		<script src="js/serializeform.js" type="text/javascript"></script>
		<script src="js/jquery.multiple.select.js" type="text/javascript"></script>
		<script src="calendar/dhtmlgoodies_calendar.js"></script>
		<script src="js/validacampo.js" type="text/javascript"></script>
		<script>
		/*if(top.window.location.href!="http://verificentrosdf.net/")
		top.window.location.href="http://verificentrosdf.net";*/
		
		
		
		
		function pulsar(e) {
		tecla=(document.all) ? e.keyCode : e.which;
		if(tecla==13) return false;
		}';
		foreach($array_modulos as $k=>$v){
			echo 'var menu'.$k.'=0;';
		}
		
		echo '
		function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
		var	horas = parseInt(cadena.substr(12,1));
		else
		var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
		var	minuto = parseInt(cadena.substr(15,1));
		else
		var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
		var	segundo = parseInt(cadena.substr(18,1));
		else
		var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
		var	mes = parseInt(cadena.substr(6,1));
		else
		var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
		var	dia = parseInt(cadena.substr(9,1));
		else
		var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
		segundo=0;
		minuto++;
		if (minuto==60) {
		minuto=0;
		horas++;
		if (horas==24) {
		horas=0;
		dia++;
		if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
		dia=1;
		mes++;
		}
		if(mes==13){
		mes=1;
		anio++;
		}
		}
		}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;
		
		document.getElementById("idreloj").innerHTML = horaImprimible;
		
		setTimeout("mueveReloj()",1000)
		}
		</script>
		
		<!-- Add jQuery library -->
		<!--<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>-->
		
		<!-- Add mousewheel plugin (this is optional) -->
		<script type="text/javascript" src="assets/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
		
		<!-- Add fancyBox -->
		<link rel="stylesheet" href="assets/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
		<script type="text/javascript" src="assets/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>	
		
		</head>
		
		
		
		<form name="forma" id="forma" method="POST" enctype="multipart/form-data">
		
		
		
		<!-- Definicion de variables ocultas -->
		
		<input type="hidden" name="cmd" id="cmd">
		
		<input type="hidden" name="cmdreferer" id="cmdreferer">
		
		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="cveusuario" id="cveusuario" value="'.$_POST['cveusuario'].'">
		<input type="hidden" name="plazausuario" id="plazausuario" value="'.$_POST['plazausuario'].'">
		
		<input type="hidden" name="cvemenu" id="cvemenu" value="'.$_POST['cvemenu'].'">
		
		<input type="hidden" name="cveregistro" id="cveregistro" value="'.$_POST['cveregistro'].'">
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		
		<body'; if($enter==1) echo ' onkeypress="return pulsar(event)"'; echo '>
		<div id="panel" class="panel"></div>
		<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
		<td background="images/bannertop-bg.gif"><span class="whiteText17">Grupo Zitlaltepec </span></td>
		<!--<td bgcolor="#FFFFFF" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="whiteText17"><img src="images/foldio-logo-inicio.jpg" height="48px"/></span></td>-->
		
		</tr>
		
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="170" valign="top" bgcolor="#FFFFFF">
		
		';	
		if(nivelUsuario()==0){
			echo '<script>alert("No tiene acceso al menu");document.forma.cvemenu.value=2;atcr("inicio.php","",0,0);</script>';
		}
		if(!$nomenu)
		menuppal2($SESSION);
		
		
		echo '
		
		
		
		</td>
		
		<td width="6" valign="top" background="images/collapse_side_bg.gif"><img src="images/collapse_side_bg.gif" width="6" height="1" /></td>
		
		<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="30%" height="24" nowrap background="images/optionHeader.gif"><b>:: '.$menuEncabezado.' ::</b></td>
		
		<td background="images/optionHeader.gif"><div align="right">Bienvenido '.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.gif"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>
		
		<td width="15%" background="images/optionHeader.gif" align="center" nowrap>';
		echo '<a href="logout.php">Cerrar Sesion</a>';
		
		echo '</td>
		
		</tr>
		
		</table>
		
		<br />
		
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<tr><td>
		
		<!-- INICIO REGION EDITABLE -->
		
		';
		
	}
	
	function top2($SESSION) {
		
		
		
		global $base,$PHP_SELF,$array_modulos,$nombrelink,$_POST;
		
		
		
		//$url=split("/",$PHP_SELF);
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		
		
		
		$menuRS=mysql_query("SELECT * FROM menu");
		
		while($Menu=mysql_fetch_array($menuRS)) {
			
			if($url[0]==$Menu['link'])
			
			$menuEncabezado=$Menu['nombre'];
			
		}
		
		
		
		echo '
		
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
		
		
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>:: Grupo Zitlaltepec ::</title>
		
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="calendar/dhtmlgoodies_calendar.css" />
		<style>
		.colorrojo { color: #FF0000 } 
		.panel {
		background:#DFE6EF;
		top:0px;
		left:0px;
		display:none;
		position:absolute;
		filter:alpha(opacity=40);
		opacity:.4;
        }
		</style>
		<script src="js/rutinas.js"></script>
		
		<script src="js/jquery-1.2.6.min.js" type="text/javascript"></script>
		
		<script src="calendar/dhtmlgoodies_calendar.js"></script>
		
		<script>
		/*var fecha = "'.fechaLocal().' '.horaLocal().'";
		var	momentoActual = new Date(fecha);
		var	hora = momentoActual.getHours();
		var	minuto = momentoActual.getMinutes();
		var	segundo = momentoActual.getSeconds();
		var	dia = momentoActual.getDate();
		var	mes = momentoActual.getMonth()+1;
		var	anio = momentoActual.getFullYear();*/
		/*var	horas = parseInt("'.substr(horaLocal(),0,2).'");
		var	minuto = parseInt("'.substr(horaLocal(),3,2).'");
		var	segundo = parseInt("'.substr(horaLocal(),6,2).'");
		var	anio = parseInt("'.substr(fechaLocal(),0,4).'");
		var	mes = parseInt("'.substr(fechaLocal(),5,2).'");
		var	dia = parseInt("'.substr(fechaLocal(),8,2).'");*/
		';
		foreach($array_modulos as $k=>$v){
			echo 'var menu'.$k.'=0;';
		}
		echo '
		function mueveReloj(){
		cadena=document.getElementById("idreloj").innerHTML;
		if(cadena.substr(11,1)=="0")
		var	horas = parseInt(cadena.substr(12,1));
		else
		var	horas = parseInt(cadena.substr(11,2));
		if(cadena.substr(14,1)=="0")
		var	minuto = parseInt(cadena.substr(15,1));
		else
		var	minuto = parseInt(cadena.substr(14,2));
		if(cadena.substr(17,1)=="0")
		var	segundo = parseInt(cadena.substr(18,1));
		else
		var	segundo = parseInt(cadena.substr(17,2));
		var	anio = parseInt(cadena.substr(0,4));
		if(cadena.substr(5,1)=="0")
		var	mes = parseInt(cadena.substr(6,1));
		else
		var	mes = parseInt(cadena.substr(5,2));
		if(cadena.substr(8,1)=="0")
		var	dia = parseInt(cadena.substr(9,1));
		else
		var	dia = parseInt(cadena.substr(8,2));
		segundo++;
		if (segundo==60) {
		segundo=0;
		minuto++;
		if (minuto==60) {
		minuto=0;
		horas++;
		if (horas==24) {
		horas=0;
		dia++;
		if((dia==31 && (mes==4 || mes==6 || mes==9 || mes==11)) || (dia==32 && (mes==1 || mes==3 || mes==5 || mes==7 || mes==8 || mes==10 || mes==12)) || (dia==29 && mes==2 && (anio%4)!=0) || (dia==30 && mes==2 && (anio%4)==0)){
		dia=1;
		mes++;
		}
		if(mes==13){
		mes=1;
		anio++;
		}
		}
		}
		}
		if(horas<10) horas="0"+parseInt(horas);
		if(minuto<10) minuto="0"+parseInt(minuto);
		if(segundo<10) segundo="0"+parseInt(segundo);
		if(dia<10) dia="0"+parseInt(dia);
		if(mes<10) mes="0"+parseInt(mes);
		horaImprimible = anio+"-"+mes+"-"+dia+" "+horas+":"+minuto+ ":"+segundo;
		
		document.getElementById("idreloj").innerHTML = horaImprimible;
		
		setTimeout("mueveReloj()",1000)
		}
		</script>
		
		</head>
		
		
		
		<form name="forma" id="forma" method="POST" enctype="multipart/form-data">
		
		
		
		<!-- Definicion de variables ocultas -->
		
		<input type="hidden" name="cmd" id="cmd">
		
		<input type="hidden" name="cmdreferer" id="cmdreferer">
		
		<input type="hidden" name="reg" id="reg">
		
		<input type="hidden" name="numeroPagina" id="numeroPagina" value="0">
		<input type="hidden" name="plazausuario" id="plazausuario" value="'.$_POST['plazausuario'].'">
		
		<body>
		<div id="panel" class="panel"></div>
		<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
	    <td background="images/bannertop-bg.png"><span class="whiteText17">Grupo Zitlaltepec</span></td>
		
		</tr>
		
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="6" valign="top" background="images/collapse_side_bg.png"><img src="images/collapse_side_bg.png" width="6" height="1" /></td>
		
		<td valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		
		<tr>
		
		<td width="30%" height="24" nowrap background="images/optionHeader.png"><b>:: '.$nombrelink.' ::</b></td>
		
		<td background="images/optionHeader.png"><div align="right">'.$SESSION['NomUsuario'].'</div></td>
		
		<td background="images/optionHeader.png"><div align="center" id="idreloj">'.fechaLocal().' '.horaLocal().'</div></td>
		
		<td width="15%" background="images/optionHeader.png" align="center" nowrap>&nbsp;</td>
		
		</tr>
		
		</table>
		
		<br />
		
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<tr><td>
		
		<!-- INICIO REGION EDITABLE -->
		
		';
		
	}
	
	
	function bottom() {
		global $_SESSION;
		
		
		echo '
		
		<!-- FIN REGION EDITABLE -->		
		
		</td></tr>
		
		</table>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		<p>&nbsp;</p>
		
		</td>
		
		</tr>
		
		<tr>
		
	    <td colspan="3" valign="top" bgcolor="#9CDAFE">&nbsp;</td>
		
		</tr>
		
		</table>
		
		</body>
		<script>
		mueveReloj();
		window.onload=function(){
		if (self.screen.availWidth) {
		$("#panel").css("width",parseFloat(self.screen.availWidth)+50);
		}
		if (self.screen.availHeight) {
		$("#panel").css("height",self.screen.availHeight);
		}
        }  
        $(".placas").validCampo("abcdefghijklmn�opqrstuvwxyzABCDEFGHIJKLMN�OPQRSTUVWXYZ1234567890");
        $(".enteros").validCampo("1234567890");
		';
		/*$rs=mysql_query("SELECT chat,ide FROM usuarios where cve='".$_SESSION['CveUsuario']."'");
			$ro=mysql_fetch_array($rs);
			if($ro['chat']==1){
			echo '    creaChat("'.$_SESSION['CveUsuario'].'", "'.$ro['ide'].'", 1);
			';
		}*/
		echo '
		</script>
		';
		/*?>
			<!--Start of Tawk.to Script-->
			<script type="text/javascript">
			var $_Tawk_API={},$_Tawk_LoadStart=new Date();
			(function(){
			var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
			s1.async=true;
			s1.src='https://embed.tawk.to/556d4902faef9f244f834bcc/default';
			s1.charset='UTF-8';
			s1.setAttribute('crossorigin','*');
			s0.parentNode.insertBefore(s1,s0);
			})();
			</script>
			<!--End of Tawk.to Script-->
		<?php */ echo '
		</form>
		</html>
		
		';
		
	}
	
	function nivelUsuario(){
		global $_POST,$base,$_SESSION;
		if($_POST['cveusuario']==1 || $_POST['cvemenu']==13 || $_POST['cvemenu']==2){
			return 3;
		}
		else{
			$res=mysql_query("SELECT * FROM usuario_accesos WHERE usuario='".$_POST['cveusuario']."' AND plaza='".$_POST['plazausuario']."' AND menu='".$_POST['cvemenu']."'");
			if($row=mysql_fetch_array($res)){
				return $row['acceso'];
			}
			else{
				return 0;
			}
		}
	}
	
	function menuppal2($SESSION) {
		global $base,$array_modulos,$array_plaza,$PHP_SELF,$_POST;
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr><td height="20" bgcolor="#9CDAFE"><span class="style1">Menu '.$array_plaza[$_POST['plazausuario']].'</span></td></tr>
		<tr><td><a href="#" onClick="document.forma.cvemenu.value=1;atcr(\'inicio.php\',\'\',\'\',\'\')">-P&aacute;gina de Inicio</a></td></tr>
		<tr><td><a href="#" onClick="document.forma.cvemenu.value=2;atcr(\'cambiopass.php\',\'\',\'\',\'\')">-Cambiar Password</a></td></tr>
		<tr><td><a href="http://catemaco.grupozitlaltepec.com.mx/" target="_blank" onClick="">-Catemaco</a></td></tr>';
		
		if($_POST['plazausuario']>0){
			if(count($array_plaza)>1){
				echo '<tr><td><a href="#" onClick="atcr(\'inicio.php\',\'\',\'cambiarplaza\',\'\')">-Cambiar Plaza</a></td></tr>';
			}
			$mostrar="";
			foreach($array_modulos as $k=>$v){ 
				if($_POST['cveusuario']==1){
					$rs=mysql_query("SELECT * FROM menu WHERE modulo='$k' ORDER BY orden");
				}
				elseif($SESSION['TipoUsuario']==1){
					$rs=mysql_query("SELECT * FROM menu WHERE modulo='$k'and cve!='67' and cve!='68' ORDER BY orden");
				}
				else{
					$rs=mysql_query("SELECT a.* FROM menu as a INNER JOIN usuario_accesos as b ON (b.menu=a.cve AND b.usuario='".$_POST['cveusuario']."' AND b.plaza='".$_POST['plazausuario']."' AND b.acceso>0) WHERE a.modulo='$k' and a.cve!='67' and a.cve!='68' ORDER BY a.orden");
				}
				if(mysql_num_rows($rs)>0){
					
					echo '
					<tr>	  
					<td height="20" bgcolor="#9CDAFE">
					<span id="tmenu1" class="style1" onClick="if((menu'.$k.'%2)==0) $(\'.cmenu'.$k.'\').show(\'slow\'); else $(\'.cmenu'.$k.'\').hide(\'slow\'); menu'.$k.'++;">
					'.$v.'
					</span>
					</td>
					</tr>
					<tr><td><table class="cmenu'.$k.'" style="display:none">';
					while($ro=mysql_fetch_array($rs)) {
						
						IF($ro['link'] == "monitoreo.php"){
							
							echo "<tr><td><a href='{$ro['link']}' >-{$ro['nombre']}</a></td></tr>";
							
							
						}
						else{
							echo '
							<tr><td><a href="#" onClick="
							objeto=crearObjeto();
							if (objeto.readyState != 0) {
							alert(\'Error: El Navegador no soporta AJAX\');
							} else {
							objeto.open(\'POST\',\'inicio.php\',true);
							objeto.setRequestHeader(\'Content-Type\',\'application/x-www-form-urlencoded\');
							objeto.send(\'ajax=99&usuario='.$_POST['cveusuario'].'&cvereg='.$_POST['cveregistro'].'&idmenu='.$ro['cve'].'\');
							objeto.onreadystatechange = function()
							{
							if (objeto.readyState==4)
							{document.forma.cvemenu.value='.$ro['cve'].';atcr(\''.$ro['link'].'\',\''.$ro['target'].'\',\'0\',\'\');}
							}
							}
							">-'.$ro['nombre'].'</a></td></tr>';
							if($_POST['cvemenu']==$ro['cve'])
							$mostrar='cmenu'.$k;
						}
					}
					echo '</table></td></tr>';
				}
			}
		}
		
		echo '</table>';
		if($mostrar!='') {
			echo '<script language="javascript">$(\'.'.$mostrar.'\').show();'.substr($mostrar,1).'++;</script>';
		}
	}
	
	function menunavegacion() {
		
		
		
		global $totalRegistros, $eTotalPaginas, $eNumeroPagina, $primerRegistro, $eAnteriorPagina, $eSiguientePagina, $eNumeroPagina;
		
		
		
		echo '
		
		
		
		<table width="100%" height="20" border="0" cellpadding="0" cellspacing="0">
		
		<tr>
		
		<td width="20%" class="">'.$totalRegistros.'</font> Registro(s)</td>';
		
		if ($eTotalPaginas>0) {
			
			echo '
			
			<td width="60%" class="" align="right">P&aacute;gina <font class="fntN10B">';print $eNumeroPagina+1; echo'</font> de <font class="fntN10B">'; print $eTotalPaginas+1; echo'</font> </td>';
			
			if ($primerRegistro>0) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina(0);"><img src="images/mover-primero.gif" width="10" height="12" border="0" align="absmiddle" title="Inicio"></a> </td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-primero-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eAnteriorPagina>=0) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eAnteriorPagina.');"><img src="images/mover-anterior.gif" width="7" height="12" border="0" align="absmiddle" title="Anterior"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-anterior-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eSiguientePagina<=$eTotalPaginas) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><a href="JavaScript:moverPagina('.$eSiguientePagina.');"><img src="images/mover-siguiente.gif" width="7" height="12" border="0" align="absmiddle" title="Siguiente"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-siguiente-d.gif" width="7" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
			if ($eNumeroPagina<$eTotalPaginas) {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"> <a href="JavaScript:moverPagina('.$eTotalPaginas.');"><img src="images/mover-ultimo.gif" width="10" height="12" border="0" align="absmiddle" title="Fin"></a></td>';
				
				} else {
				
				echo '
				
				<td width="12" align="center" class="sanLR10"><img src="images/mover-ultimo-d.gif" width="10" height="12" border="0" align="absmiddle"></td>';
				
			}
			
			
			
		}
		
		echo '
		
		</tr>
		
		</table>';
		
		
		
	}
	
	
	
	
	
	function menu() {
		
		echo '';
		
	}
	
	
	
	// Renglon en fondo Blanco
	
	function rowc() {
		
		echo '<tr bgcolor="#ffffff" onmouseover="sc(this, 1, 0);" onmouseout="sc(this, 0, 0);" onmousedown="sc(this, 2, 0);">';
		
	}
	
	
	
	// Renglones que cambian el color de fondo
	
	function rowb($imprimir = true, $style='', $class='') {
		
		static $rc;
		$regresar = '';
		if ($rc) {
			if($imprimir)
			echo '<tr bgcolor="#d5d5d5" '.$style.' '.$class.' onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);" onmousedown="sc(this, 2, 1);">';
			else
			$regresar = '<tr bgcolor="#d5d5d5" onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);" onmousedown="sc(this, 2, 1);">';
			$rc=FALSE;
			
		}
		
		else {
			if($imprimir)
			echo '<tr bgcolor="#e5e5e5" onmouseover="sc(this, 1, 2);" onmouseout="sc(this, 0, 2);" onmousedown="sc(this, 2, 2);">';
			else
			$regresar= '<tr bgcolor="#e5e5e5" onmouseover="sc(this, 1, 2);" onmouseout="sc(this, 0, 2);" onmousedown="sc(this, 2, 2);">';
			
			$rc=TRUE;
			
		}
		if(!$imprimir)
		return $regresar;
		
	}
	
	
	
	
	
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
		
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		
		
		
		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
		
		
		
		switch ($theType) {
			
			case "text":
			
			$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			
			break;    
			
			case "long":
			
			case "int":
			
			$theValue = ($theValue != "") ? intval($theValue) : "NULL";
			
			break;
			
			case "double":
			
			$theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
			
			break;
			
			case "date":
			
			$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			
			break;
			
			case "defined":
			
			$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
			
			break;
			
		}
		
		return $theValue;
		
	}
	
	
	
	
	
	function diaSemana($fecha) {
		
		$weekDay=array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO');
		
		$ano=substr($fecha,0,4);
		
		$mes=substr($fecha,5,2);
		
		$dia=substr($fecha,8,2);
		
		$numDia=jddayofweek ( cal_to_jd(CAL_GREGORIAN, date($mes),date($dia), date($ano)) , 0 );
		
		$result=$weekDay[$numDia];
		
		return $result;
		
	}
	
	
	
	function horaLocal() {
		
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		$hora= date("H:i:s", $new_U);
		
		$hora=date( "Y-m-d H:i:s" , strtotime ( "-5 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$hora=date( "H:i:s" , strtotime ( "0 minute" , strtotime($hora) ) );
		
		return $hora;
		
		//Regards. Mohammed Ahmad. MSN: m@maaking.com
		
	}
	
	function fechaLocal(){
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		//$fecha= date("Y-m-d", $new_U);
		
		$fecha=date( "Y-m-d H:i:s" , strtotime ( "-5 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fecha=date( "Y-m-d" , strtotime ( "0 minute" , strtotime($fecha) ) );
		
		return $fecha;
	}
	
	function fechahoraLocal(){
		$differencetolocaltime=1;
		
		$new_U=date("U")+$differencetolocaltime*3600;
		
		//$fulllocaldatetime= date("d-m-Y h:i:s A", $new_U);
		
		$//fechahora= date("Y-m-d H:i:s", $new_U);
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "-5 hour" , strtotime(date("Y-m-d H:i:s")) ) );
		
		$fechahora=date( "Y-m-d H:i:s" , strtotime ( "0 minute" , strtotime($fechahora) ) );
		
		return $fechahora;
	}
	
	function fecha_letra($fecha){
		$fecven=split("-",$fecha);
		$fecha_letra=$fecven[2]." de ";;
		switch($fecven[1]){
			case "01":$fecha_letra.="Enero";break;
			case "02":$fecha_letra.="Febrero";break;
			case "03":$fecha_letra.="Marzo";break;
			case "04":$fecha_letra.="Abril";break;
			case "05":$fecha_letra.="Mayo";break;
			case "06":$fecha_letra.="Junio";break;
			case "07":$fecha_letra.="Julio";break;
			case "08":$fecha_letra.="Agosto";break;
			case "09":$fecha_letra.="Septiembre";break;
			case "10":$fecha_letra.="Octubre";break;
			case "11":$fecha_letra.="Noviembre";break;
			case "12":$fecha_letra.="Diciembre";break;
		}
		$fecha_letra.=" del ".$fecven[0]."";
		return $fecha_letra;
	}
	
	function fechaNormal($fecha){
		$arrFecha=explode("-",$fecha);
		return $arrFecha[2].'/'.$arrFecha[1].'/'.$arrFecha[0];
	}
	
	function traer_numero_semana($fechasem){
		global $base;
		$anio=substr($fechasem,0,4);
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		if($fechasem<$fecha){
			$anio--;
			$fecha=$anio.'-01-01';
			$arfecha=explode("-",$fecha);
			$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
			if($dia!=1){
				$dias=8-$dia;
				$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
			}
			$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		}
		$res=mysql_query("SELECT TO_DAYS('$fechasem')-TO_DAYS('$fecha')");
		$row=mysql_fetch_array($res);
		$semana=intval($row[0]/7)+1;
		return $semana;
	}
	
	function traer_fechas_semana($semana,$anio){
		$fecha=$anio.'-01-01';
		$arfecha=explode("-",$fecha);
		$dia=date("w", mktime(0, 0, 0, intval($arfecha[1]), intval($arfecha[2]), $arfecha[0]));
		if($dia!=1){
			$dias=8-$dia;
			$fecha=date( "Y-m-d" , strtotime ( "+".$dias." day" , strtotime($fecha) ) );
		}
		$fecha=date( "Y-m-d" , strtotime ( "+2 day" , strtotime($fecha) ) );
		$fecha_ini=date( "Y-m-d" , strtotime ( "+".(($semana-1)*7)." day" , strtotime($fecha) ) );
		$fecha_fin=date( "Y-m-d" , strtotime ( "+6 day" , strtotime($fecha_ini) ) );
		return $fecha_ini.' - '.$fecha_fin;
	}
	
	function calculaDias($fecha1,$fecha2){
		global $base;
		$rs=mysql_db_query($base,"SELECT to_days('$fecha2')-to_days('$fecha1')");
		$ro=mysql_fetch_array($rs);
		return $ro[0]+1;
	}
	
	
	
	function edad($rfc){
		$anio=intval("19".substr($rfc,4,2));
		$mes=intval(substr($rfc,6,2));
		$dia=intval(substr($rfc,8,2));
		
		$anio2=intval(substr(fechaLocal(),0,4));
		$mes2=intval(substr(fechaLocal(),5,2));
		$dia2=intval(substr(fechaLocal(),8,2));
		
		$edad=$anio2-$anio;
		if($mes2<$mes){
			$edad--;
		}
		elseif($mes2==$mes){
			if($dia2<$dia){
				$edad--;
			}
		}
		return $edad;
	}
	
	function antiguedad($fecha_inicio){
		global $base;
		$res=mysql_query("SELECT DATEDIFF(CURDATE(),'$fecha_inicio') as dias");
		$row=mysql_fetch_array($res);
		$semanas = intval($row['dias']/7);
		return $semanas;
	}
	
	function calcular_imss($salario_integrado){
		global $base, $_POST, $cveempresanomina;
		$res = mysql_query("SELECT primariesgo FROM empresas WHERE cve = '".$cveempresanomina."'");
		$Empresa = mysql_fetch_array($res);
		$res = mysql_query("SELECT * FROM impuestos_imss ORDER BY cve DESC LIMIT 1");
		$row = mysql_fetch_array($res);
		$art106ii = ($salario_integrado - ($row['smdf']*3)) * $row['porc106II'] / 100;
		if($art106ii < 0) $art106ii = 0;
		$art25 = $salario_integrado * $row['porc25'] / 100;
		
		$art107i_ii = $salario_integrado * $row['porc107I_II'] / 100;
		
		$art147 = $salario_integrado * $row['porc147'] / 100;
		
		$art168ii = $salario_integrado * $row['porc168II'] / 100;
		
		$imss = $art106ii + $art25 + $art107i_ii + $art147 + $art168ii;
		mysql_select_db($base);
		return round($imss,2);
	}
	
	function asistencia($personal, $tipo, $fecha_ini, $fecha_fin){
		global $base;
		$res = mysql_query("SELECT COUNT(*) as dias_alta, SUM(IF(estatus=1,1,0)) as trabajados, SUM(IF(estatus=0,1,0)) as faltas 
		FROM asistencia WHERE personal = '$personal' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
		$row = mysql_fetch_array($res);
		return $row[$tipo];
	}
	
	function dias_vacaciones($personal, $tipo, $fecha_alta, $fecha_baja, $periodo = 0){
		global $base;
		$resPersonal=mysql_query("SELECT * FROM personal WHERE cve='".$personal."'");
		$Personal=mysql_fetch_array($resPersonal);
		$fecha_ini = explode("-",$fecha_alta);
		$fecha_fin = explode("-",$fecha_baja);
		$anios = $fecha_fin[0]-$fecha_ini[0];
		if($anios>0){
			if(($fecha_fin[1].'-'.$fecha_fin[2])<($fecha_ini[1]."-".$fecha_ini[2])){
				$anios--;
			}
		}
		if($tipo==1){//FINIQUITO
			if($anios==0){
				$resDias = mysql_query("SELECT * FROM dias_vacaciones WHERE cve=1");
				$rowDias = mysql_fetch_array($resDias);
				$dias = $rowDias['anteriores'];
				$resDiasT = mysql_query("SELECT DATEDIFF('".$fecha_baja."','".$fecha_alta."')");
				$rowDiasT = mysql_fetch_array($resDiasT);
				$dias_transcurridos = $rowDiasT[0];
				$dias_pagar = round($dias * $dias_transcurridos / 365,2);
			}
			else{
				$anios2=$anios;
				if($anios>34) $anios2=34;
				$resDias = mysql_query("SELECT * FROM dias_vacaciones WHERE cve=$anios2");
				$rowDias = mysql_fetch_array($resDias);
				if($periodo == 0){
					$dias = $rowDias['actuales'];
					$fecha_anio_anterior = $fecha_fin[0].'-'.$fecha_ini[1].'-'.$fecha_ini[2];
					if($fecha_baja<$fecha_anio_anterior)
					$fecha_anio_anterior = ($fecha_fin[0]-1).'-'.$fecha_ini[1].'-'.$fecha_ini[2];
					$resDiasT = mysql_query("SELECT (DATEDIFF('".$fecha_baja."','".$fecha_anio_anterior."')+1)");
					$rowDiasT = mysql_fetch_array($resDiasT);
					$dias_transcurridos = $rowDiasT[0];
					$dias_pagar = round($dias * $dias_transcurridos / 365,2);
				}
				else{
					$dias = $rowDias['anteriores'];
					$dias_pagar = $dias;
				}
			}
		}
		return array('anios'=>$anios, 'dias' => $dias, 'dias_pagar' => $dias_pagar);
		
	}
	
	function datos_correctos_timbre($personal){
		global $base;
		$res = mysql_query("SELECT * FROM personal WHERE cve='$personal'");
		$row = mysql_fetch_array($res);
		
		if($row['fecha_imss']<='0000-00-00' || trim($row['imss'])=='' || strlen(trim($row['rfc']))!=13 || trim($row['curp'])=='')
		return false;
		else
		return true;
		
	}
	function saldo_unidad($unidad, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base,$pre;
		$cargo = 0;
		$abono = 0;
		$filtro=$filtro1="";
		if($tipo == 0){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 1){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad' AND fecha<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion < '$fecha_ini' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C' AND fecha_aplicacion<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C' AND fecha < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C' AND fecha < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha < '$fecha_ini'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C' AND fecha<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha_aplicacion<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C' AND fecha_aplicacion<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C' AND fecha<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C' AND fecha_aplicacion<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 2){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($dato==0) $monto = $abono-$cargo;
		elseif($dato==1) $monto = $cargo;
		else $monto = $abono;
		return $monto;
	}
	
	function saldo_unidad_2($unidad, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base,$pre;
		$cargo = 0;
		$abono = 0;
		$filtro=$filtro1="";
		if($tipo == 0){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 1){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad' AND LEFT(fecha,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.LEFT(fecha_aplicacion,7) < '$fecha_ini' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C' AND LEFT(fecha,7) < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) < '$fecha_ini'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7)<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 2){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(cargo) FROM cargos_unidades WHERE unidad = '$unidad' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(b.monto) FROM vale_utilidad a INNER JOIN vale_utilidad_detalle b ON a.cve = b.vale WHERE a.estatus != 'C' AND a.LEFT(fecha_aplicacion,7) BETWEEN '$fecha_ini' AND '$fecha_fin' AND b.unidad = '$unidad'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_origen = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(total) FROM cargos_variables_unidadeszitla WHERE unidad = '$unidad' AND sta!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_operador WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
				/*$res = mysql_query("SELECT SUM(monto) FROM condonacion_tarjetas_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
					$row = mysql_fetch_array($res);
				$cargo -= $row[0];*/
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto+abono_adeudo_operador) FROM recaudacion_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(abono_adeudo_operador) FROM recaudacion_operador WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM abono_general_unidad WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM traspaso_unidades WHERE unidad_destino = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_autobus WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE unidad = '$unidad' AND estatus!='C' AND LEFT(fecha_aplicacion,7) BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($dato==0) $monto = $abono-$cargo;
		elseif($dato==1) $monto = $cargo;
		else $monto = $abono;
		return $monto;
	}
	
	function saldo_operador($operador, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base,$pre;
		$cargo = 0;
		$abono = 0;
		$filtro=$filtro1="";
		if($tipo == 0){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_cargo WHERE operador = '$operador' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE operador = '$operador' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 1){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_cargo WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 2){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_cargo WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM deuda_operador_abono WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($dato==0) $monto = $abono-$cargo;
		elseif($dato==1) $monto = $cargo;
		else $monto = $abono;
		return $monto;
	}
	
	function saldo_operador_fianza($operador, $tipo = 0, $dato = 0, $fecha_ini = "", $fecha_fin = ""){
		global $base,$pre;
		$cargo = 0;
		$abono = 0;
		$filtro=$filtro1="";
		if($tipo == 0){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_fianza WHERE operador = '$operador' AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_operador WHERE operador = '$operador' AND cargo=5 AND estatus!='C'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 1){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_fianza WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion < '$fecha_ini'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_operador WHERE operador = '$operador' AND cargo=5 AND estatus!='C' AND fecha<'$fecha_ini'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($tipo == 2){
			if($dato == 0 || $dato == 1){
				$res = mysql_query("SELECT SUM(monto) FROM devolucion_fianza WHERE operador = '$operador' AND estatus!='C' AND fecha_aplicacion BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$cargo += $row[0];
			}
			if($dato == 0 || $dato == 2){
				$res = mysql_query("SELECT SUM(monto) FROM recaudacion_operador WHERE operador = '$operador' AND cargo=5 AND estatus!='C' AND fecha BETWEEN '$fecha_ini' AND '$fecha_fin'");
				$row = mysql_fetch_array($res);
				$abono += $row[0];
			}
		}
		if($dato==0) $monto = $abono-$cargo;
		elseif($dato==1) $monto = $cargo;
		else $monto = $abono;
		return $monto;
	}
	
?>
