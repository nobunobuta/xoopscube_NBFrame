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
NBFrame::using('Action');

class SimpleLinkDefaultAction extends NBFrameAction {
    var $mList;
    var $mModuleObject;

    function prepare() {
        parent::prepare();

        $this->setDefaultTemplate($this->prefix('main.html'));

        $this->mRequest->defParam('cat', 'GET', 'int');
    }

    function executeDefaultOp() {
        $this->mList = array();

        $this->mModuleObject =& $this->mEnvironment->getModule();

        $linkHandler =& NBFrame::getHandler('SimpleLinkLink', $this->mEnvironment);
        $categoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);

        $category = $this->mRequest->getParam('cat');

        if (!empty($category)) {
            $criteria =& $categoryHandler->getChildrenCriteria('category_id', $category);
        } else {
            $criteria =& new CriteriaElement();
        }
        $criteria->setSort('category_weight');
        $categoryObjects =& $categoryHandler->getNestedObjects($criteria, '');

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
