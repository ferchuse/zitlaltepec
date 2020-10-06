<?php
	
	//Conexion con la base
	
	if($_SERVER["SERVER_NAME"] == "localhost" ){
		
		
		$base = "zitlaltepec";
		if (!$MySQL=@mysql_connect('localhost', 'sistemas', 'Glifom3dia')) {
			$t=time();
			while (time()<$t+5) {}
			if (!$MySQL=@mysql_connect('localhost', 'sistemas', 'Glifom3dia')) {
				$t=time();
				while (time()<$t+10) {}
				if (!$MySQL=@mysql_connect('localhost', 'sistemas', 'Glifom3dia')) {
					echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
					echo '<h4>Por favor intente mas tarde.-</h4>';
					exit;
				}
			}
		}
	}
	
	if($_SERVER["SERVER_NAME"] == "pruebas.grupozitlaltepec.com.mx" ){
		
		
		$base = "rhgaazco_pruebas_zit";
		if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
			$t=time();
			while (time()<$t+5) {}
			if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
				$t=time();
				while (time()<$t+10) {}
				if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
					echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
					echo '<h4>Por favor intente mas tarde.-</h4>';
					exit;
				}
			}
		}
	}
	
	if($_SERVER["SERVER_NAME"] == "grupozitlaltepec.com.mx" ){
		$base = "rhgaazco_zitlaltepec";
		if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
			$t=time();
			while (time()<$t+5) {}
			if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
				$t=time();
				while (time()<$t+10) {}
				if (!$MySQL=@mysql_connect('localhost', 'rhgaazco_zitlalte', 'Zitla@2020')) {
					echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
					echo '<h4>Por favor intente mas tarde.-</h4>';
					exit;
				}
			}
		}
		
	}
	
	
		// if($_SERVER["SERVER_NAME"] != "localhost") {
			mysql_query( "SET NAMES 'utf8'") or die("Error Cambiando charset").mysql_error();
			
			// mysqli_query($link, "SET sql_mode = ''") or die("Error Cambiando sqlmode").mysqli_error($link);
			
			// mysqli_query($link, "SET SESSION sql_mode = ''") or die("Error Cambiando charset").mysqli_error($link);
			
			mysql_query("SET CHARACTER SET utf8") or die("Error en charset UTF8".mysql_error());
		// }
		
	
	mysql_select_db($base);
?>
