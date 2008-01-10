<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectForm')) {
    class NBFrameObjectForm {
        var $mEnvironment;
        var $mAction;
        var $mElements;
        var $mName;
        var $mCaption;
        var $mFormAction;
        var $mToken = 0;
        var $mDirName = '';
        var $mLanguage;
        var $mReqType = 'POST';
        var $mHiddenSysField = '';

        function NBFrameObjectForm($environment) {
            include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
            $this->mElements = array();
            $this->mEnvironment = $environment;
            $this->mLanguage =& NBFrame::getLanguageManager();
        }
        
        function prepare() {
        }

        function bindAction(&$action, $token=0) {
            $this->mAction =& $action;
            $this->mName = $action->mName;
            $this->mFormAction = $action->mUrl;
            $this->mDirName = $action->mDirName;
            $this->mCaption = $action->mCaption;
            $this->mToken = $token;
            if ($action->mHalfAutoForm || preg_match('/^NBFrameObjectForm$/i', get_class($this))) {
                NBFrame::using('TebleParser');
                $parser =& new NBFrameTebleParser($action->mObjectHandler->db);
                $parser->setFormElements($action->mObjectHandler->mTableName, $this);
            }
         }
        
        function addElement($name, &$formElement) {
            $this->mElements[$name] =& $formElement;
        }

        function addOptionArray($name,$options) {
            if (method_exists($this->mElements[$name], 'addOptionArray')) {
                $this->mElements[$name]->addOptionArray($options);
            }
        }

        function addHiddenSysFields() {
            $this->mHiddenSysField = '_NBsys_update_count_old';
            $this->addElement('_NBsys_update_count', new XoopsFormHidden($this->mHiddenSysField, 0));
        }

        function &buildEditForm(&$object) {
            if (!file_exists(XOOPS_ROOT_PATH.'/class/xoopsform/formtoken.php')) {
                $this->mToken=0;
            }

            $formEdit =& new XoopsThemeForm($this->mCaption, $this->mName, $this->mFormAction);
            foreach ($this->mElements as $key=>$formElement) {
                if (method_exists($formElement, 'setValue')) {
                    $formElement->setValue($object->getVar($key,'e'));
                } else if (is_a($formElement, 'XoopsFormLabel')) {
                    $formElement->_value = $object->getVar($key);
                }
                $formEdit->addElement($formElement,$object->vars[$key]['required']);
                unset($formElement);
            }
            if ($object->isNew()) {
                if ($this->mToken) {
                    $formEdit->addElement(new XoopsFormToken(XoopsMultiTokenHandler::quickCreate($this->mName.'_insert')));
                }
                $formEdit->addElement(new XoopsFormHidden('op','insert'));
            } else {
                if ($this->mToken) {
                    $formEdit->addElement(new XoopsFormToken(XoopsMultiTokenHandler::quickCreate($this->mName.'_save')));
                }
                $formEdit->addElement(new XoopsFormHidden('op','save'));
            }
            $formEdit->addElement(new XoopsFormButton('', 'submit', 'OK', 'submit'));

            return $formEdit;
        }

        function preInsert() {
        }

        function setupRequests($reqTypes='POST') {
            $this->preInsert();
            $this->mReqType = $reqTypes;
            foreach(array_keys($this->mElements) as $name) {
                if ($name == '_NBsys_update_count') $name = $this->mHiddenSysField;
                if (!$this->mAction->mRequest->defined($name)) {
                    $this->mAction->mRequest->defParam($name, $reqTypes, 'raw');
                }
            }
            $this->mAction->mRequest->parseRequest();
        }
        

        function defParam($name, $valType = '', $defaultValue = NBFRAME_NO_DEFAULT_PARAM, $mustExist = false) {
            $this->mAction->mRequest->defParam($name, $this->mReqType, $valType, $defaultValue, $mustExist);
        }

        function render(&$object) {
            $formEdit =& $this->buildEditForm($object);
            $str = $formEdit->render();
            return $str;
        }

        function __l($msg) {
            $args = func_get_args();
            return $this->mLanguage->__l($msg, $this->mLanguage->_getParams($args));
        }

        function __e($msg) {
            $args = func_get_args();
            return $this->mLanguage->__e($msg, $this->mLanguage->_getParams($args));
        }

    }
}
?>
