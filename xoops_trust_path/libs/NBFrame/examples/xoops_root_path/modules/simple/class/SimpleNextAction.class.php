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
NBFrame::using('Action');

class SimpleNextAction extends NBFrameAction {
    var $mContent;

    function prepare() {
        parent::prepare();
        $this->setDefaultTemplate($this->prefix('main.html'));
    }

    function executeDefaultOp() {
        $this->mContent = 'Good bye World';
        return NBFRAME_ACTION_VIEW_DEFAULT;
    }

    function viewDefaultOp() {
        $this->mXoopsTpl->assign('content', $this->mContent);

    }
}
