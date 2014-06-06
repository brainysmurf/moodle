<form class="form" action="#">

	<!-- Activity or PD -->
	<input type="hidden" name="type" value="<?=$itemType?>" />

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Name</label>
		<div class="col-md-9">
			<input type="text" id="title" name="name" class="form-control" placeholder="Name of the activity" value="<?=(FORMACTION == 'edit' ? $editItem->name : '')?>" />
		</div>
	</div>

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Description</label>
		<div class="col-md-9">
			<textarea name="description" class="form-control" placeholder="What is the homework?" rows="10"><?=(FORMACTION == 'edit' ? $editItem->description : '')?></textarea>
		</div>
	</div>

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Which season(s) does this activity run in?</label>
		<div class="col-md-9">

			<p><label><input type="checkbox" name="season[]" value="1" /> S1</label></p>
			<p><label><input type="checkbox" name="season[]" value="2" /> S2</label></p>
			<p><label><input type="checkbox" name="season[]" value="3" /> S3</label></p>

		</div>
	</div>

	<?php
	$label = 'Add Activity';
	?>

	<div class="form-group">
		<div class="col-md-offset-3 col-md-5">
			<button type="submit" class="btn btn-lg"><?=$label?></button>
		</div>
	</div>

</form>
