<?php
/**
  * selects the theme for the site
  *
  * PHP Version 5
  *
  * @category WebworksWebme
  * @package  None
  * @author   Kae Verens <kae@webworks.ie>
  * @license  GPL Version 2
  * @link     http://www.webworks.ie/
 */

/**
  * recursively copy one directory to another
  *
  * @param string $src the source directory
  * @param string $dst destination to copy the source to
  *
  * @return void
**/
function Themes_recursiveCopy($src, $dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				Themes_recursiveCopy($src . '/' . $file, $dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
} 
echo '<h2>'.__('Themes').'</h2>';
// { handle actions
if ($action=='set_theme') {
	if (is_dir($DBVARS['theme_dir'].'/'.$_REQUEST['theme'])) {
		$DBVARS['theme']=$_REQUEST['theme'];
		$DBVARS['theme_variant']=@$_REQUEST['theme_variant'];
		Themes_recursiveCopy(
			$DBVARS['theme_dir'].'/'.$_REQUEST['theme'],
			$DBVARS['theme_dir_personal'].'/'.$_REQUEST['theme']
		);
		config_rewrite();
		cache_clear('pages');
	}
}
// }
// { samples
if (!isset($DBVARS['theme_dir'])) {
	$DBVARS['theme_dir']=SCRIPTBASE.'/ww.skins';
}
$dir=new DirectoryIterator($DBVARS['theme_dir']);
$themes_found=0;
foreach ($dir as $file) {
	if ($file->isDot()
		|| !file_exists($DBVARS['theme_dir'].'/'.$file.'/screenshot.png')
	) {
		continue;
	}
	$themes_found++;
	echo '<div style="width:250px;text-align:center;border:1px solid #000;'
		.'margin:5px;height:250px;float:left;';
	if ($file==$DBVARS['theme']) {
		echo 'background:#ff0;';
	}
	echo '"><form method="post" action="siteoptions.php">'
		.'<input type="hidden" name="page" value="themes" />'
		.'<input type="hidden" name="action" value="set_theme" />'
		.'<input type="hidden" name="theme" value="'
		.htmlspecialchars($file).'" />';
	$size=getimagesize('../ww.skins/'.$file.'/screenshot.png');
	$w=$size[0]; $h=$size[1];
	if ($w>240) {
		$w=$w*(240/$w);
		$h=$h*(240/$w);
	}
	if ($h>172) {
		$w=$w*(172/$h);
		$h=$h*(172/$h);
	}
	echo '<img src="/ww.skins/'.htmlspecialchars($file)
		.'/screenshot.png" width="'.(floor($w)).'" height="'
		.(floor($h)).'" /><br />'
		.'<strong>',htmlspecialchars($file),'</strong><br />';
	if (is_dir($DBVARS['theme_dir'].'/'.$file.'/cs')) {
		$dir2=new DirectoryIterator($DBVARS['theme_dir'].'/'.$file.'/cs');
		echo 'variant: <select name="theme_variant">';
		foreach ($dir2 as $file2) {
			if ($file2->isDot()) {
				continue;
			}
			$file2=preg_replace('/\.css$/', '', $file2);
			$sel=$file2==$DBVARS['theme_variant']?' selected="selected"':'';
			echo '<option',$sel,'>',htmlspecialchars($file2),'</option>';
		}
		echo '</select>';
	}
	echo '<br /><input type="submit" value="set theme" /></form></div>';
}
if ($themes_found==0) {
	echo '<em>No themes found. Download a theme and unzip it into the '
		.'/ww.skins/ directory.</em>';
}
// }
