<?php
$constpref = NBFrame::langConstPrefix('MI', NBFRAME_TARGET_TEMP);
$adminmenu[1]['title'] = constant($constpref.'AD_MENU0');
$adminmenu[1]['link'] = "?action=admin.SimpleLinkLinkAdmin";
$adminmenu[2]['title'] = constant($constpref.'AD_MENU1');
$adminmenu[2]['link'] = "?action=admin.SimpleLinkCategoryAdmin";
?>