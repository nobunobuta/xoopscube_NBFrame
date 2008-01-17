<?php
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
