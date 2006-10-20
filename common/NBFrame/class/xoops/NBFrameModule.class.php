<?php
if(!class_exists('NBFrameModule')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $tableName = 'modules';
        var $mUseModuleTablePrefix = false;
    }
}
?>
