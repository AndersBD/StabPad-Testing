<?php

	// site prefix
 	if(!isset($site_prefix))
		$site_prefix='';


 	if(!isset($base_dir))
		$base_dir='release';

	// check if settings are being passed to this file
	if(!isset($settings)) {
		$file='configs/tattoo.php';

		if($_GET['release'] && file_exists('configs/'.$_GET['release'].'.php'))
			$file='configs/'.$_GET['release'].'.php';

		include($file);
	}
?>
