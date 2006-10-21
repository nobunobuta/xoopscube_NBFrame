<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameEnvironment')) {
    class NBFrameEnvironment {
        var $mOrigDirName;
        var $mDirBase;
        var $mDirName;
        var $mUrlBase;
        var $mAttributeArr;

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

        function prefix($basename) {
            return $this->mDirName.'_'.$basename;
        }
        
    }
}
?>
