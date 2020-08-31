<?php 

include ("main.php"); 
if($_POST['ajax']==99){
	mysql_query("INSERT registros_sistemamov SET cveacceso='".$_POST['cvereg']."',usuario='".$_POST['usuario']."',menu='".$_POST['idmenu']."',fechahora='".fechaLocal()." ".horaLocal()."'");
	exit();
}
if(!$_POST['plazausuario'] && count($array_plaza) == 1){
	foreach($array_plaza as $k=>$v)
		$_POST['plazausuario']=$k;
}
if($_POST['cmd']=='cambiarplaza'){
	$_POST['plazausuario']=0;
}


top($_SESSION);
	
echo '<h1><font color="BLACK">Bienvenidos</font></h1>';

bottom(); 
 
?>

