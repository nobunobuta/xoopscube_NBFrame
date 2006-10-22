<?php
if(!class_exists('NBFrameModule')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $mTableName = 'modules';
        var $mUseModuleTablePrefix = false;
    }
}
?>
