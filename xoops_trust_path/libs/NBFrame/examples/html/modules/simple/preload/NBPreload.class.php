<?php
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$preloadEnvironment =& NBFrame::prepare('', NBFRAME_TARGET_BLOCK);
@include NBFRAME_BASE_DIR.'/include/NBFramePreload.inc.php';
?>