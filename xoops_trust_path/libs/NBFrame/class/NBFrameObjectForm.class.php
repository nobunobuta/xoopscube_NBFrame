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

    class NBFrameObjectFormElements {
        var $mElements;
        var $mCaption;
        var $mDelimiter;
        var $mName;

        function NBFrameObjectFormElements($caption='', $delimiter="&nbsp;", $name="") {
            $this->mElements = array();
            $this->mCaption = $caption;
            $this->mDelimiter = $delimiter;
            $this->mName = $name;
        }
        
        function addElement($name, &$formElement, $before=null) {
            $this->mElements[$name] =& $formElement;
            if ($before && isset($this->mElements[$before])) {
                $this->moveBefore($name, $before);
            }
        }

        function replaceElement($name, &$formElement, $before=null) {
            if (isset($this->mElements[$name])) {
                $oldElement =& $this->mElements[$name];
                $this->mElements[$name] =& $formElement;
                unset($oldElement);
                if ($before && isset($this->mElements[$before])) {
                    $this->moveBefore($name, $before);
                }
            }
        }

        function removeElement($name) {
            if (isset($this->mElements[$name])) {
                unset($this->mElements[$name]);
            }
        }

        function moveBefore($name, $target) {
            $newArray = array();
            if (isset($this->mElements[$name])&&isset($this->mElements[$target])) {
                foreach($this->mElements as $key=>$element) {
                    if ($key == $target) {
                        $newArray[$name] =& $this->mElements[$name];
                    }
                    if ($key != $name) {
                        $newArray[$key] =& $this->mElements[$key];
                    }
                }
            }
            $this->mElements = $newArray;
        }

        function moveAfter($name, $target) {
            $newArray = array();
            if (isset($this->mElements[$name])&&isset($this->mElements[$target])) {
                foreach($this->mElements as $key=>$element) {
                    if ($key != $name) {
                        $newArray[$key] =& $this->mElements[$key];
                    }
                    if ($key == $target) {
                        $newArray[$name] =& $this->mElements[$name];
                    }
                }
            }
            $this->mElements = $newArray;
        }

        function moveInto($name, $target) {
            if (isset($this->mElements[$name]) && isset($this->mElements[$target]) && is_a($this->mElements[$target], 'NBFrameObjectFormElements')) {
                $this->mElements[$target]->addElement($name, $this->mElements[$name]);
                unset($this->mElements[$name]);
            }
        }

        function addOptionArray($name,$options) {
            if (method_exists($this->mElements[$name], 'addOptionArray')) {
                $this->mElements[$name]->addOptionArray($options);
            }
        }

        function _setFormElementValue($key, &$formElement, &$object) {
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
        }

        function build(&$container, &$object) {
            foreach ($this->mElements as $key=>$formElement) {
                $this->_setFormElementValue($key, $formElement, $object);
                if (is_a($formElement, 'XoopsFormElement')) {
                    $container->addElement($formElement, $object->vars[$key]['required']);
                } else if (is_a($formElement, 'NBFrameObjectFormElements')) {
                    $elementTray =& new XoopsFormElementTray($formElement->mCaption, $formElement->mDelimiter, $formElement->mName);
                    $formElement->build($elementTray, $object);
                    $container->addElement($elementTray);
                }
                unset($formElement);
            }
        }

        function setupRequests(&$request, $reqTypes, $verifyFields, $doPreInsert) {
            $verifyFieldNames = array_keys($verifyFields);
            foreach(array_keys($this->mElements) as $name) {
                if (in_array($name, $verifyFieldNames)) $name = $verifyFields[$name];
                if (!$request->defined($name)) {
                    if (isset($this->mElements[$name])) {
                        if (is_a($this->mElements[$name], 'NBFrameFormDateTime')) {
                            $request->defParam($name, $reqTypes, 'array-datetime');
                        } else if (is_a($this->mElements[$name], 'XoopsFormSelectGroup') && $this->mElements[$name]->isMultiple()) {
                            if ($doPreInsert) {
                                $request->defParam($name, $reqTypes, 'array-int',  array());
                            }
                        } else if (is_a($this->mElements[$name], 'NBFrameObjectFormElements')) {
                            $this->mElements[$name]->setupRequests($request, $reqTypes, $verifyFields, $doPreInsert);
                        } else {
                            $request->defParam($name, $reqTypes, 'raw');
                        }
                    } else {
                        $request->defParam($name, $reqTypes, 'raw');
                    }
                }
            }
        }
    }
    
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
            $this->mElements =& new NBFrameObjectFormElements();
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
        
        function addElement($name, &$formElement, $before=null) {
            $this->mElements->addElement($name, $formElement, $before);
        }

        function replaceElement($name, &$formElement, $before=null) {
            $this->mElements->replaceElement($name, $formElement, $before);
        }

        function removeElement($name) {
            $this->mElements->removeElement($name);
        }

        function moveBefore($name, $target) {
            $this->mElements->moveBefore($name, $target);
        }

        function moveAfter($name, $target) {
            $this->mElements->moveAfter($name, $target);
        }

        function moveInto($name, $target) {
            $this->mElements->moveInto($name, $target);
        }

        function addOptionArray($name,$options) {
            $this->mElements->addOptionArray($name,$options);
        }

        function addObjectOptionArray($name, $objectClass, $criteria=null, $gperm_mode='', $bypassAdminCheck=false) {
            $objectHandler =& NBFrame::getHandler($objectClass, $this->mEnvironment);
            $this->addOptionArray($name, $objectHandler->getSelectOptionArray($criteria, $gperm_mode, $bypassAdminCheck));
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
            NBFrame::using('xoopsform.ThemeForm');
            $formEdit =& new NBFrameThemeForm($this->mCaption, $this->mName, $this->mFormAction);
            $this->mElements->build($formEdit, $object);
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

        function setupRequests($reqTypes='POST', $doPreInsert=true) {
            if ($doPreInsert) {
                $this->preInsert();
            }
            $this->mReqType = $reqTypes;
            $this->mElements->setupRequests($this->mAction->mRequest,$reqTypes, $this->mVerifyFields, $doPreInsert);
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
