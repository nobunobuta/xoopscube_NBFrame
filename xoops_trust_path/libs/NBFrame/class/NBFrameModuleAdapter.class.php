<?php
/**
 *
 * @package NBFrame
 * @version $Id: NBFrame.class.php 1389 2008-03-11 07:16:17Z nobunobu $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('XCube_Root')) exit();
if (!class_exists('NBFrameModuleAdapter')) {
    class NBFrameModuleAdapter extends Legacy_ModuleAdapter
    {
        function getAdminMenu()
        {
            if ($this->_mAdminMenuLoadedFlag) {
                return $this->mAdminMenu;
            }
            
            $dmy =& $this->mXoopsModule->getInfo();
            $root =& XCube_Root::getSingleton();

            //
            // Load admin menu, and add preference menu by own judge.
            //
            $dirName = $this->mXoopsModule->get('dirname');
            @include XOOPS_ROOT_PATH.'/modules/'.$dirName.'/include/NBFrameAdminMenu.inc.php';
            $this->mAdminMenu = $adminmenu;

            if ($this->mXoopsModule->get('hasnotification')
                || ($this->mXoopsModule->getInfo('config') && is_array($this->mXoopsModule->getInfo('config')))
                || ($this->mXoopsModule->getInfo('comments') && is_array($this->mXoopsModule->getInfo('comments')))) {
                    $this->mAdminMenu[] = array(
                        'link' => $this->_getPreferenceEditUrl($environment),
                        'title' => _PREFERENCES,
                        'absolute' => true);
            }
                
            if ($this->mXoopsModule->hasHelp()) {
                $this->mAdminMenu[] = array('link' =>  $root->mController->getHelpViewUrl($this->mXoopsModule),
                                              'title' => _HELP,
                                              'absolute' => true);
            }

            $this->_mAdminMenuLoadedFlag = true;
            
            if ($this->mAdminMenu) {
                foreach ($this->mAdminMenu as $key=>$menu) {
                    if (!isset($menu['absolute']) || (isset($menu['absolute']) && $menu['absolute'] != true)) {
                        $menu['link'] = XOOPS_MODULE_URL . '/' . $this->mXoopsModule->get('dirname') . '/' . $menu['link'];
                    }
                    $this->mAdminMenu[$key] = $menu;
                }
            }
            return $this->mAdminMenu;
        }

        function _getPreferenceEditUrl(&$environment)
        {
            if (NBFrame::checkAltSys(false) && $environment->getAttribute('UseAltSys')) {
                return $environment->getActionUrl('NBFrame.admin.AltSys', array('page'=>'mypreferences'), 'html',false, false);
            } else {
                $root =& XCube_Root::getSingleton();
                return $root->mController->getHelpViewUrl($this->mXoopsModule);
            }
        }
    }
}
?>
