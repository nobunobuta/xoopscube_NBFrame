<?php
if(!class_exists('NBFrameBlock')) {
    class NBFrameBlock extends NBFrameObject
    {
        function NBFrameBlock() {
            parent::NBFrameObject();
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

        function &getVar_modules($value, $format) {
            if ($value === null) {
                $blockModuleLinkHandler =& NBFrame::getHandler('NBFrame.xoops.BlockModuleLink', $this->mEnvironment);
                $criteria =& new Criteria('block_id', $this->getVar('bid'));
                $resultSet = $blockModuleLinkHandler->open($criteria);
                $value = array();
                while ($row = $blockModuleLinkHandler->db->fetchArray($resultSet)) {
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
            $this->vars['options']['value'] = $value;
            $this->vars['options']['changed'] = true;
        }

        function &getVar_is_custom($value, $format) {
            if ($value === null) {
                $value = ($this->getVar('block_type') == 'C' ||
                          $this->getVar('block_type') == 'E') ? true : false;
                $this->vars['is_custom']['value'] = $value;
            }
            return $value;
        }

        function &getVar_edit_form($value, $format) {
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
        var $mUseModuleTablePrefix = false;

        function insert(&$object,$force=false,$updateOnlyChanged=false)  {
            if ($object->isNew()) {
                $object->setVar('isactive', 1);
            }
            $modules = $object->getVar('modules');
            
            $object->setVar('last_modified', time());
            $result = parent::insert($object, $force, $updateOnlyChanged);
            if ($result) {
                if ($modules !== null) {
                    $blockModuleLinkHandler =& NBFrame::getHandler('NBFrame.xoops.BlockModuleLink', $this->mEnvironment);
                    $blockModuleLinkHandler->insert($object->getVar('bid'), $modules, $force);
                }
            }
            return $result;
        }

        function delete(&$object, $force=false) {
            $result = parent::delete(&$object, $force=false);
            if($result) {
                $blockModuleLinkHandler =& NBFrame::getHandler('NBFrame.xoops.BlockModuleLink', $this->mEnvironment);
                $result = $blockModuleLinkHandler->deleteBlock($object->getVar('bid'));
            }
            return $result;
        }
    }
}
?>
