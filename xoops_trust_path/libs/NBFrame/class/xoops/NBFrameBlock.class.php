<?php
if(!class_exists('NBFrameBlock')) {
    class NBFrameBlock extends NBFrameObject
    {
        function prepare() {
            $this->setVarType('options', XOBJ_DTYPE_CUSTOM);
            $this->setVarType('block_type', XOBJ_DTYPE_OTHER);
            $this->setVarType('template', XOBJ_DTYPE_OTHER);
            $this->setVarRequired('content', false);

            $this->setAttribute('modules', null, XOBJ_DTYPE_CUSTOM);
            $this->setAttribute('is_custom', null, XOBJ_DTYPE_CUSTOM);
            $this->setAttribute('edit_form', null, XOBJ_DTYPE_CUSTOM);

            $this->setNameField('name');
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
        var $mTableName = 'newblocks';
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

        function getSideListArray() {
            return array(
                0 => $this->__l('Left block'),
                1 => $this->__l('Right block'),
                3 => $this->__l('Center block - left'),
                4 => $this->__l('Center block - right'),
                5 => $this->__l('Center block - center'),
            );
        }

        function getBlockCacheTimeListArray() {
            return array(
               '0' => _NOCACHE,
               '30' => sprintf(_SECONDS, 30),
               '60' => _MINUTE,
               '300' => sprintf(_MINUTES, 5),
               '1800' => sprintf(_MINUTES, 30),
               '3600' => _HOUR,
               '18000' => sprintf(_HOURS, 5),
               '86400' => _DAY,
               '259200' => sprintf(_DAYS, 3),
               '604800' => _WEEK,
               '2592000' => _MONTH
            );
        }

        function getModuleListArray() {
            $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', $this->mEnvironment);
            $criteria = new CriteriaCompo(new Criteria('hasmain', 1));
            $criteria->add(new Criteria('isactive', 1));
            $module_list =& $moduleHandler->getSelectOptionArray($criteria);
            $module_list[-1] = $this->__l('Top Page');
            $module_list[0] = $this->__l('All Pages');
            ksort($module_list);
            return $module_list;
        }
    }
}
?>