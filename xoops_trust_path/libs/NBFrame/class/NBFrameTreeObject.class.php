<?php
/**
 * Generic Tree Table Manupulation XoopsObject class
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
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
