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
    $preloadDir = XOOPS_TRUST_PATH.'/modules/'.$preloadEnvironment->mOrigDirName.'/preload/';
    if(is_dir($preloadDir)) {
        $preloadFiles = glob($preloadDir.'*.class.php');
        if (is_array($preloadFiles)) {
            foreach($preloadFiles as $preloadFile) {
                require_once $preloadFile;
                if (preg_match("/(\w+)\.class\.php/", $preloadFile, $preloadMatches)) {
                    $preloadBaseClassName = ucfirst($preloadEnvironment->mOrigDirName).'_Base_'.$preloadMatches[1];
                    $preloadClassName = ucfirst($preloadEnvironment->mDirName).'_'.$preloadMatches[1];

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
