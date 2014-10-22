<form class="form" method="post" action="<?=$activityCenter->getPath()?>actions/saveactivity.php">

	<input type="hidden" name="action" value="<?=FORMACTION?>" />

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Category</label>
		<div class="col-md-9">
			<select name="categoryid" class="form-control" >
				<?php

				//load categories in activity category
				$activityCategory = coursecat::get(1);
				$activitySubcategores = $activityCategory->get_children();

function displayCategories($categories)
{
	global $editItem;
	foreach ($categories as $category) {
		// For elementary we have to go deeper...
		if ($category->id == 118) {
			displayCategories($category->get_children());
			continue;
		}
		$selcted = FORMACTION == 'edit' && $editItem->categoryid == $category->id;
		echo '<option value="' . $category->id . '"' . ($selected ? ' selected="selected"' : '').'>' . $category->name . '</option>';
	}
}

				displayCategories($activitySubcategores);
				?>
			</select>

		</div>
	</div>

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Activity Name</label>
		<div class="col-md-9">
			<input type="text" id="title" name="name" class="form-control" placeholder="Name of the activity" value="<?=(FORMACTION == 'edit' ? $editItem->name : '')?>" />
		</div>
	</div>

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">Description</label>
		<div class="col-md-9">
			<textarea name="summary" class="form-control" placeholder="Enter a description of the activity, including which days it runs?" rows="10"><?=(FORMACTION == 'edit' ? $editItem->summary : '')?></textarea>
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

	<div class="form-group">
		<label for="shared" class="col-md-3 control-label">How many supervisors does this activity need?</label>
		<div class="col-md-9">

			<p><input type="text" class="form-control" name="supervisors" value="<?=(FORMACTION == 'edit' ? $editItem->activitysupervisors : 0)?>" /></p>

		</div>
	</div>

	<?php
	if (FORMACTION == 'add') {
		?>
		<div class="form-group">
			<label for="maxEnrolledUsers" class="col-md-3 control-label">How many students can join the activity? (0 = unlimited)</label>
			<div class="col-md-9">

				<p><input type="text" class="form-control" name="maxEnrolledUsers" value="0" /></p>

			</div>
		</div>

		<div class="form-group">
			<label for="parentsCanEnrol" class="col-md-3 control-label">Can parents enrol their children?</label>
			<div class="col-md-9">

				<label><input type="radio" name="parentsCanEnrol" value="1" checked="checked" /> Yes</label>
				&nbsp;&nbsp;&nbsp;
				<label><input type="radio" name="parentsCanEnrol" value="0" /> No</label>

			</div>
		</div>

		<?
	}
	?>

	<?php
	if (FORMACTION == 'edit') {
		$label = 'Save Changes';
	} else {
		$label = 'Add Activity';
	}
	?>

	<div class="form-group">
		<div class="col-md-offset-3 col-md-5">
			<button type="submit" class="btn btn-lg"><?=$label?></button>
		</div>
	</div>

</form>
