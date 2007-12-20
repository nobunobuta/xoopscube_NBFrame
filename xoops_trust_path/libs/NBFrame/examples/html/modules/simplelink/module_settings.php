<?php
if (class_exists('NBFrame')) {
    $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
//**************************************************
    $environment->setOrigDirName('simplelink');
//**************************************************
    if ($fname = NBFrame::findFile('module_settings.php', $environment, '/')) @include $fname;
    if ($fname = NBFrame::findFile('custom_settings.php', $environment, '/')) @include $fname;
}
?>
