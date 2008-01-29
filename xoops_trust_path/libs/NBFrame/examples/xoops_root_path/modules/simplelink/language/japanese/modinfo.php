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
$constpref = NBFrame::langConstPrefix('MI', $mydirname);

if (defined( 'FOR_XOOPS_LANG_CHECKER' ) || !defined($constpref.'LANGUAGE_MODINFO_READ')) {

define ($constpref.'LANGUAGE_MODINFO_READ','1');
// Module Info
define($constpref.'DESC','簡単なリンク集');

define($constpref.'AD_MENU0','リンクの管理');
define($constpref.'AD_MENU1','カテゴリーの管理');
}
?>
