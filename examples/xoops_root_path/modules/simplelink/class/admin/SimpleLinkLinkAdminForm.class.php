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
if (!class_exists('SimpleLinkLinkAdminForm')) {
    NBFrame::using('ObjectForm');

    class SimpleLinkLinkAdminForm extends NBFrameObjectForm {
        function prepare() {
            $this->addElement('link_category_id', new XoopsFormSelect($this->__l('link_category_id'),'link_category_id'));
            
            $categoryHandler =& NBFrame::getHandler('SimpleLinkCategory', $this->mEnvironment);
            $this->addOptionArray('link_category_id', $categoryHandler->getSelectOptionArray());
        }
    }
}
?>
