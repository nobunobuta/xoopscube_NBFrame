<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
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
    require_once NBFRAME_BASE_DIR.'/class/NBFrameBase.class.php';
    require_once NBFRAME_BASE_DIR.'/class/NBFrame.class.php';
    NBFrameBase::prePrepare($_moduleBaseDir);
    require $_moduleBaseDir.'/module_settings.php';
    require_once NBFRAME_BASE_DIR.'/include/NBFrameLoadCommon.inc.php';
} else {
    die('NBFrame does not exist');
}
?>
