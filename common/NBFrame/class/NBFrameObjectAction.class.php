<?php
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
        var $mListPerPage = 30;
        /**
         * @var int
         */
        var $mListStart;
        /**
         * @var string
         */
        var $mListSort;
        /**
         * @var string
         */
        var $mListOrder;
        /**
         * @var string
         */
        var $mViewTemplate;
        /**
         * @var string
         */
        var $mObjectKeyField;

        var $mAttributes;
        
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
        }

        function setObjectKeyField() {
            $object =& $this->mObjectHandler->create();
            $objectKey = $object->getKeyFields();
            $this->mObjectKeyField = $objectKey[0];
        }
        
        function setObjectForm($className) {
            $this->mObjectForm =& NBFrame::getinstance($className, $this->mEnvironment, 'Form');
            if (!$this->mObjectForm) {
                NBFrame::using('ObjectForm');
                $this->mObjectForm =& New NBFrameObjectForm($this->mEnvironment);
                NBFrame::using('TebleParser');
                $parser = new NBFrameTebleParser($this->mObjectHandler->db);
                $parser->setFormElements($this->mObjectHandler->tableName, $this->mObjectForm);
            }
        }

        function setObjectList($className) {
            $this->mObjectList =& NBFrame::getinstance($className, $this->mEnvironment, 'List');
            if (!$this->mObjectList) {
                NBFrame::using('ObjectList');
                $this->mObjectList =& New NBFrameObjectList($this->mEnvironment);
                NBFrame::using('TebleParser');
                $parser = new NBFrameTebleParser($this->mObjectHandler->db);
                $parser->setListElements($this->mObjectHandler->tableName, $this->mObjectList);
            }
            $this->mObjectList->bindAction($this);
            $this->mObjectList->prepare();
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
            $object =& $this->mObjectHandler->create();
            $object->setFormVars($_POST,'');
            return $this->_showForm($object, $this->__l('New'));
        }

        function executeEditOp() {
            if (isset($_GET[$this->mObjectKeyField])) {                $object =& $this->mObjectHandler->get(intval($_GET[$this->mObjectKeyField]));
                return $this->_showForm($object, $this->__l('Edit'));
            } else {
                $this->mErrorMsg = $this->__e('Invalid Request');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function _showForm(&$object, $caption, $errmsg='') {
            if (is_object($object)) {
                if (!$object->checkGroupPerm('write')) {
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
            $object =& $this->mObjectHandler->get(intval($_POST[$this->mObjectKeyField]));
            return $this->_insert($object,  $this->__l('Edit'));
        }
        
        function _insert(&$object, $caption) {
            if (class_exists('XoopsMultiTokenHandler') && !XoopsMultiTokenHandler::quickValidate($this->mName.'_'.$this->mOp)) {
                if (is_object($object)) {
                    $this->_showForm($object, $caption, $this->__e('Token Error'));
                } else {
                    $this->mErrorMsg = $this->__e('Token Error');
                    return NBFRAME_ACTION_ERROR;
                }
            }
            if (is_object($object)) {
                $this->mObject =& $object;
                $object->setFormVars($_POST,'');
                if (!$object->checkGroupPerm('write')) {
                    $this->mErrorMsg = $this->__e('Permission Error');
                    return NBFRAME_ACTION_ERROR;
                }
                if ($this->mObjectHandler->insert($object,false,true)) {
                    return NBFRAME_ACTION_SUCCESS;
                } else {
                    $this->_showForm($object, $caption, $this->mObjectHandler->getErrors());
                    $this->mExtraShowMethod = 'FormOp';
                    return NBFRAME_ACTION_VIEW_EXTRA;
                }
            } else {
                $this->mErrorMsg = $this->__e('No Record is found');
                return NBFRAME_ACTION_ERROR;
            }
        }
        
        function executeDeleteOp() {
            if (isset($_GET[$this->mObjectKeyField])) {
                $key = intval($_GET[$this->mObjectKeyField]);                $object =& $this->mObjectHandler->get($key);
                if (!$object->checkGroupPerm('write')) {
                    $this->mErrorMsg = $this->__e('Permission Error');
                    return NBFRAME_ACTION_ERROR;
                }
                if (is_object($object)) {
                    return NBFRAME_ACTION_VIEW_DEFAULT;
                }
            }
            $this->mErrorMsg = $this->__e('No Record is found');
            return NBFRAME_ACTION_ERROR;
        }

        function executeDeleteokOp() {
           if (class_exists('XoopsMultiTokenHandler') && !XoopsMultiTokenHandler::quickValidate(XOOPS_TOKEN_DEFAULT)) {
                $this->mErrorMsg = $this->__e('Token Error');
                return NBFRAME_ACTION_ERROR;
            }
            if (isset($_POST[$this->mObjectKeyField])) {                $key = intval($_POST[$this->mObjectKeyField]);                $object =& $this->mObjectHandler->get($key);
            } else {
                $object = false;
            }
            if (is_object($object)) {                if (!$object->checkGroupPerm('write')) {
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
            $this->mErrorMsg = $this->__e('No Record is found');
            return NBFRAME_ACTION_ERROR;
        }

        function executeListOp() {
            $perpage = 30;
            $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
            $order = (isset($_GET['order'])&& $_GET['order']=='desc') ? 'desc' : 'asc';
            $sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort'],ENT_QUOTES) : $this->mObjectKeyField;
            if (!$this->mObjectList->inKey($sort)) $sort = $this->mObjectKeyField;
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
            
            $this->mObjectArr =& $this->mObjectHandler->getObjects($criteria);
            $this->mObjectAllCount = $this->mObjectHandler->getCount($criteria);
            return NBFRAME_ACTION_VIEW_DEFAULT;
            break;
        }

        function executeViewOp() {
            if (isset($_GET[$this->mObjectKeyField])) {                if ($object =& $this->mObjectHandler->get(intval($_GET[$this->mObjectKeyField]))) {
                    if (!$object->checkGroupPerm('read')) {
                        $this->mErrorMsg = $this->__e('Permission Error');
                        return NBFRAME_ACTION_ERROR;
                    }
                    $this->mObject =& $object;
                    return NBFRAME_ACTION_VIEW_DEFAULT;
                }
            }
            $this->mErrorMsg = $this->__e('No Record is found');
            return NBFRAME_ACTION_ERROR;
        }


        function preViewFormOp() {
            $this->mCurrentTemplate = $this->mFormTemplate;
        }
        function viewFormOp() {
            if (is_object($this->mObjectForm)) {
                $this->mObjectForm->bindAction($this, 1);
                $this->mObjectForm->prepare();

                $xoopForm =& $this->mObjectForm->buildEditForm($this->mObject);
                $xoopForm->assign($this->mXoopsTpl);
                
                $this->mXoopsTpl->assign('title', $this->mCaption);
                $this->mXoopsTpl->assign('formhtml', $xoopForm->render());
                $this->mXoopsTpl->assign('errmsg', $this->mErrorMsg);
            }
        }

        function preViewListOp() {
            $this->mCurrentTemplate = $this->mListTemplate;
        }
        
        function viewListOp() {
            if (is_object($this->mObjectList)) {
                $this->mObjectList->buildList($this->mObjectArr, $this->mListSort, $this->mListOrder);
                $this->_getPageNav();
                
                $this->mXoopsTpl->assign('title',$this->mCaption.' &raquo; '.$this->__l('List'));
                $this->mXoopsTpl->assign('headers',$this->mObjectList->mListHeaders);
                $this->mXoopsTpl->assign('records',$this->mObjectList->mListRecords);
                $this->mXoopsTpl->assign('lang', array('new'=>$this->__l('New')));
                $this->mXoopsTpl->assign('newlink', $this->addUrlParam('op=new'));
                $this->mXoopsTpl->assign('pagenav', $this->mPageNav->renderNav());
            }
        }

        function _getPageNav() {
            require_once XOOPS_ROOT_PATH.'/class/pagenav.php';
            $extra = 'sort='.$this->mListSort.'&amp;order='.$this->mListOrder;
            if (!empty($this->mActionName)) {
                $extra .= '&action='.$this->mActionName;
            }
            $this->mPageNav =& new XoopsPageNav($this->mObjectAllCount, $this->mListPerPage, $this->mListStart, 'start', $extra);
        }

        function preViewViewOp() {
            $this->mCurrentTemplate = $this->mViewTemplate;
        }

        function viewViewOp() {
        }

        function preViewDeleteOp() {
            $this->mCurrentTemplate = $this->mFormTemplate;
        }

        function viewDeleteOp() {
            ob_start();
            $key = intval($_GET[$this->mObjectKeyField]);            xoops_confirm(array('op'=>'deleteok',$this->mObjectKeyField=>$key), $this->mUrl, $this->__l("Delete this Record? [ID=%d]",$key));
            $this->mXoopsTpl->assign('formhtml',ob_get_contents());
            ob_end_clean();
            $this->mXoopsTpl->assign('title',$this->mCaption.' &raquo; '.$this->__l('Delete'));
        }

    }
}
?>
