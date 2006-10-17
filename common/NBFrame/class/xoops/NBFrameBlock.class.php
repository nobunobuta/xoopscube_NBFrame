<?php
if(!class_exists('NBFrameBlock')) {
    NBFrame::using('Object');
    NBFrame::using('ObjectHandler');

    class NBFrameBlock extends NBFrameObject
    {
        function NBFrameBlock() {
            $this->initVar('bid', XOBJ_DTYPE_INT, null, false);
            $this->initVar('mid', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('func_num', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('options', XOBJ_DTYPE_CUSTOM, null, false, 255);
            $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 150);
            $this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 150);
            $this->initVar('content', XOBJ_DTYPE_TXTAREA, null, false);
            $this->initVar('side', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('weight', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('visible', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('block_type', XOBJ_DTYPE_OTHER, null, false);
            $this->initVar('c_type', XOBJ_DTYPE_OTHER, null, false);
            $this->initVar('isactive', XOBJ_DTYPE_INT, null, false);
            $this->initVar('dirname', XOBJ_DTYPE_TXTBOX, null, false, 50);
            $this->initVar('func_file', XOBJ_DTYPE_TXTBOX, null, false, 50);
            $this->initVar('show_func', XOBJ_DTYPE_TXTBOX, null, false, 50);
            $this->initVar('edit_func', XOBJ_DTYPE_TXTBOX, null, false, 50);
            $this->initVar('template', XOBJ_DTYPE_OTHER, null, false);
            $this->initVar('bcachetime', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('last_modified', XOBJ_DTYPE_INT, 0, false);

            $this->setAttribute('modules', null, XOBJ_DTYPE_CUSTOM);
            $this->setAttribute('is_custom', null, XOBJ_DTYPE_CUSTOM);
            $this->setAttribute('edit_form', null, XOBJ_DTYPE_CUSTOM);

            $this->setKeyFields(array('bid'));
            $this->setNameField('name');

            $this->setAutoIncrementField('bid');
        }

        function getVar_modules($value, $format) {
            if ($value === null) {
                $modules = array();
                $db =& $this->_handler->db;
                $sql = 'SELECT module_id FROM '.$db->prefix('block_module_link').' WHERE block_id='.intval($this->getVar('bid'));
                $result = $this->_handler->query($sql);
                $value = array();
                while ($row = $db->fetchArray($result)) {
                    $value[] = intval($row['module_id']);
                }
                $this->vars['modules']['value'] = $value;
            }
            return $value;
        }

        function setVar_modules($value) {
            $this->vars['modules']['value'] = $value;
            $this->vars['modules']['changed'] = true;
        }
        
        function setVar_options($value) {
            if (is_array($value)) {
                if (count($value)>0) {
                    $value = implode('|', $value);
                } else {
                    $value = '';
                }
            }
            $this->vars['modules']['value'] = $value;
            $this->vars['modules']['changed'] = true;
        }
        
        function getVar_is_custom($value, $format) {
            if ($value === null) {
                $value = ($this->getVar('block_type') == 'C' ||
                          $this->getVar('block_type') == 'E') ? true : false;
                $this->vars['is_custom']['value'] = $value;
            }
            return $value;
        }
        
        function getVar_edit_form($value, $format) {
            if ($value === null) {
                if (!$this->getVar('is_custom')) {
                    $edit_func = $this->getVar('edit_func');
                    if (!$edit_func) {
                        $value= false;
                    }
                    if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file'))) {
                        include_once XOOPS_ROOT_PATH.'/modules/'.$this->getVar('dirname').'/blocks/'.$this->getVar('func_file');
                        $options = explode('|', $this->getVar('options'));
                        $value = $edit_func($options);
                        if (!$value) {
                            $value= false;
                        }
                    } else {
                        $value= false;
                    }
                } else {
                    $value= false;
                }
                $this->vars['edit_form']['value'] = $value;
            }
            return $value;
        }
    }
    
    class NBFrameBlockHandler extends NBFrameObjectHandler {
        var $tableName = 'newblocks';

        function insert(&$object,$force=false,$updateOnlyChanged=false)  {
            if ($object->isNew()) $object->setVar('isactive', 1);
            $object->setVar('last_modified', time());
            $result = parent::insert($object, $force, $updateOnlyChanged);
            if($result) {
                if ($object->getVar('modules') !== null) {
                    $bid = $object->getVar('bid');
                    $modules = $object->getVar('modules');
                    $table = $this->db->prefix('block_module_link');
                    $sql = sprintf("DELETE FROM %s WHERE block_id = %u", $table, $bid);
                    $result = $this->query($sql);
                    if ($result) {
                        foreach ($modules as $mid) {
                            $sql = sprintf("INSERT INTO %s (block_id, module_id) VALUES (%u, %d)", $table, $bid, intval($mid));
                            $this->query($sql);
                        }
                    }
                }
            }
            return $result;
        }

        function delete(&$object, $force=false) {
            $result = parent::delete(&$object, $force=false);
            if($result) {
                $bid = $object->getVar('bid');
                $modules = $object->getVar('modules');
                $table = $this->db->prefix('block_module_link');
                $sql = sprintf("DELETE FROM %s WHERE block_id = %u", $table, $bid);
                $result = $this->query($sql);
            }
            return $result;
        }
    }
}
?>
