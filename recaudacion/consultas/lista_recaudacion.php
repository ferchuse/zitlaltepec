<?php 
	session_start();
	include('../../conexi.php');
	include('../../funciones/generar_select.php');
	include('../../funciones/dame_permiso.php');
	$link = Conectarse();
	$filas = array();
	$respuesta = array();
	$totales = array_fill (  0 ,  1 , 0 ); //Llena el array totales con 10 elementos en 0s
	
	
	
	$consulta = "SELECT *,
	operadores.nombre AS operadores_nombre ,
	recaudacion_autobus.cve AS recaudacion_autobus_cve,
	recaudaciones.nombre AS recaudacion_nombre,
	tarjetas_unidad.cve AS tarjeta,
	empresas.nombre as empresas_nombre,
	usuarios.nombre as usuarios_nombre,
	tarjetas_unidad.estatus AS tarjetas_estatus,
	recaudacion_autobus.estatus AS recaudacion_estatus
	
	FROM recaudacion_autobus
	
	LEFT JOIN monitoreo ON monitoreo.tarjeta = recaudacion_autobus.tarjeta
	LEFT JOIN tarjetas_unidad ON tarjetas_unidad.cve = recaudacion_autobus.tarjeta
	LEFT JOIN recaudaciones ON recaudaciones.cve = recaudacion_autobus.recaudacion
	LEFT JOIN empresas ON empresas.cve = tarjetas_unidad.empresa
	LEFT JOIN unidades ON unidades.cve = tarjetas_unidad.unidad
	LEFT JOIN operadores ON operadores.cve = tarjetas_unidad.operador
	LEFT JOIN usuarios ON usuarios.cve = recaudacion_autobus.usuario
	WHERE 1
	";
	
	$consulta.=  " 
	AND  DATE(fecha_creacion)
	BETWEEN '{$_GET['fecha_inicial']}' 
	AND '{$_GET['fecha_final']}'"; 
	
	
	
	if($_GET["usuarios_cve"] != ""){
		$consulta.=  " AND usuarios.cve = '{$_GET["usuarios_cve"]}'"; 
	}
	
	$consulta.=  " ORDER BY recaudacion_autobus.cve"; 
	
	
	
	$result = mysqli_query($link,$consulta);
	
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			die("<div class='alert alert-danger'>No hay registros</div>");
		}
		
		while($fila = mysqli_fetch_assoc($result)){
			
			$filas[] = $fila ;
		}
	?>
	
	
	<table class="table table-bordered table-condensed" id="dataTable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th>Folio</th>
				<th>Fecha </th>
				<th>Recaudación </th>
				<th>Unidad</th>
				<th>Operador</th>
				<th>Tarjeta</th>
				<th>Empresa</th>
				<th>Observaciones</th>
				<th>Utilidad</th>
				<th>Usuario</th>
			</thead>
			<tbody id="tabla_DB">
				<?php 
					foreach($filas as $index=>$fila){
					?>
					<tr>
						<td class="text-center"> 
							<?php if($fila["recaudacion_estatus"] != 'C'){
								
								$totales[0]+= $fila["utilidad"];
								if(dame_permiso("recaudacion.php", $link) == '3'){ 
								?>
								<button class="btn btn-danger cancelar" title="Cancelar" data-id_registro='<?php echo $fila['recaudacion_autobus_cve']?>'>
									<i class="fas fa-times"></i>
								</button>
								
								<?php
								}
							?>
							<button class="btn btn-outline-info imprimir" data-id_registro='<?php echo $fila['recaudacion_autobus_cve']?>'>
								<i class="fas fa-print"></i>
							</button>
							<?php
							}
							else{
								echo "<span class='badge badge-danger'>".$fila["recaudacion_estatus"]."<br>".$fila["datos_cancelacion"]."</span>";
							}
							?>
						</td>
						<td><?php echo $fila["recaudacion_autobus_cve"]?></td>
						<td><?php echo $fila["fecha_creacion"]?></td>
						<td><?php echo $fila["recaudacion_nombre"]?></td>
						<td><?php echo $fila["no_eco"]?></td>
						<td><?php echo $fila["operadores_nombre"]?></td>
						<td><?php echo $fila["tarjeta"]?></td>
						<td><?php echo $fila["empresas_nombre"]?></td>
						<td><?php echo $fila["obs"]?></td>
						<td>$<?php echo $fila["total_utilidad"]?></td>
						<td><?php echo $fila["usuarios_nombre"]?></td>
							
					</tr>
					<?php
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<?php
						foreach($totales as $i =>$total){
						?>
						<td class="h6">$<?php echo number_format($total)?></td>
						<?php	
						}
					?>
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