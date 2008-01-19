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
if (!class_exists('SimpleLinkCategoryAdminAction')) {
    NBFrame::using('AdminMaintAction');

    class SimpleLinkCategoryAdminAction extends NBFrameAdminMaintAction {
        function prepare() {
            $this->mHalfAutoForm = true;
            parent::prepare('SimpleLinkCategory', $this->__l("Category Admin"));
        }
        function &getListObjects($criteria)
        {
            $objects =& $this->mObjectHandler->getNestedObjects($criteria, '&#8211;&raquo;');
            return $objects;
        }
    }
}
?>
