<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminMaintAction')) {
    NBFrame::using('ObjectAction');

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

            $this->mFormTemplate = 'admin/NBFrameAdminForm.html';
            $this->setObjectForm('admin.'.$name.'Admin');
            $this->mListTemplate = 'admin/NBFrameAdminList.html';
            $this->setObjectList('admin.'.$name.'Admin');
            $this->mBypassAdminCheck = false; // Administrator Group members can edit everything in admin screen
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
            $this->mEnvironment->redirect($this->mActionName, 2, $this->__l('Action Success'));
        }

        function executeActionError() {
            $action = $this->mEnvironment->getAttribute('AdminMainAction');
            $this->mEnvironment->redirect($action, 2, $this->mErrorMsg);
        }
    }
}
?>
