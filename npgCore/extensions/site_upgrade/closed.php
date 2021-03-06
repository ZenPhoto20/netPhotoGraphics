<?php

/**
 * DO NOT MODIFY THIS FILE.
 * If you wish to change the appearance or behavior of
 * the site when closed you may edit the .htm and .xmp files
 */
if (file_exists(dirname(dirname(__DIR__)) . '/CORE_FOLDER/CONFIGFILE')) {
	$_contents = file_get_contents(dirname(dirname(__DIR__)) . '/CORE_FOLDER/CONFIGFILE');
	if (strpos($_contents, '<?php') !== false)
		$_contents = '?>' . $_contents;
	eval($_contents);
	$_conf_vars = $_zp_conf_vars;
	if (isset($_conf_vars['site_upgrade_state']) && $_conf_vars['site_upgrade_state'] == 'open') {
		// site is now open, redirect to index
		header("HTTP/1.0 307 Found");
		header("Status: 307 Found");
		header('Location: SITEINDEX');
		exit();
	}
}

$glob = array();
if (($dir = opendir(__DIR__)) !== false) {
	while (($file = readdir($dir)) !== false) {
		preg_match('~(.*)\-closed\.*~', $file, $matches);
		if (isset($matches[1]) && $matches[1]) {
			$glob[$matches[1]] = $file;
		}
	}
}
$xml = '';
foreach ($glob as $key => $file) {
	if (isset($_GET['$key'])) {
		$path = __DIR__ . '/' . $file;
		$xml = file_get_contents($path);
		$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>' . date("r", time()) . '</pubDate>', $xml);
		echo $xml;
	}
}
if (empty($xml)) {
	echo file_get_contents(__DIR__ . '/closed.htm');
}
?>