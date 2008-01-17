<?php
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$preloadEnvironment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
@include NBFRAME_BASE_DIR.'/include/NBFramePreload.inc.php';
?>