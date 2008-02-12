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
        var $mStartTime;
        var $mElapsedTime;

        function NBFrameAction(&$environment) {
            $this->mEnvironment = $environment;
            $this->mDirName = $environment->getDirName();
                $this->mOrigDirName = $environment->getOrigDirName();
            if (empty($this->mOrigDirName)) {
                $this->mOrigDirName = $this->mDirName;
            }
            $this->mUrl = XOOPS_URL.xoops_getenv('PHP_SELF');
            $this->mLanguage =& $environment->getLanguageManager();
            $this->mLanguage->loadModuleLanguageFile('main.php');
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
        
        function getUrl($paramArray=array()) {
            return $this->mEnvironment->getActionURL($this->mActionName, $paramArray);
        }
        
        function addUrlParam($str) {
            if (!empty($this->mActionName)) {
                return $this->mUrl. '&'. $str;
            } else {
                return $this->mUrl. '?'. $str;
            }
        }

        function _actionDispatch() {
            if (!empty($this->mExecutePermission) && !NBFrame::checkRight($this->mExecutePermission)) {
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
            $this->mStartTime = NBFrame::getClock();
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
                    $postViewMethod = 'postView'.ucfirst($this->mOp).'Op';
                    if (method_exists($this, $postViewMethod)) {
                        $this->$postViewMethod();
                    }
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
                    $postViewExtraMethod = 'postView'.$this->mExtraShowMethod;
                    if (method_exists($this, $postViewExtraMethod)) {
                        $this->$postViewExtraMethod();
                    }
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
             $this->mElapsedTime = NBFrame::getClock() - $this->mStartTime;
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
            $this->mEnvironment->redirect($this->mActionName, 2, $this->__l('Action Success'));
        }

        function executeActionError() {
            $this->mEnvironment->redirect('', 2, $this->mErrorMsg);
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
