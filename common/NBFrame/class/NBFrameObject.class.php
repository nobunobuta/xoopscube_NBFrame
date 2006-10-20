<?php
if (!class_exists('NBFrame')) exit();
/**
* Generic Table Manupulation XoopsObject class
 *
 * @copyright copyright (c) 2004-2006 Kowa.ORG
 * @author Nobuki Kowa <Nobuki@Kowa.ORG>
 * @package NBFrameObject
 */
if(!class_exists('NBFrameObject')) {
    require_once XOOPS_ROOT_PATH.'/kernel/object.php';

    if (!defined('XOBJ_DTYPE_FLOAT')) define('XOBJ_DTYPE_FLOAT', 101);
    if (!defined('XOBJ_DTYPE_CUSTOM')) define('XOBJ_DTYPE_CUSTOM', 102);
    if (!defined('XOBJ_VCLASS_TFIELD')) define('XOBJ_VCLASS_TFIELD', 1);
    if (!defined('XOBJ_VCLASS_ATTRIB')) define('XOBJ_VCLASS_ATTRIB', 2);
    if (!defined('XOBJ_VCLASS_EXTRA')) define('XOBJ_VCLASS_EXTRA', 3);
    if (!defined('XOBJ_DTYPE_STRING')) define('XOBJ_DTYPE_STRING', 1);
    if (!defined('XOBJ_DTYPE_TEXT')) define('XOBJ_DTYPE_TEXT', 2);
    if (!defined('XOBJ_DTYPE_BOOL')) define('XOBJ_DTYPE_BOOL', 103);

    class NBFrameObject  extends XoopsObject
    {
        var $_extra_vars = array();
        var $_keys;
        var $_nameField;
        var $_autoIncrement;
        var $_listTableElements;
        var $_handler;
        var $_form;
        var $_list;
        var $_className;
        
        function NBFrameObject() {
            //親クラスのコンストラクタ呼出
            $this->XoopsObject();
            $this->_handler = null;
            $this->_className=get_class($this);
        }
        
        function prepare() {
        }
        
        function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '') {
            parent::initVar($key, $data_type, $value, $required, $maxlength, $options);
            $this->vars[$key]['var_class'] = XOBJ_VCLASS_TFIELD;
        }

        function setAttribute($key, $value, $data_type=XOBJ_DTYPE_OTHER) {
            $this->vars[$key] = array('value' => $value, 'required' => false, 'data_type' => $data_type, 'maxlength' => null, 'changed' => false, 'options' => '');
            $this->vars[$key]['var_class'] = XOBJ_VCLASS_ATTRIB;
        }

        function setKeyFields($keys) {
            $this->_keys = $keys;
        }

        function getKeyFields() {
            return $this->_keys;
        }

        function isKey($field) {
            return in_array($field,$this->_keys);
        }

        function cacheKey() {
            $recordKeys = $this->getKeyFields();
            $recordVars = $this->getVars();
            $cacheKey = array();
            foreach ($this->getKeyFields() as $k => $v) {
                $cacheKey[$v] = $this->getVar($v);
            }
            return(serialize($cacheKey));
        }

        function getKey($format = 's') {
            if (!array($this->_keys)) {
                return false;
            } else {
                return $this->getVar($this->_keys[0], $format);;
            }
        }

        //AUTO_INCREMENT属性のフィールドはテーブルに一つしかない前提
        function setAutoIncrementField($fieldName) {
            $this->_autoIncrement = $fieldName;
        }

        function &getAutoIncrementField() {
            return $this->_autoIncrement;
        }

        function isAutoIncrement($fieldName) {
            return ($fieldName == $this->_autoIncrement);
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
            return $this->_extra_vars[$key];
        }

        function setExtraVar($key, $value) {
            $this->_extra_vars[$key] =& $value;
        }

        function setNameField($fieldname) {
            $this->nameField = $fieldname;
        }

        function getName($format = 's') {
            if ($this->nameField) {
                return $this->getVar($this->nameField, $format);
            } else {
                return false;
            }
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
                    //個別の変数Getがあれば実行;
                    $setMethod = 'setVar_'.$key;
                    if(method_exists($this, $setMethod)) {
                        $this->$setMethod($value, $not_gpc);
                        $this->setDirty();
                    } else {
                        $this->vars[$key]['value'] = $value;
                        $this->vars[$key]['not_gpc'] = $not_gpc;
                        $this->vars[$key]['changed'] = true;
                        $this->setDirty();
                    }
                } else {
                    $this->vars[$key]['value'] = $value;
                    $this->vars[$key]['not_gpc'] = $not_gpc;
                    $this->vars[$key]['changed'] = true;
                    $this->setDirty();
                }
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

        function checkGroupPerm($mode) {
            foreach ($this->vars as $k => $v) {
                $value = $v['value'];
                //個別の変数権限チェックがあれば実行;
                $checkMethod = 'checkGroupPerm_'.$k;
                if(method_exists($this, $checkMethod)) {
                    $this->$checkMethod($value, $mode);
                }
            }
            if (count($this->_errors) > 0) {
                return false;
            }
            return true;
        }

        function cleanVars() {
            $iret =parent::cleanVars();
            foreach ($this->vars as $k => $v) {
                $cleanv = $v['value'];
                if (!$v['changed']) {
                } else {
                    $cleanv = is_string($cleanv) ? trim($cleanv) : $cleanv;
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
            foreach ($this->_extra_vars as $k => $v) {
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
