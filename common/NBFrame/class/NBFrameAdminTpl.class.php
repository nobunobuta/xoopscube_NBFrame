<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameAdminTpl')) {
    require_once(XOOPS_ROOT_PATH.'/class/template.php');
    class NBFrameAdminTpl extends XoopsTpl {
        var $mDirName;
        var $mLanguage;

        function NBFrameAdminTpl($dirname, &$language)
        {
            parent::XoopsTpl();
            $this->mDirName = $dirname;
            $this->mLanguage =& $language;
            $this->template_dir = NBFRAME_BASE_DIR . '/templates';
            $this->error_reporting = error_reporting();
        }

        function fetch($tplfile, $cache_id = null, $compile_id = null, $display = false)
        {
            if (!$compile_id) {
                $compile_id = $this->mDirName.'_admin_';
            }
            return parent::fetch($tplfile, $cache_id, $compile_id, $display);
        }
    }
}
?>
