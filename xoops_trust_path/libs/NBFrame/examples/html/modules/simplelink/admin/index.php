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
$environment =& NBFrameBase::prepare();
require_once NBFRAME_BASE_DIR.'/include/NBFrameLoadCommon.inc.php';
NBFrame::executeAction($environment,'NBFrame.admin.AdminIndex');
?>
