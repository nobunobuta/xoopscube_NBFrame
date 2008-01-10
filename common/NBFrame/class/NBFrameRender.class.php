<?php
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
            $this->mXoopsTpl->register_compiler_function('__l', array(&$this,'__l_s'));
            $this->mXoopsTpl->register_compiler_function('__e', array(&$this,'__e_s'));
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
