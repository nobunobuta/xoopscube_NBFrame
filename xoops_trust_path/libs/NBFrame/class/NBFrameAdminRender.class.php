<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminRender')) {
    NBFrame::using('Render');
    class NBFrameAdminRender extends NBFrameRender {
        function NBFrameAdminRender(&$action) {
            parent::NBFrameRender(&$action);
            $this->_loadAdminCommon();
            $this->mLanguage->setInAdmin(true);
        }
        function &start() {
            global $xoopsConfig, $xoopsModule;
            NBFrame::using('AdminTpl');
            $this->mXoopsTpl =& new NBFrameAdminTpl($this);
            $this->_addSmartyPugin();
            xoops_cp_header();
            $this->renderMyMenu();
            return $this->mXoopsTpl;
        }
        
        function end() {
            global $xoopsConfig, $xoopsModule;
            if (!empty($this->mTemplate)) {
                $this->mXoopsTpl->display($this->mTemplate);
            }
            xoops_cp_footer();
        }

        function renderMyMenu() {
            $adminmenu = array();
            include NBFrame::findFile('NBFrameAdminMenu.inc.php', $this->mAction->mEnvironment, 'include');
            $module =& $GLOBALS['xoopsModule'];
            if( $module->getVar('hasconfig') ){
                if (NBFrame::checkAltSys(false) && $this->mAction->mEnvironment->getAttribute('UseAltSys')) {
                    array_push($adminmenu,
                                 array( 'title' => _PREFERENCES ,
                                        'link' => '?action=NBFrame.admin.AltSys&page=mypreferences'
                                 )
                               );
                } else if (class_exists('XCube_Root')) {
                    if (is_dir(XOOPS_ROOT_PATH.'/modules/legacy/')) {
                        $sysDir = 'legacy';
                    } else {
                        $sysDir = 'base';
                    }
                    array_push($adminmenu,
                                 array( 'title' => _PREFERENCES ,
                                        'absolute' => true,
                                        'link' => XOOPS_URL.'/modules/'.$sysDir.'/admin/?action=PreferenceEdit&confmod_id=' . $module->getvar('mid')
                                 )
                               );
                } else {
                    array_push($adminmenu,
                                 array( 'title' => _PREFERENCES ,
                                        'absolute' => true,
                                        'link' => XOOPS_URL.'/modules/system/admin.php?fct=preferences&op=showmod&mod=' . $module->getvar('mid')
                                 )
                               );
                }
            }
            $menuitem_count = 0 ;
            $mymenu_uri = empty( $mymenu_fake_uri ) ? $_SERVER['REQUEST_URI'] : $mymenu_fake_uri ;
            $mymenu_link = substr( strstr( $mymenu_uri , '/admin/' ) , 1 ) ;

            // hilight
            foreach( array_keys( $adminmenu ) as $i ) {
                if(!isset($adminmenu[$i]['absolute'])) {
                    $adminmenu[$i]['absolute'] = false;
                }
                if( $mymenu_link == $adminmenu[$i]['link'] ) {
                    $adminmenu[$i]['color'] = '#FFCCCC' ;
                    $adminmenu_hilighted = true ;
                } else {
                    $adminmenu[$i]['color'] = '#DDDDDD' ;
                }
            }
            if( empty( $adminmenu_hilighted ) ) {
                foreach( array_keys( $adminmenu ) as $i ) {
                    if( stristr( $mymenu_uri , $adminmenu[$i]['link'] ) ) {
                        $adminmenu[$i]['color'] = '#FFCCCC' ;
                    }
                }
            }
            $this->mXoopsTpl->assign('adminmenu', $adminmenu);
            $this->mXoopsTpl->assign('myurlbase', XOOPS_URL.'/modules/'.$this->mDirName);
            $this->mXoopsTpl->display('admin/NBFrameAdminMyMenu.html');
        }
        
        function _loadAdminCommon() {
            global $xoopsDB, $xoopsTpl, $xoopsRequestUri, $xoopsModule, $xoopsModuleConfig,
                   $xoopsModuleUpdate, $xoopsUser, $xoopsUserIsAdmin, $xoopsTheme, $xoopsAction,
                   $xoopsConfig, $xoopsOption, $xoopsCachedTemplate, $xoopsLogger, $xoopsDebugger;
            if ($this->mAction->mLoadCommon) {
                require_once XOOPS_ROOT_PATH.'/include/cp_header.php';
            }
        }
    }
}
?>
