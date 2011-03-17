<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher';
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
} 
if (file_exists($base)) {
	rrmdir($base);
}
mkdir($base);

$pids=dbAll('select id from pages');
$page_names=array();
foreach ($pids as $pid) {
	$page=Page::getInstance($pid['id']);
	$url='http://'.$_SERVER['HTTP_HOST'].$page->getRelativeURL();
	$relname=$page->getRelativeURL();
	$name=preg_replace('#^/#', '', $relname);
	$name=str_replace('/', '@', $name).'.html';
	$f=file_get_contents($url);
	file_put_contents($base.'/'.$name, $f);
	if ($page->special&1) {	// home page
		file_put_contents($base.'/index.html', $f);
	}
	$page_names[]=array($relname, $name);
}
mkdir($base.'/tmp');
file_put_contents($base.'/tmp/page_names.json', json_encode($page_names));
