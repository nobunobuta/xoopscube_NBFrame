<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameBase')) {
    class NBFrameBase {
        var $mEnvironment;
        var $mLanguage;

        function NBFrameBase(&$environment) {
            $this->mEnvironment =& NBFrame::makeClone($environment);
            $this->mLanguage =& $environment->getLanguageManager();
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
