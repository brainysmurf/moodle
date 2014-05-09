<?php
	// Get all the user's classes
	$groups = $hwblock->getUsersGroups($USER->id);

	$selectedCourseID = '';
	$selectedGroupID = '';
	if (isset($editItem)) {
		$selectedGroupID = $editItem->groupid;
	} elseif ($_GET['groupid']) {
		$selectedGroupID = $_GET['groupid'];
	}
?>

<form class="form form-horizontal addHomeworkForm" role="form" method="post">

	<?php if (FORMACTION == 'edit') { ?>
	<input type="hidden" name="editid" value="<?=$editItem->id?>" />
	<?php } ?>

	<input type="hidden" name="action" value="<?=(FORMACTION == 'edit' ? 'saveedit' : 'save')?>" />

	<div class="form-group">
		<label for="assigned" class="col-md-3 control-label">Class <i class="icon-magic"></i></label>
		<div class="col-md-9">
			<select name="groupid" class="form-control" id="groupIDSelelect">
				<option value="">Please select...</option>
			<?php
			foreach ($groups as $courseID => $enrollment) {
				foreach ($enrollment['groups'] as $groupID => $group) {
					// TODO: Ability to pass courseid in the URL and select the first group in the course
					//(isset($courseid) && $course->id == $courseid ? 'selected': '')
					echo '<option value="' . $groupID . '" data-courseid="' . $courseID . '" ' . ($groupID == $selectedGroupID ? 'selected' : '') . '>';
						echo $enrollment['course']->fullname;
						if (trim($group['teacher'])) {
							echo ' (' . $group['teacher'] . '\'s Class)';
						} else {
							echo ' (' . $group['name'] . ')';
						}
						if ($groupID == $selectedGroupID) {
							$selectedCourseID = $courseID;
						}
					echo '</option>';
				}
			}
			?>
			</select>
			<input type="hidden" name="courseid" value="<?=(FORMACTION == 'edit' ? $editItem->courseid : $selectedCourseID)?>" />

		</div>
	</div>

	<div class="form-group">
		<label for="description" class="col-md-3 control-label">Description <i class="icon-edit"></i></label>
		<div class="col-md-9">
			<textarea name="description" class="form-control" placeholder="What is the homework?" rows="10"><?=(FORMACTION == 'edit' ? $editItem->description : '')?></textarea>
		</div>
	</div>

	<div class="form-group">
		<label for="assigned" class="col-md-3 control-label">Start Date <i class="icon-play-circle"></i></label>
		<div class="col-md-9">
			<input type="text" id="startdate" name="startdate" class="form-control" value="<?=(FORMACTION == 'edit' ? $editItem->startdate : date('Y-m-d'))?>" />
			<script>
			$(function(){
				$('#startdate').datepicker({
					minDate: -7,
					maxDate: "+1Y",
					dateFormat: 'yy-mm-dd',
					numberOfMonths: 2,
					onSelect: setPossibleDays,
					onClose: function(selectedDate) {
						$('#duedate').datepicker("option", "minDate", selectedDate);
					}
				});
				$(document).on('change', '#startdate', setPossibleDays);
			});
			</script>
		</div>
	</div>

	<div class="form-group">
		<label for="due" class="col-md-3 control-label">Due Date <i class="icon-bell"></i></label>
		<div class="col-md-9">
			<input type="text" id="duedate" name="duedate" class="form-control" placeholder="Enter a date the assignment should be handed in by." value="<?=(FORMACTION == 'edit' ? $editItem->duedate : '')?>" />
			<script>
			$(function(){
				$('#duedate').datepicker({
					minDate: 0,
					maxDate: "+1Y",
					dateFormat: 'yy-mm-dd',
					numberOfMonths: 2,
					onSelect: setPossibleDays
				});
				$(document).on('change', '#duedate', setPossibleDays);
			});
			</script>
		</div>
	</div>

	<div class="form-group" id="assignedDatesGroup">
		<label for="assigned" class="col-md-3 control-label">Assigned Days <i class="icon-calendar"></i></label>
		<div class="col-md-9">
			<p class="help-block">Which days should students work on this task?</p>
			<input id="assigneddates" type="hidden" name="assigneddates" value="" />
			<ul id="possibleDays" class="row"></ul>
		</div>
	</div>

	<?php if (FORMACTION == 'edit') {
		// Show the assigned day toggle buttons on pageload if editing and existing item
		echo '<script> var homeworkFormAssignedDates = ' . json_encode($editItem->getAssignedDates()) . '; </script>';
	} ?>

	<div class="form-group">
		<label for="duration" class="col-md-3 control-label">Duration <i class="icon-time"></i></label>
		<div class="col-md-9">
			<p class="help-block">How long should students spend on the task on each assigned day? Drag the two ends of the slider to change.</p>
			<input type="hidden" name="duration" class="form-control" value="" />
			<span class="help-text" id="duration-help"></span>
			<div id="duration-slider"></div>

			<script>
			$(function() {

				var durationLabels = {
					0: '0 minutes',
					15: '15 minutes',
					30: '30 minutes',
					45: '45 minutes',
					60: '1 hour',
					75: '1 hour 15 minutes',
					90: '1 hour 30 minutes',
					105: '1 hour 45 minutes',
					120: '2 hours',
					135: '2 hours 15 minutes',
					150: '2 hours 30 minutes',
					165: '2 hours 45 minutes',
					180: '3 or more hours',
				};

				function setDuration(min, max) {
					//Set the label
					if (min == max) {
						$('#duration-help').html('<strong>' + durationLabels[min] + '</strong>');
					} else {
						$('#duration-help').html('Between <strong>' + durationLabels[min] + '</strong> and <strong>' + durationLabels[max] + '</strong>');
					}

					$('input[name=duration]').val(min + '-' + max);
				}

				<?php
					if (FORMACTION == 'edit') {
						$duration = explode('-', $editItem->duration);
						$initialMinDuration = $duration[0];
						$initialMaxDuration = $duration[1];
					} else {
						$initialMinDuration = 0;
						$initialMaxDuration = 30;
					}
				?>

				$('#duration-slider').slider({
					range: true,
					min: 0,
					step: 15,
					max: 180,
					values: [<?=$initialMinDuration?>, <?=$initialMaxDuration?>],
					slide: function(event, ui) {
						setDuration(ui.values[0], ui.values[1]);
					}
				});

				setDuration(<?=$initialMinDuration?>, <?=$initialMaxDuration?>);
			});
			</script>
		</div>
	</div>

	<div class="form-group">
		<div class="col-md-offset-3 col-md-5">
			<button type="submit" class="btn btn-lg">Submit</button>
		</div>
	</div>

</form>
