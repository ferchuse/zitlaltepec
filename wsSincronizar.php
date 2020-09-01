<?php
set_time_limit(0);
require_once("nusoap/nusoap.php");
$base='road';
$namespace = "http://road.checame.net/sincronizarservices";
// create a new soap server
$server = new soap_server();
// configure our WSDL
$server->configureWSDL("wssincronizar");
// set our namespace
$server->wsdl->schemaTargetNamespace = $namespace;
//Definimos la estructura de la Respuesta
$server->wsdl->addComplexType(
    'Response',
    'complexType',
    'struct',
    'all',
    '',
    array(
		'resultado'           => array('name'=>'resultado',          'type'=>'xsd:boolean'),
		'mensaje'             => array('name'=>'mensaje',            'type'=>'xsd:string')
	)
);

// registar WebMethod para leer informacion de una tabla
$server->register(
                // nombre del metodo
                'ReadTable',
                // lista de parametros
                array('tabla'=>'xsd:string','taquilla'=>'xsd:integer'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Leer informacion de una tabla');

// registrar WebMethod para actualizar los Boletos
$server->register(
                // nombre del metodo
                'UpdateTickets', 		 
                // lista de parametros
                array('taquilla'=>'xsd:integer','fechainicial'=>'xsd:string','fechafinal'=>'xsd:string','tickets'=>'xsd:string'), 
                // valores de return
                array('return'=>'tns:Response'),
                // namespace
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // descripcion: documentacion del metodo
                'Actualizar tabla de boletos');
				
function ReadTable($tabla, $taquilla=0){
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']==''){
		//Tomar la informacion de la tabla
		$atablas=array('costo_boletos'=>'*','unidades'=>'cve,no_eco,estatus','taquillas'=>'*','usuarios'=>'cve,nombre,usuario,password,tipo_taquilla,estatus', 'costo_boletos_sencillos'=>'*', 'taquillas_sencillos' => '*');
		if(array_key_exists($tabla,$atablas)){
			$strdata='';
			$query="Select {$atablas[$tabla]} from $tabla";
			if($tabla=="taquillas" || $tabla=='taquillas_sencillos') $query.=" where cve=".$taquilla;
			try{
				$rs = mysql_query($query);
				while($row=mysql_fetch_array($rs)){
					$strvalores='';
					foreach($row as $key=>$val){
						if(!is_numeric($key)){
							if($strvalores!='')
								$strvalores.=',';
							$strvalores.="$key='".addslashes($val)."'";
						}
					}
					$strdata.="$strvalores;\n";
				}
				$respuesta['mensaje']=base64_encode($strdata);
				$respuesta['resultado']=true;
			}
			catch(Exception $e){
				$respuesta['mensaje']="Exepcion:".$e->getCode()." ".$e->getMessage();
			}
		}
		else{
			$respuesta['mensaje']="No se ha Configurado la tabla:$tabla, para sincronizar";
		}
	}
	return $respuesta;
}
function UpdateTickets($taquilla,$fechainicial,$fechafinal,$tickets){
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']==''){
		$strData=base64_decode($tickets);
		if ($strData[0] == '['){
			$vecData = json_decode($strData, true);
			$resultado = array();
			foreach($vecData As $dato){
				if($res = mysql_query($dato['query'])){
					$resultado[] = array('folio' => $dato['folio']);
				}
			}
			$respuesta['mensaje'] = json_encode($resultado);
		}
		else{
			$vecData=explode("\n", $strData);
			foreach($vecData As $query){
				mysql_query($query);
			}
		}
		$respuesta['resultado']=true;
	}
	return $respuesta;	
} 
function ConectarDB(){
	$msg="OK";
	//Conexion con la base
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
	mysql_select_db("rhgaazco_zitlalte");
	return $msg;
}
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);

?>
