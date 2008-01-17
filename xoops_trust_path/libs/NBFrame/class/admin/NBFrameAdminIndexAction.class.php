<?php
if (!class_exists('NBFrameAdminIndexAction')) {
    NBFrame::using('AdminAction');
    
    class NBFrameAdminIndexAction extends NBFrameAdminAction {
        function executeDefaultOp() {
//         NBFrame::executeAction('',$this->mEnvironment->getAttribute('AdminMainAction'));
           header('Location:'.XOOPS_URL.'/modules/'.$this->mDirName.'/?action='.$this->mEnvironment->getAttribute('AdminMainAction'));
        }
    }
}
?>
