<?php 
require_once('/var/www/road/public_html/subs/cnx_db.php');

/**$fecha1=$_POST['fecha_ini'];
$fecha2=$_POST['fecha_fin'];
$fecha1="2020-02-19";
$fecha2="2020-02-25";*/

$fecha11=date("Y-m-d");
$fecha1= date("Y-m-d",strtotime($fecha11."- 1 days"));

//echo $fecha1;

$url = 'http://62.151.178.53:12056/api/v1/basic/alarm/detail';

//create a new cURL resource
$ch = curl_init($url);
$body = array(
'key' => 'zT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3DzT908g2j9njBdXcJpcq4BJqnWtIaiiF08Nr%2FDHMVS1QAcxuiP4PcBQ%3D%3D',
    'terid' => array("0060036CCE"),
    'type' => array(18),
    'starttime' => "$fecha1",
    'endtime' => "$fecha1",
 );
 /*    'starttime' => "$fecha1",
    'endtime' => "$fecha2",*/
$payload = json_encode($body);

//attach encoded JSON string to the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

//set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

//return response instead of outputting
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//execute the POST request
$result = curl_exec($ch);

//close cURL resource
curl_close($ch);

$variables = json_decode($result, true);

$dato="";
$datos=array();
$datoss=array();
$x=0;
$dat="";
foreach($variables as $k=>$v){
 	 ///////echo $k1.' -- '.$v1.'</br>';
	foreach($v as $k1=>$v1){
	 //echo $k1.' ++ ';
	  $datos="";
	  $x=0;
	  foreach($v1 as $k2=>$v2){
		  //echo $k2.' -- '.$x.'</br>';
		  if($x>0){$datos.=",";}
		  $datos.="".$k2."='".$v2."'";	
          
		$x++;	
	  }
	  
	  		  $re="INSERT datos_alarm SET ".$datos."";
			 // echo $re;
	   		 mysql_query($re) or die (mysql_error());
	}
}

?>

