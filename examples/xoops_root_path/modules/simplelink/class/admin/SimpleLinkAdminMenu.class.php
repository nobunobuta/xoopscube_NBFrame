<?php
if( ! class_exists( 'SimpleLinkAdminMenu' ) ) {
    NBFrame::using('Menu');
    class SimpleLinkAdminMenu extends NBFrameMenu
    {
        function getAdminMenu() {
            $constpref = NBFrame::langConstPrefix('MI', '', NBFRAME_TARGET_LOADER);
            $adminmenu = array();
            $adminmenu[] = array (
                'title' => constant($constpref.'AD_MENU0'),
                'link'  => $this->mEnvironment->getActionUrl('admin.SimpleLinkLinkAdmin', array(), 'html', true),
            );
            $adminmenu[] = array (
                'title' => constant($constpref.'AD_MENU1'),
                'link'  => $this->mEnvironment->getActionUrl('admin.SimpleLinkCategoryAdmin', array(), 'html', true),
            );
            return $adminmenu;
        }
    }
}
