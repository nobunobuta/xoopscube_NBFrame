<?php
if (!class_exists('NBFrameLanguage')) {
    class NBFrameLanguage
    {
        var $mDirName;
        var $mOrigDirName;
        var $mInAdmin;
        var $mEnvironment;
        var $mSalt ;
        var $mCachePath ;

        function NBFrameLanguage($target, $inAdmin=false) {
            $this->mEnvironment =& NBFrame::getEnvironments($target);
            $this->mDirName = $this->mEnvironment->mDirName;
            $this->mSalt = substr( md5( XOOPS_ROOT_PATH . XOOPS_DB_USER . XOOPS_DB_PREFIX ) , 0 , 6 );
            if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/cache')) {
        	    $this->mCachePath = XOOPS_TRUST_PATH.'/cache' ;
        	} else {
        	    $this->mCachePath = XOOPS_ROOT_PATH.'/cache' ;
        	}
            if (!empty($this->mEnvironment->mOrigDirName)) {
                $this->mOrigDirName = $this->mEnvironment->mOrigDirName;
            } else {
                $this->mOrigDirName = $this->mDirName;
            }
            switch ($target) {
                case NBFRAME_TARGET_MAIN:
                    $this->loadModuleLanguageFile('main.php');
                    break;
                case NBFRAME_TARGET_BLOCK:
                    $this->loadModuleLanguageFile('blocks.php');
                    break;
                case NBFRAME_TARGET_INSTALLER:
                    $this->loadModuleLanguageFile('modinfo.php');
                    break;
                default:
                    break;
            }
            $this->setInAdmin($inAdmin);
            if (defined('NBFRAME_BASE_DIR') && file_exists(NBFRAME_BASE_DIR.'/language/'.$GLOBALS['xoopsConfig']['language'].'/NBFrameCommon.php')) {
                $this->_load(NBFRAME_BASE_DIR.'/language/'.$GLOBALS['xoopsConfig']['language'].'/NBFrameCommon.php');
            }
        }

        function setInAdmin($inAdmin) {
            $this->mInAdmin = $inAdmin;
            if ($inAdmin) {
                $this->loadModuleLanguageFile('modinfo.php');
                $this->loadModuleLanguageFile('admin.php');
            }
        }

        function loadModuleLanguageFile($filename) {
            $languageFile=null;
        	$cacheFile = $this->_getCacheFileName( $filename , $this->mDirName) ;
        	
        	if (file_exists($cacheFile)) {
        	    $languageFile = $cacheFile;
        	    require_once $languageFile;
        	} else {
                $fileOffset = '/modules/'.$this->mOrigDirName.'/language/'.$GLOBALS['xoopsConfig']['language'].'/'.$filename;
                if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH. $fileOffset)) {
                    $languageFile = XOOPS_TRUST_PATH.$fileOffset;
            	    require $languageFile;
                } else if (file_exists(XOOPS_ROOT_PATH.'/common'.$fileOffset)) {
                    $languageFile =  XOOPS_ROOT_PATH.'/common'.$fileOffset;
            	    require $languageFile;
                }
            }
            if (!empty($languageFile)) {
//                $this->_load($languageFile);
	            if (!defined('_MYLANGADMIN_'.md5($languageFile))) define('_MYLANGADMIN_'.md5($languageFile) ,1);
            }
        }

        function _load($languageFile) {
            if (!empty($languageFile)) {
//                if (defined('XOOPS_CUBE_LEGACY')) {
//                    $root=&XCube_Root::getSingleton();
//                    $root->mLanguageManager->_loadFile($languageFile);
//                } else {
                    require $languageFile;
//                }
            }
        }

        function _getParams($array) {
            if (count($array) > 1) {
                array_shift($array);
                return $array;
            } else {
                return array();
            }
        }
        function __l($msg, $params=array()) {
            return $this->getLangResouce($msg, $params, $type='LANG');
        }
        
        function __l_s($msg, $params=array()) {
            return $this->getLangResouce($msg, $params, $type='LANG', true);
        }

        function __e($msg, $params=array()) {
            return $this->getLangResouce($msg, $params, $type='ERROR');
        }
        
        function __e_s($msg, $params=array()) {
            return $this->getLangResouce($msg, $params, $type='ERROR', true);
        }

        function getLangResouce($msg, $params=array(), $type='LANG', $retSouce=false) {
            $msgKey = str_replace(' ','_',strtoupper($msg));
            $msgKey = preg_replace('/[^A-Z0-9_]/','', $msgKey);
            if ($this->mInAdmin) {
                $msgConstPrefix = 'AD_';
            } else {
                $msgConstPrefix = '';
            }
            if (defined('_'.$msgConstPrefix.strtoupper($this->mDirName).'_'.$type.'_'.$msgKey)) {
                $msgExpr ='constant("'.'_'.$msgConstPrefix.strtoupper($this->mDirName).'_'.$type.'_'.$msgKey.'")';
            } else if (defined('_'.strtoupper($this->mDirName).'_'.$type.'_'.$msgKey)) {
                $msgExpr ='constant("'.'_'.strtoupper($this->mDirName).'_'.$type.'_'.$msgKey.'")';
            } else if (defined('_'.$msgConstPrefix.strtoupper($this->mOrigDirName).'_'.$type.'_'.$msgKey)) {
                $msgExpr ='constant("'.'_'.$msgConstPrefix.strtoupper($this->mOrigDirName).'_'.$type.'_'.$msgKey.'")';
            } else if (defined('_'.strtoupper($this->mOrigDirName).'_'.$type.'_'.$msgKey)) {
                $msgExpr ='constant("'.'_'.strtoupper($this->mOrigDirName).'_'.$type.'_'.$msgKey.'")';
            } else if (isset($GLOBALS['NBFrameLanguage'][$msg])) {
                $msgExpr ='$GLOBALS["NBFrameLanguage"]["'.$msg.'"]';
            } else if (isset($GLOBALS['NBFrameLanguage'][$msgKey])) {
                $msgExpr ='$GLOBALS["NBFrameLanguage"]["'.$msgKey.'"]';
            } else {
                $msgExpr =' "'.$msg.'"';
            }
            if (count($params)==0) {
                if (!$retSouce) {
                    eval('$retVal ='.$msgExpr.';');
                    return $retVal;
                } else {
                    return 'echo '.$msgExpr.';';
                }
            } else {
                if (!$retSouce) {
                    eval('$retVal =vsprintf('.$msgExpr.',$params);');
                    return $retVal;
                } else {
                    return 'vprintf('.$msgExpr.',$params);';
                }
            }
        }
        function _getCacheFileName($resource , $mydirname) {
            return $this->mCachePath . '/lang_' . $this->mSalt . '_' . $mydirname . '_' . $GLOBALS['xoopsConfig']['language'] . '_' . $resource ;
        }
    }
}
?>