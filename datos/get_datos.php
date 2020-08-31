<?php
require_once('/var/www/road/public_html/subs/cnx_db.php');


//require_once('../subs/cnx_db.php');
//include("../parametros-navegacion.php");

$variable = file_get_contents('http://104.192.6.151/semovi/api/getWSR7.php');
$variables = json_decode($variable, true);

$dato="";
$datos=array();
$datoss=array();
$x=0;
$dat="";
foreach($variables as $k=>$v){
	foreach($v as $k1=>$v1){
		if($x>0){$dat=",";}
	//	echo $k.'++'.$k1.'=';
	$dato.=$dat.$k1."="; $datoss[$k][$k1]="";
		foreach($v1 as $k2=>$v2){
		//echo''.$k1[0].' - '.$k1.'  </br>';
	//	echo'  '.$v2.''; 
	$dato.=$v2; $datoss[$k][$k1].= $v2;
		}
		if($k1=="_id"){$v1="";}
	//	echo'  '.$v1.'</br>';
	$dato.=$v1; $datoss[$k][$k1].=$v1;
	$x++;
	}	
}

//echo'</br></br>';



foreach($datoss as $k=>$v){
	$campo="";
	$valor="";
	$y=0;
	$dat="";
	foreach($v as $k1=>$v1){
		if($y>0){$dat=",";}
//		echo ''.$k.''.$k1.'=='.$v1.'</br>';
//		$re="INSERT INTO datos (".$k1.") values('".$v1."') ";
//		echo $re;`s
//		mysql_query($re) or die (mysql_error());
		$campo.=$dat.$k1;
		$valor.=$dat."'".$v1."'";
			$y=$y+1;
//	$x=++;
	}
	$re="INSERT INTO datos (".$campo.") values(".$valor.") ";
		//echo $re;
		mysql_query($re) or die (mysql_error());
}

//echo $campo;
//echo '</br>';
//echo $valor;
//print_r($datos);

//print_r($variables[1])


?>