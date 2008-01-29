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
    if (!defined('NBFRAME_TARGET_TEMP')) define('NBFRAME_TARGET_TEMP', 99);

    if (!defined('NBFRAME_NO_DEFAULT_PARAM')) define('NBFRAME_NO_DEFAULT_PARAM', '__nodefault__');

    class NBFrameBase {
        /**
         * Pre Preparing in NBFrameLoader
         *
         * @param string $currentDirBase
         */
        function prePrepare($currentDirBase) {
            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP, true);
            $environment->setDirBase($currentDirBase);
        }

        
        /**
         * Preparing Target Environment
         *
         * @param string $origDirName
         * @param int    $target
         *
         */
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
                    NBFrameBase::getLanguageManager($target);
                }
            } else {
                $environment = null;
            }
            return $environment;
        }

        function &getLanguageManager($target=NBFRAME_TARGET_MAIN) {
            static $mLanguageArr;
            NBFrame::using('Language');
            if (!empty($target)) {
                $environment =& NBFrame::getEnvironments($target);
                $dirName = $environment->mDirName;
            } else {
                $environment = null;
                $dirName = '_NB_System_';
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

        // Utilitiy Functions for Install Module
        function &getXoopsVersionFileName($origDirName) {
            $environment =& NBFrameBase::prepare($origDirName, NBFRAME_TARGET_INSTALLER);
            $fileName= NBFrame::findFile('xoops_version.php', $environment, '', false);
            return $fileName;
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

            NBFrameBase::_prepareCustomInstaller($modversion);

            $installHelper =& NBFrameBase::getInstallHelper();
            if ($installHelper->isPreModuleUpdate() && !$installHelper->isPreModuleUpdateDone() ) {
                $installHelper->preUpdateProcessforDuplicate();
                if(!defined('XOOPS_CUBE_LEGACY')) {
                    $installHelper->preBlockUpdateProcess($modversion);
                }
            }
        }

        function _prepareCustomInstaller(&$modversion) {
            $installHelper =& NBFrameBase::getInstallHelper();
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

            $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_INSTALLER);
            $dirname = $environment->mDirName;

            if (!isset($mInstallHelperArr[$dirname])) {
                NBFrame::using('InstallHelper');
                $mInstallHelperArr[$dirname] =& new NBFrameInstallHelper($environment);
            }
            return $mInstallHelperArr[$dirname];
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
            $languageManager =& NBFrameBase::getLanguageManager(NBFRAME_TARGET_TEMP);
            $adminmenu = array();
            if ($environment->getAttribute('UseBlockAdmin')) {
                $adminmenu[] = array('title' => $languageManager->__l('Block Admin'),
                                     'link'  => '?action=NBFrame.admin.BlocksAdmin' );
            }
            if (NBFrameBase::checkAltSys(false)&&$environment->getAttribute('UseAltSys')) {
                if ($environment->getAttribute('UseTemplateAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Template Admin'),
                                         'link'  => '?action=NBFrame.admin.AltSys&page=mytplsadmin' );
                }
                if ($environment->getAttribute('UseLanguageAdmin')) {
                    $adminmenu[] = array('title' => $languageManager->__l('Language Admin'),
                                         'link'  => '?action=NBFrame.admin.AltSys&page=mylangadmin' );
                }
            }
            return $adminmenu;
        }

        function isNoCommonAction($className, $environment) {
            $noCommonActions = $environment->getAttribute('NoCommonAction');
            if (!is_array($noCommonActions)) return false;
            return in_array($className, $noCommonActions);
        }
    }
}
?>
