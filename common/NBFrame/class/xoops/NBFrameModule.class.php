<?php
if(!class_exists('NBFrameModule')) {
    NBFrame::using('Object');
    NBFrame::using('ObjectHandler');

    class NBFrameModule extends NBFrameObject
    {
        function NBFrameModule() {
            parent::NBFrameObject();
            $this->initVar('mid', XOBJ_DTYPE_INT, null, false);
            $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 150);
            $this->initVar('version', XOBJ_DTYPE_INT, 100, false);
            $this->initVar('last_update', XOBJ_DTYPE_INT, null, false);
            $this->initVar('weight', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('isactive', XOBJ_DTYPE_INT, 1, false);
            $this->initVar('dirname', XOBJ_DTYPE_OTHER, null, true);
            $this->initVar('hasmain', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('hasadmin', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('hassearch', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('hasconfig', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('hascomments', XOBJ_DTYPE_INT, 0, false);
            $this->initVar('hasnotification', XOBJ_DTYPE_INT, 0, false);

            $this->setKeyFields(array('mid'));
            $this->setNameField('name');

            $this->setAutoIncrementField('mid');
        }
    }
    
    class NBFrameModuleHandler extends NBFrameObjectHandler
    {
        var $tableName = 'modules';
    }
}
?>
