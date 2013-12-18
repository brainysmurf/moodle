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

$mediacore_url = 'http://dragontv.ssis-suzhou.net'; // no slash at the end, please.
$max_per_page = 6;

require 'mediacore.inc.php';
$here = $_SERVER['SCRIPT_NAME'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
					"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title></title> <!-- blank for aesthetics -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/mediacore.js"></script>
	<link rel="stylesheet" type="text/css" href="css/mediacore.css"/>
	<script type="text/javascript">
	$(function(){
		<!-- The following is funky, but without it, IE8 on XP acts a fool. -->
		<!-- TODO Figure out why IE8 hates Allan and fix it -->
		function clicker() {
			return true;
		}
	});
	</script>
</head>
<body class="" onload="MediaCoreDialog.init();">
<div id="mcore-embed">
	<div class="mcore-header">
		<div class="mcore-pagination">
		<a <?php if($offset != 0): ?>
			href="?page=<?php print $previous ?>"
			<?php endif; ?>	
			class="mcore-btn mcore-prev <?php if($offset == 0) print "mcore-disabled" ?>"
			title="Goto Previous Page"
			onclick="clicker()">
				&#8656;
		</a>
		<a <?php if(!$maxedout): ?>
			href="?page=<?php print $next ?>"
			<?php endif; ?>
			class="mcore-btn mcore-next <?php if($maxedout) print "mcore-disabled" ?>"
			title="Goto Next Page"
			onclick="clicker()">
				&#8658;
		</a>
		</div>
		<div class="mcore-title">DragonTV</div>
	</div>
	<form class="mcore-search" action="" method="get">
		<div>
		<?php if($searchquery): ?>
		<a class="mcore-clear-search" href="?search=">x</a>
		<?php endif; ?>
		<input type="text" 
				name="search" 
				id="search" 
				class="mcore-search-field"/>
		</div>
	</form>
	<div class="mcore-content">

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
				<span class="mcore-border"></span>
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
	

	</div> <!-- /.mcore-content -->
	<div class="mcore-footer">
		Page <?php echo $currentpage ?> of <?php echo $howmanypages ?>
	</div>
</div> <!-- /#mcore-embed -->
</body>
</html>
