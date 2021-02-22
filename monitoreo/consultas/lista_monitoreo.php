<?php 
	session_start();
	include('../../conexi.php');
	include('../../funciones/generar_select.php');
	include('../../funciones/dame_permiso.php');
	$link = Conectarse();
	$filas = array();
	$respuesta = array();
	$totales = array_fill (  0 ); //Llena el array totales con 10 elementos en 0s
	
	
	
	$consulta = "SELECT *, usuarios.nombre as usuarios_nombre
	FROM monitoreo
	LEFT JOIN tarjetas_unidad ON tarjetas_unidad.cve = monitoreo.tarjeta
	LEFT JOIN empresas ON empresas.cve = tarjetas_unidad.empresa
	LEFT JOIN unidades ON unidades.cve = tarjetas_unidad.unidad
	LEFT JOIN usuarios ON usuarios.cve = monitoreo.usuario
	WHERE 1
	";
	
	$consulta.=  " 
	AND  DATE(fecha_monitoreo)
	BETWEEN '{$_GET['fecha_inicial']}' 
	AND '{$_GET['fecha_final']}'"; 
	
	
	
	if($_GET["usuarios_cve"] != ""){
		$consulta.=  " AND usuarios.cve = '{$_GET["usuarios_cve"]}'"; 
	}
	
	$consulta.=  " ORDER BY id_monitoreo"; 
	
	
	
	$result = mysqli_query($link,$consulta);
	
	if($result){
		
		if( mysqli_num_rows($result) == 0){
			die("<div class='alert alert-danger'>No hay registros</div>");
		}
		
		while($fila = mysqli_fetch_assoc($result)){
			// console_log($fila);
			$filas[] = $fila ;
		}
	?>
	
	<pre hidden>
		Id_empresas <?php echo $_SESSION["id_empresas"]?>
		Session Id <?php echo session_id()?>
		Sesiion Estatus <?php echo session_status()?>
		Consulta <?php echo $consulta?>
	</pre>
	<table class="table table-bordered table-condensed" id="dataTable" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th>Folio</th>
				<th>Fecha </th>
				<th>Unidad</th>
				<th>Aforador</th>
				<th>Tarjeta</th>
				<th>Monto</th>
				<th>Vueltas</th>
				<th>Observaciones</th>
				
			</thead>
			<tbody id="tabla_DB">
				<?php 
					foreach($filas as $index=>$fila){
						
					?>
					<tr>
						<td class="text-center"> 
							<?php if($fila["estatus_monitoreo"] != 'Cancelado'){
								$total+= $fila["utilidad"];
								if(dame_permiso("monitoreo.php", $link) == '3'){ 
								?>
								<button class="btn btn-danger cancelar" title="Cancelar" data-id_registro='<?php echo $fila['id_monitoreo']?>'>
									<i class="fas fa-times"></i>
								</button>
								
								<?php
								}
							?>
							<button hidden class="btn btn-outline-info imprimir" data-id_registro='<?php echo $fila['recaudacion_operador_cve']?>'>
								<i class="fas fa-print"></i>
							</button>
							<?php
							}
							else{
								echo "<span class='badge badge-danger'>".$fila["estatus_monitoreo"]."<br>".$fila["datos_cancelacion"]."</span>";
							}
							?>
							<a href="ver_monitoreo.php?id_monitoreo=<?php echo $fila['id_monitoreo']?>" class="btn btn-default " title="Cancelar" data-id_registro='<?php echo $fila['id_monitoreo']?>'>
								<i class="fas fa-search"></i>
							</a>
						</td>
						<td><?php echo $fila["id_monitoreo"]?></td>
						<td><?php echo $fila["fecha_monitoreo"]?></td>
						<td><?php echo $fila["no_eco"]?></td>
						<td><?php echo $fila["usuarios_nombre"]?></td>
						<td><?php echo $fila["tarjeta"]?></td>
						<td>$<?php echo number_format($fila["utilidad"])?></td>
						<td><?php echo $fila["vueltas"]?></td>
						<td><?php echo $fila["observaciones"]?></td>
						
					</tr>
					<?php
						
						// if($fila["estatus_reciboSalidas"] != "Cancelado"){
						
						
						// }
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
					
					<td class="h6">$<?php echo number_format($total)?></td>
					
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	
	<?php
		
		// print_r($_SESSION);
	}
	else {
		echo  "Error en ".$consulta.mysqli_Error($link);
	}
	
?>			