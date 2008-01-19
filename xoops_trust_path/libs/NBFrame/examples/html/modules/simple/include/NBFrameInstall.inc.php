<?php
/**
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
    include dirname(__FILE__).'/NBFrameLoader.inc.php';
    NBFrame::prepare(null, NBFRAME_TARGET_INSTALLER);
    $installHelper =& NBFrame::getInstallHelper();
    $installHelper->prepareOnInstallFunction();
    $installHelper->prepareOnUpdateFunction();
    $installHelper->prepareOnUninstallFunction();
?>
