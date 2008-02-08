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
if (!class_exists('NBFrameEnvironment')) {
    class NBFrameEnvironment {
        var $mOrigDirName;
        var $mDirBase;
        var $mDirName;
        var $mUrlBase;
        var $mAttributeArr;
        var $mTarget;
        var $mModule = null;
        var $mLanguageManager = null;
        var $mModuleInfo = array();

        function NBFrameEnvironment($origDirName='', $currentDirBase='') {
            $this->setOrigDirName($origDirName);
            $this->setDirBase($currentDirBase);
        }

        function setOrigDirName($origDirName='') {
            if (!empty($origDirName)) {
                $this->mOrigDirName = $origDirName;
            }
        }

        function setDirBase($dirBase='') {
            if (!empty($dirBase)) {
                $this->mDirBase = $dirBase;
                $this->mDirName = basename($dirBase);
                $this->mUrlBase = XOOPS_URL.'/modules/'.$this->mDirName;
            }
        }

        function setAttribute($name, $value) {
            $this->mAttributeArr[$name] = $value;
        }

        function getAttribute($name='') {
            if (empty($name)) {
                return $this->mAttributeArr;
            } else if (isset($this->mAttributeArr[$name])) {
                return $this->mAttributeArr[$name];
            } else {
                return null;
            }
        }

        function &getModule() {
            if (!is_object($this->mModule)) {
                $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                $this->mModule =& $moduleHandler->getByEnvironment($this);
            }
            return $this->mModule;
        }
        
        function &getLanguageManager() {
            if (!is_object($this->mLanguageManager)) {
                $this->mLanguageManager =& NBFrameBase::getLanguageManager($this);
            }
            return $this->mLanguageManager;
        }

        function prefix($basename) {
            return $this->mDirName.'_'.$basename;
        }

        function __l($msg) {
            $language =& $this->getLanguageManager();
            $args = func_get_args();
            return $language->__l($msg, $language->_getParams($args));
        }

        function __e($msg) {
            $language =& $this->getLanguageManager();
            $args = func_get_args();
            return $language->__e($msg, $language->_getParams($args));
        }
        
    }
}
?>
