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
$envtemp =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
$adminmenu = array();
if ($fname = NBFrame::findFile('admin_menu.inc.php', $envtemp, 'include'))  @include $fname;
$adminmenu = array_merge($adminmenu, NBFrameBase::getAdminMenu($envtemp));
?>
