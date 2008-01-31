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
include dirname(__FILE__).'/NBFrameLoader.inc.php';
$environment =& NBFrameBase::prepare(NBFRAME_TARGET_INSTALLER);
$installHelper =& NBFrameBase::getInstallHelper($environment);
$installHelper->prepareOnInstallFunction();
$installHelper->prepareOnUpdateFunction();
$installHelper->prepareOnUninstallFunction();
?>
