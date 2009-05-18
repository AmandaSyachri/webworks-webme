<?php
echo '<h2>'.__('Themes').'</h2>';
// { handle actions
if($action=='set_theme'){
	if(is_dir(THEME_DIR.'/'.$_REQUEST['theme'])){
		$DBVARS['theme']=$_REQUEST['theme'];
		$DBVARS['theme_variant']=@$_REQUEST['theme_variant'];
		config_rewrite();
	}
}
// }
// { samples
	$dir=new DirectoryIterator(THEME_DIR);
	$themes_found=0;
	foreach($dir as $file){
		if($file->isDot())continue;
		if(!file_exists(THEME_DIR.'/'.$file.'/screenshot.png'))continue;
		$themes_found++;
		echo '<div style="width:250px;text-align:center;border:1px solid #000;margin:5px;height:250px;float:left;';
		if($file==$DBVARS['theme'])echo 'background:#ff0;';
		echo '"><form method="post" action="siteoptions.php"><input type="hidden" name="page" value="themes" /><input type="hidden" name="action" value="set_theme" />';
		echo '<input type="hidden" name="theme" value="'.htmlspecialchars($file).'" />';
		echo '<img src="/ww.skins/'.htmlspecialchars($file).'/screenshot.png" />';
		echo '<strong>',htmlspecialchars($file),'</strong><br />';
		if(is_dir(THEME_DIR.'/'.$file.'/cs')){
			$dir2=new DirectoryIterator(THEME_DIR.'/'.$file.'/cs');
			echo 'variant: <select name="theme_variant">';
			foreach($dir2 as $file2){
				if($file2->isDot())continue;
				$file2=preg_replace('/\.css$/','',$file2);
				$sel=$file2==$DBVARS['theme_variant']?' selected="selected"':'';
				echo '<option',$sel,'>',htmlspecialchars($file2).'</option>';
			}
			echo '</select>';
		}
		echo '<br /><input type="submit" value="'.__('set theme').'" /></form></div>';
	}
	if($themes_found==0){
		echo '<em>No themes found. Download a theme and unzip it into the /ww.skins/ directory.</em>';
	}
// }