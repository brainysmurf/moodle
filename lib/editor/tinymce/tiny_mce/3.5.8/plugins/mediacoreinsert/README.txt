     __  _____________   _______   __________  ____  ______
    /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
   / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/   
  / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___   
 /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/   
                 Moodle/TinyMCE Plugin 1.0 beta


[MediaCore](http://mediacore.com/) is an online video platform for managing, encoding, monetizing and delivering video to mobile and desktop devices. MediaCore makes it easy for any organization to share video either publicly or privately and build an amazing user experience on both desktop and mobile browsers around their own content. 

Who's using Mediacore? More and more MediaCore powered sites are popping up all over the world. You can learn more about some of these sites here on our [MediaCore showcase](http://mediacore.com/why-mediacore).

== Installation ==

1) Unzip/copy the provided archive into TinyMCE's plugins folder e.g.
	/path/to/moodle/lib/editor/tinymce/tiny_mce/3.4.6/plugins/

2) Open mediacore.php and fill in the configuration value at the
	top of the file.

3) Now we need to let Moodle know about the TinyMCE plugin. Open:
	/editor/tinymce/lib.php

	a) At the bottom of the file, just above "return $params", add:
		// ADDED FOR MEDIACORE 
		$params['plugins'] .= ",mediacorefilter";
		$params['theme_advanced_buttons3_add'] = $params['theme_advanced_buttons3_add'] . ",|,mediacorefilter";

4) Add some content; see the MediaCore button; click it. You're go to go!




