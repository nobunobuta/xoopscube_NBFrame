<?php
$xoopsOption['nocommon'] = true;
$_moduleBaseDir = dirname(dirname(__FILE__));
require_once dirname(dirname($_moduleBaseDir)).'/mainfile.php';

if (!defined('NBFRAME_BASE_DIR')) {
    if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_TRUST_PATH.'/libs/NBFrame');
    } else if (file_exists(XOOPS_ROOT_PATH.'/common/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_ROOT_PATH.'/common/libs/NBFrame');
    } else if (file_exists($_moduleBaseDir.'/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', $_moduleBaseDir.'/libs/NBFrame');
    }
}
if (defined('NBFRAME_BASE_DIR')) {
    require_once NBFRAME_BASE_DIR.'/include/NBFrameCommon.inc.php';
    require_once NBFRAME_BASE_DIR.'/class/NBFrame.class.php';
    NBFrame::prePrepare($_moduleBaseDir);
    require $_moduleBaseDir.'/module_settings.php';
    require_once NBFRAME_BASE_DIR.'/include/NBFrameLoadCommon.inc.php';
} else {
    die('NBFrame does not exist');
}
?>
