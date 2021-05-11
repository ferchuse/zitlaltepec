<?php 
	session_start();
	include('../../conexi.php');
	include('../../funciones/generar_select.php');
	include('../../funciones/dame_permiso.php');
	$link = Conectarse();
	$filas = array();
	$respuesta = array();
	$totales = array_fill (  0 ,  1 , 0 ); //Llena el array totales con 10 elementos en 0s
	
	
	
	$consulta = "SELECT *
	FROM bases_registros
	LEFT JOIN checadores USING(id_checadores)
	LEFT JOIN bases ON bases_registros.id_base = bases.id_base

	WHERE 1
	";
	
	$consulta.=  " 
	AND  DATE(fecha_registro)
	BETWEEN '{$_GET['fecha_inicial']}' 
	AND '{$_GET['fecha_final']}'"; 
	
	
	
	if($_GET["usuarios_cve"] != ""){
		$consulta.=  " AND usuarios.cve = '{$_GET["usuarios_cve"]}'"; 
	}
	
	// $consulta.=  " ORDER BY recaudacion_monitoreo.cve"; 
	
	
	
	$result = mysqli_query($link,$consulta);
	
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			die("<div class='alert alert-danger'>No hay registros</div>");
		}
		
		while($fila = mysqli_fetch_assoc($result)){
			
			$filas[] = $fila ;
		}
	?>
	<pre hidden>
		<?php echo $consulta ?>
	</pre>
	
	<table class="table table-bordered table-condensed" id="dataTable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Folio Tarjeta</th>
				<th>Fecha </th>
				<th>Unidad</th>
				<th>Base</th>
				<th>Usuario</th>
			</thead>
			<tbody id="tabla_DB">
				<?php 
					foreach($filas as $index=>$fila){
					?>
					<tr>						
						<td><?php echo $fila["tarjeta"]?></td>
						<td><?php echo $fila["fecha_registro"]?></td>
						<td><?php echo $fila["num_eco"]?></td>
						<td><?php echo $fila["base"]?></td>
						<td><?php echo $fila["nombre"]?></td>
					</tr>
					<?php
					}
				?>
			</tbody>
			<tfoot class="bg-secondary h5 text-white">
				<tr>
					<td><?php echo count($filas);?> Registros</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					
				</tr>
			</tfoot>
		</table>
	</div>
	
	<?php
		
		
	}
	else {
		echo  "Error en ".$consulta.mysqli_Error($link);
	}
	
?>			