<?php
if (!class_exists('SimpleTable1AdminAction')) {
    NBFrame::using('AdminMaintAction');
    class SimpleTable1AdminAction extends NBFrameAdminMaintAction {
       function prepare() {
           parent::prepare('SimpleTable1', $this->__l('Table'));
       }
    }
}
?>