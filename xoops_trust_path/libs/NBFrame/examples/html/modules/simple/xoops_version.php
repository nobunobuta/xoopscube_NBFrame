<?php
include dirname(__FILE__).'/include/NBFrameLoader.inc.php';
// Include Moudle xoops_version.php
include (NBFrame::getXoopsVersionFileName(null));
// On Update Pre Process
NBFrame::parseXoopsVerionFile($modversion);
?>
