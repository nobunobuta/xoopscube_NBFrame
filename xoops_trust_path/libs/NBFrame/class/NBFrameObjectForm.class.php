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
        var $mEnableVerify = true;
        var $mVerifyFields = array();
        
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

        function addVerifyFields($name, $fieldName='') {
            if ($this->mEnableVerify) {
                if (empty($fieldName)) $fieldName = $name.'_old';
                $this->mVerifyFields[$name] = $fieldName;
                $this->addElement($name, new XoopsFormHidden($fieldName, 0));
            }
        }

        function canVerify() {
            if ($this->mEnableVerify && (count($this->mVerifyFields) >0)) {
                return true;
            } else {
                return false;
            }
        }

        function &buildEditForm(&$object) {
            if (!file_exists(XOOPS_ROOT_PATH.'/class/xoopsform/formtoken.php')) {
                $this->mToken=0;
            }
            NBFrame::using('XoopsForm');
            $formEdit =& new NBFrameXoopsForm($this->mCaption, $this->mName, $this->mFormAction);
            foreach ($this->mElements as $key=>$formElement) {
                if (method_exists($formElement, 'setValue')) {
                    $formElement->setValue($object->getVar($key,'e'));
                } else if (is_a($formElement, 'XoopsFormLabel')) {
                    $formElement->_value = $object->getVar($key,'s');
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
            $verifyFieldNames = array_keys($this->mVerifyFields);
            foreach(array_keys($this->mElements) as $name) {
                if (in_array($name, $verifyFieldNames)) $name = $this->mVerifyFields[$name];
                if (!$this->mAction->mRequest->defined($name)) {
                    $this->mAction->mRequest->defParam($name, $reqTypes, 'raw');
                }
            }
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
