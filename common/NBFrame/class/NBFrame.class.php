<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
if (!class_exists('NBFrame')) {
    if (!defined('NBFRAME_TARGET_MAIN')) define('NBFRAME_TARGET_MAIN',1);
    if (!defined('NBFRAME_TARGET_BLOCK')) define('NBFRAME_TARGET_BLOCK',2);
    if (!defined('NBFRAME_TARGET_INSTALLER')) define('NBFRAME_TARGET_INSTALLER',3);
    if (!defined('NBFRAME_TARGET_TEMP')) define('NBFRAME_TARGET_TEMP', 99);

    if (!defined('NBFRAME_NO_DEFAULT_PARAM')) define('NBFRAME_NO_DEFAULT_PARAM', '__nodefault__');

    class NBFrame {
        /**
         * Declaration of NBFrame Class
         *
         * @param string $className
         * @param NBFrameEnvironment $environment
         * @param string $classType
         */
        function using($className, $environment='', $classType='class') {
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
                $dirName = $environment->mCurrentDirName;
                $fileName = NBFrame::findFile($className.'.class.php', $environment, $classType.$classOffset, true, $dirName.'_');
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
            $environment->setCurrentDirBase($currentDirBase);
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
                $environment->setCurrentDirBase($envtemp->mCurrentDirBase);
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
            if (isset($mEnvironmentArr[$target])) {
                $ret =& $mEnvironmentArr[$target];
            } else if ($force) {
                NBFrame::using('Environment');
                $mEnvironmentArr[$target] =& new NBFrameEnvironment();
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
            if (empty($defaultAction)) $defaultAction = $environment->getAttribute('defaultActionOp');
            if (empty($allowedAction)) $allowedAction = $environment->getAttribute('allowedActionOp');
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

            if ($action =& NBFrame::getInstance($className, $environment, 'Action')) {
                $action->mActionName = $requestAction;
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
            $dirName = $environment->mCurrentDirName;
            
            $instance = null;

            if (class_exists($dirName.'_'.$classBaseName)) {
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
            $dirName = $origDirName = '';
            if (!empty($environment)) {
                $dirName = $environment->mCurrentDirName;
                $origDirName = $environment->mOrigDirName;;
                $key = $dirName.'_'.$className;
                if (empty($origDirName)) {
                    $origDirName = $dirName;
                }
            } else {
                $key = $className;
            }

            $handlerClassName = $className.'Handler';

            if (!isset($mHandlerArr[$key])) {
                if (!empty($dirName) && !class_exists($handlerClassName)) {
                    NBFrame::using('Object');
                    NBFrame::using('ObjectHandler');

                    $dirName = $environment->mCurrentDirName;
                    $fileName = NBFrame::findFile($className.'.class.php', $environment, 'class' , true, $dirName.'_');
                    if ($fileName) require_once $fileName;
                }
                if (class_exists($dirName.'_'.$handlerClassName)) {
                    $mHandlerArr[$key] =& new $dirName.'_'.$handlerClassName($GLOBALS['xoopsDB']);
                    $ret =& $mHandlerArr[$key];
                } else if (class_exists($handlerClassName)) {
                    $mHandlerArr[$key] =& new $handlerClassName($GLOBALS['xoopsDB']);
                    $ret =& $mHandlerArr[$key];
                }
                if ($ret && !empty($environment)) {
                    $ret->setTableBaseName($dirName.'_'.$ret->getTableBaseName());
                    $ret->mEnvironment = $environment;
                }
            } else {
                $ret =& $mHandlerArr[$key];
            }
            return $ret;
        }

        function &getLanguageManager($target=NBFRAME_TARGET_MAIN) {
            static $mLanguageArr;
            NBFrame::using('Language');
            $environment =& NBFrame::getEnvironments($target);
            $origDirName = $environment->mOrigDirName;
            if (empty($mLanguageArr[$origDirName][$target])) {
                $mLanguageArr[$origDirName][$target] =& new NBFrameLanguage($target);
            }
            return $mLanguageArr[$origDirName][$target];
        }

        // Utilitiy Functions for Install Module

        function &getXoopsVersionFileName($origDirName) {
            $environment =& NBFrame::prepare($origDirName, NBFRAME_TARGET_INSTALLER);
            $fineName= NBFrame::findFile('xoops_version.php', $environment, '', false);
            return $fineName;
        }
        
        function &getInstallHelper($dupmark='XX') {
            static $mInstallHelperArr;

            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            $dirname = $environment->mCurrentDirName;
            $origname = $environment->mOrigDirName;

            if (!isset($mInstallHelperArr[$dirname])) {
                NBFrame::using('InstallHelper');
                $mInstallHelperArr[$dirname] =& new NBFrameInstallHelper($dirname, $origname, $dupmark);
            }
            return $mInstallHelperArr[$dirname];
        }

        function setModuleTemplate($basename) {
            $installHelper =& NBFrame::getInstallHelper();
            return $installHelper->setModuleTemplateforDuplicate($basename);
        }
        
        function setBlockTemplate($basename, $isBlock=false) {
            $installHelper =& NBFrame::getInstallHelper();
            return $installHelper->setBlockTemplateforDuplicate($basename);
        }
        
        function onInstallProcess(&$module, $options=null) {
            $installHelper =& NBFrame::getInstallHelper();
            $ret = $installHelper->postInstallProcessforDuplicate();
            if (!$installHelper->executeCustomInstallProcess($options, $module)) $ret = false;
            return $ret;
        }

        function onUpdateProcess(&$module, $prevVer, $options=null) {
            $installHelper =& NBFrame::getInstallHelper();
            $installHelper->putPreProcessMsg();
            $ret = $installHelper->postUpdateProcessforDuplicate();
            if (!$installHelper->executeCustomUpdatellProcess($options, $module, $prevVer)) $ret = false;
            return $ret;
        }

        function onUninstallProcess(&$module, $options=null) {
            $installHelper =& NBFrame::getInstallHelper();
            $ret = $installHelper->executeCustomInstallProcess($options);
            return $ret;
        }

        function executePreUpdateProcess($modversion) {
            $installHelper =& NBFrame::getInstallHelper();
            if ($installHelper->isPreModuleUpdate() && !$installHelper->isPreModuleUpdateDone() ) {
                $installHelper->preUpdateProcessforDuplicate();
                $installHelper->preBlockUpdateProcess($modversion);
            }
        }

        function prepareInstaller(&$modversion) {
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

        function prepareOnInstallFunction() {
            $installHelper =& NBFrame::getInstallHelper();
            $options = $installHelper->mOnInstallOption;
            $dirName = $installHelper->mDirName;
            $str = 'function xoops_module_install_'.$dirName.'(&$module) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= 'return NBFrame::onInstallProcess(&$module, $options); }';
            eval($str);
        }

        function prepareOnUpdateFunction() {
            $installHelper =& NBFrame::getInstallHelper();
            $options = $installHelper->mOnUpdateOption;
            $dirName = $installHelper->mDirName;
            $str = 'function xoops_module_update_'.$dirName.'(&$module, $prevVer) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= 'return NBFrame::onUpdateProcess(&$module, $prevVer, $options); }';
            eval($str);
        }

        function prepareOnUninstallFunction() {
            $installHelper =& NBFrame::getInstallHelper();
            $options = $installHelper->mOnUninstallOption;
            $dirName = $installHelper->mDirName;
            $str = 'function xoops_module_uninstall_'.$dirName.'(&$module) {';
            $str .= '$options=array();';
            if (is_array($options) && !empty($options['file']) && !empty($options['func'])) {
                $str .= '$options["file"]="'.$options['file'].'";';
                foreach($options['func'] as $funcname) {
                    $str .= '$options["func"][]="'.$funcname.'";';
                }
            }
            $str .= 'return NBFrame::onUninstallProcess(&$module, $options); }';
            eval($str);
        }

        // Utilitiy Functions for Blocks

        function prepareBlockFunction(&$environment) {
            $blockClasses = $environment->getAttribute('blockHandler');
            foreach($blockClasses as $blockClass) {
                NBFrame::using('blocks.'.$blockClass, $environment);
                NBFrame::prepareBlockEditFunction($environment, $blockClass);
                NBFrame::prepareBlockShowFunction($environment, $blockClass);
            }
        }

        function prepareBlockEditFunction($environment, $className) {
            $dirName = $environment->mCurrentDirName;
            $envStr = serialize($environment);
            $str = 'function b_'.$dirName.'_'.$className.'_edit($option) {'."\n";
            $str .= '  $environment =& unserialize(\''.$envStr.'\');'."\n";
            $str .= 'return '.$className.'::edit($environment, $option); }';
            eval($str);
        }

        function prepareBlockShowFunction($environment, $className) {
            $dirName = $environment->mCurrentDirName;
            $envStr = serialize($environment);
            $str = 'function b_'.$dirName.'_'.$className.'_show($option) {'."\n";
            $str .= '  $environment =& unserialize(\''.$envStr.'\');'."\n";
            $str .= 'return '.$className.'::show($environment, $option); }';
            eval($str);
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
        
        function findFile($name, $environment, $offset='', $searchCurrent=true, $customPrefix='') {
            static $fileNames;
            if (isset($fileNames[$offset][$name])) {
                return $fileNames[$offset][$name];
            }
            $origDirName = $environment->mOrigDirName;
            $dirName = $environment->mCurrentDirName;
            if (!empty($offset)) {
                $offset = preg_replace('/^\//','',trim($offset));
                $offset = preg_replace('/\/$/','',$offset);
                $offset.='/';
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
    }
}
?>
