<?php 
	session_start();
	include('../../../conexi.php');
	include('../../../funciones/generar_select.php');
	include('../../../funciones/dame_permiso.php');
	$link = Conectarse();
	$filas = array();
	$respuesta = array();
	$totales = array_fill (  0 ,  1 , 0 ); //Llena el array totales con 10 elementos en 0s
	
	
	
	$consulta = "SELECT * FROM recibos_extra
	LEFT JOIN empresas USING(id_empresas) 
	LEFT JOIN beneficiarios USING(id_beneficiarios) 
	LEFT JOIN motivos_salida USING(id_motivosSalida)
	LEFT JOIN usuarios USING(id_usuarios)
	WHERE 1
	";
	
	$consulta.=  " 
	AND  DATE(fecha_reciboSalidas)
	BETWEEN '{$_GET['fecha_inicial']}' 
	AND '{$_GET['fecha_final']}'"; 
	
	if($_GET['referencia'] != ""){
		$consulta.=  " AND referencia =  '{$_GET['referencia']}' "; 
	}
	
	if($_GET['id_beneficiarios'] != ""){
		$consulta.=  " AND id_beneficiarios =  '{$_GET['id_beneficiarios']}' "; 
	}
	
	if($_GET["id_empresas"] != ""){
		$consulta.=  " AND recibos_extra.id_empresas = '{$_GET["id_empresas"]}'"; 
	}
	
	$consulta.=  " ORDER BY id_reciboSalidas"; 
	
	
	
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
				<th>Referencia</th>
				<th>Fecha </th>
				<th>Beneficiario</th>
				<th>Motivo</th>
				<th>Empresa</th>
				<th>Monto</th>
				<th>Observaciones</th>
				<th>Usuario</th>
			</thead>
			<tbody id="tabla_DB">
				<?php 
					foreach($filas as $index=>$fila){
					?>
					<tr>
						<td class="text-center"> 
							<?php if($fila["estatus_reciboSalidas"] != 'Cancelado'){
								
								$totales[0]+= $fila["monto_reciboSalidas"];
								if(dame_permiso("recibos_extra.php", $link) == 'Supervisor'){ 
								?>
								<button class="btn btn-danger cancelar" title="Cancelar" data-id_registro='<?php echo $fila['id_reciboSalidas']?>'>
									<i class="fas fa-times"></i>
								</button>
								
								<?php
								}
							?>
							<button class="btn btn-outline-info imprimir" data-id_registro='<?php echo $fila['id_reciboSalidas']?>'>
								<i class="fas fa-print"></i>
							</button>
							<?php
							}
							else{
								echo "<span class='badge badge-danger'>".$fila["estatus_reciboSalidas"]."<br>".$fila["datos_cancelacion"]."</span>";
							}
							?>
						</td>
						<td><?php echo $fila["id_reciboSalidas"]?></td>
						<td><?php echo $fila["referencia"]?></td>
						<td><?php echo $fila["fecha_reciboSalidas"]?></td>
						<td><?php echo $fila["nombre_beneficiarios"]?></td>
						<td><?php echo $fila["nombre_motivosSalida"]?></td>
						<td><?php echo $fila["nombre_empresas"]?></td>
						<td>$<?php echo $fila["monto_reciboSalidas"]?></td>
						<td><?php echo $fila["observaciones_reciboSalidas"]?></td>
						<td><?php echo $fila["nombre_usuarios"]?></td>
							
					</tr>
					<?php
						
						if($fila["estatus_reciboSalidas"] != "Cancelado"){
							
							
						}
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