<?php
if(!defined( 'XOOPS_ROOT_PATH')) exit ;
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$environment =& NBFrame::prepare(NBFRAME_TARGET_BLOCK);
$environment->preparePluginFunction(basename(__FILE__), '%s_new($limit=0, $offset=0)');
$environment->preparePluginFunction(basename(__FILE__), '%s_num()');
$environment->preparePluginFunction(basename(__FILE__), '%s_data($limit=0, $offset=0)');
?>
