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
if (!class_exists('NBFrameAdminTpl')) {
    require_once(XOOPS_ROOT_PATH.'/class/template.php');
    class NBFrameAdminTpl extends XoopsTpl {
        var $mDirName;
        var $mLanguage;
        var $mEnvironment;
        
        function NBFrameAdminTpl(&$render)
        {
            parent::XoopsTpl();
            $this->mDirName = $render->mDirName;
            $this->mLanguage =& $render->mLanguage;
            $this->mEnvironment =& $render->mAction->mEnvironment;
            $this->compile_check = true;
            $this->error_reporting = error_reporting();
        }

        function fetch($tplfile, $cache_id = null, $compile_id = null, $display = false)
        {
            $filename = $this->mEnvironment->findFile($tplfile, '/templates');
            if (empty($filename)) {
                $this->template_dir = NBFRAME_BASE_DIR . '/templates';
            } else {
                $this->template_dir = dirname($filename);
                $tplfile = basename($filename);
            }
            if (!$compile_id) {
                $compile_id = $this->mDirName.'_admin_';
            }
            return parent::fetch($tplfile, $cache_id, $compile_id, $display);
        }
    }
}
?>
