<?php
if (!class_exists('SimpleLinkMainAction')) {
    NBFrame::using('AdminMaintAction');

    class SimpleLinkMainAction extends NBFrameAdminMaintAction {
        function prepare() {
            $this->mHalfAuto = true;
            parent::prepare('SimpleLink', $this->__l('Link Admin'));
        }
    }
}
?>