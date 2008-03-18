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
if (!isset($preloadEnvironment)) exit();
if (class_exists('XCube_Root') && isset($this) && is_a($this, 'Legacy_Controller')) {
    if (!class_exists('NBFramePreloadCommon')) {
        class NBFramePreloadCommon extends XCube_ActionFilter
        {
            function preBlockFilter() {
                $this->mController->mRoot->mDelegateManager->add("Legacy_Utils.CreateModule",'NBFrame::createModel');
            }
        }
        $preloadInstance =& new NBFramePreloadCommon($this);
        $this->addActionFilter($preloadInstance);
    }
    $preloadDir = XOOPS_TRUST_PATH.'/modules/'.$preloadEnvironment->getOrigDirName().'/preload/';
    if(is_dir($preloadDir)) {
        $preloadFiles = glob($preloadDir.'*.class.php');
        if (is_array($preloadFiles)) {
            foreach($preloadFiles as $preloadFile) {
                require_once $preloadFile;
                if (preg_match("/(\w+)\.class\.php/", $preloadFile, $preloadMatches)) {
                    $preloadBaseClassName = ucfirst($preloadEnvironment->getOrigDirName()).'_Base_'.$preloadMatches[1];
                    $preloadClassName = ucfirst($preloadEnvironment->getDirName()).'_'.$preloadMatches[1];

                    if (class_exists($preloadBaseClassName)) {
                        if (!class_exists($preloadClassName)) {
                            $evalStr = 'class '.$preloadClassName.' extends '.$preloadBaseClassName.' {}';
                            eval($evalStr);
                        }
                        $preloadInstance =& new $preloadClassName($this, $preloadEnvironment);
                        $this->addActionFilter($preloadInstance);
                    }
                }
            }
        }
    }
}

?>
