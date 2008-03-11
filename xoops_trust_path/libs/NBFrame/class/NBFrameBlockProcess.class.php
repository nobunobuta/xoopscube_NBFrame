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
if (!class_exists('NBFrameBlockProcess')) {
    class NBFrameBlockProcess {
        var $mEnvironment;
        var $mXoopsTpl;
        var $mLanguage;
        var $mDirName;

        function prepare(&$environment) {
            $this->mEnvironment = $environment;
            $this->mDirName = $environment->mDirName;
            $this->mLanguage =& $environment->mLanguage;
            if (is_object($GLOBALS['xoopsTpl'])) {
                $GLOBALS['xoopsTpl']->register_function('NBBlockMsg', array('NBFrame','_Smarty_NBBlockMsg'));
                $GLOBALS['xoopsTpl']->register_function('NBBlockError', array('NBFrame','_Smarty_NBBlockError'));
                $GLOBALS['xoopsTpl']->register_function('NBBlockActionUrl', array('NBFrame','_Smarty_NBBlockActionUrl'));
            }
        }
    }
}
?>
