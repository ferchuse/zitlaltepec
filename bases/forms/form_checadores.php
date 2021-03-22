<form id="form_edicion" autocomplete="off" class="was-validated">
	<div class="modal " id="modal_edicion">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Nuevo Tablerista</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				
				<!-- Modal body -->
				<div class="modal-body">
					
					<input hidden name="id_checadores" id="id_checadores" value="">
					
					<div class="form-group">
						<label>Nombre:</label>				
						<input class="form-control" name="nombre" id="nombre" required>
					</div>
					<div class="form-group" hidden >
						<label >Contraseña:</label>
						<input class="form-control" type="password" name="password" id="password">
					</div>
					<div class="form-group">
						<label >Sitio:</label>
						<?= generar_select($link, "bases", "id_base" , "base", false, false, true)?>
					</div>
					<div class="form-group">
						<label >Estatus:</label>
						<select class="form-control" id="estatus" name="estatus" required>
							<option value="">Seleccione</option>
							<option selected value="Activo">Activo</option>
							<option value="Inactivo">Inactivo</option>
						</select>
					</div>
				</div>
				
				
				<!-- Modal footer -->
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">
					<i class="fas fa-times"></i> Cancelar</button>
					<button type="submit" class="btn btn-success " >
					<i class="fas fa-save"></i> Guardar </button>
				</div>
			</div>
		</div>
	</div>
</form>		
