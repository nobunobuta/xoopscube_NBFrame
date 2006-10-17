<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminAction')) {
    NBFrame::using('Action');
    
    class NBFrameAdminAction extends NBFrameAction{
        function NBFrameAdminAction(&$environment) {
            parent::NBFrameAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }
    }
}
?>
