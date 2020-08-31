<?php
$respuesta = array();
$pdo = null;

function getConexion($dbGps){
	global $respuesta;
	$hostDb = 'localhost';
	$usuarioDb = 'gps';
	$passwordDb = 'ballena';
	$dsn = "mysql:host=$hostDb;dbname=$dbGps;charset=utf8;";
	$parametros = array(
    	PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
	);

	try{
    	$pdo = new PDO($dsn, $usuarioDb, $passwordDb, $parametros);
	}catch(PDOException $e){
		$respuesta['validado'] = false;
		$respuesta['mensaje'] = 'Problemas al conectar con la base de datos';
		echo(json_encode($respuesta));
	  exit(0);
	}

	return $pdo;
}

define('GPS_DB', 'gps');
define('GPS_SKYMEDIA_DB', 'gps_skymedia');

define('OPC', $_POST['opc']);
define('LOGIN', 1);
define('DESCARGAR_PLACAS', 2);
define('GUARDAR_MENSAJE', 3);

switch(OPC){
	case LOGIN;
		$pdo = getConexion(GPS_DB);
		break;

	case DESCARGAR_PLACAS:
	case GUARDAR_MENSAJE:
		$pdo = getConexion(GPS_SKYMEDIA_DB);
		break;
}

try{
	if(OPC == LOGIN){
		$query = 'select * from usuarios where usuario = :user and password = :pass';
		$stmt = $pdo->prepare($query);
		$stmt->bindParam(':user', $_POST['user'], PDO::PARAM_STR);
		$stmt->bindParam(':pass', $_POST['pass'], PDO::PARAM_STR);
		$stmt->execute();

		if(!$result = $stmt->fetch(PDO::FETCH_ASSOC))
			throw new Exception('El usuario no existe o los datos son incorrectos');

		if($result['estatus'] != 'A')
			throw new Exception('El usuario no esta activo');

		$respuesta['validado'] = true;
		$respuesta['usuario'] = $result;
	}

	if(OPC == DESCARGAR_PLACAS){
		$query = 'select * from gps_objects where 1';
		$imei = '';
		
		if($_POST['imei'] != ''){
			$imei = "%{$_POST['imei']}%";
			$query .= ' and imei like :imei';
		}

		$query .= ' order by imei';
		$stmt = $pdo->prepare($query);

		if($imei != '')
			$stmt->bindParam(':imei', $imei, PDO::PARAM_STR);
		
		$stmt->execute();
		$placas = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$respuesta['placas'] = array();
		$respuesta['validado'] = true;
		
		foreach($placas as $placa){
			$query = "select c.* from comandos as c
						inner join comandosxplaca as cp on c.id = cp.idComando
						where cp.idPlaca = :idPlaca";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':idPlaca', $placa['id'], PDO::PARAM_INT);
			$stmt->execute();
			$placa['comandos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$respuesta['placas'][] = $placa;
		}
	}

	if(OPC == GUARDAR_MENSAJE){
		$idPlaca = $_POST['idPlaca'];
		$telefono = $_POST['telefono'];
		$comando = $_POST['comando'];

		if($idPlaca == '' || $telefono == '' || $comando == '')
			throw new Exception('El idPlaca, telefono y comando no pueden venir vacios.');
		
		$query = 'insert into sms (mensaje, telefono, idPlaca) values(:msj, :tel, :idPlaca)';
		$stmt = $pdo->prepare($query);
		$stmt->bindParam(':msj', $comando, PDO::PARAM_STR);
		$stmt->bindParam(':tel', $telefono, PDO::PARAM_STR);
		$stmt->bindParam(':idPlaca', $idPlaca, PDO::PARAM_INT);
		$stmt->execute();
		$respuesta['lastInsertId'] = $pdo->lastInsertId();
		$respuesta['validado'] = true;
	}
}catch(Exception $e){
	$respuesta['validado'] = false;
	$respuesta['mensaje'] = $e->getMessage();
}

echo(json_encode($respuesta));
?>
