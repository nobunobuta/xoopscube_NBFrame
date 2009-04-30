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
if (!class_exists('SimpleTableAdminAction')) {
    NBFrame::using('AdminMaintAction');
    class SimpleTableAdminAction extends NBFrameAdminMaintAction {
        function prepare() {
            parent::prepare('SimpleTable', $this->__l('Table'));
       }
    }
}
?>
