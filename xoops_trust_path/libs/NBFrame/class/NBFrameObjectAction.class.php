<?php
/**
 *
 * @package NBFrame
 * @version $Id: NBFrameObjectAction.class.php 1391 2008-03-11 07:17:10Z nobunobu $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectAction')) {
    NBFrame::using('Action');

    class NBFrameObjectAction extends NBFrameAction{
        /**
         * @var string
         */
        var $mName;
        /**
         * @var string
         */
        var $mCaption;
        /**
         * @var NBFrameObjectHandler
         */
        var $mObjectHandler;
        /**
         * @var NBFrameObjectForm
         */
        var $mObjectForm;
        /**
         * @var NBFrameObjectList
         */
        var $mObjectList;
        /**
         * @var Criteria
         */
        var $mListFilterCriteria = null;
        var $mListFilterJoinDef = false;
        var $mListFilterHaving = '';
        /**
         * @var NBFrameObject
         */
        var $mObject = null;
        
        var $mObjectArr = array();

        var $mObjectAllCount = 0;
        
        var $mPageNav;
        /**
         * @var string
         */
        var $mErrorMsg = '';
        /**
         * @var string
         */
        var $mFormTemplate;
        /**
         * @var string
         */
        var $mListTemplate;
        /**
         * @var int
         */
        var $mListPerPageDefault = 20;
        var $mListPerPage;
        /**
         * @var int
         */
        var $mListStart;
        /**
         * @var string
         */
        var $mListSortDefault = null;
        var $mListSort;
        /**
         * @var string
         */
        var $mListOrderDefault = null;
        var $mListOrder;
        /**
         * @var string
         */
        var $mViewTemplate;
        /**
         * @var string
         */
        var $mObjectKeyFields;

        var $mAttributes;
        
        var $mHalfAutoForm = false;
        var $mHalfAutoList = false;
        
        var $mBypassAdminCheck = true;
        
        var $mListNaviExtra = array();
        
        var $mConfirmParam = array();
        
        function NBFrameObjectAction(&$environment) {
            parent::NBFrameAction($environment);
        }
        
        function prepare($classprefix, $name, $caption) {
            parent::prepare();
            $this->mDefaultOp = 'edit';
            $this->mAllowedOp = array('edit','new','insert','save');

            $this->mName = $name;
            $this->mCaption = $caption;
            if (!$this->mObjectHandler) {
                $this->mObjectHandler =& NBFrame::getHandler($classprefix, $this->mEnvironment);
            }
            $this->setObjectKeyField();

            if (empty($this->mListSortDefault)) $this->mListSortDefault = $this->mObjectKeyFields[0];
            if (empty($this->mListOrderDefault)) $this->mListOrderDefault = 'asc';
        }

        function setObjectKeyField() {
            $this->mObjectKeyFields = $this->mObjectHandler->getKeyFields();
        }
        
        function setObjectForm($className) {
            $this->mObjectForm =& $this->mEnvironment->getinstance($className, 'Form');
            if (!$this->mObjectForm) {
                NBFrame::using('ObjectForm');
                $this->mObjectForm =& New NBFrameObjectForm($this->mEnvironment);
            }
        }

        function setObjectList($className) {
            $this->mObjectList =& $this->mEnvironment->getinstance($className, 'List');
            if (!$this->mObjectList) {
                NBFrame::using('ObjectList');
                $this->mObjectList =& New NBFrameObjectList($this->mEnvironment);
            }
        }

        function setFormTemplate($formTemplate) {
            $this->mFormTemplate = $formTemplate;
        }

        function setListTemplate($listTemplate) {
            $this->mListTemplate = $listTemplate;
        }

        function setViewTemplate($viewTemplate) {
            $this->mViewTemplate = $viewTemplate;
        }

        function executeNewOp() {
            if (is_object($this->mObjectForm)) {
                $this->mObjectForm->bindAction($this, 1);
                $this->mObjectForm->prepare();
                $this->mObjectForm->setupRequests('', false);
            }
            $object =& $this->mObjectHandler->create();
            if (is_object($this->mObjectForm) && $this->mObjectForm->canVerify()) {
                $object->enableVerify();
            }

            $object->SetRequestVars($this->mRequest);

            return $this->_showForm($object, $this->__l('New'));
        }

        function executeEditOp() {
            if (!($keys = $this->_requestKeyValue('GET'))) {
                $this->mErrorMsg =  $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->get($keys);
            return $this->_showForm($object, $this->__l('Edit'));
        }

        function _showForm(&$object, $caption, $errmsg='') {
            if (is_object($object)) {
                if (!$object->checkGroupPerm('write', $this->mBypassAdminCheck)) {
                    $this->mErrorMsg = $this->__e('Permission Error');
                    return NBFRAME_ACTION_ERROR;
                }
                $this->mErrorMsg = $errmsg;
                $this->mCaption = $this->mCaption.' &raquo; '.$caption;
                $this->mObject =& $object;
                $this->mExtraShowMethod = 'FormOp';
                return NBFRAME_ACTION_VIEW_EXTRA;
            } else {
                $this->mErrorMsg = $this->__e('No Record is found');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeInsertOp() {
            $object =& $this->mObjectHandler->create();
            return $this->_insert($object, $this->__l('New'));
        }

        function executeSaveOp() {
            if (!($keys = $this->_requestKeyValue())) {
                $this->mErrorMsg =  $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->get($keys);
            return $this->_insert($object,  $this->__l('Edit'));
        }
        
        function _insert(&$object, $caption) {
            if (class_exists('XoopsMultiTokenHandler') && !XoopsMultiTokenHandler::quickValidate($this->mName.'_'.$this->mOp)) {
                if (is_object($object)) {
                    return $this->_showForm($object, $caption, $this->__e('Token Error'));
                } else {
                    $this->mErrorMsg = $this->__e('Token Error');
                    return NBFRAME_ACTION_ERROR;
                }
            }
            if (is_object($object)) {
                $this->mObject =& $object;
                if (is_object($this->mObjectForm)) {
                    $this->mObjectForm->bindAction($this, 1);
                    $this->mObjectForm->prepare();
                    $this->mObjectForm->setupRequests('POST');
                    if ($this->mObjectForm->canVerify()) {
                        $object->enableVerify();
                    }
                }
                if ($this->mRequest->hasError()) {
                    return $this->_showForm($object, $caption, $this->mRequest->getErrors());
                }
                
                $object->SetRequestVars($this->mRequest);
                
                if (!$object->checkGroupPerm('write', $this->mBypassAdminCheck)) {
                    $this->mErrorMsg = $this->__e('Permission Error');
                    return NBFRAME_ACTION_ERROR;
                }
                if ($this->mObjectHandler->insert($object,false,true)) {
                    return NBFRAME_ACTION_SUCCESS;
                } else {
                    return $this->_showForm($object, $caption, $this->mObjectHandler->getErrors());
                }
            } else {
                $this->mErrorMsg = $this->__e('No Record is found');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeDeleteOp() {
            if (!($keys = $this->_requestKeyValue('GET'))) {
                $this->mErrorMsg =  $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->get($keys);
            if (!is_object($object)) {
                $this->mErrorMsg = $this->__e('No Record is found');
                return NBFRAME_ACTION_ERROR;
            }
            if (!$object->checkGroupPerm('write', $this->mBypassAdminCheck)) {
                $this->mErrorMsg = $this->__e('Permission Error');
                return NBFRAME_ACTION_ERROR;
            }
            $this->mObject =& $object;
            $this->mConfirmParam = $this->mObject->getKey('s', true);
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }

        function executeDeleteokOp() {
           if (class_exists('XoopsMultiTokenHandler') && !XoopsMultiTokenHandler::quickValidate(XOOPS_TOKEN_DEFAULT)) {
                $this->mErrorMsg = $this->__e('Token Error');
                return NBFRAME_ACTION_ERROR;
            }
            if (!($keys = $this->_requestKeyValue())) {
                $this->mErrorMsg =  $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->get($keys);
            if (!is_object($object)) {                $this->mErrorMsg = $this->__e('No Record is found');
                return NBFRAME_ACTION_ERROR;
            }
            if (!$object->checkGroupPerm('write', $this->mBypassAdminCheck)) {
                $this->mErrorMsg = $this->__e('Permission Error');
                return NBFRAME_ACTION_ERROR;
            }
            if ($this->mObjectHandler->delete($object)) {
                return NBFRAME_ACTION_SUCCESS;
            } else {
                $this->mErrorMsg = $this->__e('Record Delete Error');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeListOp() {
            $start = isset($_GET['list_start']) ? intval($_GET['list_start']) : 0;
            if (strtolower($this->mListOrderDefault) == 'asc') {
                $order = (isset($_GET['list_order'])&& $_GET['list_order']=='desc') ? 'desc' : 'asc';
            } else {
                $order = (isset($_GET['list_order'])&& $_GET['list_order']=='asc') ? 'asc' : 'desc';
            }
            $sort = isset($_GET['list_sort']) ? htmlspecialchars($_GET['list_sort'],ENT_QUOTES) : $this->mListSortDefault;
            $perpage = (!empty($_GET['list_perpage'])) ? intval($_GET['list_perpage']) : $this->mListPerPageDefault;
            if ($this->mListFilterCriteria) {
                $criteria =& $this->mListFilterCriteria;
            } else {
                $criteria =& new Criteria(1, intNBCriteriaVal(1));
            }
            $criteria->setStart($start);
            $criteria->setLimit($perpage);
            $criteria->setSort($sort);
            $criteria->setOrder($order);
            
            $this->mListStart = $start;
            $this->mListSort = $sort;
            $this->mListOrder = $order;
            $this->mListPerPage = $perpage;
            
            $this->mObjectArr =& $this->getListObjects($criteria, $this->mListFilterJoinDef, $this->mListFilterHaving);
//            echo $this->mObjectHandler->getLastSQL()."<br>";
            $this->mObjectAllCount = $this->getListCount($criteria, $this->mListFilterJoinDef, $this->mListFilterHaving);
//            echo $this->mObjectHandler->getLastSQL()."<br>"; echo $this->mObjectAllCount;
            return NBFRAME_ACTION_VIEW_DEFAULT;
            break;
        }

        function &getListObjects($criteria, $joindef=false, $having='') {
            if ($joindef) {
                $this->mObjectHandler->setAlias('main');
                $objects =& $this->mObjectHandler->getObjects($criteria, false, 'main.*', true, $joindef, $having);
            } else {
                $objects =& $this->mObjectHandler->getObjects($criteria, false, '*', true, $joindef, $having);
            }
            return $objects;
        }

        function &getListCount($criteria, $joindef=false, $having='') {
            if ($joindef) {
                $this->mObjectHandler->setAlias('main');
            }
            $count = $this->mObjectHandler->getCount($criteria, true, $joindef, $having);
            return $count;
        }

        function executeViewOp() {
            if (!($keys = $this->_requestKeyValue('GET'))) {
                $this->mErrorMsg =  $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
            $object =& $this->mObjectHandler->get($keys);
            if (!$object->checkGroupPerm('read', $this->mBypassAdminCheck)) {
                $this->mErrorMsg = $this->__e('Permission Error');
                return NBFRAME_ACTION_ERROR;
            }
            if (is_object($object)) {
                $this->mObject =& $object;
                return NBFRAME_ACTION_VIEW_DEFAULT;
            }
            $this->mErrorMsg = $this->__e('No Record is found');
            return NBFRAME_ACTION_ERROR;
        }

        function preViewFormOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mFormTemplate);
            }
        }

        function viewFormOp() {
            if (empty($this->mObjectForm)) {
                $this->setObjectForm('dummyForm');
            }

            if (is_object($this->mObjectForm)) {
                $this->mObjectForm->bindAction($this, 1);
                $this->mObjectForm->prepare();

                $xoopsForm =& $this->mObjectForm->buildEditForm($this->mObject);
                if ($this->mRender->mTemplate) {
                    $xoopsForm->assign($this->mXoopsTpl);
                    $this->mXoopsTpl->assign('title', $this->mCaption);
                    $this->mXoopsTpl->assign('formhtml', $xoopsForm->render());
                    $this->mXoopsTpl->assign('errmsg', $this->mErrorMsg);
                } else {
                    echo $xoopsForm->render();
                }
            }
        }

        function preViewListOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mListTemplate);
            }
        }
        
        function viewListOp() {
            if (empty($this->mObjectList)) {
                $this->setObjectList('dummyList');
            }
            if (is_object($this->mObjectList)) {
                $this->mObjectList->bindAction($this);
                $this->mObjectList->prepare();

                $this->mObjectList->buildList($this->mObjectArr, $this->mListSort, $this->mListOrder);
                $this->_getPageNav();
                if ($this->mRender->mTemplate) {
                    $this->mXoopsTpl->assign('title',$this->mCaption.' &raquo; '.$this->__l('List'));
                    $this->mXoopsTpl->assign('headers',$this->mObjectList->mListHeaders);
                    $this->mXoopsTpl->assign('records',$this->mObjectList->mListRecords);
                    $this->mXoopsTpl->assign('lang', array('new'=>$this->__l('New')));
                    $this->mXoopsTpl->assign('newlink', $this->getUrl(array('op'=>'new')));
                    $this->mXoopsTpl->assign('pagenav', $this->mPageNav->renderNav());
                } else {
                    $headers = $this->mObjectList->mListHeaders;
                    $records = $this->mObjectList->mListRecords;
                    $pagenav = $this->mPageNav->renderNav();
                    include NBFRAME_BASE_DIR.'/templates/NBFrameList.tpl.php';
                }
            }
        }

        function _getPageNav() {
            $paramArray = array_merge(array('op'=>$this->mOp), $this->mListNaviExtra);
            if ($this->mListSort != $this->mListSortDefault) {
                $paramArray['list_sort'] = $this->mListSort;
            }
            if ($this->mListOrder != $this->mListOrderDefault) {
                $paramArray['list_order'] = $this->mListOrder;
            }
            if ($this->mListPerPage != $this->mListPerPageDefault) {
                $paramArray['list_perpage'] = $this->mListPerPage;
            }
            NBFrame::using('PageNav');
            $this->mPageNav =& new NBFramePageNav($this->mObjectAllCount, $this->mListPerPage, $this->mListStart, 'list_start', $this->getUrl($paramArray));
        }

        function preViewViewOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mViewTemplate);
            }
        }

        function viewViewOp() {
        }

        function preViewDeleteOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mFormTemplate);
            }
        }

        function viewDeleteOp() {
            if ($this->mRender->mTemplate) {
                ob_start();
            }
            $key = $this->mObject->getKey('s', true);
            xoops_confirm(array_merge(array('op'=>'deleteok'),$this->mConfirmParam), $this->getUrl(), $this->__l("Delete this Record? [ID=%d]",implode(',',$key)));
        
            if ($this->mRender->mTemplate) {
                $this->mXoopsTpl->assign('formhtml',ob_get_contents());
                ob_end_clean();
                $this->mXoopsTpl->assign('title',$this->mCaption.' &raquo; '.$this->__l('Delete'));
            }
        }

        function _requestKeyValue($method='POST') {
            $keyArray = array();
            foreach($this->mObjectKeyFields as $key) {
                $attrib = $this->mObjectHandler->getKeyAttrib($key);
                if ($attrib == XOBJ_DTYPE_INT) {
                    $this->mRequest->defParam($key, $method, 'int', NBFRAME_NO_DEFAULT_PARAM, true);
                } else if ($attrib == XOBJ_DTYPE_FLOAT) {
                    $this->mRequest->defParam($key, $method, 'float', NBFRAME_NO_DEFAULT_PARAM, true);
                } else {
                    $this->mRequest->defParam($key, $method, 'var', NBFRAME_NO_DEFAULT_PARAM, true);
                }
                if ($this->mRequest->hasError()) {
                    return null;
                }
                $keyArray[$key] = $this->mRequest->getParam($key);
            }
            return $keyArray;
        }

    }
}
?>
