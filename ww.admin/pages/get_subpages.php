<?php
require '../../ww.incs/basics.php';
if(!is_admin())exit;
require '../../common/menus.php';

$p=(int)$_REQUEST['p'];
echo json_encode(array('pid'=>$p,'subpages'=>menu_getChildren($p,0,1)));