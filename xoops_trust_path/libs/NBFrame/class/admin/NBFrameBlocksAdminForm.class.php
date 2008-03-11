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
if (!class_exists('NBFrameBlocksAdminForm')) {
    NBFrame::using('ObjectForm');

    class NBFrameBlocksAdminForm extends NBFrameObjectForm {
        function prepare() {
            if ($this->mAction->mObject->getVar('name')) {
                $this->addElement('name', new XoopsFormLabel($this->__l('Name')));
            }

            $this->addElement('side', new XoopsFormSelect($this->__l('Side'), 'side'));
            $this->addElement('weight', new XoopsFormText($this->__l('Weight'), 'weight', 2, 5));
            $this->addElement('visible', new XoopsFormRadioYN($this->__l('Visible'), 'visible'));
            $this->addElement('modules', new XoopsFormSelect($this->__l('Visible in'), 'modules', array(), 5, true));
            $this->addElement('title', new XoopsFormText($this->__l('Title'), 'title', 50, 255, ''));
            if (!$this->mAction->mObject->getVar('is_custom')) {
                if ($this->mAction->mObject->getVar('edit_form') != false) {
                    $this->addElement('edit_form', new XoopsFormLabel($this->__l('Block option(s)'), ''));
                }
            }

            $this->addElement('bcachetime', new XoopsFormSelect($this->__l('Cache time'), 'bcachetime'));
            $this->addElement('bid', new XoopsFormHidden('bid', 0));

            $this->addOptionArray('side', $this->mAction->mObjectHandler->getSideListArray());
            $this->addOptionArray('bcachetime', $this->mAction->mObjectHandler->getBlockCacheTimeListArray());
            $this->addOptionArray('modules',$this->mAction->mObjectHandler->getModuleListArray());
        }
        function preInsert() {
            $this->defParam('modules', 'array-int',  array());
            if (!$this->mAction->mObject->getVar('is_custom')) {
                if ($this->mAction->mObject->getVar('edit_form') != false) {
                    $this->defParam('options', 'raw', '');
                }
            }
        }
    }
}
?>
