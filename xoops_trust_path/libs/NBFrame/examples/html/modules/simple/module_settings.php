<?php
if (class_exists('NBFrame')) {
    $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
//**************************************************
    $environment->setOrigDirName('simple');
//**************************************************
    if ($fname = NBFrame::findFile('module_settings.php', $environment, '/')) @include $fname;
}
?>
