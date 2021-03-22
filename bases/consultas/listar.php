<?php 
		session_start();
    include('../../../conexi.php');
    $link = Conectarse();
    $lista = array();
    $respuesta = array();
    $tabla = $_POST['tabla'];
        
    
    if(isset($_POST['id_campo'])){
        $campo = $_POST['campo'];
        $id_campo = $_POST['id_campo'];
        $consulta = "SELECT * FROM $tabla WHERE $campo=$id_campo";
				$consulta = " AND id_administrador = {$_COOKIE["id_administrador"]}";
    }elseif(isset($_POST['subconsulta'])){
        $subconsulta = $_POST['subconsulta'];
        $consulta = "SELECT * FROM $tabla $subconsulta";
    }elseif(isset($_POST['id_campo']) && isset($_POST['subconsulta'])){
        $campo = $_POST['campo'];
        $id_campo = $_POST['id_campo'];
        $subconsulta = $_POST['subconsulta'];
        "SELECT * FROM $tabla $subconsulta WHERE $campo=$id_campo";
    }else{
        $consulta = "SELECT * FROM $tabla";
					$consulta = " AND id_administrador = {$_COOKIE["id_administrador"]}";
    }
	
		
		
    $query = mysqli_query($link,$consulta);
    if($query){
        $respuesta['num_rows'] = mysqli_num_rows($query);
            while($row = mysqli_fetch_assoc($query)){
                $lista[] = $row;
            }
            $respuesta['estatus'] = 'success';
            $respuesta['mensaje'] = $lista;
            $respuesta['query'] = $consulta;
    }else {
        $respuesta["estatus"] = "error";
        $respuesta["mensaje"] = "Error ".mysqli_error($link);
				$respuesta['query'] = $consulta;
    }

    echo json_encode($respuesta);
?>