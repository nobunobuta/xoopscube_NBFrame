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
if( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) $mydirname = 'simplelink' ;
$constpref = NBFrame::langConstPrefix('AD', $mydirname);

if (defined( 'FOR_XOOPS_LANG_CHECKER' ) || !defined($constpref.'LANGUAGE_ADMIN_READ')) {

define($constpref.'LANGUAGE_ADMIN_READ','1');

define($constpref.'LANG_LINK_ADMIN','リンクの管理');
define($constpref.'LANG_CATEGORY_ADMIN','カテゴリーの管理');
}
?>
