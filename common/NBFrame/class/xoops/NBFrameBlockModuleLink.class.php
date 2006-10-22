<?php
if(!class_exists('NBFrameBlockModuleLink')) {
    class NBFrameBlockModuleLinkHandler extends NBFrameObjectHandler {
        var $mTableName = 'block_module_link';
        var $mUseModuleTablePrefix = false;
        
        function insert($bid, $modules, $force=false) {
            $this->deleteBlock($bid);
            foreach($modules as $mid) {
                $object =& $this->create();
                $object->setVar('block_id', $bid, true);
                $object->setVar('module_id', $mid, true);
                $result = parent::insert($object, $force);
                unset($object);
                if (!$result) break;
            }
        }

        function deleteBlock($bid, $force=false) {
            $criteria =& new Criteria('block_id', $bid);
            return $this->deleteAll($criteria, $force);
        }

        function deleteModule($mid, $force=false) {
            $criteria =& new Criteria('Module_id', $mid);
            return $this->deleteAll($criteria, $force);
        }
    }
}
?>
