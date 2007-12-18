<?php
if (!class_exists('NBFrame')) exit();

if(!class_exists('NBFrameTreeObjectHandler')) {
    NBFrame::using('TreeObject');
    NBFrame::using('ObjectHandler');

    class NBFrameTreeObjectHandler  extends NBFrameObjectHandler
    {
        
        function &getNestedObjects($criteria = null, $padChar='&#8211;')
        {
            $records = array();
            $objects = $this->getObjects($criteria);
            $resultObjects = array();
            if ($objects) {
                $this->_getNestedObjects($padChar, 0, 0, $objects, $resultObjects);
            }
            return $resultObjects;
        }
        
        function _getNestedObjects($padChar, $parent, $level, &$objects, &$resultObjects)
        {
            if ($padChar != '') {
                $padString = str_repeat($padChar, $level).' ';
            } else {
                $padString = '';
            }
            for ($i=0; $i < count($objects); $i++) {
                $object =& $objects[$i];
                if ($parent == $object->getParentKey()) {
                    $key = $object->getKey();
                    $object->setVar($object->getNameField(), $padString.$object->getName(), true);
                    $object->setExtraVar('_object_level_', $level+1);
                    $resultObjects[] = $object;
                    $this->_getNestedObjects($padChar, $key, $level+1, $objects, $resultObjects);
                }
            }
        }

        function &getChildrenCriteria($keyName, $currentKey)
        {
            $criteria =& new CriteriaCompo(new Criteria($keyName, $currentKey));
            
            $objects =& $this->getObjects();
            $resultObjects = array();
            if ($objects) {
                $this->_getNestedObjects('', $currentKey, 0, $objects, $resultObjects);
            }
            if (count($resultObjects) > 0) {
                foreach($resultObjects as $object) {
                    $criteria->add(new Criteria($keyName, $object->getKey()), 'OR');
                }
            }
            return $criteria;
        }

        function getSelectOptionArray($criteria=null, $gperm_mode='', $padChar='&#8211;')
        {
            $objects =& $this->getNestedObjects($criteria);
            $optionArray = array();
            foreach ($objects as $object) {
                if (!empty($gperm_mode) && !$object->checkGroupPerm($gperm_mode)) {
                    continue;
                }
                $optionArray[$object->getKey()] = $object->getName();
            }
            return $optionArray;
        }

        function getParentSelectOptionArray($currentKey)
        {
            $optionArray=array(0=>'-----');
            $record =& $this->create(false);
            $keys = $record->getKeyFields();
            $criteria =& new Criteria($keys[0], $currentKey, '<>');
            $criteria->setSort($record->getNameField());
            $optionArray += $this->getSelectOptionArray($criteria);
            return $optionArray;
        }
        
        function &getParent($currentKey) {
            $parentObject = false;
            if ($currentObject =& $this->get($currentKey)) {
                if($parentKey = $currentObject->getParentKey()) {
                    $parentObject =& $this->get($parentKey);
                }
            }
            return $parentObject;
        }
        
        function getParentPath($currentKey)
        {
            $pathArray = array();
            while(1) {
                if ($parentObject =& $this->getParent($currentKey)) {
                    $parentKey = $parentObject->getKey();
                    $parentName = $parentObject->getName();
                    array_unshift($pathArray, array('key'=>$parentKey, 'name'=>$parentName));
                    $currentKey = $parentKey;
                } else {
                    break;
                }
            }
            return $pathArray;
        }
    }
}
?>
