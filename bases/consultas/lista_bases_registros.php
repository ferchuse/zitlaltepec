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
	
	LEFT JOIN monitoreo ON monitoreo.tarjeta = recaudacion_monitoreo.tarjeta
	
	WHERE 1
	";
	
	$consulta.=  " 
	AND  DATE(fecha_registro)
	BETWEEN '{$_GET['fecha_inicial']}' 
	AND '{$_GET['fecha_final']}'"; 
	
	
	
	if($_GET["usuarios_cve"] != ""){
		$consulta.=  " AND usuarios.cve = '{$_GET["usuarios_cve"]}'"; 
	}
	
	$consulta.=  " ORDER BY recaudacion_monitoreo.cve"; 
	
	
	
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
				<th></th>
				<th>Folio</th>
				<th>Fecha </th>
				<th>Recaudación </th>
				<th>Unidad</th>
				<th>Operador</th>
				<th>Tarjeta</th>
				<th>Empresa</th>
				<th>Observaciones</th>
				<th>Efectivo Recaudado</th>
				<th>Efectivo a Entregar</th>
				<th>Usuario</th>
			</thead>
			<tbody id="tabla_DB">
				<?php 
					foreach($filas as $index=>$fila){
					?>
					<tr>
						<td class="text-center"> 
							<?php if($fila["recaudacion_estatus"] != 'C'){
								
								$totales[0]+= $fila["efectivo_recaudado"];
								$totales[1]+= $fila["efectivo_entregar"];
								if(dame_permiso("recaudacion.php", $link) == '3'){ //Permiso Supervisor
									// echo dame_permiso("recaudacion.php", $link);
								?>
								<button class="btn btn-danger cancelar" title="Cancelar" data-id_registro='<?php echo $fila['recaudacion_monitoreo_cve']?>'>
									<i class="fas fa-times"></i>
								</button>
								<button class="btn btn-outline-info imprimir" data-id_registro='<?php echo $fila['recaudacion_monitoreo_cve']?>'>
									<i class="fas fa-print"></i>
								</button>
								<?php
								}
							?>
							
							<?php
							}
							else{
								echo "<span class='badge badge-danger'>CANCELADO<br>".$fila["recaudacion_fechacan"]."<br>".$fila["usuario_cancela"]."</span>";
							}
							?>
						</td>
						<td><?php echo $fila["recaudacion_monitoreo_cve"]?></td>
						<td><?php echo $fila["fecha"]?></td>
						<td><?php echo $fila["recaudacion_nombre"]?></td>
						<td><?php echo $fila["no_eco"]?></td>
						<td><?php echo $fila["operadores_nombre"]?></td>
						<td><?php echo $fila["tarjeta"]?></td>
						<td><?php echo $fila["empresas_nombre"]?></td>
						<td><?php echo $fila["obs"]?></td>
						<td>$<?php echo $fila["efectivo_recaudado"]?></td>
						<td>$<?php echo $fila["efectivo_entregar"]?></td>
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