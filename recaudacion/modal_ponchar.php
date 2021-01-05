
<!-- The Modal -->
<div class="modal fade" id="modal_ponchar">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title text-center"></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			
			<!-- Modal body -->
			<div class="modal-body">
				
				<div class="form-group">
					<label for="boleto">Boleto</label>
					<input type="number" class="form-control" id="boleto" name="boleto"  required>
				</div>
				
				<form id="form_boletos">
					<table  id="tablaboletossencillos" class="table-bordered table-condensed">
						<thead>
							
							<tr bgcolor="#E9F2F8">
								
								<th></th>
								<th>Taquilla</th>
								<th>Folio</th>
								<th>Fecha</th>
								<th>Hora</th>
								<th>Boleto</th>
								<th>Costo</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						
						
					</table>
				</form>
				
			</div>
			<!-- Modal footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
				
			</div>
			
		</div>
	</div>
</div>

