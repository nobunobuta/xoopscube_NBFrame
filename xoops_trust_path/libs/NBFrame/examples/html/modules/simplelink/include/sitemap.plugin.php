<?php
if(!defined( 'XOOPS_ROOT_PATH')) exit ;
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$environment =& NBFrame::prepare(NBFRAME_TARGET_BLOCK);
$environment->preparePluginFunction(basename(__FILE__), 'b_sitemap_%s()');
?>
