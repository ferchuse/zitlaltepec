<?php
set_time_limit(0);
require_once("nusoap/nusoap.php");
$base='enero_aaz';
$namespace = "http://mdz.mx/sincronizarservices";
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
                array('tabla'=>'xsd:string'), 
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
				
function ReadTable($tabla){
	global $base;
	$respuesta['resultado']=false;
	$respuesta['mensaje']='';
	$strcnn=ConectarDB();
	if($strcnn!="OK")
		$respuesta['mensaje']=$strcnn;
	if($respuesta['mensaje']==''){
		//Tomar la informacion de la tabla
		$atablas=array('mdz_taquilleros'=>'cve,nombre,usuario,pass,1 as tipo','mdz2_parque'=>'cve,empresa,no_eco','mdz_precios'=>'cve,nombre,precio,estatus','mdz_empresas'=>'*','mdz_revendedores'=>'cve,nombre,estatus,rfc,curp');
		if(array_key_exists($tabla,$atablas)){
			$strdata='';
			$query="Select {$atablas[$tabla]} from $tabla";
			try{
				$rs = mysql_query($query);
				while($row=mysql_fetch_array($rs)){
					$strvalores='';
					foreach($row as $key=>$val){
						if(!is_numeric($key)){
							if($strvalores!='')
								$strvalores.=',';
							$strvalores.="$key='$val'";
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
		//Eliminar los registros de la taquilla y periodo
		$query="Delete from mdz2_boletos where taq='$taquilla' and fecha between '$fechainicial' And '$fechafinal'";
		//mysql_query($query);
		//Insertar los registros actuales
		$strData=base64_decode($tickets);
		$vecData=explode("\n", $strData);
		foreach($vecData As $query){
			mysql_query($query);
		}
		$respuesta['resultado']=true;
	}
	return $respuesta;	
} 
function ConectarDB(){
	$msg="OK";
	//Conexion con la base
	if (!$MySQL=@mysql_connect('localhost', 'road', 'TKU2m3JkNpYJefBP')) {
		$t=time();
		while (time()<$t+5) {}
		if (!$MySQL=@mysql_connect('localhost', 'road', 'TKU2m3JkNpYJefBP')) {
			$t=time();
			while (time()<$t+10) {}
			if (!$MySQL=@mysql_connect('localhost', 'road', 'TKU2m3JkNpYJefBP')) {
			echo '<br><br><br><h3 align=center">Hay problemas de comunicaci&oacute;n con la Base de datos.</h3>';
			echo '<h4>Por favor intente mas tarde.-</h4>';
			exit;
			}
		}
	}
	mysql_select_db("enero_aaz");
	return $msg;
}
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);

?>
