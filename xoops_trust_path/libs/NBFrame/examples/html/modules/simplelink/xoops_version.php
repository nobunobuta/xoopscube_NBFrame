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
// Include Moudle xoops_version.php
include (NBFrame::getXoopsVersionFileName(null));
// Parse xoops_version.php
NBFrame::parseXoopsVerionFile($modversion);
?>
