<?php
if (!class_exists('SimpleLinkLinkAdminForm')) {
    NBFrame::using('ObjectForm');

    class SimpleLinkLinkAdminForm extends NBFrameObjectForm {
        function prepare() {
            $this->addElement('link_category_id', new XoopsFormSelect($this->__l('link_category_id'),'link_category_id'));
            
            $categoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);
            $this->addOptionArray('link_category_id', $categoryHandler->getSelectOptionArray());
        }
    }
}
?>
