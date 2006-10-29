<?php
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$envtemp =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
$adminmenu = array();
if ($fname = NBFrame::findFile('admin_menu.inc.php', $envtemp, 'include'))  @include $fname;
$adminmenu = array_merge($adminmenu, NBFrame::getAdminMenu($envtemp));
?>
