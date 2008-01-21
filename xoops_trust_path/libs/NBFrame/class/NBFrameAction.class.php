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
if (!class_exists('NBFrameAction')) {
    define('NBFRAME_ACTION_SUCCESS', '0');
    define('NBFRAME_ACTION_ERROR', '10');
    define('NBFRAME_ACTION_VIEW_DEFAULT', '-1');
    define('NBFRAME_ACTION_VIEW_EXTRA', '90');
    define('NBFRAME_ACTION_VIEW_NONE', '99');

    class NBFrameAction {
        var $mEnvironment;
        var $mActionName = '';
        var $mDirName;
        var $mOrigDirName;
        var $mOp = '';
        var $mDefaultOp = 'default';
        var $mAllowedOp = array('default');
        var $mXoopsTpl;
        var $mUrl;
        var $mCurrentTemplate = '';
        var $mDefaultTemplate = '';
        var $mExtraTemplate;
        var $mExtraShowMethod;
        var $mExecutePermission = '';
        var $mLanguage;
        var $mLoadCommon = true;
        var $mDialogMode = false;
        var $mRequest;

        function NBFrameAction(&$environment) {
            $this->mEnvironment = $environment;
            $this->mDirName = $environment->mDirName;
            if (!empty($environment->mOrigDirName)) {
                $this->mOrigDirName = $environment->mOrigDirName;
            } else {
                $this->mOrigDirName = $this->mDirName;
            }
            $this->mUrl = XOOPS_URL.xoops_getenv('PHP_SELF');
            $this->mLanguage =& NBFrame::getLanguageManager();
            NBFrame::using('ModuleRender');
            $this->mRender =& new NBFrameModuleRender($this);
            NBFrame::using('Request');
            $this->mRequest =& new NBFrameRequest;
        }

        function prepare() {
            if (!empty($this->mActionName)) {
                $this->mUrl .= '?action='.$this->mActionName;
            }
        }

        function setTemplate($template) {
            $this->mCurrentTemplate = $template;
        }

        function setDefaultTemplate($defaultTemplate) {
            $this->mDefaultTemplate = $defaultTemplate;
        }

        function prefix($base_name) {
            return $this->mDirName.'_'.$base_name;
        }

        function getUrlBase() {
            return XOOPS_URL.'/modules/'.$this->mDirName;
        }
        
        function addUrlParam($str) {
            if (!empty($this->mActionName)) {
                return $this->mUrl. '&'. $str;
            } else {
                return $this->mUrl. '?'. $str;
            }
        }

        function _actionDispatch() {
            if (!empty($this->mExecutePermission) && !NBFrameCheckRight($this->mExecutePermission, 1)) {
                $this->mErrorMsg = $this->__e('Permission Error');
                return NBFRAME_ACTION_ERROR;
            }

            $this->mOp = $this->mRequest->getParam('op');
            $executeMethod = 'execute'.ucfirst($this->mOp).'Op';
            if (in_array($this->mOp, $this->mAllowedOp)) {
                if (method_exists($this, $executeMethod)) {
                    return $this->$executeMethod();
                } else {
                    return NBFRAME_ACTION_VIEW_DEFAULT;
                }
            } else {
                $this->mErrorMsg = $this->__e('Invalid Operation');
                return NBFRAME_ACTION_ERROR;
            }
        }

        function executeDefaultOp() {
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }

        function setExecutePermission($permission) {
             $this->mExecutePermission = $permission;
        }

        function execute() {
            $this->mRequest->defParam('op', '', 'var', $this->mDefaultOp);
            $result = $this->_actionDispatch();
            switch ($result) {
                case NBFRAME_ACTION_VIEW_DEFAULT:
                    $preViewMethod = 'preView'.ucfirst($this->mOp).'Op';
                    if (method_exists($this, $preViewMethod)) {
                        $this->$preViewMethod();
                    }
                    $this->startRender();
                    $viewMethod = 'view'.ucfirst($this->mOp).'Op';
                    if (method_exists($this, $viewMethod)) {
                        $this->$viewMethod();
                    }
                    $this->endRender();
                    break;
                case NBFRAME_ACTION_VIEW_EXTRA:
                    $preViewExtraMethod = 'preView'.$this->mExtraShowMethod;
                    if (method_exists($this, $preViewExtraMethod)) {
                        $this->$preViewExtraMethod();
                    }
                    $this->startRender();
                    $extraMethod = 'view'.$this->mExtraShowMethod;
                    if (method_exists($this, $extraMethod)) {
                        $this->$extraMethod();
                    }
                    $this->endRender();
                    break;
                case NBFRAME_ACTION_SUCCESS:
                    $this->executeActionSuccess();
                    break;
                case NBFRAME_ACTION_ERROR:
                    $this->executeActionError();
                    break;
                default:
                    break;
             }
        }

        function startRender() {
            $this->mRender->setTemplate($this->mCurrentTemplate);
            $this->mXoopsTpl =& $this->mRender->start();
        }

        function endRender() {
            $this->mRender->end();
        }

        function preViewDefaultOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mDefaultTemplate);
            }
        }

        function viewDefaultOp() {
        }

        function preViewDefaultExtraOp() {
            if (empty($this->mCurrentTemplate)) {
                $this->setTemplate($this->mDefaultTemplate);
            }
        }

        function executeActionSuccess() {
            redirect_header($this->mUrl, 2, $this->__l('Action Success'));
        }

        function executeActionError() {
            redirect_header($this->mUrl, 2, $this->mErrorMsg,2);
        }

        function __l($msg) {
            $args = func_get_args();
            return $this->mLanguage->__l($msg, $this->mLanguage->_getParams($args));
        }

        function __e($msg) {
            $args = func_get_args();
            return $this->mLanguage->__e($msg, $this->mLanguage->_getParams($args));
        }

        function __l_s($str, &$smarty) {
            if (preg_match('/["\'](\w*)["\']/',$str,$match)) {
                $str = $match[1];
                return $this->mLanguage->__l_s($str);
            }
        }

        function __e_s($str, &$smarty) {
            if (preg_match('/["\'](\w*)["\']/',$str,$match)) {
                $str = $match[1];
                return $this->mLanguage->__e_s($str);
            }
        }
    }
}
?>
