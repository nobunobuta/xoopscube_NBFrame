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
if (!class_exists('SimpleLinkLinkAdminList')) {
    NBFrame::using('ObjectList');

    class SimpleLinkLinkAdminList extends NBFrameObjectList
    {
        var $categoryHandler;

        function prepare() {
            $this->categoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);
            $this->addElement('link_id', '#', 20, array('sort'=>true));
            $this->addElement('link_name', $this->__l('link_name'), 200, array('sort'=>true));
            $this->addElement('link_category_id', $this->__l('link_category_id'), 150, array('sort'=>true));
            $this->addElement('link_weight', $this->__l('link_weight'), 150, array('sort'=>true));
            $this->addElement('__SimpleEditLink__','',50, array('caption'=>$this->__l('Edit')));
            $this->addElement('__SimpleDeleteLink__','',50, array('caption'=>$this->__l('Delete')));
        }
        
        function formatItem_link_category_id($value) {
            return $this->categoryHandler->getName($value);
        }
    }
}
?>
