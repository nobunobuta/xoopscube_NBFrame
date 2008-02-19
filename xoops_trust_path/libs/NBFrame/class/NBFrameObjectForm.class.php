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
    NBFrame::using('Base');
    class NBFrameObjectForm extends NBFrameBase {
        var $mAction;
        var $mElements;
        var $mName;
        var $mCaption;
        var $mFormAction;
        var $mToken = 0;
        var $mDirName = '';
        var $mReqType = 'POST';
        var $mEnableVerify = true;
        var $mVerifyFields = array();
        
        function NBFrameObjectForm(&$environment) {
            parent::NBFrameBase($environment);

            include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
            $this->mElements = array();
        }
        
        function prepare() {
        }

        function bindAction(&$action, $token=0) {
            $this->mAction =& $action;
            $this->mName = $action->mName;
            $this->mFormAction = $action->getUrl();
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

        function addObjectOptionArray($name, $objectClass) {
            $objectHandler =& NBFrame::getHandler($objectClass, $this->mEnvironment);
            $this->addOptionArray($name, $objectHandler->getSelectOptionArray());
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
                if (is_a($formElement, 'XoopsFormDateTime')) {
                    $value = intval($object->getVar($key));
                    foreach($formElement->getElements() as $subElement) {
                        if (is_a($subElement, 'XoopsFormTextDateSelect')) {
                            $subElement->setValue($value);
                        } else {
                            $datetime = getdate($value);
                            $timevalue=$datetime['hours'] * 3600 + 600 * ceil($datetime['minutes'] / 10);
                            $subElement->_value = array();
                            $subElement->setValue($timevalue);
                        }
                    }
                } else if (method_exists($formElement, 'setValue')) {
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
                    if (isset($this->mElements[$name])) {
                        if (is_a($this->mElements[$name], 'XoopsFormDateTime')) {
                            $this->defParam($name, 'array-datetime');
                        } else if (is_a($this->mElements[$name], 'XoopsFormSelectGroup') && $this->mElements[$name]->isMultiple()) {
                            $this->defParam($name, 'array-int',  array());
                        } else {
                            $this->defParam($name, 'raw');
                        }
                    } else {
                        $this->defParam($name, 'raw');
                    }
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
    }
}
?>
