<?php
if (!class_exists('SimpleTable1AdminList')) {
    NBFrame::using('ObjectList');
    class SimpleTable1AdminList extends NBFrameObjectList {
        function prepare() {
            parent::prepare();
            $this->addElement('id', '#', 20, array('sort'=>true));
            $this->addElement('name',  $this->__l('Name'), 300);
            $this->addElement('tel_num', $this->__l('Tel Num'), 50, array('sort'=>true));
            $this->addElement('__SimpleEditLink__','',50, array('caption'=>$this->__l('Edit')));
            $this->addElement('__SimpleDeleteLink__','',50, array('caption'=>$this->__l('Delete')));
        }
    }
}
?>
