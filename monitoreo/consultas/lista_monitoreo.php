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
	
	
	
	if($_GET["num_eco"] != ""){
		$consulta.=  " AND unidades.no_eco = '{$_GET["num_eco"]}'"; 
	}
	
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
	
	<div class="table-responsive">
		<table class="table table-bordered table-condensed" id="dataTable" width="100%" cellspacing="0">
			<thead>
				<tr>
					<th></th>
					<th>Folio</th>
					<th>Fecha </th>
					<th>Unidad</th>
					<th>Aforador</th>
					<th>Tarjeta</th>
					<th>Vueltas</th>
					
					<th>Ingreso Bruto</th>
					<th>Casetas</th>
					<th>Diesel</th>
					<th>Despachadores</th>
					<th>Comision</th>
					<th>Incentivo</th>
					<th>Bases</th>
					<th>Utilidad</th>
					<th>Observaciones</th>
				</tr>
			</thead>
			<tbody id="tabla_DB">
				<?php 
				$totales = array();
					foreach($filas as $index=>$fila){
						
					?>
					<tr class="text-right">
						<td class="text-center"> 
							<?php 
								
								if($fila["estatus_monitoreo"] != 'Cancelado'){
									$totales[1]+= $fila["ingreso_bruto"];
									$totales[2]+= $fila["casetas"];
									$totales[3]+= $fila["diesel"];
									$totales[4]+= $fila["despachadores"];
									$totales[5]+= $fila["comision"];
									$totales[6]+= $fila["incentivo"];
									$totales[7]+= $fila["bases"];
									$totales[8]+= $fila["utilidad"];
									if(dame_permiso("monitoreo.php", $link) == '3'){
									?>
									<button class="btn btn-sm btn-danger cancelar" title="Cancelar" data-id_registro='<?php echo $fila['id_monitoreo']?>'>
										<i class="fas fa-times"></i>
									</button>
									
									<?php
									}
								?>
								<button hidden class="btn btn-sm btn-outline-info imprimir" data-id_registro='<?php echo $fila['recaudacion_operador_cve']?>'>
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
						<td><?php echo $fila["vueltas"]?></td>
						<td>$<?php echo number_format($fila["ingreso_bruto"])?></td>
						<td>$<?php echo number_format($fila["casetas"])?></td>
						<td>$<?php echo number_format($fila["diesel"])?></td>
						<td>$<?php echo number_format($fila["despachadores"])?></td>
						<td>$<?php echo number_format($fila["comision"])?></td>
						<td>$<?php echo number_format($fila["incentivo"])?></td>
						<td>$<?php echo number_format($fila["bases"])?></td>
						<td class="<?php echo $fila["utilidad"] > 0 ? "text-success" : "text-danger" ?>" >$<?php echo number_format($fila["utilidad"])?></td>
						<td><?php echo $fila["observaciones"]?></td>
						
					</tr>
					<?php
						
						// if($fila["estatus_reciboSalidas"] != "Cancelado"){
						
						
						// }
					}
				?>
			</tbody>
			<tfoot class="bg-secondary text-white h6">
				<tr>
					<td><?php echo count($filas)?> Registros</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					
					<?php foreach($totales as $total){?>
						<td class="text-right">$<?php echo number_format($total)?></td>
						<?php 
						}
					?>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	
	
	<?php
		
		 // print_r($totales);
	}
	else {
		echo  "Error en ".$consulta.mysqli_Error($link);
	}
	
?>										