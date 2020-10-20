<?php
	
	
	function dame_permiso($url_paginas,$link){
		// $respuesta = "Sin Acceso";
		// return false;
		$consulta = "SELECT * FROM usuarios 
		LEFT JOIN usuario_accesos ON usuario_accesos.usuario = usuarios.cve
		LEFT JOIN menu ON usuario_accesos.menu = menu.cve 
		WHERE link = '$url_paginas' 
		AND usuarios.cve = {$_SESSION["CveUsuario"]}";
		
		
		$result = mysqli_query($link, $consulta) or die("Error dame_permiso($consulta) ". mysqli_error($link));
		
		if(mysqli_num_rows($result) > 0){
			while($fila = mysqli_fetch_assoc($result)){
				
				$respuesta= $fila["permiso"];
			}
			
			if($respuesta == "0" || $respuesta == "" ){
				return "hidden"; 
			}
			else{
				return $respuesta;
			}
			
			
		}
		else{
			return "hidden"; 
			return false;//"Pagina no existe, $url_paginas,{$_SESSION["id_usuarios"]}, $consulta";
		}
		
	}
	
?>