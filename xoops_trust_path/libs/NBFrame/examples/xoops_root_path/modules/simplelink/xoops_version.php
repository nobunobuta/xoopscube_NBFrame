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
$modEnv =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
$langPrefix = NBFrame::langConstPrefix('MI', NBFRAME_TARGET_INSTALLER);
$modversion['name'] = 'SimpleLink';   // It'll be rewritten like "SimleLink [dirname]"
$modversion['version'] = '0.01';
$modversion['description'] = constant($langPrefix.'DESC');
$modversion['credits'] = '';
$modversion['author'] = 'NobuNobu';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'images/logo.png';   // It'll be rewritten. Logo image must be placed in images directory.
$modversion['dirname'] = 'simplelink';   // It'll be rewritten with real dirname;

// Menu
$modversion['hasMain'] = 1;
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';   // It'll be rewritten
$modversion['adminmenu'] = 'include/admin_menu.inc.php'; // It'll be rewritten
$modversion['hasconfig'] = 1;

// Search
$modversion['hasSearch'] = 1;
$modversion['search']['class'] = 'SimpleLinkSeach';
$modversion['search']['func'] = 'search';
?>
