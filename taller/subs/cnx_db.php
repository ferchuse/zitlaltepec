<?php

//Conexion con la base
if (!$MySQL=@mysql_connect('localhost', 'road', 'bAllenA6##6')) {
	$t=time();
	while (time()<$t+5) {}
	if (!$MySQL=@mysql_connect('localhost', 'road', 'bAllenA6##6')) {
		$t=time();
		while (time()<$t+10) {}
		if (!$MySQL=@mysql_connect('localhost', 'road', 'bAllenA6##6')) {
		echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
		echo '<h4>Por favor intente mas tarde.-</h4>';
		exit;
		}
	}
}
$base='road_gps';
mysql_select_db($base);



?>