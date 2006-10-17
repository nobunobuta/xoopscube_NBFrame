<?php
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
            $this->addElement('modules', new XoopsFormSelect($this->__l('Visible in'), 'modules', '', 5, true));
            $this->addElement('title', new XoopsFormText($this->__l('Title'), 'title', 50, 255, ''));
            if (!$this->mAction->mObject->getVar('is_custom')) {
                if ($this->mAction->mObject->getVar('edit_form') != false) {
                    $this->addElement('edit_form', new XoopsFormLabel($this->__l('Block option(s)'), ''));
                }
            }

            $this->addElement('bcachetime', new XoopsFormSelect($this->__l('Cache time'), 'bcachetime'));
            $this->addElement('bid', new XoopsFormHidden('bid', 0));


            $this->addOptionArray('side', array(
                0 => $this->__l('Left block'),
                1 => $this->__l('Right block'),
                3 => $this->__l('Center block - left'),
                4 => $this->__l('Center block - right'),
                5 => $this->__l('Center block - center'),
            ));

            $this->addOptionArray('bcachetime', array(
               '0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE,
               '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30),
               '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY,
               '259200' => sprintf(_DAYS, 3), '604800' => _WEEK, '2592000' => _MONTH
            ));

            $module_handler =& xoops_gethandler('module');
            $criteria = new CriteriaCompo(new Criteria('hasmain', 1));
            $criteria->add(new Criteria('isactive', 1));
            $module_list =& $module_handler->getList($criteria);
            $module_list[-1] = $this->__l('Top Page');
            $module_list[0] = $this->__l('All Pages');
            ksort($module_list);
            $this->addOptionArray('modules',$module_list);
        }
    }
}
?>
