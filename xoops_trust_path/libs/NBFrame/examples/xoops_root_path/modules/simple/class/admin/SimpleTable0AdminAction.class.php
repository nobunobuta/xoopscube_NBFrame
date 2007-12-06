<?php
if (!class_exists('SimpleTable0AdminAction')) {
    NBFrame::using('AdminMaintAction');
    class SimpleTable0AdminAction extends NBFrameAdminMaintAction {
        function prepare() {
//            $this->mObjectHandler = NBFrame::getHandler('realname.users', $this->mEnvironment);
            parent::prepare('SimpleTable0', $this->__l('Table'));
       }
    }
}
?>