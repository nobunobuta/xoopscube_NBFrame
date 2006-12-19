<?php
if(!class_exists('NBFrameModuleHandler')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $mTableName = 'modules';
        var $mUseModuleTablePrefix = false;
    }
}
?>
