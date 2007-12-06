<?php
if (!class_exists('SimpleTable1AdminForm')) {
    NBFrame::using('ObjectForm');
    class SimpleTable1AdminForm extends NBFrameObjectForm {
        function prepare() {
            parent::prepare();
            $this->addElement('id',new XoopsFormHidden('id', 0));
            $this->addElement('name',new XoopsFormText($this->__l('Name'), 'name', 35, 255));
            $this->addElement('tel_num',new XoopsFormText($this->__l('Tel Number'), 'tel_num', 35, 16));
            $this->addElement('desc',new XoopsFormDhtmlTextArea($this->__l('Description'), 'desc', '', 8, 40));
        }   
    }
}
?>