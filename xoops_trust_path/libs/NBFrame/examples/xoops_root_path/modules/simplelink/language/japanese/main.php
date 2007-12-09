<?php
$constpref = NBFrame::langConstPrefix('', NBFRAME_TARGET_TEMP);
if (!defined($constpref.'LANGUAGE_MAIN_READ')) {
define ($constpref.'LANGUAGE_MAIN_READ','1');

define($constpref.'LANG_ID','#');
define($constpref.'LANG_NAME','サイト名');
define($constpref.'LANG_DESC','サイトの説明');
define($constpref.'LANG_URL', 'サイトのURL');
define($constpref.'LANG_WEIGHT','サイト表示順');

define($constpref.'LANG_LINK_LIST','サイトの一覧');
define($constpref.'ERROR_NO_LINKS','サイトが定義されていません');
}
?>
