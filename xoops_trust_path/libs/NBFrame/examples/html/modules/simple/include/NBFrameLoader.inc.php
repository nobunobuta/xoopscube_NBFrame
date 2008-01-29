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
$_NBFrame_moduleBaseDir = dirname(dirname(__FILE__));
require_once dirname(dirname($_NBFrame_moduleBaseDir)).'/mainfile.php';

if (!defined('NBFRAME_BASE_DIR')) {
    if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_TRUST_PATH.'/libs/NBFrame');
    } else if (file_exists(XOOPS_ROOT_PATH.'/common/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', XOOPS_ROOT_PATH.'/common/libs/NBFrame');
    } else if (file_exists($_NBFrame_moduleBaseDir.'/libs/NBFrame/include/NBFrameCommon.inc.php')) {
        define('NBFRAME_BASE_DIR', $_NBFrame_moduleBaseDir.'/libs/NBFrame');
    }
}
if (defined('NBFRAME_BASE_DIR')) {
    require NBFRAME_BASE_DIR.'/include/NBFrameCommon.inc.php';
} else {
    die('NBFrame does not exist');
}
?>
