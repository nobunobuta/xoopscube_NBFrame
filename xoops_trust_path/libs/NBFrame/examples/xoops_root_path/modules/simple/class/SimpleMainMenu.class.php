<?php
if( ! class_exists( 'SimpleMainMenu' ) ) {
    NBFrame::using('Menu');
    class SimpleMainMenu extends NBFrameMenu
    {
        function getMainMenu() {
            $menu = array();
//            $menu[] = array('name' => 'name', 'url' => '');
            return $menu;
        }
    }
}
