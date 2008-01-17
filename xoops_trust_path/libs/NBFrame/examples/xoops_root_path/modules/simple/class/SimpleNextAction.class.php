<?php
NBFrame::using('Action');

class SimpleNextAction extends NBFrameAction {
    var $mContent;

    function prepare() {
        parent::prepare();
        $this->setDefaultTemplate($this->prefix('main.html'));
    }

    function executeDefaultOp() {
        $this->mContent = 'Good bye World';
        return NBFRAME_ACTION_VIEW_DEFAULT;
    }

    function viewDefaultOp() {
        $this->mXoopsTpl->assign('content', $this->mContent);

    }
}
