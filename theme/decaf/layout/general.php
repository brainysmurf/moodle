<?php
if (!empty($CFG->themedir) and file_exists("$CFG->themedir/decaf")) {
	require_once ($CFG->themedir."/decaf/lib.php");
} else {
	require_once ($CFG->dirroot."/theme/decaf/lib.php");
}

$hasheading = ($PAGE->heading);
$hasnavbar = ( $PAGE->bodyid != 'page-site-index' && empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));

// $PAGE->blocks->region_has_content('region_name') doesn't work as we do some sneaky stuff
// to hide nav and/or settings blocks if requested
$blocks_side_pre = trim($OUTPUT->blocks_for_region('side-pre'));
$hassidepre = strlen($blocks_side_pre);
$blocks_side_post = trim($OUTPUT->blocks_for_region('side-post'));
$hassidepost = strlen($blocks_side_post);

if (empty($PAGE->layout_options['noawesomebar'])) {
	$topsettings = $this->page->get_renderer('theme_decaf','topsettings');
	decaf_initialise_awesomebar($PAGE);
	$awesome_nav = $topsettings->navigation_tree($this->page->navigation);
	$awesome_settings = $topsettings->settings_tree($this->page->settingsnav);
}

$custommenu = $OUTPUT->render_awesomebar(); //comes from decaf/renderers.php render_custom_menu()

$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (method_exists($OUTPUT, 'course_header') && empty($PAGE->layout_options['nocourseheaderfooter'])) {
	$courseheader = $OUTPUT->course_header();
	$coursecontentheader = $OUTPUT->course_content_header();
	if (empty($PAGE->layout_options['nocoursefooter'])) {
		$coursecontentfooter = $OUTPUT->course_content_footer();
		$coursefooter = $OUTPUT->course_footer();
	}
}

$bodyclasses = array();

if(!empty($PAGE->theme->settings->useeditbuttons) && $PAGE->user_allowed_editing()) {
	decaf_initialise_editbuttons($PAGE);
	$bodyclasses[] = 'decaf_with_edit_buttons';
}

if ($hassidepre && !$hassidepost) {
	$bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
	$bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
	$bodyclasses[] = 'content-only';
}

if(!empty($PAGE->theme->settings->persistentedit) && $PAGE->user_allowed_editing()) {
	if(property_exists($USER, 'editing') && $USER->editing) {
		$OUTPUT->set_really_editing(true);
	}
	$USER->editing = 1;
	$bodyclasses[] = 'decaf_persistent_edit';
}

if (!empty($PAGE->theme->settings->footnote)) {
	$footnote = $PAGE->theme->settings->footnote;
} else {
	$footnote = '<!-- There was no custom footnote set -->';
}

if (check_browser_version("MSIE", "0")) {
	header('X-UA-Compatible: IE=edge');
}
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
	<title><?php echo ltrim($PAGE->title,': ') ?></title>
	<link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
	<?php echo $OUTPUT->standard_head_html() ?>

	<link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css" />

	<link rel="stylesheet" type="text/css" href="/theme/decaf/style/arts.css?v=2" />
	<style type="text/css" id="artStyle"></style>
	<script src="/theme/decaf/javascript/arts.js?v=2" type="text/javascript"></script>

</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html();
if (empty($PAGE->layout_options['noawesomebar'])) { ?>
	<div id="awesomebar" class="decaf-awesome-bar">
		<?php
			if (
				$this->page->pagelayout != 'maintenance' // Don't show awesomebar if site is being upgraded
				&&
				!(get_user_preferences('auth_forcepasswordchange') && !session_is_loggedinas()) // Don't show it when forcibly changing password either
			  )
			  {
				if ($hascustommenu && !empty($PAGE->theme->settings->custommenuinawesomebar) && empty($PAGE->theme->settings->custommenuafterawesomebar))
				{
					echo $custommenu;
				}

				//Course administration menu
				echo $awesome_settings;

				if ($hascustommenu && !empty($PAGE->theme->settings->custommenuinawesomebar) && !empty($PAGE->theme->settings->custommenuafterawesomebar))
				{
					echo $custommenu;
				}

				echo $topsettings->settings_search_box();
			}
		?>
	</div>
<?php } ?>

<div id="page">

<?php if ($hasheading || $hasnavbar) { ?>

   <?php
   		//Disable headers by commenting out
   	    //Some of these colors come from http://www.computerhope.com/htmcolor.htm
   		$artHeaders = array(
   			1 => 'C48189',   # Pink Bow
   			2 => '7F525D',   # Dull Purple
   			3 => '800000',   # Maroon
   			4 => '0E6B13',   # Royal Blue     was --0E6B13
   			5 => '171717',   # Midnight
   			6 => '1F1358',   # Dark purple
   			7 => '583759',   # Plum Purple
   			8 => '550000',   # Dark Burgandy              8C001A  was 550000
   			9 => '990012',   # Chilli pepper was aa0000
   			10 => '25383C',  # Dark Slate Grey
   			11 => '34282C'  # Charcoal
   		);
   		//	12 => 'D4A017'   # Olypmic Gold
   		//);

		global $SESSION;
		if (isset($_GET['header'])) {
			$artHeader = $_GET['header'];
		} elseif (false && isset($SESSION) && isset($SESSION->artHeader)) {
			$artHeader = $SESSION->artHeader;
		} else {
			$artHeader = array_rand($artHeaders);
		}

		if (!isset($artHeaders[$artHeader])) {
			$artHeader = array_rand($artHeaders);
		}

		$artColors = $artHeaders[$artHeader];
		$headerBg = '/theme/decaf/pix/artsheaders/original/'.$artHeader.'.jpg';
		$filteredHeaderBg = '/theme/decaf/pix/artsheaders/'.$artHeader.'.jpg';
   ?>

   <script>
   	$(function(){
   		setArtColors('<?=$artColors?>');
   		setTimeout(artModeOn, 2000);
   	});
   </script>

	<div id="page-header" style="background-image:url(<?php echo $headerBg; ?>);">
		<div id="page-header-filtered-bg"  style="background-image:url(<?php echo $filteredHeaderBg; ?>);"></div>
		<div id="page-header-gradient"></div>
		<div id="page-header-wrapper">

			<?php if ($hasheading) { ?>

				<?php
					$heading = str_replace('DragonNet Frontpage', 'DragonNet' , $PAGE->heading);

					if ($heading == 'DragonNet') {
						$height = '';
						$heading = str_replace('DragonNet','<img src="/theme/decaf/pix/artslogo2.png" alt="DragonNet"/>', $heading);
						$heading .= " &hearts;s Arts Week";
					}
				 ?>

				<h1 class="headermain"><?php echo $heading ?></h1>
				<div class="headermenu">
				<?php
					if (!empty($PAGE->theme->settings->showuserpicture)) {
						if (isloggedin()) {
							echo ''.$OUTPUT->user_picture($USER, array('size'=>55)).'';
						} else {
							echo '<img class="userpicture" src="' . $OUTPUT->pix_url('image', 'theme') . '" />';
						}
					}
			 	//echo $OUTPUT->login_info();
				echo $OUTPUT->lang_menu();

				echo $PAGE->headingmenu;
				include __DIR__ . '/header-search-box.php';
			} //end if hasheading ?>
			</div>

		</div>
	</div>

	<?php if ($hascustommenu && empty($PAGE->theme->settings->custommenuinawesomebar)) { ?>
	  <div id="custommenu" class="decaf-awesome-bar"><?php echo $custommenu; ?></div>
	<?php } ?>

	<?php if (!empty($courseheader)) { ?>
		<div id="course-header"><?php echo $courseheader; ?></div>
	<?php } ?>

	<?php if ($hasnavbar) { ?>
		<div class="navbar clearfix">
			<div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
			<div class="navbutton"> <?php echo $PAGE->button; ?></div>
		</div>
	<?php } ?>

<?php } ?>
<!-- END OF HEADER -->
<div id="page-content-wrapper" class="clearfix">
	<div id="page-content">

		<div id="centerCol" class="<?php if (!$hassidepost&&!$hassidepre) { echo 'fullWidth'; } ?>">
			<div class="region-content">
				<?php
					echo $coursecontentheader;
					echo method_exists($OUTPUT, "main_content")?$OUTPUT->main_content():core_renderer::MAIN_CONTENT_TOKEN;
					echo $coursecontentfooter;
				?>
			</div>
		</div>

		<?php if ( $hassidepre || $hassidepost ) { ?>
		<div id="side-post" class="block-region">
			<div class="region-content">
				 <?php
					echo $blocks_side_pre;
					echo $blocks_side_post;
				?>
			</div>
		</div>
		<?php } ?>

	</div>
</div>

<!-- START OF FOOTER -->
	<?php if (!empty($coursefooter)) { ?>
		<div id="course-footer"><?php echo $coursefooter; ?></div>
	<?php } ?>
	<?php if ($hasfooter) { ?>
	<div id="page-footer" class="clearfix">
		<p><a href="#"><i class="icon-arrow-up"></i> Go back to the top of the page</a></p>
		<p class="footnote"><?php echo $footnote; ?></p>
		<p><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
		<?php
	   //echo $OUTPUT->login_info();
	   //echo $OUTPUT->home_link();
		echo $OUTPUT->standard_footer_html();
		?>
	</div>
	<?php } ?>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
<div id="back-to-top">
	<a class="arrow" href="#">▲</a>
	<a class="text" href="#">Back to Top</a>
</div>
<script type="text/javascript">
YUI().use('node', function(Y) {
	window.thisisy = Y;
	Y.one(window).on('scroll', function(e) {
		var node = Y.one('#back-to-top');

		if (Y.one('window').get('docScrollY') > Y.one('#page-content-wrapper').getY()) {
			node.setStyle('display', 'block');
		} else {
			node.setStyle('display', 'none');
		}
	});

});
</script>
</body>
</html>
