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
NBFrame::using('ObjectAction');
if (!class_exists('NBFrameCommentAction')) {
    class NBFrameCommentAction extends NBFrameObjectAction {
    	function prepare() {
            parent::prepare('NBFrame.xoops.Comment', 'simplenews', $this->__l('SimpleNews'));
        	$this->mAllowedOp = array('new', 'edit', 'insert', 'save', 'delete', 'deleteok');
       	}
    }
}
