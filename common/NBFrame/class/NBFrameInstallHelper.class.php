<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameInstallHelper')) {
    class NBFrameInstallHelper
    {
        var $mDupMark;
        var $mOrigName;
        var $mDirName;

        var $mPreProcessMsg = array();
        var $mPostProcessMsg = array();
        var $mPreModuleUpdateDone = false;
        
        var $mOnInstallOption = null;
        var $mOnUpdateOption = null;
        var $mOnUninstallOption = null;
        
        var $mModuleInfo = null;
        
        function NBFrameInstallHelper($dirname, $orig_name, $dupmark='XX') {
            $this->mOrigName = $orig_name;
            $this->mDirName = $dirname;
            $this->mDupMark = $dupmark;
            if( defined('XOOPS_CUBE_LEGACY')) {
                $root =& XCube_Root::getSingleton();
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleInstall.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleUnInstall.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleUpdate.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
            }
        }

        // Method for Duplicated Modules
        
        function postInstallProcessforDuplicate() {
            $this->renameTables();
            $this->renameTemplates();
            $this->storeTemplates();
            return true;
        }
        
        function preUpdateProcessforDuplicate() {
            $this->renameTemplates(true);
            return true;
        }
        
        function postUpdateProcessforDuplicate() {
            $this->renameTemplates();
            $this->storeTemplates();
            return true;
        }

        function renameTables() {
            $moduleInfo = $this->_getModuleInfo();
            $this->addMsg('Rename Tables');
            foreach($moduleInfo['tables'] as $table) {
                $orig_table = preg_replace('/^'.$this->mDirName.'_/',$this->mDupMark.$this->mOrigName.$this->mDupMark.'_', $table);
                $sql = 'RENAME TABLE '.$GLOBALS['xoopsDB']->prefix($orig_table).' TO '.$GLOBALS['xoopsDB']->prefix($table);
                $GLOBALS['xoopsDB']->query($sql);
                $this->addMsg('  <b>'.$orig_table.'</b> to <b>'.$table.'</b>');
            }
        }

        function storeTemplates($reverse = false) {
            $tplHandler =& xoops_gethandler('tplfile');
            $criteria =& new CriteriaCompo(new Criteria('tpl_module', $this->mDirName));
            $tplObjects = $tplHandler->getObjects($criteria);
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            $this->addMsg('Storing template content');

            foreach($tplObjects as $object) {
                $tplFile = $object->getVar('tpl_file');
                $tplType = $object->getVar('tpl_type');
                $fileName = preg_replace('/^'.$this->mDirName.'_/', '', $tplFile);
                if ($tplType == 'module') {
                    $fileName = NBFrame::findFile($fileName, $environment, 'templates', false);
                } else {
                    $fileName = NBFrame::findFile($fileName, $environment, 'templates/blocks', false);
                }
                if ($fileName) {
                    $fileContent = file($fileName);
                    $fileContent = implode('', $fileContent);
                    $object->setVar('tpl_source', $fileContent);
                    $object->setVar('tpl_lastmodified', filemtime($fileName));
                    $tplHandler->insert($object);
                    $this->addMsg('  <b>'.$tplFile.'</b>');
                }
            }
            
        }

        function renameTemplates($reverse = false) {
            $moduleInfo = $this->_getModuleInfo();
            $tplHandler =& xoops_gethandler('tplfile');
            $criteria =& new CriteriaCompo(new Criteria('tpl_module', $this->mDirName));
            $tplObjects = $tplHandler->getObjects($criteria);
            if (!$reverse) {
                $this->addMsg('Rename Templates');
            } else {
                $this->addPreMsg('Rename Templates for updating module.');
            }
            foreach($tplObjects as $object) {
                $orig_fname = $object->getVar('tpl_file');
                if (!$reverse) {
                    $fname = preg_replace('/'.$this->mDupMark.$this->mOrigName.$this->mDupMark.'_/', $this->mDirName.'_', $orig_fname);
                    $object->setVar('tpl_file', $fname);
                    $tplHandler->insert($object);
                    $this->addMsg('  <b>'.$orig_fname.'</b> to <b>'.$fname.'</b>');
                } else {
                    $fname = preg_replace('/^'.$this->mDirName.'_/', $this->mDupMark.$this->mOrigName.$this->mDupMark.'_', $orig_fname);
                    $object->setVar('tpl_file', $fname);
                    $tplHandler->insert($object);
                    $this->addPreMsg('  <b>'.$orig_fname.'</b> to <b>'.$fname.'</b>');
                }
            }
            if (!class_exists('XoopsBlock')) {
                require_once XOOPS_ROOT_PATH.'/class/xoopsblock.php';
            }
            $blockObject =& new XoopsBlock();
            $blockObjects =& $blockObject->getByModule($moduleInfo['mid']);
            foreach($blockObjects as $object) {
                $orig_fname = $object->getVar('template');
                if (!$reverse) {
                    $fname = preg_replace('/'.$this->mDupMark.$this->mOrigName.$this->mDupMark.'_/', $this->mDirName.'_', $orig_fname);
                    $object->setVar('template', $fname);
                    $object->store();
                    $this->addMsg('Rename Block Template <b>'.$orig_fname.'</b> to <b>'.$fname.'</b>');
                } else {
                    $fname = preg_replace('/^'.$this->mDirName.'_/', $this->mDupMark.$this->mOrigName.$this->mDupMark.'_', $orig_fname);
                    $object->setVar('template', $fname);
                    $object->store();
                    $this->addPreMsg('Rename Block Template <b>'.$orig_fname.'</b> to <b>'.$fname.'</b>');
                }
            }
        }
        
        function setModuleTemplateforDuplicate($tplName) {
            $template = array();
            if($this->isPreModuleInstall()||$this->isPreModuleUpdate()) {
                $template['file'] = $this->mDupMark.$this->mOrigName.$this->mDupMark.'_'.$tplName;
            } else {
                $template['file'] = $this->mDirName.'_'.$tplName;
                $template['orig_file'] = $tplName;
            }
            return $template;
        }

        function setBlockTemplateforDuplicate($tplName) {
            $template = array();
            if($this->isPreModuleInstall()||$this->isPreModuleUpdate()) {
                $template['template'] = $this->mDupMark.$this->mOrigName.$this->mDupMark.'_'.$tplName;
            } else {
                $template['template'] = $this->mDirName.'_'.$tplName;
                $template['orig_template'] = $tplName;
            }
            return $template;
        }

        // Method for Keep Block options
        function preBlockUpdateProcess($moduleInfo) {
            $moduleInfo = $this->_getModuleInfo($moduleInfo);

            $count = count($moduleInfo['blocks']);
            if ($count) {
                $this->addPreMsg('Preparing Block parameter for updating module.');
            }
            $sql = "SELECT * FROM ".$GLOBALS['xoopsDB']->prefix('newblocks')."
                     WHERE mid=".$moduleInfo['mid']." AND block_type <>'D' AND func_num > $count";
            $fresult = $GLOBALS['xoopsDB']->query($sql);
            while ($fblock = $GLOBALS['xoopsDB']->fetchArray($fresult)) {
                $this->addPreMsg("  Non Defined Block <b>".$fblock['name']."</b> will be deleted");
                $sql = "DELETE FROM ".$GLOBALS['xoopsDB']->prefix('newblocks')." WHERE bid='".$fblock['bid']."'";
                $iret = $GLOBALS['xoopsDB']->query($sql);
            }           
            for ($i=1 ; $i<=$count ; $i++) {
                $sql = "SELECT name,options FROM ".$GLOBALS['xoopsDB']->prefix('newblocks')."
                         WHERE mid=".$moduleInfo['mid']." AND func_num=".$i." AND
                               show_func='".addslashes($moduleInfo['blocks'][$i]['show_func'])."' AND
                               func_file='".addslashes($moduleInfo['blocks'][$i]['file'])."'";
                $fresult = $GLOBALS['xoopsDB']->query($sql);
                $fblock = $GLOBALS['xoopsDB']->fetchArray($fresult);
                if ( isset( $fblock['options'] ) ) {
                    $old_vals=explode("|",$fblock['options']);
                    $def_vals=explode("|",$moduleInfo['blocks'][$i]['options']);
                    if (count($old_vals) == count($def_vals)) {
                        $moduleInfo['blocks'][$i]['options'] = $fblock['options'];
                        $this->addPreMsg("  Option's values of the block <b>".$fblock['name']."</b> will be kept. (value = <b>".$fblock['options']."</b>)");
                    } else if (count($old_vals) < count($def_vals)){
                        for ($j=0; $j < count($old_vals); $j++) {
                            $def_vals[$j] = $old_vals[$j];
                        }
                        $moduleInfo['blocks'][$i]['options'] = implode("|",$def_vals);
                        $this->addPreMsg("  Option's values of the block <b>".$fblock['name']."</b> will be kept and new option(s) are added. (value = <b>".$moduleInfo['blocks'][$i]['options']."</b>)");
                    } else {
                        $this->addPreMsg("  Option's values of the block <b>".$fblock['name']."</b> will be reset to the default, because of some decrease of options. (value = <b>".$moduleInfo['blocks'][$i]['options']."</b>)");
                    }
                }
            }
            return true;
        }

        // Methods for Custom Installer process
        function prepareOnInstallFunction() {
            $options = $this->mOnInstallOption;
            $dirName = $this->mDirName;
            $str = 'function xoops_module_install_'.$dirName.'(&$module) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= '$installHelper =& NBFrame::getInstallHelper();';
            $str .= 'return $installHelper->onInstallProcess(&$module, $options); }';
            eval($str);
        }

        function prepareOnUpdateFunction() {
            $options = $this->mOnInstallOption;
            $dirName = $this->mDirName;
            $str = 'function xoops_module_update_'.$dirName.'(&$module, $prevVer) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= '$installHelper =& NBFrame::getInstallHelper();';
            $str .= 'return $installHelper->onUpdateProcess(&$module, $prevVer, $options); }';
            eval($str);
        }

        function prepareOnUninstallFunction() {
            $options = $this->mOnInstallOption;
            $dirName = $this->mDirName;
            $str = 'function xoops_module_uninstall_'.$dirName.'(&$module) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= '$installHelper =& NBFrame::getInstallHelper();';
            $str .= 'return $installHelper->onUninstallProcess(&$module, $options); }';
            eval($str);
        }

        function onInstallProcess(&$module, $options=null) {
            $ret = $this->postInstallProcessforDuplicate();
            if (!$this->executeCustomInstallProcess($options, $module)) $ret = false;
            return $ret;
        }

        function onUpdateProcess(&$module, $prevVer, $options=null) {
            $this->putPreProcessMsg();
            $ret = $this->postUpdateProcessforDuplicate();
            if (!$this->executeCustomUpdatellProcess($options, $module, $prevVer)) $ret = false;
            return $ret;
        }

        function onUninstallProcess(&$module, $options=null) {
            $ret = $this->executeCustomInstallProcess($options);
            return $ret;
        }

        function executeCustomInstallProcess($options, &$module) {
            $ret = true;
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
                @include_once  NBFrame::findFile(basename($options['file']),$environment ,dirname($options['file']),false);
                foreach($options['func'] as $funcname) {
                    if (function_exists($funcname)) {
                        $this->addMsg('Execute Custom functon <b>'.$funcname.'</b>');
                        $ret1 = $funcname($this, $module);
                        if (!$ret1) {
                            $ret = false;
                            $this->addMsg('Fail.');
                        }
                    }
                }
            }
            return $ret;
        }

        function executeCustomUpdatellProcess($options, &$module, $prevVer) {
            $ret = true;
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
                @include_once  NBFrame::findFile(basename($options['file']),$environment ,dirname($options['file']),false);
                foreach($options['func'] as $funcname) {
                    if (function_exists($funcname)) {
                        $this->addMsg('Execute Custom functon <b>'.$funcname.'</b>');
                        $ret1 = $funcname($this, $module, $prevVer);
                        if (!$ret1) {
                            $ret = false;
                            $this->addMsg('Fail.');
                        }
                    }
                }
            }
            return $ret;
        }
        // Methods for Check Environment

        function isPreModuleInstall() {
            if (defined('XOOPS_CUBE_LEGACY') && class_exists('XCube_Root')) {
                $action =& $this->_getActionFrame();
                if (is_a($action,'Legacy_ActionFrame')) {
                    if ($action->mAdminFlag && 
                        (($action->mActionName == 'ModuleInstall')||($action->mActionName == 'InstallWizard')) &&
                        $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['dirname'] == $this->mDirName) {
                        return true;
                    }
                }
            }
            if (!empty($_POST['fct']) && !empty($_POST['op']) &&
                    $_POST['fct'] == 'modulesadmin' &&
                    $_POST['op'] == 'install_ok' &&
                    $_POST['module'] == $this->mDirName )
            {
                $ref = xoops_getenv('HTTP_REFERER');
                if( $ref == '' || strpos( $ref , XOOPS_URL.'/modules/system/admin.php' ) === 0 ) {
                    return true;
                }
            }
            return false;
        }

        function isPreModuleUpdateDone() {
            if ($this->mPreModuleUpdateDone) {
                return true;
            } else {
                $this->mPreModuleUpdateDone = true;
                return false;
            }
        }

        function isPreModuleUpdate() {
            if (defined('XOOPS_CUBE_LEGACY') && class_exists('XCube_Root')) {
                $action =& $this->_getActionFrame();
                if (is_a($action,'Legacy_ActionFrame')) {
                    if ($action->mAdminFlag && 
                        $action->mActionName == 'ModuleUpdate' &&
                        $_SERVER['REQUEST_METHOD'] == 'POST' &&
                        $_REQUEST['dirname'] == $this->mDirName) {
                        return true;
                    }
                }
            }
            if (!empty($_POST['fct']) && !empty($_POST['op']) &&
                    $_POST['fct'] == 'modulesadmin' &&
                    $_POST['op'] == 'update_ok' &&
                    $_POST['dirname'] == $this->mDirName )
            {
                $ref = xoops_getenv('HTTP_REFERER');
                if( $ref == '' || strpos( $ref , XOOPS_URL.'/modules/system/admin.php' ) === 0 ) {
                    return true;
                }
            }
            return false;
        }

        function isPreModuleUninstall() {
            if (defined('XOOPS_CUBE_LEGACY') && class_exists('XCube_Root')) {
                $action =& $this->_getActionFrame();
                if (is_a($action,'Legacy_ActionFrame')) {
                    if ($action->mAdminFlag && 
                        $action->mActionName == 'ModuleUninstall' &&
                        $_SERVER['REQUEST_METHOD'] == 'POST' &&
                        $_REQUEST['dirname'] == $this->mDirName) {
                        return true;
                    }
                }
            }
            if (!empty($_POST['fct']) && !empty($_POST['op']) &&
                    $_POST['fct'] == 'modulesadmin' &&
                    $_POST['op'] == 'uninstall_ok' &&
                    $_POST['module'] == $this->mDirName )
            {
                $ref = xoops_getenv('HTTP_REFERER');
                if( $ref == '' || strpos( $ref , XOOPS_URL.'/modules/system/admin.php' ) === 0 ) {
                    return true;
                }
            }
            return false;
        }

        // Methods for Installer Messages.
        function addPreMsg($msg) {
            $this->mPreProcessMsg[] = $msg;
        }

        function addMsg($msg) {
            if(defined('XOOPS_CUBE_LEGACY')) {
                $this->mPostProcessMsg[] = $msg;
            } else {
                $GLOBALS['msgs'][] = $msg;
            }
        }

        function putPreProcessMsg() {
            if(defined('XOOPS_CUBE_LEGACY')) {
                $this->mPostProcessMsg = array_merge($this->mPreProcessMsg, $this->mPostProcessMsg);
            } else {
                if (!empty($GLOBALS['msgs'])) {
                    $GLOBALS['msgs'] = array_merge($this->mPreProcessMsg, $GLOBALS['msgs']);
                } else {
                    $GLOBALS['msgs'] = $this->mPreProcessMsg;
                }
            }
        }

        function putCubeMsg(&$module,&$log) {
            if(is_array($this->mPostProcessMsg)) {
                foreach($this->mPostProcessMsg as $message) {
                    $log->add(strip_tags($message));
                }
            }
        }

        // Misc Methods.
        function _getModuleInfo($moduleInfo=null) {
            $moduleHandler =& xoops_gethandler('module');
            $moduleObject =& $moduleHandler->getByDirname($this->mDirName);
            if (empty($moduleInfo)) {
                if (empty($this->mModuleInfo[$this->mDirName])) {
                    $this->mModuleInfo[$this->mDirName] =& $moduleObject->getInfo();
                }
                $moduleInfo = $this->mModuleInfo[$this->mDirName];
            }
            
            $moduleInfo['mid'] = $moduleObject->getVar('mid');
            return $moduleInfo;
        }
        
        function &_getActionFrame() {
            static $action = null;
            if (!empty($action)) return $action;
            $root =& XCube_Root::getSingleton();
            if (isset($root->mController->mActionStrategy)) {
                $action =& $root->mController->mActionStrategy;
            } else if (isset($root->mController->mExecute)) {
                $callbacks0 =& $root->mController->mExecute->_mCallbacks;
                foreach($callbacks0 as $callbacks) {
                    foreach($callbacks as $callback) {
                        if (is_array($callback[0]) && is_object($callback[0][0])) {
                            $action =& $callback[0][0];
                        }
                    }
                }
            }
            if (!is_a($action,'Legacy_ActionFrame')) {
                $action = null;
            }
            return $action;
        }
    }
}
?>
