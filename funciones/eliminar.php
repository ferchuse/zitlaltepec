<?php 
    include('../conexi.php');
    $link = Conectarse();

    $respuesta = array();

    $tabla = $_POST['tabla'];
    $id_campo = $_POST['id_campo'];
    $campo = $_POST['campo'];

    $eliminar = "DELETE FROM $tabla WHERE $campo = $id_campo";

    $query = mysqli_query($link,$eliminar);

    if($query){
        $respuesta['estatus'] = "success";
        $respuesta['mensaje'] = "Eliminado";
    }else{
        $respuesta['estatus'] = "error";
        $respuesta['mensaje'] = "Error al eliminar $eliminar ".mysqli_error($link);
    }

    echo json_encode($respuesta);

?>