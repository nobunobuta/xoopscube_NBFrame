<?php
if (!class_exists('NBFrameAltSysAction')) {
    NBFrame::using('AdminAction');
    
    class NBFrameAltSysAction extends NBFrameAdminAction {
        var $mRequestedOp;
        function prepare() {
            parent::prepare();
            if (isset($_REQUEST['op'])) {
                $this->mRequestedOp = $_REQUEST['op'];
                $_REQUEST['op'] = 'default';
                $_GET['op'] = 'default';
            }
        }
        function viewDefaultOp() {
            global $xoopsConfig, $xoopsOption, $xoopsModule, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsGTicket ;
            global $mydirname, $mydirpath, $mytrustdirpath;

            $lib = 'altsys';
            $page = preg_replace( '[^a-zA-Z0-9_-]' , '' , @$_GET['page'] ) ;
            $mydirname = $this->mEnvironment->mCurrentDirName;
            $mydirpath = $this->mEnvironment->mCurrentDirBase;
            if (is_dir(XOOPS_ROOT_PATH. '/common/modules/'.$this->mEnvironment->mOrigDirName)) {
                $mytrustdirpath = XOOPS_ROOT_PATH. '/common/modules/'.$this->mEnvironment->mOrigDirName;
            } else if (!defined('XOOPS_TRUST_PATH') && is_dir(XOOPS_TRUST_PATH. '/modules/'.$this->mEnvironment->mOrigDirName)) {
                $mytrustdirpath = XOOPS_TRUST_PATH. '/modules/'.$this->mEnvironment->mOrigDirName;
            }
            $_GET['op'] = $this->mRequestedOp;
            if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ) ) {
                include XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ;
            } else if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ) ) {
                include XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ;
            } else {
                die( 'wrong request' ) ;
            }
        }
    }
}
?>
