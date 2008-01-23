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
if (class_exists('NBFrame')) {
    $environment =& NBFrame::getEnvironments(NBFRAME_TARGET_TEMP);
//**************************************************
    $environment->setOrigDirName('simplelink');
//**************************************************
    if ($fname = NBFrame::findFile('module_settings.php', $environment, '/')) @include $fname;
    if ($fname = NBFrame::findFile('custom_settings.php', $environment, '/')) @include $fname;
}
?>
