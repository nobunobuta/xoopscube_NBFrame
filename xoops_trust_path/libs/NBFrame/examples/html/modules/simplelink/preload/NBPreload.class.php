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
include dirname(dirname(__FILE__)).'/include/NBFrameLoader.inc.php';
$preloadEnvironment =& NBFrame::getEnvironment(NBFRAME_TARGET_LOADER);
@include NBFRAME_BASE_DIR.'/include/NBFramePreload.inc.php';
?>