<?php
$action = 'add';
$display = '';
if (isset($_POST['action']))
	$action = $_POST['action'];
if ($action == 'update')
	$display = 'style="display: none;"'
?>
<div class="row justify-content-center">
	<div class="col-lg-10">
		<div class="row">
			<div class="col-lg">
				<div class="p-5">
					<?php echo $err ?>
					<h3 class="text-center"><b><?php echo strtoupper($title) ?></b></h3>
					<form method="post" enctype="multipart/form-data">
						<input type="hidden" name="pkey" value="">
						<input type="hidden" name="action" value="<?php echo $action ?>">

						<div class="form-group row">
							<label for="name" class="col-sm-3 col-form-label">Name Bonus</label>
							<div class="col-sm">
								<input type="text" class="form-control" id="name" name="name" placeholder="Name Bonus">
							</div>
						</div>

						<div class="form-group row">
							<label for="bonus" class="col-sm-3 col-form-label">Bonus</label>
							<div class="col-sm">
								<input type="text" class="form-control format-number" id="bonus" name="bonus" placeholder="RP. xxxx">
							</div>
						</div>

						<!-- detaile -->
						<div class="form-group row">
							<div class="col-sm">
								<table class="table">
									<thead>
										<tr>
											<th scope="col">Employe Name</th>
											<th scope="col">Bonus</th>
											<th scope="col"></th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody>
										<tr <?php echo $display ?>>
											<input type="hidden" name="detailKey[]">
											<td>
												<select class="form-control" name="detailEmployeKey[]">
													<!-- <option disabled selected value>----------</option> -->
													<?php foreach ($selVal as $val) { ?>
														<option value="<?php echo $val['pkey'] ?>"><?php echo $val['name'] ?></option>
													<?php } ?>
												</select>

											</td>
											<td><input type="text" class="form-control format-number" name="detailBonus[]" placeholder="Bonus %"></td>
											<td>%</td>
											<td><b class="text-danger btn closeDetail">X</b></td>
										</tr>
										<?php
										//jika update/edit
										if ($action == 'update') {
										?>
											<?php foreach ($dataDetail as $key => $valuee) {
												$employed = $valuee['employeekey'];
											?>
												<?php print_r($employed) ?>
												<tr>
													<input type="hidden" name="detailKey[]" value="<?php echo $valuee['pkey'] ?>">
													<td>
														<select class="form-control" name="detailEmployeKey[]" value="<?php echo $value['employeekey'] ?>">
															<!-- <option disabled selected value>----------</option> -->
															<?php foreach ($selVal as $val) { ?>
																<option value="<?php echo $val['pkey'] ?>" <?php if ($val['pkey'] == $employed) echo 'selected' ?>><?php echo $val['name'] ?></option>
															<?php } ?>
														</select>
													</td>
													<td><input type="text" class="form-control format-number" name="detailBonus[]" placeholder="Bonus %" value="<?php echo $valuee['percentage'] ?>"></td>
													<td>%</td>
													<td><b class="text-danger btn closeDetail">X</b></td>
												</tr>
											<?php } ?>
										<?php } //jika update/edit
										?>
									</tbody>
									<tfoot>
										<tr>
											<td><button type="button" class="btn btn-primary" name="addDetail">Tambah</button></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
						<!-- detaile -->


						<div class="form-group row mt-5">
							<div class="col-sm">
								<button type="submit" class="btn btn-primary btn-block">Submit</button>
							</div>
							<div class="col-sm">
								<a href="<?php echo base_url($baseUrl . 'List') ?>" class="btn btn-warning btn-block">Cancel</a>
							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>
</div>