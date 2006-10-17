<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminMaintAction')) {
    NBFrame::using('ObjectAction');
    NBFrame::using('ObjectUtil');

    class NBFrameAdminMaintAction extends NBFrameObjectAction{
        function NBFrameAdminMaintAction(&$environment) {
            parent::NBFrameObjectAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }

        function prepare($name, $caption) {
            $this->mLanguage->setInAdmin(true);
            parent::prepare($name, $name, $caption);
            $this->mDefaultOp = 'list';
            $this->mAllowedOp = array('list','new','edit','insert','save','delete','deleteok');
            NBFrame::using('AdminTpl');
            $this->mXoopsTpl =& new NBFrameAdminTpl($this->mDirName, $this->mLanguage);

            $this->mFormTemplate = 'NBFrameAdminForm.html';
            $this->setObjectForm('admin.'.$name.'Admin');
            $this->mListTemplate = 'NBFrameAdminList.html';
            $this->setObjectList('admin.'.$name.'Admin');
            $this->setObjectKeyField();
        }
/*
        function startRender() {
            global $xoopsConfig, $xoopsModule;
            xoops_cp_header();
            $this->renderMyMenu();
        }

        function endRender() {
            global $xoopsConfig, $xoopsModule;
            $this->mXoopsTpl->display($this->mCurrentTemplate);
            xoops_cp_footer();
        }
*/
        function viewFormOp() {
            parent::viewFormOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function viewListOp() {
            parent::viewListOp();
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
            $this->mXoopsTpl->assign('extrahtml', '');
        }

        function viewDeleteOp() {
            parent::viewDeleteOp();
            $this->mXoopsTpl->assign('extrahtml', '');
            $this->mXoopsTpl->assign('errmsg', '');
            $this->mXoopsTpl->assign('modulename', $GLOBALS['xoopsModule']->getVar('name'));
        }

        function executeActionSuccess() {
            redirect_header($this->mUrl, 2, $this->__l('Action Success'));
        }

        function executeActionError() {
            redirect_header($this->mUrl, 2, $this->mErrorMsg,2);
        }
/*
        function renderMyMenu() {
            include NBFrame::findFile('admin_menu.inc.php', $this->mEnvironment, 'include');
            $module =& $GLOBALS['xoopsModule'];
            if( $module->getvar('hasconfig') ){
                array_push($adminmenu,
                             array( 'title' => _PREFERENCES ,
                                    'link' => 'admin/admin.php?fct=preferences&op=showmod&mod=' . $module->getvar('mid')
                             )
                           );
            }
            $menuitem_count = 0 ;
            $mymenu_uri = empty( $mymenu_fake_uri ) ? $_SERVER['REQUEST_URI'] : $mymenu_fake_uri ;
            $mymenu_link = substr( strstr( $mymenu_uri , '/admin/' ) , 1 ) ;

            // hilight
            foreach( array_keys( $adminmenu ) as $i ) {
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
            $this->mXoopsTpl->assign('myurlbase', $this->getUrlBase());
            $this->mXoopsTpl->display('NBFrameAdminMyMenu.html');
        }
*/
    }
}
?>
