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
            if (!$this->mAction->mDialogMode) {
                include XOOPS_ROOT_PATH.'/header.php';
            } else {
                if (class_exists('XCube_Root')) {
                    $root=&XCube_Root::getSingleton();
                    $root->mController->setDialogMode(true);
                    include XOOPS_ROOT_PATH.'/header.php';
                } else {
                    xoops_header(false);
                    require_once XOOPS_ROOT_PATH.'/class/template.php';
                    $GLOBALS['xoopsTpl'] = new XoopsTpl();
                }
            }
            $this->mXoopsTpl =& $GLOBALS['xoopsTpl'];
            $this->_addSmartyPugin();
            return $this->mXoopsTpl;
        }
        
        function end() {
            global $xoopsConfig, $xoopsOption, $xoopsModule, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger;;
            
            if (!$this->mAction->mDialogMode) {
                include XOOPS_ROOT_PATH.'/footer.php';
            } else {
                if (class_exists('XCube_Root')) {
                    include XOOPS_ROOT_PATH.'/footer.php';
                } else {
                    xoops_footer();
                }
            }
        }
    }
}
?>
