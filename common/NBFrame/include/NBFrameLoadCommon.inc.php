<?php
$environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
$noCommonActions = $environment->getAttribute('NoCommonAction');
if (!is_array($noCommonActions)) {
    $noCommonActions = array();
}
if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], $noCommonActions)) {
    require_once XOOPS_ROOT_PATH .'/include/common.php';
} else {
    foreach (array('GLOBALS', '_SESSION', 'HTTP_SESSION_VARS', '_GET', 'HTTP_GET_VARS', '_POST',
                   'HTTP_POST_VARS', '_COOKIE', 'HTTP_COOKIE_VARS', '_REQUEST', '_SERVER',
                   'HTTP_SERVER_VARS', '_ENV', 'HTTP_ENV_VARS', '_FILES', 'HTTP_POST_FILES') as $bad_global) {
        if (isset($_REQUEST[$bad_global])) {
           exit();
        }
    }
    require_once XOOPS_ROOT_PATH.'/include/functions.php';
    require_once XOOPS_ROOT_PATH.'/class/errorhandler.php';
    require_once XOOPS_ROOT_PATH.'/class/logger.php';
    require_once XOOPS_ROOT_PATH.'/include/functions.php';
    require_once XOOPS_ROOT_PATH.'/class/database/databasefactory.php';
    require_once XOOPS_ROOT_PATH.'/kernel/object.php';
    require_once XOOPS_ROOT_PATH.'/class/criteria.php';
    require_once XOOPS_ROOT_PATH.'/class/module.textsanitizer.php';
}
?>
