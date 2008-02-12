<?php
if( ! class_exists( 'NBFrameMenu' ) ) {
    class NBFrameMenu
    {
        var $mEnvironment;

        function NBFrameMenu(&$environment) {
            $this->mEnvironment =& $environment;
        }
    }
}