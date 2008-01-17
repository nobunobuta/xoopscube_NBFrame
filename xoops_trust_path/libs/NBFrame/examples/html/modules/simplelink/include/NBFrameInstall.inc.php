<?php
    include dirname(__FILE__).'/NBFrameLoader.inc.php';
    NBFrame::prepare(null, NBFRAME_TARGET_INSTALLER);
    $installHelper =& NBFrame::getInstallHelper();
    $installHelper->prepareOnInstallFunction();
    $installHelper->prepareOnUpdateFunction();
    $installHelper->prepareOnUninstallFunction();
?>
