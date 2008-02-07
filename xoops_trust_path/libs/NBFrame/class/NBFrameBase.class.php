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
if (!class_exists('NBFrameBase')) {
    if (!defined('NBFRAME_TARGET_MAIN')) define('NBFRAME_TARGET_MAIN',1);
    if (!defined('NBFRAME_TARGET_BLOCK')) define('NBFRAME_TARGET_BLOCK',2);
    if (!defined('NBFRAME_TARGET_INSTALLER')) define('NBFRAME_TARGET_INSTALLER',3);
    if (!defined('NBFRAME_TARGET_SYS')) define('NBFRAME_TARGET_SYS', 4);
    if (!defined('NBFRAME_TARGET_LOADER')) define('NBFRAME_TARGET_LOADER', 99);

    if (!defined('NBFRAME_NO_DEFAULT_PARAM')) define('NBFRAME_NO_DEFAULT_PARAM', '__nodefault__');

    class NBFrameBase {
        /**
         * Preparing Target Environment
         *
         * @param string $origDirName
         * @param int    $target
         *
         */
        function &prepare($target=NBFRAME_TARGET_MAIN) {
            $envtemp =& NBFrame::getEnvironments(NBFRAME_TARGET_LOADER);
            if (!empty($envtemp)) {
                $environment =& NBFrame::getEnvironments($target, $envtemp->mDirName, true);
                $environment->setOrigDirName($envtemp->mOrigDirName);
                $environment->setDirBase($envtemp->mDirBase);
                $environment->mAttributeArr = $envtemp->mAttributeArr;
                if ($target != NBFRAME_TARGET_MAIN) {
                    $environment->getLanguageManager();
                }
            } else {
                $environment = null;
            }
            return $environment;
        }

        function &getLanguageManager(&$environment) {
            static $mLanguageArr;
            NBFrame::using('Language');
            if (!empty($environment)) {
                $dirName = $environment->mDirName;
                $target = $environment->mTarget;
            } else {
                $dirName = '_NB_System_';
                $target = 0;
            }
            if (empty($mLanguageArr[$dirName][$target])) {
                $mLanguageArr[$dirName][$target] =& new NBFrameLanguage($target, $environment);
            }
            return $mLanguageArr[$dirName][$target];
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

        function parseXoopsVerionFile(&$modversion, &$environment) {
            if (!empty($environment->mModuleInfo)) {
                $modversion = $environment->mModuleInfo;
                return;
            }
            $modversion['name'] .= ' ['.$environment->mDirName.']';
            $modversion['dirname'] = $environment->mDirName;
            if (!empty($modversion['image'])) {
                $modversion['image'] = NBFrame::getActionUrl($environment, 'NBFrame.GetModuleIcon', array('file'=>basename($modversion['image'])), 'html',true);
            } else {
                $modversion['image'] = NBFrame::getActionUrl($environment, 'NBFrame.GetModuleIcon', array(), 'html',true);
            }

            if (@$modversion['hasAdmin']){
                $modversion['adminindex'] = NBFrame::getActionUrl($environment, $environment->getAttribute('AdminMainAction'), array(), 'html',true);
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
            $tempaltePath = NBFrame::findFile('templates', $environment, '');
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

            NBFrameBase::_prepareCustomInstaller($modversion, $environment);

            $installHelper =& NBFrameBase::getInstallHelper($environment);
            if ($installHelper->isPreModuleUpdate() && !$installHelper->isPreModuleUpdateDone() ) {
                $installHelper->preUpdateProcessforDuplicate();
                if(!defined('XOOPS_CUBE_LEGACY')) {
                    $installHelper->preBlockUpdateProcess($modversion);
                }
            }
            $environment->mModuleInfo = $modversion;
        }

        function _prepareCustomInstaller(&$modversion, &$environment) {
            $installHelper =& NBFrameBase::getInstallHelper($environment);
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

        function &getInstallHelper(&$environment) {
            static $mInstallHelperArr;

            $dirName = $environment->mDirName;

            if (!isset($mInstallHelperArr[$dirName])) {
                NBFrame::using('InstallHelper');
                $mInstallHelperArr[$dirName] =& new NBFrameInstallHelper($environment);
            }
            return $mInstallHelperArr[$dirName];
        }

        // Utilitiy Functions

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

        function getAdminMenu($environment) {
            $languageManager =& $environment->getLanguageManager();
            $languageManager->setInAdmin(true);
            $adminmenu = array();
            if ($environment->getAttribute('UseBlockAdmin')) {
                $adminmenu[] = array('title' => $languageManager->__l('Block Admin'),
                                     'link'  => NBFrame::getActionUrl($environment, 'NBFrame.admin.BlocksAdmin', array(), 'html', true, false));
            }
            if (NBFrameBase::checkAltSys(false)&&$environment->getAttribute('UseAltSys')) {
                if ($environment->getAttribute('UseTemplateAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Template Admin'),
                                         'link'  => NBFrame::getActionUrl($environment, 'NBFrame.admin.AltSys', array('page'=>'mytplsadmin'), 'html', true, false));
                }
                if ($environment->getAttribute('UseLanguageAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Language Admin'),
                                         'link'  => NBFrame::getActionUrl($environment, 'NBFrame.admin.AltSys', array('page'=>'mylangadmin'), 'html', true, false));
                }
            }
            return $adminmenu;
        }

        function isNoCommonAction($className, $environment) {
            $noCommonActions = $environment->getAttribute('NoCommonAction');
            if (!is_array($noCommonActions)) return false;
            return in_array($className, $noCommonActions);
        }

        function parseURL(&$environment) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $paramPath = '';
                $hostName = preg_replace('!(^https?\:[\d]*//[^/]+).*$!','\\1',XOOPS_URL);
                if (preg_match('/^'.preg_quote($environment->mUrlBase, '/').'\/(?:(?:index|page)(?:\.php)?\/)?(.*)$/', $hostName.$_SERVER['REQUEST_URI'], $matches)) {
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
                        $environment->setAttribute('RawParam', $paramPath.'.'.$paramExt);
                    }
//                    var_dump($_GET);exit();
                }
            }
        }
    }
}
?>
