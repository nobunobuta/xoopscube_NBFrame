<?php
$constpref = NBFrame::langConstPrefix('', NBFRAME_TARGET_TEMP);
if (!defined($constpref.'LANGUAGE_MAIN_READ')) {
define ($constpref.'LANGUAGE_MAIN_READ','1');

define($constpref.'LANG_LINK_ID','#');
define($constpref.'LANG_LINK_NAME','������̾');
define($constpref.'LANG_LINK_DESC','�����Ȥ�����');
define($constpref.'LANG_LINK_URL', '�����Ȥ�URL');
define($constpref.'LANG_LINK_CATEGORY_ID', '�����ȤΥ��ƥ��꡼');
define($constpref.'LANG_LINK_WEIGHT','������ɽ����');

define($constpref.'LANG_CATEGORY_ID','#');
define($constpref.'LANG_CATEGORY_NAME','���ƥ��꡼̾');
define($constpref.'LANG_CATEGORY_DESC','���ƥ��꡼������');
define($constpref.'LANG_CATEGORY_URL', '���ƥ��꡼��URL');
define($constpref.'LANG_CATEGORY_PARENT_ID', '���ƥ��꡼�Υ��ƥ��꡼');
define($constpref.'LANG_CATEGORY_WEIGHT','���ƥ��꡼ɽ����');

define($constpref.'LANG_LINK_LIST','�����Ȥΰ���');
define($constpref.'LANG_CATEGORY_LIST','���ƥ��꡼�ΰ���');
define($constpref.'ERROR_NO_LINKS','�����Ȥ��������Ƥ��ޤ���');
}
?>