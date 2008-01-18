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
                $_POST['op'] = 'default';
            }
        }
        function viewDefaultOp() {
            global $xoopsConfig, $xoopsOption, $xoopsModule, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsGTicket ;
            global $mydirname, $mydirpath, $mytrustdirpath, $db, $myts, $language, $altsys_path;
            global $config_handler;
            
            $GLOBALS['db'] =& Database::getInstance();
            $lib = 'altsys';
            $page = preg_replace( '[^a-zA-Z0-9_-]' , '' , @$_GET['page'] ) ;
            $mydirname = $this->mEnvironment->mDirName;
            $mydirpath = $this->mEnvironment->mDirBase;
            if (is_dir(XOOPS_ROOT_PATH. '/common/modules/'.$this->mEnvironment->mOrigDirName)) {
                $mytrustdirpath = XOOPS_ROOT_PATH. '/common/modules/'.$this->mEnvironment->mOrigDirName;
            } else if (!defined('XOOPS_TRUST_PATH') && is_dir(XOOPS_TRUST_PATH. '/modules/'.$this->mEnvironment->mOrigDirName)) {
                $mytrustdirpath = XOOPS_TRUST_PATH. '/modules/'.$this->mEnvironment->mOrigDirName;
            }
            $_REQUEST['op'] = $this->mRequestedOp;
            $_GET['op'] = $this->mRequestedOp;
            $_POST['op'] = $this->mRequestedOp;
            ob_start(array(&$this, '_cutHeader'));
            if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ) ) {
                include XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ;
            } else if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ) ) {
                include XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ;
            } else {
                die( 'wrong request' ) ;
            }
            ob_end_flush();
            exit();
        }

        // Hack For cutting Altsys Generated cp_header on XOOPS2.0.x
        function _cutHeader($str) {
            if (class_exists(XCube_Root)) {
                return $str;
            } else {
                $matches = preg_split('/\<div class=\'content\'><br \/>\n/',$str);
                if  (count($matches) > 1) {
                    return $matches[1];
                } else {
                    return $matches[0];
                }
            }
        }
    }
}
?>
