<?php
NBFrame::using('Action');

class SimpleLinkDefaultAction extends NBFrameAction {
    var $mList;
    var $mModuleObject;

    function prepare() {
        parent::prepare();
        $this->setDefaultTemplate($this->prefix('main.html'));
    }

    function executeDefaultOp() {
        $linkHandler =& NBFrame::getHandler('SimpleLinkLink', $this->mEnvironment);
        $categoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);
        $criteria =& new CriteriaElement();
        $criteria->setSort('category_weight');
        $categoryObjects =& $categoryHandler->getNestedObjects($criteria, '');
        $this->mList = array();
    	$moduleHandler =& xoops_gethandler('module');
    	$this->mModuleObject =& $moduleHandler->getByDirname($this->mDirName);

        foreach($categoryObjects as $categoryObject) {
            $criteria =& new Criteria('link_category_id', $categoryObject->getVar('category_id'));
            $criteria->setSort('link_weight');
            $linkObjects = $linkHandler->getObjects($criteria);
            $categoryObject->assignVar('link_objects', $linkObjects);
            $this->mList[] = $categoryObject;
        }
        return NBFRAME_ACTION_VIEW_DEFAULT;
    }
    
    function viewDefaultOp() {
        $this->mXoopsTpl->assign('mydirname', $this->mDirName);
        $this->mXoopsTpl->assign('simplelink_list', $this->mList);
        $this->mXoopsTpl->assign('simplelink_moduleObject', $this->mModuleObject);
    }
}
