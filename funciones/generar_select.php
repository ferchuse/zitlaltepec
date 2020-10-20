<?php
	session_start();
	// include("../conexi.php");
	
	// $link = Conectarse();
	
	function generar_select($link, $tabla, $llave_primaria, $campo_etiqueta ,$filtro = false, $disabled = false ,$required = false , $id_selected = 0, $data_indice = 0, $name = "", $id = ''  ){
		$consulta = "SELECT * FROM $tabla  ORDER BY $campo_etiqueta";
		
		if($name == ""){
			$name = $llave_primaria;
		}
		if($id == ""){
			$id = $llave_primaria;
		}
		
		
		$select = "<select data-indice='$data_indice'";
		
		$select .= $required ? " required " : " ";
		$select .= $disabled ? " disabled " : " ";
		$select.= "class='form-control' name='$name' id='$id' >";
		if($filtro){
			$select .= "<option value=''>Todos</option>";
		} 
		else{
			$select .= "<option value=''>Seleccione...</option>";
		}
		
		$result = mysqli_query($link, $consulta);
		
		while($fila = mysqli_fetch_assoc($result)){
			$select.="<option value='".$fila[$llave_primaria]."'";
			$select.=$fila[$llave_primaria] == $id_selected ? " selected" : "" ;
			if($tabla = "taquillas"){
				
				$select .= " data-hora_salida='".date("H:i", strtotime($fila["hora_salida"]))."' ";
			}
			
			$select.=" >".$fila[$campo_etiqueta] ."</option>";
			
		}
		$select.="</select>";
		
		return $select;
	}
	
?>