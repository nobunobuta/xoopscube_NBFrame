<?php
if(!class_exists('NBFrameBlockModuleLink')) {
    NBFrame::using('Object');
    NBFrame::using('ObjectHandler');

    class NBFrameBlockModuleLink extends NBFrameObject {
        function NBFrameBlockModuleLink() {
            $this->initVar('block_id', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('module_id', XOBJ_DTYPE_INT, 0, false);

            $this->setKeyFields(array('block_id','module_id'));
        }
    }

    class NBFrameBlockModuleLinkHandler extends NBFrameObjectHandler {
        var $tableName = 'block_module_link';
        
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
