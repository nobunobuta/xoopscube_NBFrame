<?php
/**
 * Generic Table Manupulation XoopsObject class
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if(!class_exists('NBFrameObject')) {
    require_once XOOPS_ROOT_PATH.'/kernel/object.php';

    if (!defined('XOBJ_DTYPE_FLOAT')) define('XOBJ_DTYPE_FLOAT', 101);
    if (!defined('XOBJ_DTYPE_CUSTOM')) define('XOBJ_DTYPE_CUSTOM', 102);
    if (!defined('XOBJ_DTYPE_BOOL')) define('XOBJ_DTYPE_BOOL', 103);

    if (!defined('XOBJ_VCLASS_TFIELD')) define('XOBJ_VCLASS_TFIELD', 1);
    if (!defined('XOBJ_VCLASS_ATTRIB')) define('XOBJ_VCLASS_ATTRIB', 2);
    if (!defined('XOBJ_VCLASS_EXTRA')) define('XOBJ_VCLASS_EXTRA', 3);
    if (!defined('XOBJ_VCLASS_SFIELD')) define('XOBJ_VCLASS_SFIELD', 4);

    if (!defined('XOBJ_DTYPE_STRING')) define('XOBJ_DTYPE_STRING', 1);
    if (!defined('XOBJ_DTYPE_TEXT')) define('XOBJ_DTYPE_TEXT', 2);

    class NBFrameObject  extends XoopsObject
    {
        var $mExtraVars = array();

        var $mHandler;
        var $mClassName;
        var $mUseSystemField = false;
        var $mVerifier = array();
        var $mGroupPermAttrib = array();

        function NBFrameObject() {
            //親クラスのコンストラクタ呼出
            $this->XoopsObject();
            $this->mHandler = null;
            $this->mClassName = get_class($this);
        }
        
        function prepare() {
        }
        
        function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '') {
            parent::initVar($key, $data_type, $value, $required, $maxlength, $options);
            $this->vars[$key]['var_class'] = XOBJ_VCLASS_TFIELD;
        }

        function initSysVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '') {
            parent::initVar($key, $data_type, $value, $required, $maxlength, $options);
            $this->vars[$key]['var_class'] = XOBJ_VCLASS_SFIELD;
        }

        function setVerify($verifyField, $verifyInput) {
            if (isset($this->vars[$verifyField])) {
                $this->mVerifier[$verifyField] = $verifyInput;
            }
        }

        function setGroupPermAttrib($perm, $prefix='perm_') {
            $this->setAttribute($prefix.$perm, array(), XOBJ_DTYPE_CUSTOM);
            $this->mGroupPermAttrib[$prefix.$perm] = $perm;
        }

        function enableVerify() {
            foreach($this->mVerifier as $key=>$field) {
                if (isset($this->vars[$key])) {
                    $type = $this->vars[$key]['data_type'];
                    $this->setAttribute($field, null, $type);
                }
            }
        }

        function initSysFields() {
            if ($this->mUseSystemField == false) {
                $this->initSysVar('_NBsys_del_flag', XOBJ_DTYPE_CUSTOM); 
                $this->initSysVar('_NBsys_create_time', XOBJ_DTYPE_CUSTOM); 
                $this->initSysVar('_NBsys_create_user', XOBJ_DTYPE_CUSTOM); 
                $this->initSysVar('_NBsys_update_time', XOBJ_DTYPE_CUSTOM); 
                $this->initSysVar('_NBsys_update_user', XOBJ_DTYPE_CUSTOM); 
                $this->initSysVar('_NBsys_update_count', XOBJ_DTYPE_INT); 
                $this->setVerify('_NBsys_update_count','_NBsys_update_count_old');
                $this->mUseSystemField = true;
            }
        }

        function setAttribute($key, $value, $data_type=XOBJ_DTYPE_OTHER) {
            $this->vars[$key] = array('value' => $value, 'required' => false, 'data_type' => $data_type, 'maxlength' => null, 'changed' => false, 'options' => '');
            $this->vars[$key]['var_class'] = XOBJ_VCLASS_ATTRIB;
        }

        function setVarType($key, $data_type) {
            if (isset($this->vars[$key])) {
                $this->vars[$key]['data_type'] = $data_type;
            }
        }
        
        function setVarRequired($key, $required) {
            if (isset($this->vars[$key])) {
                $this->vars[$key]['required'] = $required;
            }
        }
        
        function setVarMaxLength($key, $maxlength) {
            if (isset($this->vars[$key])) {
                $this->vars[$key]['maxlength'] = $maxlength;
            }
        }
        
        function setVarOptions($key, $options) {
            if (isset($this->vars[$key])) {
                $this->vars[$key]['options'] = $options;
            }
        }
        
        function varsDefined() {
            if (count($this->vars)==0) return false;
            foreach($this->vars as $var) {
                if ($var['var_class'] == XOBJ_VCLASS_TFIELD) {
                    return true;
                }
            }
            return false;
        }

        function setKeyFields($keys) {
            $this->mHandler->setKeyFields($keys);
        }

        function getKeyFields() {
            return $this->mHandler->getKeyFields();
        }

        function isKey($field) {
            return $this->mHandler->getKeyFields($field);
        }

        function cacheKey() {
            $recordKeys = $this->mHandler->getKeyFields();
            $cacheKey = array();
            foreach ($recordKeys as $key) {
                $cacheKey[$key] = $this->get($key);
            }
            return serialize($cacheKey);
        }

        function getKey($format = 's') {
            $recordKeys = $this->mHandler->getKeyFields();
            if (!array($recordKeys)) {
                return false;
            } else {
                return $this->getVar($recordKeys[0], $format);;
            }
        }

        //AUTO_INCREMENT属性のフィールドはテーブルに一つしかない前提
        function setAutoIncrementField($fieldName) {
            $this->mHandler->setAutoIncrementField($fieldName);
        }

        function &getAutoIncrementField() {
            return $this->mHandler->getAutoIncrementField;
        }

        function isAutoIncrement($fieldName) {
            return $this->mHandler->isAutoIncrement($fieldName);
        }

        function setNameField($fieldname) {
            $this->mHandler->setNameField($fieldname);
        }

        function getNameField() {
            return $this->mHandler->getNameField();
        }

        function getName($format = 's') {
            $nameField = $this->mHandler->getNameField();
            if ($nameField) {
                return $this->getVar($nameField, $format);
            } else {
                return false;
            }
        }

        function resetChenged() {
            foreach($this->vars as $k=>$v) {
                $this->vars[$k]['changed'] = false;
            }
        }

        function assignVar($key, $value) {
            if (isset($value) && isset($this->vars[$key])) {
                $this->vars[$key]['value'] =& $value;
            } else {
                $this->setExtraVar($key, $value);
            }
        }

        function &getExtraVar($key) {
            return $this->mExtraVars[$key];
        }

        function setExtraVar($key, $value) {
            $this->mExtraVars[$key] =& $value;
        }

        /**
        * assign a value to a variable
        *
        * @access public
        * @param string $key name of the variable to assign
        * @param mixed $value value to assign
        * @param bool $not_gpc
        */
        function setVar($key, $value, $not_gpc = false) {
            if (!empty($key) && isset($this->vars[$key])) {
                if (($this->vars[$key]['data_type'] == XOBJ_DTYPE_CUSTOM)) {
                    //個別の変数Setがあれば実行;
                    $setMethod = 'setVar_'.$key;
                    if(method_exists($this, $setMethod)) {
                        $this->$setMethod($value);
                        $this->vars[$key]['not_gpc'] = $not_gpc;
                        $this->setDirty();
                        return;
                    }
                }
                $this->vars[$key]['value'] = $value;
                $this->vars[$key]['not_gpc'] = $not_gpc;
                $this->vars[$key]['changed'] = true;
                $this->setDirty();
            }
        }
        
        function setVarAsSQLFunc($key, $func, $param) {
            if (!empty($key) && isset($func) && isset($param) && isset($this->vars[$key])) {
                $this->vars[$key]['func'] = $func;
                $this->vars[$key]['value'] =& $param;
                $this->vars[$key]['not_gpc'] = $not_gpc;
                $this->vars[$key]['changed'] = true;
                $this->setDirty();
            }
        }

        function SetRequestVars(&$request) {
            $params = $request->getParam();
            foreach($params as $key =>$value) {
                $this->setVar($key, $value, true);
            }
        }
	    /**
        * returns a specific variable for the object in a proper format
        *
        * @access public
        * @param string $key key of the object's variable to be returned
        * @param string $format format to use for the output
        * @return mixed formatted value of the variable
        */
        function &getVar($key, $format = 's') {
            if (($this->vars[$key]['data_type'] == XOBJ_DTYPE_CUSTOM)) {
                //個別の変数Getがあれば実行;
                $getMethod = 'getVar_'.$key;
                if(method_exists($this, $getMethod)) {
                    $ret =& $this->$getMethod($this->vars[$key]['value'],$format);
                } else if (in_array($key, array_keys($this->mGroupPermAttrib))) {
                    $groupPermHandler =& NBFrame::getHandler('NBFrame.xoops.GroupPerm', NBFrame::null());
                    $ret = $groupPermHandler->getGroupIdsByObjectKey($this->mGroupPermAttrib[$key], $this);
                } else {
                    $this->vars[$key]['data_type'] = XOBJ_DTYPE_TXTBOX;
                    $ret =& parent::getVar($key, $format);
                    $this->vars[$key]['data_type'] = XOBJ_DTYPE_CUSTOM;
                }
            } else {
                $ret =& parent::getVar($key, $format);
            }
            if ($this->vars[$key]['data_type'] == XOBJ_DTYPE_TXTAREA && ($format=='e' || $format=='edit')) {
                $ret = preg_replace("/&amp;(#[0-9]+;)/i", '&$1', $ret);
            }
            return $ret;
        }

        function checkGroupPerm($mode, $bypassAdminCheck=false) {
            return true;
        }

        function checkRight($perm, $bypassAdminCheck=false) {
            $groupPermHandler =& NBFrame::getHandler('NBFrame.xoops.GroupPerm', NBFrame::null());
            return $groupPermHandler->checkRightByObjectKey($perm, $this, $bypassAdminCheck);
        }

        function cleanVars() {
            $iret =parent::cleanVars();
            foreach ($this->vars as $k => $v) {
                $cleanv = $v['value'];
                if (!$v['changed']) {
                } else {
                    $cleanv = is_string($cleanv) ? trim($cleanv) : $cleanv;
                    if (isset($v['func'])) {
                        $this->cleanVars[$k] =& $cleanv;
                    } else {
                        switch ($v['data_type']) {
                            case XOBJ_DTYPE_FLOAT:
                                $cleanv = (float)($cleanv);
                                $this->cleanVars[$k] =& $cleanv;
                                break;
                            default:
                                break;
                        }
                        //個別の変数チェックがあれば実行;
                        $checkMethod = 'checkVar_'.$k;
                        if(method_exists($this, $checkMethod)) {
                            $this->$checkMethod($cleanv);
                        }
                    }
                }
                unset($cleanv);
            }
            if (count($this->_errors) > 0) {
                return false;
            }
            $this->unsetDirty();
            return true;
        }

        function &getVarArray($type='s') {
            $varArray=array();
            foreach ($this->vars as $k => $v) {
                $varArray[$k]=$this->getVar($k,$type);
            }
            return $varArray;
        }

		function getShow($key) {
			return $this->getVar($key, 's');
		}
		
		/**
		 * Sets $value to $key property. This method calls setVar(), but make
		 * not_gpc true for the compatibility with XoopsSimpleObject.
		 * @param string $key
		 * @param mixed $value
		 */
		function set($key, $value) {
			$this->setVar($key, $value, true);
		}
	
		function get($key) {
			return $this->vars[$key]['value'];
		}

        function &exportObject() {
            $wp_object = (object) null;
            foreach ($this->vars as $k => $v) {
                $wp_object->$k = $v['value'];
            }
            foreach ($this->mExtraVars as $k => $v) {
                $wp_object->$k = $v;
            }
            return $wp_object;
        }

        function importObject(&$wp_object) {
            foreach ($this->vars as $k => $v) {
                $this->setVar($k, $wp_object->$k, true);
            }
        }
    }
}
?>
