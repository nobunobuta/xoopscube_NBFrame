<?php
if(!class_exists('NBFrameModuleHandler')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $mTableName = 'modules';
        var $mUseModuleTablePrefix = false;

        function &getByEnvironment(&$environment)
        {
            $dirName = $environment->mDirName;
            $criteria = new Criteria('dirname', $dirName);
            $objects = $this->getObjects($criteria);
            if (count($objects) > 0) {
                $object =& $objects[0];
            } else {
                $object = null;
            }
            return $object;
        }
    }
}
?>
