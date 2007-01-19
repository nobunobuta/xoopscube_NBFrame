<?php
$xoopsOption['nocommon'] = true;
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/mainfile.php';

if (!defined('NBFRAME_BASE_DIR')) {
    if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_TRUST_PATH.'/libs/NBFrame');
    } else if (file_exists(XOOPS_ROOT_PATH.'/common/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_ROOT_PATH.'/common/libs/NBFrame');
    } else {
        define('NBFRAME_BASE_DIR', dirname(__FILE__));
    }
}
if (defined('NBFRAME_BASE_DIR')) {
    require_once NBFRAME_BASE_DIR.'/include/NBFrameCommon.inc.php';
    require_once NBFRAME_BASE_DIR.'/class/NBFrame.class.php';
}
NBFrame::prePrepare(dirname(dirname(__FILE__)));
require dirname(dirname(__FILE__)).'/module_settings.php';
require_once NBFRAME_BASE_DIR.'/include/NBFrameLoadCommon.inc.php';
?>
