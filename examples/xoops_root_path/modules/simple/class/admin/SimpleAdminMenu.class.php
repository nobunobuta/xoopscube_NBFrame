<?php
if( ! class_exists( 'SimpleAdminMenu' ) ) {
    NBFrame::using('Menu');
    class SimpleAdminMenu extends NBFrameMenu
    {
        function getAdminMenu() {
            $adminmenu = array();
            return $adminmenu;
        }
    }
}
