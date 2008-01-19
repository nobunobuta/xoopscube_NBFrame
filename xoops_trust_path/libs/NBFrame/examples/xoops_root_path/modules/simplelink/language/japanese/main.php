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
$constpref = NBFrame::langConstPrefix('', NBFRAME_TARGET_TEMP);
if (!defined($constpref.'LANGUAGE_MAIN_READ')) {
define ($constpref.'LANGUAGE_MAIN_READ','1');

define($constpref.'LANG_LINK_ID','#');
define($constpref.'LANG_LINK_NAME','サイト名');
define($constpref.'LANG_LINK_DESC','サイトの説明');
define($constpref.'LANG_LINK_URL', 'サイトのURL');
define($constpref.'LANG_LINK_CATEGORY_ID', 'サイトのカテゴリー');
define($constpref.'LANG_LINK_WEIGHT','サイト表示順');

define($constpref.'LANG_CATEGORY_ID','#');
define($constpref.'LANG_CATEGORY_NAME','カテゴリー名');
define($constpref.'LANG_CATEGORY_DESC','カテゴリーの説明');
define($constpref.'LANG_CATEGORY_URL', 'カテゴリーのURL');
define($constpref.'LANG_CATEGORY_PARENT_ID', '親カテゴリー');
define($constpref.'LANG_CATEGORY_WEIGHT','カテゴリー表示順');

define($constpref.'LANG_LINK_LIST','サイトの一覧');
define($constpref.'LANG_CATEGORY_LIST','カテゴリーの一覧');
define($constpref.'ERROR_NO_LINKS','サイトが定義されていません');
}
?>
