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
if(!defined( 'XOOPS_ROOT_PATH')) exit ;
include dirname(__FILE__).'/NBFrameLoader.inc.php';
$environment =& NBFrame::prepare(NBFRAME_TARGET_INSTALLER);
$installHelper =& $environment->getInstallHelper();
$installHelper->prepareOnInstallFunction();
$installHelper->prepareOnUpdateFunction();
$installHelper->prepareOnUninstallFunction();
?>
