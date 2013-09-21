<?php

/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/   
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___   
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/   
 *                        TinyMCE Plugin
 *
 *
 */

$mediacore_url = 'http://demo.mediacore.tv'; // no slash at the end, please.
$max_per_page = 6;

require 'mediacore.inc.php';
$here = $_SERVER['SCRIPT_NAME'];

?>



	<?php if($counted == 0): ?>
	<div class="mcore-message">
		<img src="images/zero-state.png" alt="zero state">
		<h2>No media found</h2>
		<p>Either you have no media in your MediaCore library, or your search returned no results.</p>
	</div>
	<?php endif; ?>
	<?php foreach ($videos as $video): ?>
		<div class="mcore-media mcore-clearfix mcore-video">  
			<div class="mcore-thumbnail">




<!-- These links are ugly. -->
<a href="javascript:MediaCoreDialog.insert('<?php echo embeddable($video->url); ?>', '<?php echo $video->title; ?>');">



				<img src="<?php echo $video->thumbs->s->url; ?>"
						alt="<?php echo $video->title; ?>" />
				<div class="mcore-border"></div>
</a>
				<div class="mcore-overlay">
					<span class="mcore-length">
						<?php echo sec2hms($video->duration, true); ?>
					</span>
					<span class="mcore-icon"></span> 								
				</div>

			</div>
			<div class="mcore-info">
				<h3>
				



<!-- These links are ugly. -->
<a href="javascript:MediaCoreDialog.insert('<?php echo embeddable($video->url); ?>', '<?php echo $video->title; ?>');">



				<?php echo $video->title; ?>
</a>
				</h3>
				<span class="mcore-date">
					<?php echo ago(strtotime($video->publish_on)) ?>
				</span>
			</div>	
			<div class="mcore-add">
				<span class="mcore-btn mcore-add-btn">
<!-- These links are ugly. -->
<a href="javascript:MediaCoreDialog.insert('<?php echo embeddable($video->url); ?>', '<?php echo $video->title; ?>');">
						<span class="mcore-icon"></span> 
						Add
</a>					
				</span>
			</div>
	   </div> <!-- /.mcore-media -->
	<?php endforeach ?>
	

