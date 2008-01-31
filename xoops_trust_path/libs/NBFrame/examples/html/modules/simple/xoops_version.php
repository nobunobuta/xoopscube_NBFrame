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
include dirname(__FILE__).'/include/NBFrameLoader.inc.php';
$environment =& NBFrameBase::prepare(NBFRAME_TARGET_INSTALLER);
// Include Moudle xoops_version.php
if ($fileName= NBFrame::findFile('xoops_version.php', $environment, '', false)) include $fileName;
// Parse xoops_version.php
NBFrameBase::parseXoopsVerionFile($modversion, $environment);
?>
