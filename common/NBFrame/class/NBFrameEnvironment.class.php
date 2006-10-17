<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameEnvironment')) {
    class NBFrameEnvironment {
        var $mOrigDirName;
        var $mCurrentDirBase;
        var $mCurrentDirName;
        var $mCurrentUrlBase;
        var $mAttributeArr;

        function NBFrameEnvironment($origDirName='', $currentDirBase='') {
            $this->setOrigDirName($origDirName);
            $this->setCurrentDirBase($currentDirBase);
        }

        function setOrigDirName($origDirName='') {
            if (!empty($origDirName)) {
                $this->mOrigDirName = $origDirName;
            }
        }

        function setCurrentDirBase($currentDirBase='') {
            if (!empty($currentDirBase)) {
                $this->mCurrentDirBase = $currentDirBase;
                $this->mCurrentDirName = basename($currentDirBase);
                $this->mCurrentUrlBase = XOOPS_URL.'/modules/'.$this->mCurrentDirName;
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

        function prefix($basename) {
            return $this->mCurrentDirName.'_'.$basename;
        }
        
    }
}
?>
