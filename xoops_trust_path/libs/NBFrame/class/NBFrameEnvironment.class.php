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
if (!class_exists('NBFrameEnvironment')) {
    class NBFrameEnvironment {
        var $mOrigDirName;
        var $mDirBase;
        var $mDirName;
        var $mUrlBase;
        var $mAttributeArr;
        var $mTarget;
        var $mModule = null;
        var $mLanguageManager = null;
        var $mModuleInfo = array();
        
        var $mIsD3 = false;
        
        function NBFrameEnvironment($origDirName='', $currentDirBase='') {
            $this->setOrigDirName($origDirName);
            $this->setDirBase($currentDirBase);
        }

        function setOrigDirName($origDirName='') {
            if (!empty($origDirName)) {
                if (defined('XOOPS_TRUST_PATH') && is_dir(XOOPS_TRUST_PATH.'/modules/'.$origDirName)) {
                    $this->mOrigDirName = $origDirName;
                    $this->mIsD3 = true;
                }
            }
        }

        function getOrigDirName() {
            if ($this->mOrigDirName) {
                return $this->mOrigDirName;
            } else if (!$this->isD3()) {
                return $this->mDirName;
            } else {
                return '';
            }
        }

        function setDirBase($dirBase='') {
            if (!empty($dirBase)) {
                $this->mDirBase = $dirBase;
                $this->mDirName = basename($dirBase);
                $this->mUrlBase = XOOPS_URL.'/modules/'.$this->mDirName;
            }
        }

        function getDirName() {
            return $this->mDirName;
        }
        
        function getDirBase() {
            return $this->mDirBase;
        }
        
        function getUrlBase () {
            return $this->mUrlBase;
        }

        function getOrigDirBase() {
            if ($this->isD3()) {
                return XOOPS_TRUST_PATH.'/modules/'.$this->mOrigDirName;
            } else if (!$this->mIsD3) {
                return $this->mDirBase;
            }
        }

        function isD3() {
            return $this->mIsD3;
        }

        function setAttribute($name, $value) {
            $this->mAttributeArr[$name] = $value;
        }

        function getAttribute($name='') {
            if (empty($name)) {
                return $this->mAttributeArr;
            } else if (isset($this->mAttributeArr[$name])) {
                return $this->mAttributeArr[$name];
            } else {
                return null;
            }
        }

        function &getModule() {
            if (!is_object($this->mModule)) {
                $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                $this->mModule =& $moduleHandler->getByEnvironment($this);
            }
            return $this->mModule;
        }
        
        function &getLanguageManager() {
            if (!is_object($this->mLanguageManager)) {
                NBFrame::using('Language');
                $this->mLanguageManager =& new NBFrameLanguage($this);
            }
            return $this->mLanguageManager;
        }

        function prefix($basename) {
            return $this->mDirName.'_'.$basename;
        }

        /**
         * Action Executer
         *  (This is a Main Process of NBFrame)
         *
         * @param string $defaultAction
         * @param string $allowedAction[]
         * @param string $dialogAction[]
         *
         */
        function executeAction($requestAction='', $defaultAction='', $allowedAction=array(), $dialogAction=array()) {
            // Setup Default Action Array
            if (empty($defaultAction)) {
                if (empty($requestAction)) {
                    $defaultAction = $this->getAttribute('ModueleMainAction');
                } else {
                    $defaultAction = $requestAction;
                }
            }

            // Setup Allowed Action Names
            if (empty($allowedAction)) {
                $allowedAction = $this->getAttribute('AllowedAction');
                if (empty($allowedAction)) $allowedAction = array();
            }
            if (!empty($allowedAction)) {
                if ($this->getAttribute('UseAltSys')) {
                    $allowedAction[] = 'NBFrame.admin.AltSys';
                }
                if ($this->getAttribute('UseBlockAdmin')) {
                    $allowedAction[] = 'NBFrame.admin.BlocksAdmin';
                }
                $allowedAction[] = 'NBFrame.GetModuleIcon';
                $allowedAction[] = 'NBFrame.GetImage';
                $allowedAction[] = 'NBFrame.Redirect';
            }

            // Setup Dialog Action Names
            if (empty($dialogAction)) {
                $dialogAction = $this->getAttribute('DialogAction');
                if (empty($dialogAction)) $dialogAction = array();
            }
            
            // Parse Requested URL
            $this->parseURL();

            // Check Requested Action Name
            if (!empty($requestAction)) {
                $className = $requestAction;
            } else if (!empty($_REQUEST['action'])) {
                $requestAction = basename($_REQUEST['action']);
                if ($allowedAction && in_array($requestAction, $allowedAction)) {
                    $className = $requestAction;
                } else {
                    $className = '';   //@ToDo
                }
            } else {
                $requestAction = '';
                $className = $defaultAction;
            }

            // Special Check for AltSys Admin Screen Request
            if (NBFrame::checkAltSys() && 
                isset($_GET['lib']) && ($_GET['lib']=='altsys') && 
                isset($_GET['page'])) {
                $className = 'NBFrame.admin.AltSys';
            }

            // Call Custom URL Parser in a specified Action
            if ($rawParm = $this->getAttribute('RawParam')) {
                NBFrame::using($className.'Action', $this);
                if (class_exists($className.'Action') && is_callable(array($className.'Action','parseURL'),false)) {
                    $className = call_user_func(array($className.'Action','parseURL'), array(&$this), $rawParm);
                }
            }

            // Execute Automatic Module Update Sequence
            if (($this->getAttribute('AutoUpdateMode')===true) && !($this->isNoCommonAction($className))) {
                $info = $GLOBALS['xoopsModule']->getInfo();
                $installHelper =& $this->getInstallHelper();
                $installHelper->postUpdateProcessforDuplicate(true);
            }
            
            // Generage an Action class and Execute
            if ($action =& $this->getInstance($className, 'Action')) {
                $action->mActionName = $requestAction;
                if (in_array($className, $dialogAction)) {
                    $action->mDialogMode = true;
                }
                $action->prepare();
                $action->execute();
            }
        }

        /**
         * NBFrame Class instance generator
         *
         * @param string $className
         * @param string $suffix
         * @return object
         */
        function &getInstance($className, $suffix='') {
            $className = $className.$suffix;
            $classNamePath = str_replace('.', '/', basename($className));
            $classBaseName = basename($classNamePath);
            if (preg_match('/^NBFrame\.(.*)/',$className, $match)) {
                $className = $match[1];
                NBFrame::using($className);
                $classBaseName = 'NBFrame'.$classBaseName;
            } else {
                NBFrame::using($className, $this);
            }

            $instance = null;

            if (class_exists($this->prefix($classBaseName))) {
                // This class may be Module Custom Class
                $classBaseName = $this->prefix($classBaseName);
                $instance =& new $classBaseName($this);
            } else if (class_exists($classBaseName)) {
                $instance =& new $classBaseName($this);
            }
            return $instance;
        }

        function getActionURL($actionName='', $paramArray=array(), $ext='html', $ommitBase=false, $escape=true) {
            if ($ommitBase) {
                $str = '';
            } else {
                if (empty($GLOBALS['NBFrameURLShotened'])) {
                    $str = $this->mUrlBase.'/';
                } else {
                    $str = XOOPS_URL.'/'.$this->mDirName.'/';
                }
            }
            $suffix = '.'.$ext;
            if ($this->getAttribute('StaticUrlMode')) {
                $delim = '';
                if ($this->getAttribute('ModRewirteOff') && empty($GLOBALS['NBFrameURLShotened'])) {
                    $str .= 'page/';
                }
                if (!empty($actionName)) {
                    $className = $actionName.'Action';
                } else {
                    $className = $this->getAttribute('ModueleMainAction');
                }
                NBFrame::using($className, $this);
                if (class_exists($className) && is_callable(array($className,'getParamString'),false)) {
                    $str .= call_user_func(array($className,'getParamString'), array(&$this), $paramArray);
                } else {
                    if (!empty($actionName) && ($actionName != $this->getAttribute('ModueleMainAction'))) {
                        if (preg_match('/^(NBFrame\.)?(admin\.)?([A-Za-z0-9\._]+)/', $actionName, $matches)) {
                            if ($matches[1]) {
                                $str .= 'NBFrame/';
                            }
                            if ($matches[2]) {
                                $str .= 'admin/';
                            }
                        }
                        $str .= $matches[3].'Action/';
                    }
                    if (!empty($paramArray)) {
                        foreach ($paramArray as $key=>$value) {
                            if (substr($key,0,2) == '__') {
                                $suffix = $delim.$key.'__'.rawurlencode($value);
                            } else {
                                $str .= $delim.$key.'__'.rawurlencode($value);
                                $delim = '/';
                            }
                        }
                        $str .= $suffix;
                    }
                }
            } else {
                $delim = '?';
                if (!empty($actionName) && ($actionName != $this->getAttribute('ModueleMainAction'))) {
                    $str .= $delim.'action='.$actionName;
                    $delim = ($escape ? '&amp;' : '&');
                }
                if (!empty($paramArray)) {
                    foreach ($paramArray as $key=>$value) {
                        if (substr($key,0,2) == '__') {
                            $key = substr($key,2);
                        }
                        $str .= $delim.$key.'='.rawurlencode($value);
                        $delim = ($escape ? '&amp;' : '&');
                    }
                }
            }
            return $str;
        }
        
        function redirect($actionName='', $time, $msg, $paramArray=array(), $ext='html') {
            if (!empty($paramArray) && isset($paramArray['op'])) {
                $paramArray['NBFrameNextOp'] = $paramArray['op'];
                unset($paramArray['op']);
            }
            $paramArray['NBFrameNextAction'] = $actionName;
            redirect_header($this->getActionURL('NBFrame.Redirect',$paramArray, $ext), $time, $msg);
        }

        function getImageURL($fileName) {
            if ($this->getAttribute('StaticUrlMode')) {
                $str = $this->mUrlBase.'/images/'.rawurlencode($fileName);
            } else {
                $str = $this->mUrlBase.'/?action=NBFrame.GetImage&amp;NBImgFile='.rawurlencode($fileName);
            }
            return $str;
        }
        
        function getPageURL($fileName) {
            if ($this->getAttribute('StaticUrlMode')) {
                $str = $this->mUrlBase.'/contents/'.rawurlencode($fileName);
            } else {
                $str = $this->mUrlBase.'/?action=NBFrame.GetPage&amp;NBPageFile='.rawurlencode($fileName);
            }
            return $str;
        }

        function parseURL() {
            if (isset($_SERVER['REQUEST_URI'])) {
                $paramPath = '';
                $hostName = preg_replace('!(^https?\:[\d]*//[^/]+).*$!','\\1',XOOPS_URL);
                if (preg_match('/^'.preg_quote($this->mUrlBase, '/').'\/(?:(?:index|page)(?:\.php)?\/)?(.*)$/', $hostName.$_SERVER['REQUEST_URI'], $matches)) {
                    $moduleRequest = $matches[1];
                    if (preg_match('!^images/([\w_]*?\.(gif|jpeg|jpg|png|swf))([?#].*)?$!', $moduleRequest, $matches)) {
                        $_GET['action'] = 'NBFrame.GetImage';
                        $_REQUEST['action'] = 'NBFrame.GetImage';
                        $_GET['NBImgFile'] = $matches[1];
                        return;
                    } else if (preg_match('!^contents/([\w_]*?\.(html|htm))([?#].*)?$!', $moduleRequest, $matches)) {
                        $_GET['action'] = 'NBFrame.GetPage';
                        $_REQUEST['action'] = 'NBFrame.GetPage';
                        $_GET['NBContentFile'] = $matches[1];
                        return;
                    } else if (preg_match('!^(NBFrame\/)?(admin\/)?([A-Za-z0-9\._]+)Action/(.*)$!', $moduleRequest, $matches)) {
                        $_GET['action'] = '';
                        if ($matches[1]) {
                            $_GET['action'] .= 'NBFrame.';
                        }
                        if ($matches[2]) {
                            $_GET['action'] .= 'admin.';
                        }
                        $_GET['action'] .= $matches[3];
                        $_REQUEST['action'] = $_GET['action'];
                        if (preg_match('!^(.*?)\.([A-Za-z0-9]+)([?#].*)?$!', $matches[4], $matches1)) {
                            $paramPath = $matches1[1];
                            $paramExt = $matches1[2];
                        }
                    } else if (preg_match('!^(.*?)\.([A-Za-z0-9]+)([?#].*)?$!', $moduleRequest, $matches)) {
                        $paramPath = $matches[1];
                        $paramExt = $matches[2];
                    }
                    if (!empty($paramPath)) {
                        $paramArray = explode('/', $paramPath);
                        $paramIndex = 0;
                        $paramCount = count($paramArray);
                        foreach($paramArray as $paramStr) {
                            $paramIndex++;
                            $paramDelimPos =strpos($paramStr, '__',1);
                            if ($paramDelimPos !== false) {
                                $paramName = substr($paramStr, 0, $paramDelimPos);
                                if (($paramIndex == $paramCount) && (substr($paramName,0,2)=='__')) {
                                    $paramName   = substr($paramName, 2);
                                    $paramValue = substr($paramStr, $paramDelimPos+2).'.'.$paramExt;
                                } else {
                                    $paramValue = substr($paramStr, $paramDelimPos+2);
                                }
                                if (!isset($_GET[$paramName])) $_GET[$paramName] = $paramValue;
                                if (!isset($_REQUEST[$paramName])) $_REQUEST[$paramName] = $paramValue;
                            }
                        }
                        $this->setAttribute('RawParam', $paramPath.'.'.$paramExt);
                    }
//                    var_dump($_GET);exit();
                }
            }
        }

        function isNoCommonAction($className) {
            $noCommonActions = $this->getAttribute('NoCommonAction');
            if (!is_array($noCommonActions)) return false;
            return in_array($className, $noCommonActions);
        }

        function getModuleCookiePath() {
                $pathArray = explode($_SERVER['HTTP_HOST'], XOOPS_URL);
                if (empty($GLOBALS['NBFrameURLShotened'])) {
                    $cookiePath = $pathArray[1].'/modules/'.$this->mDirName;
                } else {
                    $cookiePath = $pathArray[1].'/'.$this->mDirName;
                }
                return $cookiePath;
        }
        
        function findFile($name, $offset='', $searchCurrent=true, $customPrefix='') {
            static $sFileNames;

            $key = md5($this->mDirName.$this->mOrigDirName.$offset.$name);
            if (isset($sFileNames[$key])) {
                return $sFileNames[$key];
            }
            
            if (!empty($offset)) {
                $offset = preg_replace('/^(\/)?(.*)(\/)?$/','\\2/',trim($offset));
            }
            $fileName = '';
            if (!empty($customPrefix)) {
                if ($customPrefix == '=') $customPrefix = '';
            } else {
                $customPrefix = $this->prefix('');
            }
            if ($customPrefix != '+' && $this->isD3() && file_exists($this->getDirBase().'/'.$offset.$customPrefix.$name)){
                $fileName = $this->getDirBase().'/'.$offset.$customPrefix.$name;
            } else if (empty($fileName) && file_exists($this->getOrigDirBase().'/'.$offset.$name)){
                $fileName = $this->getOrigDirBase().'/'.$offset.$name;
            } else if ($this->isD3() && $searchCurrent && file_exists($this->getDirBase().'/'.$offset.$name)){
                $fileName = $this->getDirBase().'/'.$offset.$name;
            }

            $sFileNames[$offset][$name] = $fileName;
            return $fileName;
        }


        // Utilitiy Functions for Blocks
        function prepareBlockFunction() {
            if (isset($GLOBALS['_NBBlockFuncInfo'][$this->mDirName])) {
                $blockFuncInfoArr = $GLOBALS['_NBBlockFuncInfo'][$this->mDirName];
                foreach ($blockFuncInfoArr as $funcName =>$blockFuncInfo) {
                    NBFrame::using('blocks.'.$blockFuncInfo['class'], $this);
                    $envStr = serialize($this);
                    $str = 'if (!function_exists("'.$funcName.'")) {'."\n";
                    $str .= 'function '.$funcName.'($option) {'."\n";
                    $str .= '  $environment = unserialize(\''.$envStr.'\');'."\n";
                    $str .= 'return '.$blockFuncInfo['class'].'::'.$blockFuncInfo['method'].'($environment, $option); }}';
                    eval($str);
                }
            }
        }

        // Utilitiy Functions for Search
        function prepareSearchFunction() {
            if (isset($GLOBALS['_NBSearchFuncInfo'][$this->mDirName])) {
                $class = $GLOBALS['_NBSearchFuncInfo'][$this->mDirName]['class'];
                $method = $GLOBALS['_NBSearchFuncInfo'][$this->mDirName]['method'];
                $funcName = $this->prefix($class.'_'.$method);
                NBFrame::using($class, $this);

                $envStr = serialize($this);
                $str = 'if (!function_exists("'.$funcName.'")) {'."\n";
                $str .= 'function '.$funcName.'($queryarray, $andor, $limit, $offset, $userid) {'."\n";
                $str .= '  $environment = unserialize(\''.$envStr.'\');'."\n";
                $str .= 'return '.$class.'::'.$method.'($environment, $queryarray, $andor, $limit, $offset, $userid); }}';
                eval($str);
            }
        }

        function getAdminMenu() {
            $languageManager =& $this->getLanguageManager();
            $languageManager->setInAdmin(true);
            $adminmenu = array();

            // SubMenu Settings
            if (($className = $this->getAttribute('AdminMenu')) && ($menuObject =& $this->getInstance($className, ''))) {
                $adminmenu = $menuObject->getAdminMenu();
            } else if ($menuObject =& $this->getInstance('admin.'.ucfirst($this->getOrigDirName()).'Admin', 'Menu')) {
                $adminmenu = $menuObject->getAdminMenu();
            } else if ($fname = $this->findFile('admin_menu.inc.php', 'include', false, '=')) {
                $environment = $this;
                include $fname;
            }
            if ($this->getAttribute('UseBlockAdmin')) {
                $adminmenu[] = array('title' => $languageManager->__l('Block Admin'),
                                     'link'  => $this->getActionUrl('NBFrame.admin.BlocksAdmin', array(), 'html', true, false));
            }
            if (NBFrame::checkAltSys(false)&&$this->getAttribute('UseAltSys')) {
                if ($this->getAttribute('UseTemplateAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Template Admin'),
                                         'link'  => $this->getActionUrl('NBFrame.admin.AltSys', array('page'=>'mytplsadmin'), 'html', true, false));
                }
                if ($this->getAttribute('UseLanguageAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Language Admin'),
                                         'link'  => $this->getActionUrl('NBFrame.admin.AltSys', array('page'=>'mylangadmin'), 'html', true, false));
                }
            }
            return $adminmenu;
        }

        function parseXoopsVerionFile(&$modversion) {
            if (!empty($this->mModuleInfo)) {
                $modversion = $this->mModuleInfo;
                return;
            }
            $modversion['name'] .= ' ['.$this->mDirName.']';
            $modversion['dirname'] = $this->mDirName;
            if (!empty($modversion['image'])) {
                $modversion['image'] = $this->getActionUrl('NBFrame.GetModuleIcon', array('file'=>basename($modversion['image'])), 'html',true);
            } else {
                $modversion['image'] = $this->getActionUrl('NBFrame.GetModuleIcon', array(), 'html',true);
            }

            if (@$modversion['hasAdmin']){
                $modversion['adminindex'] = $this->getActionUrl($this->getAttribute('AdminMainAction'), array(), 'html',true);
                $modversion['adminmenu'] = 'include/NBFrameAdminMenu.inc.php';
            }
            // SubMenu Settings
            if (($className = $this->getAttribute('MainMenu')) && ($mainmenu =& $this->getInstance($className, ''))) {
                $modversion['sub'] = $mainmenu->getMainMenu();
            } else if ($mainmenu =& $this->getInstance(ucfirst($this->getOrigDirName()).'Main', 'Menu')) {
                $modversion['sub'] = $mainmenu->getMainMenu();
            } else if ($fname = $this->findFile('sub_menu.inc.php', '/include', false, '=')) {
                $environment = $this;
                include $fname;
            }
            // Table Settings
            if ($fname = $this->findFile('tabledef.inc.php', '/include', false, '=')) {
                include $fname;
                $modversion['tables'] = array();
                foreach($tableDef[$this->mOrigDirName] as $key =>$value) {
                    $modversion['tables'][] = $this->prefix($key);
                }
            }
            // Template Settings
            $i = 1;
            unset($modversion['templates']);
            $modversion['templates'] = array();
            $tempaltePath = $this->findFile('templates', '');
            if ($templateFiles = glob($tempaltePath.'/*.html')) {
                foreach ($templateFiles as $templateFile) {
                    $modversion['templates'][$i] = array('file'=>$this->prefix(basename($templateFile)), 'desc'=>'');
                    $i++;
                }
            }
            $tempaltePath =$this->findFile('templates', '', false, '=');
            if ($templateFiles = glob($tempaltePath.'/'.$this->prefix('_*.html'))) {
                foreach ($templateFiles as $templateFile) {
                    $match = false;
                    foreach($modversion['templates'] as $template) {
                        if ($template['file'] == basename($templateFile)) $match = true;
                    }
                    if (!$match) {
                        $modversion['templates'][$i] = array('file'=>basename($templateFile), 'desc'=>'');
                        $i++;
                    }
                }
            }

            if (isset($modversion['blocks'])){
                foreach($modversion['blocks'] as $key=>$block) {
                    $modversion['blocks'][$key]['file'] = 'NBFrameBlockLoader.php';
                    if (isset($block['template'])) {
                        $modversion['blocks'][$key]['template'] = $this->prefix($block['template']);
                    }
                    if (isset($block['class'])) {
                        $modversion['blocks'][$key]['NBclass'] = $block['class'];
                        unset($modversion['blocks'][$key]['class']);
                        if (isset($block['show_func'])) {
                            $modversion['blocks'][$key]['NBShowMethod'] = $modversion['blocks'][$key]['show_func'];
                            $modversion['blocks'][$key]['show_func'] = $this->prefix('b_'.$modversion['blocks'][$key]['NBclass'].'_'.$block['show_func']);
                        }
                        if (isset($block['edit_func'])) {
                            $modversion['blocks'][$key]['NBEditMethod'] = $modversion['blocks'][$key]['edit_func'];
                            $modversion['blocks'][$key]['edit_func'] = $this->prefix('b_'.$modversion['blocks'][$key]['NBclass'].'_'.$block['edit_func']);
                        }
                    } else {
                        if (isset($block['show_func'])) {
                            if (preg_match('/^b_(.*)_show$/', $block['show_func'], $matches)) {
                                $modversion['blocks'][$key]['NBclass'] = $matches[1];
                                $modversion['blocks'][$key]['NBShowMethod'] = 'show';
                            }
                            $modversion['blocks'][$key]['show_func'] = $this->prefix($block['show_func']);
                        }
                        if (isset($block['edit_func'])) {
                            if (preg_match('/^b_(.*)_edit$/', $block['edit_func'], $matches)) {
                                $modversion['blocks'][$key]['NBclass'] = $matches[1];
                                $modversion['blocks'][$key]['NBEditMethod'] = 'edit';
                            }
                            $modversion['blocks'][$key]['edit_func'] = $this->prefix($block['edit_func']);
                        }
                    }
                    if (isset($block['show_func'])) {
                        $GLOBALS['_NBBlockFuncInfo'][$this->mDirName][$modversion['blocks'][$key]['show_func']]['class'] = $modversion['blocks'][$key]['NBclass'];
                        $GLOBALS['_NBBlockFuncInfo'][$this->mDirName][$modversion['blocks'][$key]['show_func']]['method'] = $modversion['blocks'][$key]['NBShowMethod'];
                    }
                    if (isset($modversion['blocks'][$key]['edit_func'])) {
                        $GLOBALS['_NBBlockFuncInfo'][$this->mDirName][$modversion['blocks'][$key]['edit_func']]['class'] = $modversion['blocks'][$key]['NBclass'];
                        $GLOBALS['_NBBlockFuncInfo'][$this->mDirName][$modversion['blocks'][$key]['edit_func']]['method'] = $modversion['blocks'][$key]['NBEditMethod'];
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
                $modversion['search']['func'] = $this->prefix($class.'_'.$method);
                $modversion['search']['file'] = 'include/NBFrameSearchLoader.php';

                $GLOBALS['_NBSearchFuncInfo'][$this->mDirName]['class'] = $class;
                $GLOBALS['_NBSearchFuncInfo'][$this->mDirName]['method'] = $method;
            }

            $this->_prepareCustomInstaller($modversion);

            $installHelper =& $this->getInstallHelper();
            if ($installHelper->isPreModuleUpdate() && !$installHelper->isPreModuleUpdateDone() ) {
                $installHelper->preUpdateProcessforDuplicate();
                if(!defined('XOOPS_CUBE_LEGACY')) {
                    $installHelper->preBlockUpdateProcess($modversion);
                }
            }
            $this->mModuleInfo = $modversion;
        }

        function _prepareCustomInstaller(&$modversion) {
            $installHelper =& $this->getInstallHelper();
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

        function &getInstallHelper() {
            static $mInstallHelperArr;

            $dirName = $this->mDirName;

            if (!isset($mInstallHelperArr[$dirName])) {
                NBFrame::using('InstallHelper');
                $mInstallHelperArr[$dirName] =& new NBFrameInstallHelper($this);
            }
            return $mInstallHelperArr[$dirName];
        }

        function __l($msg) {
            $language =& $this->getLanguageManager();
            $args = func_get_args();
            return $language->__l($msg, $language->_getParams($args));
        }

        function __e($msg) {
            $language =& $this->getLanguageManager();
            $args = func_get_args();
            return $language->__e($msg, $language->_getParams($args));
        }
        
    }
}
?>
