<?php
/**
 * Generic Tree Table Manupulation XoopsObject class
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if(!class_exists('NBFrameTreeObject')) {
    NBFrame::using('Object');

    class NBFrameTreeObject  extends NBFrameObject
    {
        function setParentField($name) {
            $this->mHandler->setParentField($name);
        }

        function getParentField() {
            return $this->mHandler->getParentField();
        }
        
        function getParentKey($format = 's') {
            return $this->getVar($this->mHandler->getParentField(), $format);;
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
