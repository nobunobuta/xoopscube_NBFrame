<?php
if (!class_exists('SimpleTableAdminAction')) {
    NBFrame::using('AdminMaintAction');
    class SimpleTableAdminAction extends NBFrameAdminMaintAction {
        function prepare() {
            parent::prepare('SimpleTable', $this->__l('Table'));
       }
    }
}
?>
