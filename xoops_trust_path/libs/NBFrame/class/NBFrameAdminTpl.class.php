<?php
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
            $this->error_reporting = error_reporting();
        }

        function fetch($tplfile, $cache_id = null, $compile_id = null, $display = false)
        {
            $filename = NBFrame::findFile($tplfile, $this->mEnvironment, '/templates', false, $this->mDirName);
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
