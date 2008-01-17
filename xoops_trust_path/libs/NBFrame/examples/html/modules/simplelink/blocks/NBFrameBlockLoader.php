<?php
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$environment =& NBFrame::prepare('', NBFRAME_TARGET_BLOCK);
NBFrame::prepareBlockFunction($environment);
?>
