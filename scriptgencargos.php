<?php

require_once('subs/cnx_db.php');

// if(date('d')=='01'){
	$res = mysql_query("SELECT * FROM cat_cargos_unidades ORDER BY cve");
	while($row = mysql_fetch_array($res)){
		mysql_query("INSERT INTO cargos_unidades (fecha, unidad, motivo, cargo, abono) 
			SELECT '".date('Y-m-01')."', a.cve, '".$row['cve']."', b.cargo_".$row['cve'].", 0 
			FROM unidades a INNER JOIN derroteros b ON b.cve = a.derrotero WHERE a.estatus=1");
	}
// }

?>