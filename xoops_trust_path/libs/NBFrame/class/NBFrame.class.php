<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
if (!class_exists('NBFrame')) {
    if (!defined('NBFRAME_TARGET_MAIN')) define('NBFRAME_TARGET_MAIN',1);
    if (!defined('NBFRAME_TARGET_BLOCK')) define('NBFRAME_TARGET_BLOCK',2);
    if (!defined('NBFRAME_TARGET_INSTALLER')) define('NBFRAME_TARGET_INSTALLER',3);
    if (!defined('NBFRAME_TARGET_SYS')) define('NBFRAME_TARGET_SYS', 4);
    if (!defined('NBFRAME_TARGET_TEMP')) define('NBFRAME_TARGET_TEMP', 99);

    if (!defined('NBFRAME_NO_DEFAULT_PARAM')) define('NBFRAME_NO_DEFAULT_PARAM', '__nodefault__');

    class NBFrame {
        /**
         * Declaration of NBFrame Class
         *
         * @param string $className
         * @param NBFrameEnvironment $environment  if null, using NBFrame core classes;
         * @param string $classType
         */
        function using($className, $environment=null, $classType='class') {
            if (substr($className, 0, 1) == '+') { // if $className starts with '+', custom Override is disabled.
                $noCustom = true;
                $className = substr($className, 1);
            } else {
                $noCustom = false;
            }
            $classPath = str_replace('.', '/', basename($className));
            $className = basename($classPath);
            $classOffset = dirname('/'.$classPath);
            if ($classOffset == '/') $classOffset = '';
            $classType = basename($classType);
            if (empty($environment)) { // use NBFrame core classes
                $fileOffset = '/'.$classType.$classOffset.'/NBFrame'.$className.'.class.php';
                if (defined('NBFRAME_BASE_DIR') && file_exists(NBFRAME_BASE_DIR.$fileOffset)) {
                    require_once NBFRAME_BASE_DIR.$fileOffset;
                }
            } else {
                if ($noCustom) {
                    $fileName = NBFrame::findFile($className.'.class.php', $environment, $classType.$classOffset, true);
                } else {
                    $dirName = $environment->mDirName;
                    $fileName = NBFrame::findFile($className.'.class.php', $environment, $classType.$classOffset, true, $dirName.'_');
                }
                if ($fileName) require_once $fileName;
            }
        }
        
        /**
         * Pre Preparing in NBFrameLoader
         *
         * @param string $currentDirBase
         */
        function prePrepare($currentDirBase) {
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP, true);
            $environment->setDirBase($currentDirBase);
        }

        function &prepare($origDirName='', $target=NBFRAME_TARGET_MAIN) {
            $envtemp =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
            if (!empty($envtemp)) {
                $environment =& NBFrame::getEnvironments($target, true);
                if (!empty($origDirName)) {
                    $environment->setOrigDirName($origDirName);
                } else {
                    $environment->setOrigDirName($envtemp->mOrigDirName);
                }
                $environment->setDirBase($envtemp->mDirBase);
                $environment->mAttributeArr = $envtemp->mAttributeArr;
                if ($target != NBFRAME_TARGET_MAIN) {
                    NBFrame::getLanguageManager($target);
                }
            } else {
                $environment = null;
            }
            return $environment;
        }

        function &getEnvironments($target=NBFRAME_TARGET_MAIN, $force = false) {
            static $mEnvironmentArr;
            if ($target == NBFRAME_TARGET_SYS) {
                $ret = null;
            } else if (isset($mEnvironmentArr[$target])) {
                if ($target == NBFRAME_TARGET_TEMP && $force) {
                    unset($mEnvironmentArr[$target]);
                    NBFrame::using('Environment');
                    $mEnvironmentArr[$target] =& new NBFrameEnvironment();
                    $mEnvironmentArr[$target]->mTarget = $target;
                }
                $ret =& $mEnvironmentArr[$target];
            } else if ($force) {
                NBFrame::using('Environment');
                $mEnvironmentArr[$target] =& new NBFrameEnvironment();
                $mEnvironmentArr[$target]->mTarget = $target;
                $ret =& $mEnvironmentArr[$target];
            } else {
                $ret = null;
            }
            return $ret;
        }

        /**
         * Enter description here...
         *
         * @param string $origDirName
         * @param string $className
         */
        function executeAction($origDirName='', $defaultAction='', $allowedAction=array()) {
            $environment =& NBFrame::prepare($origDirName);
            if (empty($defaultAction)) {
                $defaultAction = $environment->getAttribute('ModueleMainAction');
                if (empty($allowedAction)) {
                    $allowedAction = $environment->getAttribute('AllowedAction');
                }
            }
            if ($environment->getAttribute('UseAltSys')) {
                $allowedAction[] = 'NBFrame.admin.AltSys';
            }
            if ($environment->getAttribute('UseBlockAdmin')) {
                $allowedAction[] = 'NBFrame.admin.BlocksAdmin';
            }
            $allowedAction[] = 'NBFrame.GetModuleIcon';
            $allowedAction[] = 'NBFrame.GetImage';

            $dialogAction = $environment->getAttribute('DialogAction');
            if (empty($dialogAction)) $dialogAction = array();
            
            if ($allowedAction && !empty($_REQUEST['action'])) {
                $requestAction = basename($_REQUEST['action']);
                if (in_array($requestAction, $allowedAction)) {
                    $className = $requestAction;
                } else {
                    $className = '';
                }
            } else {
                $requestAction = '';
                $className = $defaultAction;
            }
            if (NBFrame::checkAltSys() && 
                isset($_GET['lib']) && ($_GET['lib']=='altsys') && 
                isset($_GET['page'])) {
                $className = 'NBFrame.admin.AltSys';
            }
            if (($environment->getAttribute('AutoUpdateMode')===true) && !NBFrame::isNoCommonAction($className, $environment)) {
                $info = $GLOBALS['xoopsModule']->getInfo();

                $installHelper =& NBFrame::getInstallHelper();
                $installHelper->postUpdateProcessforDuplicate(true);
            }
            if ($action =& NBFrame::getInstance($className, $environment, 'Action')) {
                $action->mActionName = $requestAction;
                if (in_array($className, $dialogAction)) {
                    $action->mDialogMode = true;
                }
                $action->prepare();
                $action->execute();
            }
        }

        /**
         * Enter description here...
         *
         * @param string $className
         * @param NBFrameEnvironment $environment
         * @param string $suffix
         * @return object
         */
        function &getInstance($className, $environment, $suffix='') {
            $className = $className.$suffix;
            $classNamePath = str_replace('.', '/', basename($className));
            $classBaseName = basename($classNamePath);
            if (preg_match('/^NBFrame\.(.*)/',$className, $match)) {
                $className = $match[1];
                NBFrame::using($className);
                $classBaseName = 'NBFrame'.$classBaseName;
            } else {
                NBFrame::using($className, $environment);
            }
            $dirName = $environment->mDirName;
            
            $instance = null;

            if (class_exists($dirName.'_'.$classBaseName)) {
                // This class may be Module Custom Class
                $classBaseName = $dirName.'_'.$classBaseName;
                $instance =& new $classBaseName($environment);
            } else if (class_exists($classBaseName)) {
                $instance =& new $classBaseName($environment);
            }
            return $instance;
        }

        /**
         * Enter description here...
         *
         * @param string $className
         * @param string $dirName
         * @param string $origDirName
         * @return NBFrameObjectHandler
         */
        function &getHandler($className, &$environment) {
            static $mHandlerArr;
            $ret = false;
            $dirName = '';

            $classPath = str_replace('.', '/', basename($className));
            $classBaseName = basename($classPath);
            $classOffset = dirname('/'.$classPath);
            if ($classOffset == '/') $classOffset = '';
            $classOffsetName = str_replace('/', '.', dirname($classPath));

            if (preg_match('/^NBFrame\.(.*)/', $className, $match)) {
                $key = $className;
            } else if (!empty($environment)) {
                $dirName = $environment->mDirName;
                $key = $dirName.'_'.$className;
            } else {
                $key = $className;
            }

            $handlerClassName = $classBaseName.'Handler';

            if (!isset($mHandlerArr[$key])) {
                if (preg_match('/^NBFrame\.(.*)/', $className, $match)) {
                    NBFrame::using('Object');
                    NBFrame::using('ObjectHandler');
                    $className = $match[1];
                    NBFrame::using($className);
                    $classBaseName = 'NBFrame'.$classBaseName;
                    $handlerClassName = $classBaseName.'Handler';
                } else if (!empty($environment) && !class_exists($handlerClassName)) {
                    NBFrame::using('Object');
                    NBFrame::using('ObjectHandler');
                    $fileName = NBFrame::findFile($className.'.class.php', $environment, 'class' , true, $dirName.'_');
                    if ($fileName) require_once $fileName;
                }
                if (class_exists($dirName.'_'.$handlerClassName)) {
                    // This handler may be Module custom handler
                    $mHandlerArr[$key] =& new $dirName.'_'.$handlerClassName($GLOBALS['xoopsDB']);
                    $ret =& $mHandlerArr[$key];
                } else if (class_exists($handlerClassName)) {
                    $mHandlerArr[$key] =& new $handlerClassName($GLOBALS['xoopsDB']);
                    $ret =& $mHandlerArr[$key];
                } else if (preg_match('/^realname\.(\w+)/i', $className, $match)) {
                    // Automatic Creating Handler Class with Table Names
                    $handlerClassName = 'NBFrameRealTable'.$handlerClassName;
                    $entityClassName = 'NBFrameRealTable'.$classBaseName;
                    $mHandlerArr[$key] =& new NBFrameObjectHandler($GLOBALS['xoopsDB']);
                    $ret =& $mHandlerArr[$key];
                    $ret->setTableBaseName($match[1]);
                    $ret->mUseModuleTablePrefix = false;
                    $ret->mClassName = $handlerClassName;
                    $ret->mEntityClassName = $entityClassName;
                }
                
                if ($ret && !empty($environment) && $ret->mUseModuleTablePrefix) {
                    $ret->setTableBaseName($dirName.'_'.$ret->getTableBaseName());
                }
                if ($ret && !empty($environment)) {
                    $ret->mEnvironment =& NBFrame::makeClone($environment);
                    $target = $environment->mTarget;
                } else {
                    $target = 0;
                }
                $ret->mLanguage =& NBFrame::getLanguageManager($target);
            } else {
                $ret =& $mHandlerArr[$key];
            }
            return $ret;
        }

        function &getLanguageManager($target=NBFRAME_TARGET_MAIN) {
            static $mLanguageArr;
            NBFrame::using('Language');
            if (!empty($target)) {
                $environment =& NBFrame::getEnvironments($target);
                $dirName = $environment->mDirName;
            } else {
                $dirName = '_NB_System_';
            }
            if (empty($mLanguageArr[$dirName][$target])) {
                $mLanguageArr[$dirName][$target] =& new NBFrameLanguage($target);
            }
            return $mLanguageArr[$dirName][$target];
        }

        // Utilitiy Functions for Install Module

        function &getXoopsVersionFileName($origDirName) {
            $environment =& NBFrame::prepare($origDirName, NBFRAME_TARGET_INSTALLER);
            $fileName= NBFrame::findFile('xoops_version.php', $environment, '', false);
            return $fileName;
        }
        
        function &getInstallHelper() {
            static $mInstallHelperArr;

            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            $dirname = $environment->mDirName;
            $origname = $environment->mOrigDirName;

            if (!isset($mInstallHelperArr[$dirname])) {
                NBFrame::using('InstallHelper');
                $mInstallHelperArr[$dirname] =& new NBFrameInstallHelper($dirname, $origname);
            }
            return $mInstallHelperArr[$dirname];
        }

        function parseXoopsVerionFile(&$modversion) {
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);

            $modversion['name'] .= ' ['.$environment->mDirName.']';
            $modversion['dirname'] = $environment->mDirName;
            if (!empty($modversion['image'])) {
                $modversion['image'] = '?action=NBFrame.GetModuleIcon&file='.basename($modversion['image']);
            } else {
                $modversion['image'] = '?action=NBFrame.GetModuleIcon';
            }

            if (@$modversion['hasAdmin']){
                $modversion['adminindex'] = 'index.php?action='.$environment->getAttribute('AdminMainAction');
                $modversion['adminmenu'] = 'include/NBFrameAdminMenu.inc.php';
            }
            // SubMenu Settings
            if ($fname = NBFrame::findFile('sub_menu.inc.php',$environment, '/include', false, $environment->mOrigDirName)) {
                include $fname;
            }
            // Table Settings
            if ($fname = NBFrame::findFile('tabledef.inc.php',$environment, '/include', false, $environment->mOrigDirName)) {
                include $fname;
                $modversion['tables'] = array();
                foreach($tableDef[$environment->mOrigDirName] as $key =>$value) {
                    $modversion['tables'][] = $environment->mDirName.'_'.$key;
                }
            }
            // Template Settings
            $tempaltePath = NBFrame::findFile('templates',$environment, '');
            $templateFiles = glob($tempaltePath.'/*.html');
            $i = 1;
            unset($modversion['templates']);
            foreach ($templateFiles as $templateFile) {
                $modversion['templates'][$i] = array('file'=>$environment->prefix(basename($templateFile)), 'desc'=>'');
                $i++;
            }
            if (isset($modversion['blocks'])){
                foreach($modversion['blocks'] as $key=>$block) {
                    $modversion['blocks'][$key]['file'] = 'NBFrameBlockLoader.php';
                    if (isset($block['template'])) {
                        $modversion['blocks'][$key]['template'] = $environment->prefix($block['template']);
                    }
                    if (isset($block['class'])) {
                        $modversion['blocks'][$key]['NBclass'] = $block['class'];
                        unset($modversion['blocks'][$key]['class']);
                        if (isset($block['show_func'])) {
                            $modversion['blocks'][$key]['NBShowMethod'] = $modversion['blocks'][$key]['show_func'];
                            $modversion['blocks'][$key]['show_func'] = $environment->prefix('b_'.$modversion['blocks'][$key]['NBclass'].'_'.$block['show_func']);
                        }
                        if (isset($block['edit_func'])) {
                            $modversion['blocks'][$key]['NBEditMethod'] = $modversion['blocks'][$key]['edit_func'];
                            $modversion['blocks'][$key]['edit_func'] = $environment->prefix('b_'.$modversion['blocks'][$key]['NBclass'].'_'.$block['edit_func']);
                        }
                    } else {
                        if (isset($block['show_func'])) {
                            if (preg_match('/^b_(.*)_show$/', $block['show_func'], $matches)) {
                                $modversion['blocks'][$key]['NBclass'] = $matches[1];
                                $modversion['blocks'][$key]['NBShowMethod'] = 'show';
                            }
                            $modversion['blocks'][$key]['show_func'] = $environment->prefix($block['show_func']);
                        }
                        if (isset($block['edit_func'])) {
                            if (preg_match('/^b_(.*)_edit$/', $block['edit_func'], $matches)) {
                                $modversion['blocks'][$key]['NBclass'] = $matches[1];
                                $modversion['blocks'][$key]['NBEditMethod'] = 'edit';
                            }
                            $modversion['blocks'][$key]['edit_func'] = $environment->prefix($block['edit_func']);
                        }
                    }
                    if (isset($block['show_func'])) {
                        $GLOBALS['_NBBlockFuncInfo'][$environment->mDirName][$modversion['blocks'][$key]['show_func']]['class'] = $modversion['blocks'][$key]['NBclass'];
                        $GLOBALS['_NBBlockFuncInfo'][$environment->mDirName][$modversion['blocks'][$key]['show_func']]['method'] = $modversion['blocks'][$key]['NBShowMethod'];
                    }
                    if (isset($modversion['blocks'][$key]['edit_func'])) {
                        $GLOBALS['_NBBlockFuncInfo'][$environment->mDirName][$modversion['blocks'][$key]['edit_func']]['class'] = $modversion['blocks'][$key]['NBclass'];
                        $GLOBALS['_NBBlockFuncInfo'][$environment->mDirName][$modversion['blocks'][$key]['edit_func']]['method'] = $modversion['blocks'][$key]['NBEditMethod'];
                    }
                }
            }
            if (!empty($modversion['hasSearch'])){
                if (isset($modversion['search']['class'])) {
                    if (isset($modversion['search']['func'])) {
                        $class = $modversion['search']['class'];
                        $method = 'search';
                        if (isset($modversion['search']['func'])) {
                            $method = $modversion['search']['func'];
                        }
                    }
                } else {
                    if (isset($modversion['search']['func'])) {
                        $class = $modversion['search']['func'];
                        $method = 'show';
                    }
                }
                $modversion['search']['func'] = $environment->prefix($class.'_'.$method);
                $modversion['search']['file'] = 'include/NBFrameSearchLoader.php';

                $GLOBALS['_NBSearchFuncInfo'][$environment->mDirName]['class'] = $class;
                $GLOBALS['_NBSearchFuncInfo'][$environment->mDirName]['method'] = $method;
            }

            NBFrame::_prepareCustomInstaller($modversion);

            $installHelper =& NBFrame::getInstallHelper();
            if ($installHelper->isPreModuleUpdate() && !$installHelper->isPreModuleUpdateDone() ) {
                $installHelper->preUpdateProcessforDuplicate();
                if(!defined('XOOPS_CUBE_LEGACY')) {
                    $installHelper->preBlockUpdateProcess($modversion);
                }
            }
        }

        function langConstPrefix($prefix='',$target=NBFRAME_TARGET_MAIN) {
            $environment =& NBFrame::getEnvironments($target);
            if ($environment) {
                $dirname = $environment->mDirName;
            } else if (!empty($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname')=='altsys' && !empty($_GET['dirname'])) {
                $dirname = htmlspecialchars($_GET['dirname'], ENT_QUOTES);
            }
            if (empty($dirname)) {
                return '';
            }
            if (!empty($prefix)) {
                return '_'.$prefix.'_'.strtoupper($dirname).'_';
            } else {
                return '_'.strtoupper($dirname).'_';
            }
        }

        function _prepareCustomInstaller(&$modversion) {
            $installHelper =& NBFrame::getInstallHelper();
            if (isset($modversion['NBFrameOnInstall']) && !empty($modversion['NBFrameOnInstall']['file']) && !empty($modversion['NBFrameOnInstall']['func'])) {
                $installHelper->mOnInstallOption = $modversion['NBFrameOnInstall'];
            } else {
                $installHelper->mOnInstallOption = null;
            }
            if (isset($modversion['NBFrameOnUpdate']) && !empty($modversion['NBFrameOnUpdate']['file']) && !empty($modversion['NBFrameOnUpdate']['func'])) {
                $installHelper->mOnUpdateOption = $modversion['NBFrameOnUpdate'];
            } else {
                $installHelper->mOnUpdateOption = null;
            }
            if (isset($modversion['NBFrameOnUninstall']) && !empty($modversion['NBFrameOnUninstall']['file']) && !empty($modversion['NBFrameOnUninstall']['func'])) {
                $installHelper->mOnUninstallOption = $modversion['NBFrameOnUninstall'];
            } else {
                $installHelper->mOnUninstallOption = null;
            }
            $modversion['onInstall'] = 'include/NBFrameInstall.inc.php';
            $modversion['onUpdate'] = 'include/NBFrameInstall.inc.php';
            $modversion['onUninstall'] = 'include/NBFrameInstall.inc.php';
        }

        // Utilitiy Functions for Blocks

        function prepareBlockFunction(&$environment) {
            if (isset($GLOBALS['_NBBlockFuncInfo'][$environment->mDirName])) {
                $blockFuncInfoArr = $GLOBALS['_NBBlockFuncInfo'][$environment->mDirName];
                foreach ($blockFuncInfoArr as $funcName =>$blockFuncInfo) {
                    NBFrame::using('blocks.'.$blockFuncInfo['class'], $environment);
                    $envStr = serialize($environment);
                    $str = 'if (!function_exists("'.$funcName.'")) {'."\n";
                    $str .= 'function '.$funcName.'($option) {'."\n";
                    $str .= '  $environment = unserialize(\''.$envStr.'\');'."\n";
                    $str .= 'return '.$blockFuncInfo['class'].'::'.$blockFuncInfo['method'].'($environment, $option); }}';
                    eval($str);
                }
            }
        }

        // Utilitiy Functions for Search
        function prepareSearchFunction(&$environment) {
            if (isset($GLOBALS['_NBSearchFuncInfo'][$environment->mDirName])) {
                $class = $GLOBALS['_NBSearchFuncInfo'][$environment->mDirName]['class'];
                $method = $GLOBALS['_NBSearchFuncInfo'][$environment->mDirName]['method'];
                $funcName = $environment->prefix($class.'_'.$method);
                NBFrame::using($class, $environment);

                $envStr = serialize($environment);
                $str = 'if (!function_exists("'.$funcName.'")) {'."\n";
                $str .= 'function '.$funcName.'($queryarray, $andor, $limit, $offset, $userid) {'."\n";
                $str .= '  $environment = unserialize(\''.$envStr.'\');'."\n";
                $str .= 'return '.$class.'::'.$method.'($environment, $queryarray, $andor, $limit, $offset, $userid); }}';
                eval($str);
            }
        }

        // Utilitiy Functions

        function getRequest($name, $reqTypes, $valType = '', $defaultValue = NBFRAME_NO_DEFAULT_PARAM, $mustExist = false){
            NBFrame::using('Request');
            static $request=null;
            if (!$request) $request =& new NBFrameRequest();
            return $request->getRequest($name, $reqTypes, $valType, $defaultValue, $mustExist);
        }

        function isRequestError($result) {
            if (is_object($result) && is_a($result, 'NBFrameRequestErr')) {
                return true;
            } else {
                return false;
            }
        }

        function checkAltSys($dirOnly=true) {
            if (defined('XOOPS_TRUST_PATH')) {
                if (is_dir(XOOPS_TRUST_PATH.'/libs/altsys')) {
                    if ($dirOnly) {
                        return true;
                    } else {
                        $module_handler =& xoops_gethandler('module') ;
                        if ($module_handler->getByDirname('altsys')) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        function isNoCommonAction($className, $environment) {
            $noCommonActions = $environment->getAttribute('NoCommonAction');
            if (!is_array($noCommonActions)) return false;
            return in_array($className, $noCommonActions);
        }

        function getAdminMenu($environment) {
            $languageManager =& NBFrame::getLanguageManager(NBFRAME_TARGET_TEMP);
            $adminmenu = array();
            if (NBFrame::checkAltSys(false)&&$environment->getAttribute('UseAltSys')) {
                if ($environment->getAttribute('UseBlockAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Block Admin'),
                                         'link'  => '?action=NBFrame.admin.AltSys&page=myblocksadmin' );
                }
                if ($environment->getAttribute('UseTemplateAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Template Admin'),
                                         'link'  => '?action=NBFrame.admin.AltSys&page=mytplsadmin' );
                }
                if ($environment->getAttribute('UseLanguageAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Language Admin'),
                                         'link'  => '?action=NBFrame.admin.AltSys&page=mylangadmin' );
                }
            } else {
                if ($environment->getAttribute('UseBlockAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Block Admin'),
                                         'link'  => '?action=NBFrame.admin.BlocksAdmin' );
                }
            }
            return $adminmenu;
        }
        
        function findFile($name, $environment, $offset='', $searchCurrent=true, $customPrefix='') {
            static $fileNames;
            $origDirName = $environment->mOrigDirName;
            $dirName = $environment->mDirName;
            $key = md5($dirName.$origDirName.$offset.$name);
            if (isset($fileNames[$key])) {
                return $fileNames[$key];
            }
            
            if (!empty($offset)) {
                $offset = preg_replace('/^\//','',trim($offset));
                $offset = preg_replace('/\/$/','',$offset);
                if ($offset != '') {
                    $offset.='/';
                }
            }
            $fileName = '';
            if (!empty($customPrefix) && file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$customPrefix.$name)){
                $fileName = XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$customPrefix.$name;
            } else if (file_exists(XOOPS_ROOT_PATH.'/common/modules/'.$origDirName.'/'.$offset.$name)) {
                $fileName = XOOPS_ROOT_PATH.'/common/modules/'.$origDirName.'/'.$offset.$name;
            } else if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/modules/'.$origDirName.'/'.$offset.$name)){
                $fileName = XOOPS_TRUST_PATH.'/modules/'.$origDirName.'/'.$offset.$name;
            } else if ($searchCurrent && file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$name)){
                $fileName = XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$name;
            }
            $fileNames[$offset][$name] = $fileName;
            return $fileName;
        }
        
        function &null()
        {
            $result = null;
            return $result;
        }
        
        function &makeClone(&$object)
        {
            $result =& __NBFrameClone($object);
            return $result;
        }
    }
}
?>
