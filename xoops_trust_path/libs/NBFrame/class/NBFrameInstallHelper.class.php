<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameInstallHelper')) {
    class NBFrameInstallHelper
    {
        var $mOrigName;
        var $mDirName;

        var $mPreProcessMsg = array();
        var $mPostProcessMsg = array();
        var $mPreModuleUpdateDone = false;
        
        var $mOnInstallOption = null;
        var $mOnUpdateOption = null;
        var $mOnUninstallOption = null;
        
        var $mModuleInfo = null;

        var $mSysFieldsArray = array(
            '_NBsys_del_flag'     =>  array('char(1)',      'NULL',     ' ',     ''),
            '_NBsys_create_time'  =>  array('timestamp',    'NULL',     null,    ''),
            '_NBsys_create_user'  =>  array('int(8)',       'NOT NULL', '0',     ''),
            '_NBsys_update_time'  =>  array('timestamp',    'NULL',     null,    ''),
            '_NBsys_update_user'  =>  array('int(8)',       'NOT NULL', '0',     ''),
            '_NBsys_update_count' =>  array('int(8)',       'NOT NULL', '1',     ''),
        );


        function NBFrameInstallHelper($dirname, $orig_name) {
            $this->mOrigName = $orig_name;
            $this->mDirName = $dirname;
            if( defined('XOOPS_CUBE_LEGACY')) {
                $root =& XCube_Root::getSingleton();
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleInstall.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleUnInstall.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
                $root->mDelegateManager->add('Legacy.Admin.Event.ModuleUpdate.'.ucfirst($dirname).'.Success', array(&$this, 'putCubeMsg'));
            }
        }

        // Method for Duplicated Modules
        
        function postInstallProcessforDuplicate($force=false) {
            $this->createTables($force);
            $this->installTemplates($force);
            return true;
        }
        
        function preUpdateProcessforDuplicate($force=false) {
            return true;
        }
        
        function postUpdateProcessforDuplicate($force=false) {
            $this->installTemplates($force);
            $this->alterTables($force);
            return true;
        }

        function createTables($force=false) {
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            if ($fname = NBFrame::findFile('tabledef.inc.php',$environment, '/include')) @include $fname;
            if (($fname0 = NBFrame::findFile('tabledef.inc.php',$environment, '/include', false, $this->mDirName.'_'))&&($fname!=$fname0)) @include $fname0;
            if (!empty($tableDef)) {
                $this->addMsg('NBFrame Automatic Table Creater start...');
                foreach($tableDef[$this->mOrigName] as $key =>$value) {
                    $tableName = $GLOBALS['xoopsDB']->prefix($this->mDirName.'_'.$key);
                    $this->addMsg(' Table '.$tableName);
                    if (!empty($value['usesys'])) {
                        if ($value['usesys'] == true) {
                            foreach ($this->mSysFieldsArray as $sysName => $sysField) {
                                if (!isset($value['fields'][$sysName])) {
                                    $value['fields'][$sysName] = $sysField;
                                }
                            }
                        }
                    }
                    $this->_createTable($tableName, $value, false, $force);
                }
                $this->addMsg('NBFrame Automatic Table Creater ends...');
            }
        }

        function alterTables($force=false) {
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            if ($fname = NBFrame::findFile('tabledef.inc.php',$environment, '/include', false, $this->mDirName.'_')) @include $fname;
            if (($fname0 = NBFrame::findFile('tabledef.inc.php',$environment, '/include', false, $this->mDirName.'_'))&&($fname!=$fname0)) @include $fname0;          
            if (!empty($tableDef)) {
                $this->addMsg('NBFrame Automatic Table Updater start...');
                foreach($tableDef[$this->mOrigName] as $key =>$value) {
                    if (!empty($value['usesys'])) {
                        if ($value['usesys'] == true) {
                            foreach ($this->mSysFieldsArray as $sysName => $sysField) {
                                if (!isset($value['fields'][$sysName])) {
                                    $value['fields'][$sysName] = $sysField;
                                }
                            }
                        }
                    }
                    $tableName = $GLOBALS['xoopsDB']->prefix($this->mDirName.'_'.$key);
                    $this->addMsg(' Table '.$tableName);
                    $this->addMsg('   Create table '.$tableName.' if it does not exist.');
                    $this->_createTable($tableName, $value, true, $force);

                    $alterParts  = array();
                    //Get existing table definition
                    $sql = 'SHOW COLUMNS FROM '.$tableName;
                    $resultColumuns = $GLOBALS['xoopsDB']->queryF($sql);

                    $tableFields = array();
                    while($row =$GLOBALS['xoopsDB']->fetchArray($resultColumuns)) {
                        $name = $row['Field'];
                        $defMatch = true;
                        if (isset($value['fields'][$name])) {
                            if (preg_match('/\s*(\w+)\s*(\(\s*([\d,]+)\s*\))?(\s+(.*))?/', $value['fields'][$name][0], $match)) {
                                $defType = strtolower($match[1]);
                                if (!empty($match[2])) $defType .='('.$match[3].')';
                                if (!empty($match[5])) $defType .=' '.strtolower($match[5]);
                            }
                            if ($defType != $row['Type']) {
                                $this->addMsg('   Field('.$name.') type is changed. ('.$row['Type'].' => '.$defType);
                                $defMatch = false;
                            }
                            $nulldef = strtoupper(trim($value['fields'][$name][1]));
                            if (!((($nulldef=='NOT NULL')&&($row['Null']=='NO'))||(($nulldef=='NULL')&&($row['Null']=='YES')))) {
                                $this->addMsg('   Field('.$name.') null definition is changed.');
                                $defMatch = false;
                            }
                            if (strtolower($value['fields'][$name][3])!=$row['Extra']) {
                                $this->addMsg('   Field('.$name.') default definition is changed.');
                                $defMatch = false;
                            }
                            if (!$defMatch) {
                                $this->addMsg('   Field('.$name.') default definition is changed.');
                                $alterParts[] = 'CHANGE COLUMN `'.$row['Field'].'` '.$this->_createFieldPart($name, $value['fields'][$name]);
                            }
                        } else {
                            $this->addMsg('   Field('.$name.') will be dropped.');
                            $alterParts[] = 'DROP COLUMN `'.$row['Field'].'`';
                        }
                        $tableFields[$row['Field']] = $row;
                        unset($row);
                    }
                    $prevField = '';
                    foreach($value['fields'] as $name=>$defArray) {
                        if (!isset($tableFields[$name])) {
                            $alterPart = 'ADD COLUMN '.$this->_createFieldPart($name, $defArray);
                            if ($prevField == '') {
                                $alterPart .= ' FIRST';
                            } else {
                                $alterPart .= ' AFTER '.$prevField;
                            }
                            $this->addMsg('   Field('.$name.') will be added.');
                            $alterParts[] = $alterPart;
                        }
                        $prevField = $name;
                    }

                    $sql = 'SHOW INDEX FROM `'.$tableName.'`';
                    $resultKeys = $GLOBALS['xoopsDB']->queryF($sql);

                    $tablePrimaryKeys = array();
                    $tableKeys = array();
                    $tabkeUniqueKeys = array();
                    
                    while($row = $GLOBALS['xoopsDB']->fetchArray($resultKeys)) {
                        if($row['Key_name'] == 'PRIMARY') {
                           $tablePrimaryKeys[$row['Seq_in_index']-1] = $row['Column_name'];
                        } else if ($row['Non_unique'] == 1) {
                           $tableKeys[$row['Key_name']][$row['Seq_in_index']-1] = $row['Column_name'];
                        } else {
                           $tabkeUniqueKeys[$row['Key_name']][$row['Seq_in_index']-1] = $row['Column_name'];
                        }
                        unset($row);
                    }
                    if (!empty($value['primary'])) {
                        if (count($tablePrimaryKeys)) {
                            $value['primary'] = preg_replace('/\s/', '', $value['primary']);
                            $primaryArray = explode(',', $value['primary']);
                            $unMatch = true;
                            if (count($primaryArray) == count($tablePrimaryKeys)) {
                                $unMatch = false;
                                for ($i=0; $i<count($primaryArray); $i++) {
                                    if ($primaryArray[$i] != $tablePrimaryKeys[$i]) $unMatch = true;
                                }
                            }
                            if ($unMatch) {
                                $alterParts[] = 'DROP PRIMARY KEY';
                                $alterParts[] = 'ADD PRIMARY KEY ('.$value['primary'].')';
                            }
                        } else {
                            $alterParts[] = 'ADD PRIMARY KEY ('.$value['primary'].')';
                        }
                    } else if (!empty($tablePrimaryKeys)) {
                        $alterParts[] = 'DROP PRIMARY KEY';
                    }
                    if (!empty($value['keys'])) {
                        foreach ($value['keys'] as $name =>$def) {
                            if (isset($tableKeys[$name])) {
                                $def = preg_replace('/\s/', '', $def);
                                $defArray = explode(',', $def);
                                $unMatch = true;
                                if (count($defArray) == count($tableKeys[$name])) {
                                    $unMatch = false;
                                    for ($i=0; $i<count($defArray); $i++) {
                                        if ($defArray[$i] != $tableKeys[$name][$i]) $unMatch = true;
                                    }
                                }
                                if ($unMatch) {
                                    $alterParts[] = 'DROP INDEX '.$name;
                                    $alterParts[] = 'ADD INDEX '.$name.' ('.$def.')';
                                }
                            } else {
                                $alterParts[] = 'ADD INDEX '.$name.' ('.$def.')';
                            }
                        }
                    }
                    foreach ($tableKeys as $name=>$def) {
                        if (!isset($value['keys']) || !isset($value['keys'][$name])) {
                            $alterParts[] = 'DROP INDEX '.$name;
                        }
                    }
                    if (!empty($value['unique'])) {
                        foreach ($value['unique'] as $name =>$def) {
                            if (isset($tabkeUniqueKeys[$name])) {
                                $def = preg_replace('/\s/', '', $def);
                                $defArray = explode(',', $def);
                                $unMatch = true;
                                if (count($defArray) == count($tabkeUniqueKeys[$name])) {
                                    $unMatch = false;
                                    for ($i=0; $i<count($defArray); $i++) {
                                        if ($defArray[$i] != $tabkeUniqueKeys[$name][$i]) $unMatch = true;
                                    }
                                }
                                if ($unMatch) {
                                    $alterParts[] = 'DROP UNIQUE';
                                    $alterParts[] = 'ADD UNIQUE ('.$def.')';
                                }
                            } else {
                                $alterParts[] = 'ADD UNIQUE ('.$def.')';
                            }
                        }
                    }
                    foreach ($tabkeUniqueKeys as $name=>$def) {
                        if (empty($value['unique']) || !isset($value['unique'][$name])) {
                            $alterParts[] = 'DROP UNIQUE '.$name;
                        }
                    }
                    if (count($alterParts)) {
                        $alterSQL = 'ALTER TABLE `'. $tableName.'` ';
                        $comma = '';
                        foreach($alterParts as $alterPart) {
                            $alterSQL .= $comma.$alterPart;
                            $comma = ",\n";
                        }
                        $this->addMsg('   Alter table '.$tableName.'.');
                        if ($force) {
                            $GLOBALS['xoopsDB']->queryF($alterSQL);
                        } else {
                            $GLOBALS['xoopsDB']->query($alterSQL);
                        }
                        $error = $GLOBALS['xoopsDB']->error();
                        if (!empty($error)) {
                            $this->addMsg($alterSQL);
                            $this->addMsg($GLOBALS['xoopsDB']->error());
                        }
                    }
                }
                $this->addMsg('NBFrame Automatic Table Updater ends...');
            }
        }

        function _createTable($tableName, $tableDef, $createIfNotExists=false, $force=false)
        {
            if ($createIfNotExists) {
                $ifStr = 'IF NOT EXISTS ';
            } else {
                $ifStr = '';
            }
            $createSQL = 'CREATE TABLE '.$ifStr.'`'.$tableName.'` (';
            $comma = '';
            if (!empty($tableDef['fields'])) {
                foreach ($tableDef['fields'] as $name =>$defArray) {
                    $createSQL .= $comma. $this->_createFieldPart($name, $defArray);
                    $comma = ', ';
                }
            } else {
                contiue;
            }
/*
            if (!empty($tableDef['usesys'])) {
                if ($tableDef['usesys'] == true) {
                    foreach ($this->mSysFieldsArray as $name=>$sysField) {
                        $createSQL .= $comma. $this->_createFieldPart($name, $sysField);
                    }
                }
            }
*/
            if (!empty($tableDef['primary'])) {
                $createSQL .= $comma. 'PRIMARY KEY ('.$tableDef['primary']. ')';
            }
            if (!empty($tableDef['keys'])) {
                foreach ($tableDef['keys'] as $name =>$def) {
                    $createSQL .= $comma. 'KEY '.$name.' ('.$def. ')';
                  }
            }
            if (!empty($tableDef['unique'])) {
                foreach ($tableDef['unique'] as $name =>$def) {
                    $createSQL .= $comma. 'UNIQUE '.$name.' ('.$def. ')';
                }
            }
            if (!empty($tableDef['fulltext'])) {
                foreach ($tableDef['fulltext'] as $name =>$def) {
                    $createSQL .= $comma. 'FULLTEXT '.$name.' ('.$def. ')';
                }
            }
            $createSQL .= ') TYPE=MyISAM';
            if (!$createIfNotExists) {
                $this->addMsg('   Create table '.$tableName.'.');
            }
            if ($force) {
                $GLOBALS['xoopsDB']->query($createSQL);
            } else {
                $GLOBALS['xoopsDB']->queryF($createSQL);
            }
            $error = $GLOBALS['xoopsDB']->error();
            if (!empty($error)) {
                $this->addMsg($createSQL);
                $this->addMsg($GLOBALS['xoopsDB']->error());
            }
        }

        function _createFieldPart($name, $defArray) {
            $createSQL = '`'.$name.'` '.$defArray[0]. ' '. $defArray[1];
            if ($defArray[2] != null) {
                $createSQL .= ' default '."'".mysql_real_escape_string($defArray[2])."'";
            }
            if (!empty($defArray[3])) {
                $createSQL .= ' '.$defArray[3];
            }
            return $createSQL;
        }

        function installTemplates($force=false) {
            require_once XOOPS_ROOT_PATH.'/class/template.php' ;
            $tpl = new XoopsTpl();
            $this->addMsg('NBFrame Duplicatable Template Definition starts...');
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            $tempaltePath = NBFrame::findFile('templates',$environment, '');
            $moduleInfo = $this->_getModuleInfo();
            $tplFileHandler =& NBFrame::getHandler('NBFrame.xoops.TplFile', $environment);
            $templateFiles = glob($tempaltePath.'/*.html');
            foreach ($templateFiles as $templateFile) {
                $templateFileBaseName = basename($templateFile);
                $templateFile = NBFrame::findFile($templateFileBaseName, $environment, '/templates', false, $this->mDirName.'_');
                $templateName = $this->mDirName.'_'.$templateFileBaseName;
                $fileContent = file($templateFile);
                $fileContent = implode('', $fileContent);

                $criteria =& new CriteriaCompo(new Criteria('tpl_module', $this->mDirName));
                $criteria->add(new Criteria('tpl_file', $templateName));
                $criteria->add(new Criteria('tpl_tplset', 'default'));
                $criteria->add(new Criteria('tpl_type', 'module'));
                if ($tplFileObjects = $tplFileHandler->getObjects($criteria)) {
                    foreach($tplFileObjects as $tplFileObject) {
                        if ($tplFileObject->get('tpl_lastmodified') <> filemtime($templateFile)) {
                            $tplFileObject->set('tpl_lastmodified', filemtime($templateFile));
                            $tplFileObject->set('tpl_source', $fileContent);
                            $tplFileHandler->insert($tplFileObject, $force);

                            $tpl->clear_cache('db:'.$templateName);
                            $tpl->clear_compiled_tpl('db:'.$templateName);
                        }
                    }
                } else {
                    $tplFileObject =& $tplFileHandler->create();
                    $tplFileObject->set('tpl_refid', $moduleInfo['mid']);
                    $tplFileObject->set('tpl_module', $this->mDirName);
                    $tplFileObject->set('tpl_tplset', 'default');
                    $tplFileObject->set('tpl_file', $templateName);
                    $tplFileObject->set('tpl_desc', '');
                    $tplFileObject->set('tpl_lastmodified', filemtime($templateFile));
                    $tplFileObject->set('tpl_type', 'module');
                    $tplFileObject->set('tpl_source', $fileContent);
                    $tplFileHandler->insert($tplFileObject, $force);

                    $tpl->clear_cache('db:'.$templateName);
                    $tpl->clear_compiled_tpl('db:'.$templateName);
                }
                $this->addMsg('  Define Module Template('.$templateName.')');
            }
            if (!empty($moduleInfo['blocks'])) {
                foreach($moduleInfo['blocks'] as $key=>$blockDef) {
                    if (isset($blockDef['template'])) {
                        $basename = preg_replace('/^'.$this->mDirName.'_/','', $blockDef['template']);
                        $templateFile = NBFrame::findFile($basename, $environment, '/templates/blocks', false, $this->mDirName.'_');
                        if ($templateFile) {
                            $templateName = $this->mDirName.'_'.$basename;
                            $fileContent = file($templateFile);
                            $fileContent = implode('', $fileContent);
                            $criteria =& new CriteriaCompo(new Criteria('tpl_module', $this->mDirName));
                            $criteria->add(new Criteria('tpl_file', $templateName));
                            $criteria->add(new Criteria('tpl_type', 'block'));
                            $criteria->add(new Criteria('tpl_tplset', 'default'));
                            if ($tplFileObjects = $tplFileHandler->getObjects($criteria)) {
                                foreach($tplFileObjects as $tplFileObject) {
                                    if ($tplFileObject->get('tpl_lastmodified') <> filemtime($templateFile)) {
                                        $tplFileObject->set('tpl_lastmodified', filemtime($templateFile));
                                        $tplFileObject->set('tpl_source', $fileContent);
                                        $tplFileHandler->insert($tplFileObject, $force);

                                        $tpl->clear_cache('db:'.$templateName);
                                        $tpl->clear_compiled_tpl('db:'.$templateName);
                                    }
                                }
                            } else {
                                $tplFileObject =& $tplFileHandler->create();
                                $tplFileObject->set('tpl_refid', $key);
                                $tplFileObject->set('tpl_module', $this->mDirName);
                                $tplFileObject->set('tpl_tplset', 'default');
                                $tplFileObject->set('tpl_file', $templateName);
                                $tplFileObject->set('tpl_desc', '');
                                $tplFileObject->set('tpl_lastmodified', filemtime($templateFile));
                                $tplFileObject->set('tpl_type', 'block');
                                $tplFileObject->set('tpl_source', $fileContent);
                                $tplFileHandler->insert($tplFileObject, $force);

                                $tpl->clear_cache('db:'.$templateName);
                                $tpl->clear_compiled_tpl('db:'.$templateName);
                            }
                            $this->addMsg('  Define Block Template('.$templateName.')');
                        }
                    }
                }
            }
            $this->addMsg('NBFrame Duplicatable Template Definition ends...');
        }
/*
        function setModuleTemplateforDuplicate($tplName) {
            $template = array();
            $template['file'] = $this->mDirName.'_'.$tplName;
            return $template;
        }

        function setBlockTemplateforDuplicate($tplName) {
            $template = array();
            $template['template'] = $this->mDirName.'_'.$tplName;
            return $template;
        }
*/
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
            $options = $this->mOnUpdateOption;
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
            $options = $this->mOnUninstallOption;
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
                if (is_a($action,'Legacy_ActionFrame') || is_a($action,'NBFrameDummyActionFrame')) {
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
                if (is_a($action,'Legacy_ActionFrame') || is_a($action,'NBFrameDummyActionFrame')) {
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
                if (is_a($action,'Legacy_ActionFrame') || is_a($action,'NBFrameDummyActionFrame')) {
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
            $msg = str_replace(' ','&nbsp;',$msg);
            $this->mPreProcessMsg[] = $msg;
        }

        function addMsg($msg) {
            $msg = str_replace(' ','&nbsp;',$msg);
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
                            $action = $callback[0][0];
                        }
                    }
                }
            }
            if ($action == null) {
                if (is_object($root->mContext->mXoopsModule)) {
                    $moduleName = $root->mContext->mXoopsModule->getVar('dirname');
                    if (($moduleName == 'legacy') && (preg_match('!/modules/legacy/admin/!',$_SERVER['REQUEST_URI']))) {
                        $dummyAction =& new NBFrameDummyActionFrame;
                        $dummyAction->mActionName = xoops_getrequest('action');
                        $dummyAction->mAdminFlag = true;
                        return $dummyAction;
                    }
                }
            }
            if (!is_a($action,'Legacy_ActionFrame')) {
                $action = null;
            }
            return $action;
        }
    }
    class NBFrameDummyActionFrame
    {
        var $mActionName;
        var $mAdminFlag;
    }
}
?>
