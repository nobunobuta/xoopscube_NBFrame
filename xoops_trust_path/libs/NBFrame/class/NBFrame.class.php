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
         * Declaration of NBFrame Using Class
         *
         * @param string $className
         * @param NBFrameEnvironment $environment  if null, using NBFrame core classes;
         * @param string $classType
         *
         */
        function using($className, $environment=null, $classType='class') {
            // if $className starts with '+', custom Override is disabled.
            if (substr($className, 0, 1) == '+') {
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
         * Create or Return Environment of Specified Target
         *
         * @param int     $target
         * @param boolean $force (True: create a new environment if not exists)
         *
         */
        function &getEnvironments($target=NBFRAME_TARGET_MAIN, $forceCreate = false) {
            static $mEnvironmentArr;
            if ($target == NBFRAME_TARGET_SYS) {
                $ret = null;
            } else if (isset($mEnvironmentArr[$target])) {
                if ($target == NBFRAME_TARGET_TEMP && $forceCreate) {
                    unset($mEnvironmentArr[$target]);
                    NBFrame::using('Environment');
                    $mEnvironmentArr[$target] =& new NBFrameEnvironment();
                    $mEnvironmentArr[$target]->mTarget = $target;
                }
                $ret =& $mEnvironmentArr[$target];
            } else if ($forceCreate) {
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
         * @param string $defaultAction
         * @param string $defaultAction
         *
         */
        function executeAction(&$environment, $defaultAction='', $allowedAction=array()) {
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
            if (NBFrameBase::checkAltSys() && 
                isset($_GET['lib']) && ($_GET['lib']=='altsys') && 
                isset($_GET['page'])) {
                $className = 'NBFrame.admin.AltSys';
            }
            if (($environment->getAttribute('AutoUpdateMode')===true) && !NBFrameBase::isNoCommonAction($className, $environment)) {
                $info = $GLOBALS['xoopsModule']->getInfo();

                $installHelper =& NBFrameBase::getInstallHelper();
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
                $ret->mLanguage =& NBFrameBase::getLanguageManager($target);
            } else {
                $ret =& $mHandlerArr[$key];
            }
            return $ret;
        }

        function langConstPrefix($prefix='',$dirname, $target=NBFRAME_TARGET_MAIN) {
            if (empty($dirname)) {
                $environment =& NBFrame::getEnvironments($target);
                if ($environment) {
                    $dirname = $environment->mDirName;
                } else if (!empty($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname')=='altsys' && !empty($_GET['dirname'])) {
                    $dirname = htmlspecialchars($_GET['dirname'], ENT_QUOTES);
                }
            }
            if ($dirname == '') {
                return '';
            }
            if (!empty($prefix)) {
                return '_'.$prefix.'_'.strtoupper($dirname).'_';
            } else {
                return '_'.strtoupper($dirname).'_';
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
            } else if (defined('XOOPS_TRUST_PATH') && file_exists(XOOPS_TRUST_PATH.'/modules/'.$origDirName.'/'.$offset.$name)){
                $fileName = XOOPS_TRUST_PATH.'/modules/'.$origDirName.'/'.$offset.$name;
            } else if ($searchCurrent && file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$name)){
                $fileName = XOOPS_ROOT_PATH.'/modules/'.$dirName.'/'.$offset.$name;
            }
            $fileNames[$offset][$name] = $fileName;
            return $fileName;
        }
        
        function checkRight($gperm_name, $gperm_itemid=1, $bypassAdminCheck = false) {
            if (is_object($GLOBALS['xoopsUser'])) {
                $groups = $GLOBALS['xoopsUser']->getGroups();
            } else {
                $groups = array(XOOPS_GROUP_ANONYMOUS);
            }
            $groupPermHandler =& NBFrame::getHandler('NBFrame.xoops.GroupPerm', NBFrame::null());
            return $groupPermHandler->checkRight($gperm_name, $gperm_itemid, 
                                        $groups, $GLOBALS['xoopsModule']->getVar('mid'), $bypassAdminCheck);
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
        
        function getMySQLTimeStamp($timeStr) {
            if ($GLOBALS['xoopsUser']) {
                $timeoffset = $GLOBALS['xoopsUser']->getVar('timezone_offset');
            } else {
                $timeoffset = $GLOBALS['xoopsConfig']['default_TZ'];
            }
            return strtotime($timeStr) + $timeoffset * 3600;
        }
    }
}
?>
