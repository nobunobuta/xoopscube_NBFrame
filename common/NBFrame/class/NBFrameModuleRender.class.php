<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameModuleRender')) {
    NBFrame::using('Render');
    class NBFrameModuleRender extends NBFrameRender {
        function &start() {
            global $xoopsConfig, $xoopsOption, $xoopsModule, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger;
            if (!empty($this->mTemplate)) {
                $GLOBALS['xoopsOption']['template_main'] = $this->mTemplate;
            }
            include XOOPS_ROOT_PATH.'/header.php';
            $this->mXoopsTpl =& $GLOBALS['xoopsTpl'];
            $this->_addSmartyPugin();
            return $this->mXoopsTpl;
        }
        
        function end() {
            global $xoopsConfig, $xoopsOption, $xoopsModule, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger;;
            include XOOPS_ROOT_PATH.'/footer.php';
        }
    }
}
?>
