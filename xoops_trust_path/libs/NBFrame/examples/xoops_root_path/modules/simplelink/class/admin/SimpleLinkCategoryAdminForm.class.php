<?php
if (!class_exists('SimpleLinkCategoryAdminForm')) {
    NBFrame::using('ObjectForm');

    class SimpleLinkCategoryAdminForm extends NBFrameObjectForm {
        function prepare() {
            $this->addElement('category_parent_id',new XoopsFormSelect($this->__l('category_parent_id'),'category_parent_id'));
            
        }
        function &buildEditForm(&$object) {
            $parentCategoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);
            $this->addOptionArray('category_parent_id', $parentCategoryHandler->getParentSelectOptionArray($object->getKey()));
            $form =& parent::buildEditForm($object);
            return $form;
        }
    }
}
?>
