<?php 
	include('../../conexi.php');
	$link = Conectarse();
	
	$consulta = "SELECT * FROM bases "; 
	
	$result = mysqli_query($link,$consulta);
	
	if($result){
		$num_registros = mysqli_num_rows($result);
	?>
	<table class="table table-bordered" id="tabla_registros" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th class="text-center">Nombre</th>
				<th class="text-center">Editar</th>
			</tr>
		</thead>
		<tbody >
			<?php
				while($fila = mysqli_fetch_assoc($result)){?>
				
				<tr>
					<td><?php echo $fila["base"];?></td>
					<td>
						<button class="btn btn-warning btn_editar" data-id_registro="<?php echo $fila["id_base"];?>">
							<i class="fas fa-edit"></i>
						</button>
					</td>
				</tr>
				
				<?php 	
				}
			?>
		</tbody>
	</table>
	
	
	<?php
		if($num_registros == 0 ) echo "<div class='alert alert-warning'> No hay Registros</div>";
		
	}
	else {
		echo "Error en".$consulta. mysqli_error($link);
	}
	
	
?>	