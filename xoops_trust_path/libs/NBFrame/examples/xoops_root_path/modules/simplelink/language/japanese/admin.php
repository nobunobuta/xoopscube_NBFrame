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
$constpref = NBFrame::langConstPrefix('AD', NBFRAME_TARGET_TEMP);
if (!defined($constpref.'LANGUAGE_ADMIN_READ')) {
define($constpref.'LANGUAGE_ADMIN_READ','1');

define($constpref.'LANG_LINK_ADMIN','リンクの管理');
define($constpref.'LANG_CATEGORY_ADMIN','カテゴリーの管理');
}
?>
