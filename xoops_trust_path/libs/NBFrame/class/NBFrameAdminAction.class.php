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
if (!class_exists('NBFrameAdminAction')) {
    NBFrame::using('Action');
    
    class NBFrameAdminAction extends NBFrameAction{
        function NBFrameAdminAction(&$environment) {
            parent::NBFrameAction($environment);
            NBFrame::using('AdminRender');
            $this->mRender =& new NBFrameAdminRender($this);
        }
    }
}
?>
