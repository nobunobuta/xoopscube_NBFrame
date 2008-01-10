<?php
require_once XOOPS_ROOT_PATH.'/modules/legacy/class/ActionFrame.class.php';
require_once XOOPS_ROOT_PATH.'/modules/legacy/admin/actions/PreferenceEditAction.class.php';

class NBFrame_PreferenceEditAction  extends Legacy_PreferenceEditAction {
    function prepare(&$controller, &$xoopsUser) {
        $root =& XCube_Root::getSingleton();
        $root->mLanguageManager->loadPageTypeMessageCatalog('comment');
        $root->mLanguageManager->loadPageTypeMessageCatalog('notification');
        $this->mModule = $GLOBALS['xoopsModule'];
        $this->mActionForm =& new Legacy_ModulePreferenceEditForm($this->mModule);
        $root->mLanguageManager->loadModinfoMessageCatalog($this->mModule->get('dirname'));
        $root->mLanguageManager->loadModuleMessageCatalog('legacy');
        
        $handler =& xoops_gethandler('config');
        
        $criteria =& new CriteriaCompo();
        $criteria->add(new Criteria('conf_modid', $this->mActionForm->getModuleId()));
        $criteria->add(new Criteria('conf_catid', $this->mActionForm->getCategoryId()));
        
        $this->mObjects =& $handler->getConfigs($criteria);
        $this->mActionForm->prepare($this->mObjects);
    }

    function hasPermission(&$controller, &$xoopsUser) {
        $controller->mRoot->mRoleManager->loadRolesByModule($this->mModule);
        return $controller->mRoot->mContext->mUser->isInRole('Module.' . $this->mModule->get('dirname') . '.Admin');
    }
}

if (!class_exists('NBFramePreferenceAction')) {
    NBFrame::using('AdminAction');
    
    class NBFramePreferenceAction extends NBFrameAdminAction {
        function prepare() {
            $this->mDefaultTemplate = NBFRAME_BASE_DIR . '/templates/admin/NBFramePreference.html';
        }
        function executeDefaultOp() {
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }
        function viewDefaultOp() {
            $root =& XCube_Root::getSingleton();
            $controller =& $root->mController;
            $action = new NBFrame_PreferenceEditAction();
            if ($action->prepare($controller, $controller->mRoot->mContext->mXoopsUser) === false) {
                die();  //< TODO
            }
            if (!$action->hasPermission($controller, $controller->mRoot->mContext->mXoopsUser)) {
                if ($this->mAdminFlag) {
                    $controller->executeForward(XOOPS_URL . "/admin.php");
                }
                else {
                    $controller->executeForward(XOOPS_URL);
                }
            }

            $this->mXoopsTpl->assign('actionForm', $action->mActionForm);
            $this->mXoopsTpl->assign('objectArr', $action->mObjects);
            
            $this->mXoopsTpl->assign('category', $action->mCategory);
            $this->mXoopsTpl->assign('module', $action->mModule);
            
            $handler =& xoops_gethandler('timezone');
            $timezoneArr =& $handler->getObjects();
            $this->mXoopsTpl->assign('timezoneArr', $timezoneArr);
            
            $handler =& xoops_gethandler('group');
            $groupArr =& $handler->getObjects();
            $this->mXoopsTpl->assign('groupArr', $groupArr);

            $handler =& xoops_gethandler('member');
            $userArr = $handler->getUserList();
            $this->mXoopsTpl->assign('userArr', $userArr);
        }
    }
}
?>
