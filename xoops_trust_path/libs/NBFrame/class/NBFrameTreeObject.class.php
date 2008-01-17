<?php
if (!class_exists('NBFrame')) exit();
/**
* Generic Tree Table Manupulation XoopsObject class
 *
 * @copyright copyright (c) 2007 Kowa.ORG
 * @author Nobuki Kowa <Nobuki@Kowa.ORG>
 * @package NBFrameObject
 */
if(!class_exists('NBFrameTreeObject')) {
    NBFrame::using('Object');

    class NBFrameTreeObject  extends NBFrameObject
    {
        var $mParentField;
        
        function setParentField($name) {
            $this->mParentField = $name;
        }

        function getParentField() {
            return $this->mParentField;
        }
        
        function getParentKey($format = 's') {
            return $this->getVar($this->mParentField, $format);;
        }
        
        function &getParentObject() {
            $parentObject =& $this->mHandler->getParent($this->getKey());
            return $parentObject;
        }

        function getParentPath() {
            $pathArray = $this->mHandler->getParentPath($this->getKey());
            return $pathArray;
        }
    }
}
?>
