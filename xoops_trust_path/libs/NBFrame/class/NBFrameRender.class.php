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
            $this->mXoopsTpl->register_modifier('NBFrameAction', array(&$this,'_Smarty_NBFrameAction'));
            $this->mXoopsTpl->register_modifier('NBFrameImage', array(&$this,'_Smarty_NBFrameImage'));
            $this->mXoopsTpl->register_modifier('NBFramePage', array(&$this,'_Smarty_NBFramePage'));
            $this->mXoopsTpl->register_compiler_function('__l', array(&$this,'__l_s'));
            $this->mXoopsTpl->register_compiler_function('__e', array(&$this,'__e_s'));
            $this->mXoopsTpl->assign_by_ref('NBEnvrionment',$this->mAction->mEnvironment);
        }

        function _Smarty_NBFrameAction() {
            $environment =& $this->mAction->mEnvironment;
            $action = '';
            $paramArray = array();
            $args = func_get_args();
            if (count($args)>0) {
                $action = $args[0];
                if (count($args)>1) {
                    for ($i=1; $i < count($args); $i=$i+2) {
                        $paramKey = trim($args[$i]);
                        $paramValue = trim($args[$i+1]);
                        $paramArray[$paramKey] = $paramValue;
                    }
                }
            }
            return $environment->getActionURL($action, $paramArray);
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
