<?php
if(!class_exists('NBFrameTplSourceHandler')) {
    class NBFrameTplSource extends NBFrameObject {
        function prepare() {
            $this->setKeyFields(array('tpl_id'));
        }
    }
    class NBFrameTplSourceHandler extends NBFrameObjectHandler {
        var $mTableName = 'tplsource';
        var $mUseModuleTablePrefix = false;
    }
}
?>
