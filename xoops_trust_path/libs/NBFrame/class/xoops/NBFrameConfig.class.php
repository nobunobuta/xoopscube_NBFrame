<?php
if(!class_exists('NBFrameConfigHandler')) {
    class NBFrameConfigHandler extends NBFrameObjectHandler {
        var $mTableName = 'config';
        var $mUseModuleTablePrefix = false;
    }
}
?>
