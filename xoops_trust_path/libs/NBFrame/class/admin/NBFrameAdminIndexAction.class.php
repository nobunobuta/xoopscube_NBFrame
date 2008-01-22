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
if (!class_exists('NBFrameAdminIndexAction')) {
    NBFrame::using('AdminAction');
    
    class NBFrameAdminIndexAction extends NBFrameAdminAction {
        function executeDefaultOp() {
           header('Location:'.XOOPS_URL.'/modules/'.$this->mDirName.'/?action='.$this->mEnvironment->getAttribute('AdminMainAction'));
        }
    }
}
?>
