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
if (!class_exists('NBFrameRender')) {
    class NBFrameRender {
        var $mTemplate;
        var $mXoopsTpl;
        var $mAction;
        var $mLanguage;
        var $mDirName;

        function NBFrameRender(&$action) {
            $this->mAction =& $action;
            $this->mDirName = $action->mDirName;
            $this->mLanguage =& $action->mLanguage;
        }
        
        function setTemplate($template) {
            $this->mTemplate = $template;
        }
        
        function _addSmartyPugin() {
            $this->mXoopsTpl->register_modifier('__l', array(&$this,'__l'));
            $this->mXoopsTpl->register_modifier('__e', array(&$this,'__e'));
            $this->mXoopsTpl->register_modifier('NBFrameImage', array(&$this,'_Smarty_NBFrameImage'));
            $this->mXoopsTpl->register_modifier('NBFramePage', array(&$this,'_Smarty_NBFramePage'));
            $this->mXoopsTpl->register_function('NBFrameAction', array(&$this->mAction->mEnvironment,'_Smarty_NBFrameActionUrl'));
            $this->mXoopsTpl->register_compiler_function('__l', array(&$this,'__l_s'));
            $this->mXoopsTpl->register_compiler_function('__e', array(&$this,'__e_s'));
            $this->mXoopsTpl->register_function('NBFrameCrumBreadBase', array(&$this,'_Smarty_NBFrameCrumBreadBase'));
            $this->mXoopsTpl->register_function('NBFrameD3ForumComment', array(&$this,'_Smarty_NBFrameD3ForumComment'));
            $this->mXoopsTpl->assign_by_ref('NBEnvrionment',$this->mAction->mEnvironment);
        }

        function _Smarty_NBFrameD3ForumComment($params) {
            $dirname = $GLOBALS['xoopsModuleConfig']['NB_D3comment_dirname'] ;
            $forum_id = $GLOBALS['xoopsModuleConfig']['NB_D3comment_forum_id'];
            $params['view'] = $GLOBALS['xoopsModuleConfig']['NB_D3comment_view'];
            $params['mytrustdirname'] = $this->mAction->mEnvironment->mOrigDirName;
            $params['subject_escaped'] = 1;
            if(empty($dirname) || ($dirname=='----') || !preg_match('/^[0-9a-zA-Z_-]+$/' , $dirname ) || $forum_id <= 0 ||
                !file_exists(XOOPS_TRUST_PATH.'/modules/d3forum/include/comment_functions.php')) {
                return '';
            } else {
                require_once(XOOPS_TRUST_PATH.'/modules/d3forum/include/comment_functions.php') ;
                d3forum_display_comment( $dirname, $forum_id, $params);
            }
        }
        function _Smarty_NBFrameCrumBreadBase($params) {
            $environment =& $this->mAction->mEnvironment;
            if (is_object($params['category'])) {
                $category =& $params['category'];
                $categoryParentPath = $category->getParentPath();
            } else {
                $categoryParentPath = null;
            }
            if (!empty($params['action'])) {
                $actionName = $params['action'];
            } else {
                $actionName = $environment->getAttribute('ModueleMainAction');
            }
            if (!empty($params['key_name'])) {
                $keyName = $params['key_name'];
            } else {
                $keyName = 'cat';
            }
            $thisAction = $this->mAction->mActionName;
            $moduleTop = $environment->getUrlBase().'/';
            $module =& $environment->getModule();
            $moduleName = $module->getName();
            ob_start();
            include NBFRAME_BASE_DIR.'/templates/NBFrameCrumBread.tpl.php';
            $str = ob_get_contents();ob_end_clean();
            return $str;
        }

        function _Smarty_NBFrameImage($file) {
            $environment =& $this->mAction->mEnvironment;
            return $environment->getImageURL($file);
        }

        function _Smarty_NBFramePage($file) {
            $environment =& $this->mAction->mEnvironment;
            return $environment->getPageURL($file);
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
