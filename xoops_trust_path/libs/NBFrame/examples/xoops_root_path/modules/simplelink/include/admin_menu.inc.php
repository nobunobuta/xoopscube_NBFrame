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
$constpref = NBFrame::langConstPrefix('MI', NBFRAME_TARGET_TEMP);
$adminmenu[1]['title'] = constant($constpref.'AD_MENU0');
$adminmenu[1]['link'] = "?action=admin.SimpleLinkLinkAdmin";
$adminmenu[2]['title'] = constant($constpref.'AD_MENU1');
$adminmenu[2]['link'] = "?action=admin.SimpleLinkCategoryAdmin";
?>