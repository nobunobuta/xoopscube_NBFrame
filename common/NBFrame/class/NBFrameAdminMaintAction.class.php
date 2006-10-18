<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminMaintAction')) {
    NBFrame::using('ObjectAction');
    NBFrame::using('ObjectUtil');

    class NBFrameAdminMaintAction extends NBFrameObjectAction{
        function NBFrameAdminMaintAction(&$environment) {
            parent::NBFrameObjectAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }

        function prepare($name, $caption) {
            $this->mLanguage->setInAdmin(true);
            parent::prepare($name, $name, $caption);
            $this->mDefaultOp = 'list';
            $this->mAllowedOp = array('list','new','edit','insert','save','delete','deleteok');
            NBFrame::using('AdminTpl');
            $this->mXoopsTpl =& new NBFrameAdminTpl($this->mDirName, $this->mLanguage);

            $this->mFormTemplate = 'NBFrameAdminForm.html';
            $this->setObjectForm('admin.'.$name.'Admin');
            $this->mListTemplate = 'NBFrameAdminList.html';
            $this->setObjectList('admin.'.$name.'Admin');
        }
        function viewFormOp() {
            parent::viewFormOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function viewListOp() {
            parent::viewListOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function viewDeleteOp() {
            parent::viewDeleteOp();
            $this->mXoopsTpl->assign('extrahtml', '');
            $this->mXoopsTpl->assign('errmsg', '');
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
        }

        function executeActionSuccess() {
            redirect_header($this->mUrl, 2, $this->__l('Action Success'));
        }

        function executeActionError() {
            redirect_header($this->mUrl, 2, $this->mErrorMsg,2);
        }
    }
}
?>
