<?php
if (!class_exists('SimpleLinkLinkAdminAction')) {
    NBFrame::using('AdminMaintAction');

    class SimpleLinkLinkAdminAction extends NBFrameAdminMaintAction {
        function prepare() {
            $this->mHalfAuto = true;
            parent::prepare('SimpleLinkLink', $this->__l('Link Admin'));
        }
    }
}
?>