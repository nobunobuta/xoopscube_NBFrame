<?php
/**
 *
 * @package NBFrame
 * @version $Id: NBFrame.class.php 1402 2008-03-18 13:37:22Z nobunobu $
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
    if (!defined('NBFRAME_TARGET_LOADER')) define('NBFRAME_TARGET_LOADER', 99);

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
            // If $className starts with '+', custom Override is disabled.
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
                    $fileName = $environment->findFile($className.'.class.php', $classType.$classOffset, true, '+');
                } else {
                    $fileName = $environment->findFile($className.'.class.php', $classType.$classOffset);
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
        function &getEnvironment($target=NBFRAME_TARGET_MAIN, $dirName = '', $forceCreate=false) {
            static $mEnvironmentArr;
            if ($target == NBFRAME_TARGET_SYS) {
                $ret = null;
            } else if (($target == NBFRAME_TARGET_MAIN)||($target == NBFRAME_TARGET_LOADER)) {
                if (!isset($mEnvironmentArr[$target]) || $forceCreate) {
                    unset($mEnvironmentArr[$target]);
                    NBFrame::using('Environment');
                    $mEnvironmentArr[$target] = new NBFrameEnvironment();
                    $mEnvironmentArr[$target]->setTarget($target);
                }
                $ret =& $mEnvironmentArr[$target];
            } else if (isset($mEnvironmentArr[$target][$dirName])) {
                $ret =& $mEnvironmentArr[$target][$dirName];
            } else if ($forceCreate) {
                NBFrame::using('Environment');
                $mEnvironmentArr[$target][$dirName] = new NBFrameEnvironment();
                $mEnvironmentArr[$target][$dirName]->setTarget($target);
                $ret =& $mEnvironmentArr[$target][$dirName];
            } else {
                $ret = null;
            }
            return $ret;
        }

        /**
         * Get NBFrameObjectHandler Children Class Singleton Instance
         *
         * @param string $className
         * @param string $dirName
         * @param string $origDirName
         * @return NBFrameObjectHandler
         */
        function &getHandler($className, &$environment) {
            static $sHandlerArr;
            static $sNullLanguageManager = null;
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
                $dirName = $environment->getDirName();
                $key = $environment->prefix($className);
            } else {
                $key = $className;
            }

            $handlerClassName = $classBaseName.'Handler';

            if (!isset($sHandlerArr[$key])) {
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
                    $fileName = $environment->findFile($className.'.class.php', 'class');
                    if ($fileName) require_once $fileName;
                }
                if (class_exists($dirName.'_'.$handlerClassName)) {
                    // This handler may be Module custom handler
                    $sHandlerArr[$key] =& new $dirName.'_'.$handlerClassName($GLOBALS['xoopsDB']);
                    $handler =& $sHandlerArr[$key];
                } else if (class_exists($handlerClassName)) {
                    $sHandlerArr[$key] =& new $handlerClassName($GLOBALS['xoopsDB']);
                    $handler =& $sHandlerArr[$key];
                } else if (preg_match('/^realname\.(\w+)/i', $className, $match)) {
                    // Automatic Creating Handler Class with Table Names
                    $handlerClassName = 'NBFrameRealTable'.$handlerClassName;
                    $entityClassName = 'NBFrameRealTable'.$classBaseName;
                    $sHandlerArr[$key] =& new NBFrameObjectHandler($GLOBALS['xoopsDB']);
                    $handler =& $sHandlerArr[$key];
                    $handler->setTableBaseName($match[1]);
                    $handler->mUseModuleTablePrefix = false;
                    $handler->mClassName = $handlerClassName;
                    $handler->mEntityClassName = $entityClassName;
                }
                
                if ($handler && !empty($environment)) {
                    if ($handler->mUseModuleTablePrefix) {
                        $handler->setTableBaseName($dirName.'_'.$handler->getTableBaseName());
                    }
                    $handler->mEnvironment =& NBFrame::makeClone($environment);
                    $handler->mLanguageManager =& $handler->mEnvironment->getLanguageManager();
                } else {
                    if (empty($sNullLanguageManager)) {
                        NBFrame::using('Language');
                        $sNullLanguageManager =& new NBFrameLanguage(NBFrame::null());
                    }
                    $handler->mLanguageManager =& $sNullLanguageManager;
                }
            } else {
                $handler =& $sHandlerArr[$key];
            }
            return $handler;
        }

        /**
         * Preparing Target Environment
         *
         * @param string $origDirName
         * @param int    $target
         *
         */
        function &prepare($target=NBFRAME_TARGET_MAIN) {
            $envtemp =& NBFrame::getEnvironment(NBFRAME_TARGET_LOADER);
            if (!empty($envtemp)) {
                $environment =& NBFrame::getEnvironment($target, $envtemp->getDirName(), true);
                $environment->setOrigDirName($envtemp->getOrigDirName());
                $environment->setDirBase($envtemp->getDirBase());
                $environment->mAttributeArr = $envtemp->mAttributeArr;
                if ($target != NBFRAME_TARGET_MAIN) {
                    $environment->getLanguageManager();
                }
            } else {
                $environment = null;
            }
            return $environment;
        }

        // Utilitiy Functions

        function langConstPrefix($prefix='', $dirname, $target=NBFRAME_TARGET_MAIN) {
            if (empty($dirname) && $target==NBFRAME_TARGET_LOADER) {
                $environment =& NBFrame::getEnvironment(NBFRAME_TARGET_LOADER);
                if ($environment) {
                    $dirname = $environment->getDirName();
                } else if (!empty($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname')=='altsys' && !empty($_GET['dirname'])) {
                    $dirname = htmlspecialchars($_GET['dirname'], ENT_QUOTES);
                }
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
        
        function checkAltSys($dirOnly=true) {
            if (defined('XOOPS_TRUST_PATH')) {
                if (is_dir(XOOPS_TRUST_PATH.'/libs/altsys')) {
                    if ($dirOnly) {
                        return true;
                    } else {
                        $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                        if ($moduleHandler->getByDirname('altsys')) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        function display404Page() {
            header('HTTP/1.0 404 Not Found');
            header('Content-Type: text/html; charset=iso-8859-1');
            include NBFRAME_BASE_DIR.'/templates/NBFramePage404.tpl.php';
            exit();
        }
        
        function getClock() {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }

        function getMySQLTimeStamp($timeStr) {
            if ($GLOBALS['xoopsUser']) {
                $timeoffset = $GLOBALS['xoopsUser']->getVar('timezone_offset');
            } else {
                $timeoffset = $GLOBALS['xoopsConfig']['default_TZ'];
            }
            return strtotime($timeStr) + $timeoffset * 3600;
        }
        
        function toShow($text) {
            if (isset($GLOBALS['NBFrameTextFilter'])) {
                return $GLOBALS['NBFrameTextFilter']->toShow($text);
            } else if (isset($GLOBALS['NBFrameTextSanitizer'])){
                $str = $GLOBALS['NBFrameTextSanitizer']->htmlSpecialChars($text);
                return $str;
            } else if (class_exists('XCube_Root')) {
                $root = XCube_Root::getSingleton();
                $GLOBALS['NBFrameTextFilter'] =& $root->getTextFilter();
                return $GLOBALS['NBFrameTextFilter']->toShow($text);
            } else {
                $GLOBALS['NBFrameTextSanitizer'] =& MyTextSanitizer::getInstance();
                $str = $GLOBALS['NBFrameTextSanitizer']->htmlSpecialChars($text);
                return $str;
            }
        }
        function toEdit($text) {
            if (isset($GLOBALS['NBFrameTextFilter'])) {
                return $GLOBALS['NBFrameTextFilter']->toEdit($text);
            } else if (isset($GLOBALS['NBFrameTextSanitizer'])){
                $str = $GLOBALS['NBFrameTextSanitizer']->htmlSpecialChars($text);
                return $str;
            } else if (class_exists('XCube_Root')) {
                $root = XCube_Root::getSingleton();
                $GLOBALS['NBFrameTextFilter'] =& $root->getTextFilter();
                return $GLOBALS['NBFrameTextFilter']->toEdit($text);
            } else {
                $GLOBALS['NBFrameTextSanitizer'] =& MyTextSanitizer::getInstance();
                $str = $GLOBALS['NBFrameTextSanitizer']->htmlSpecialChars($text);
                return $str;
            }
        }
        function toShowTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1) {
            if (isset($GLOBALS['NBFrameTextFilter'])) {
                return $GLOBALS['NBFrameTextFilter']->toShowTarea($text, $html, $smiley, $xcode, $image, $br);
            } else if (isset($GLOBALS['NBFrameTextSanitizer'])){
                $str = $GLOBALS['NBFrameTextSanitizer']->displayTarea($text, $html, $smiley, $xcode, $image, $br);
                return $str;
            } else if (class_exists('XCube_Root')) {
                $root = XCube_Root::getSingleton();
                $GLOBALS['NBFrameTextFilter'] =& $root->getTextFilter();
                return $GLOBALS['NBFrameTextFilter']->toShowTarea($text, $html, $smiley, $xcode, $image, $br);
            } else {
                $GLOBALS['NBFrameTextSanitizer'] =& MyTextSanitizer::getInstance();
                $str = $GLOBALS['NBFrameTextSanitizer']->displayTarea($text, $html, $smiley, $xcode, $image, $br);
                return $str;
            }
        }
        
        function getLocalTimeZone() {
            if (is_object($GLOBALS['xoopsUser'])) {
                $localTZ = $GLOBALS['xoopsUser']->getVar('timezone_offset');
            } else {
                $localTZ = $GLOBALS['xoopsConfig']['default_TZ'];
            }
            return $localTZ;
        }
        
        function setPHPLocalTimeZone() {
            if (function_exists('date_default_timezone_set')) {
                if (NBFrame::getLocalTimeZone()>0) {
                  $TZStr = 'Etc/GMT-'.abs(NBFrame::getLocalTimeZone());
                } else {
                  $TZStr = 'Etc/GMT+'.abs(NBFrame::getLocalTimeZone());
                }
                date_default_timezone_set($TZStr);
            }
        }
        
        function setPHPServerTimeZone() {
            if (function_exists('date_default_timezone_set')) {
                if ($GLOBALS['xoopsConfig']['server_TZ']>0) {
                  $TZStr = 'Etc/GMT-'.abs($GLOBALS['xoopsConfig']['server_TZ']);
                } else {
                  $TZStr = 'Etc/GMT+'.abs($GLOBALS['xoopsConfig']['server_TZ']);
                }
                date_default_timezone_set($TZStr);
            }
        }

        function convLocalToServerTime($timestamp) {
            NBFrame::setPHPServerTimeZone();
            $timestamp -= (NBFrame::getLocalTimeZone() -  $GLOBALS['xoopsConfig']['server_TZ'])*3600;
            return $timestamp;
        }

        function convGmtToServerTime($timestamp) {
            NBFrame::setPHPServerTimeZone();
            $timestamp += $GLOBALS['xoopsConfig']['server_TZ']*3600;
            return $timestamp;
        }

        function convServerToLocalTime($timestamp) {
            NBFrame::setPHPServerTimeZone();
            $timestamp += (NBFrame::getLocalTimeZone() -  $GLOBALS['xoopsConfig']['server_TZ'])*3600;
            return $timestamp;
        }
        
        function getCurrentURL() {
            $parseArray = parse_url(XOOPS_URL);
            $path = @$parseArray['path'];
            $offset = preg_replace('/^'.preg_quote($path,'/').'/', '', $_SERVER['REQUEST_URI']);
            return XOOPS_URL.$offset;
        }

        function addQueryArgs($url, $queryArray) {
            $parseArray = parse_url($url);
            $prefix = (isset($parseArray['query'])) ? '&' : '?';
            foreach($queryArray as $key=>$value) {
                $url .= $prefix.$key.'='.rawurlencode($value);
                $prefix = '&';
            }
            return $url;
        }

        function removeQueryArgs($url, $querykeyArray) {
            foreach($querykeyArray as $key) {
                $url = preg_replace(
                            array('/([?&])'.preg_quote($key,'/').'\=(.*?)(&|$)/',
                                  '/([?&])&/',
                                  '/[?&]$/' 
                            ),
                            array('\\1\\3',
                                  '\\1',
                                  ''
                            ),
                            $url);
            }
            return $url;
        }
        
        function _Smarty_NBBlockMsg($params, &$smarty) {
            if (!empty($params['msg'])) {
                $environment =& $smarty->_tpl_vars['block']['NBEnvrionment'];
                return $environment->__l($params['msg']);
            }
        }
        function _Smarty_NBBlockError($params, &$smarty) {
            if (!empty($params['msg'])) {
                $environment =& $smarty->_tpl_vars['block']['NBEnvrionment'];
                return $environment->__e($params['msg']);
            }
        }
        function _Smarty_NBBlockActionUrl($params, &$smarty) {
            $environment =& $smarty->_tpl_vars['block']['NBEnvrionment'];
            return $environment->_Smarty_NBFrameActionUrl($params, $smarty);
        }
        
        function createModel(&$instance, $module) {
            $dirName = $module->get('dirname');
            if (file_exists(XOOPS_ROOT_PATH.'/modules/'.$dirName.'/include/NBFrameLoader.inc.php')) {
                NBFrame::using('ModuleAdapter');
                $instance = new NBFrameModuleAdapter($module);
            }
        }
    }
}
?>
