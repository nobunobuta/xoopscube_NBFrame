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
if (!defined('NBFRAME_BASE_DIR')) exit();
if(!defined('NBFRAME_COMMON_FUNCTION_INCLUDED')){
    define('NBFRAME_COMMON_FUNCTION_INCLUDED', 1) ;

    if (preg_match('/^4/',PHP_VERSION)) {
        include_once (dirname(__FILE__).'/NBFramePHP4.inc.php');
    } else {
        include_once (dirname(__FILE__).'/NBFramePHP5.inc.php');
    }
    require_once NBFRAME_BASE_DIR.'/class/NBFrame.class.php';
}
$environment =& NBFrame::getEnvironments(NBFRAME_TARGET_LOADER, '', true);
$environment->setDirBase($_NBFrame_moduleBaseDir);
require $_NBFrame_moduleBaseDir.'/mytrustdirname.php';
$environment->setOrigDirName($mytrustdirname);
if ($fname = $environment->findFile('module_settings.php', '/', false, '=')) @include $fname;
if ($fname = $environment->findFile('custom_settings.php', '/', false, '=')) @include $fname;
?>
