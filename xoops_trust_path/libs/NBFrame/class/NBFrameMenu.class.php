<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameMenu')) {
    NBFrame::using('Base');
    class NBFrameMenu extends NBFrameBase {
        function NBFrameMenu(&$environment) {
            parent::NBFrameBase($environment);
        }
    }
}
?>
