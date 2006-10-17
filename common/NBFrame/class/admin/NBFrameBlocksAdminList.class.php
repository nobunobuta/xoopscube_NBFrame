<?php
if (!class_exists('NBFrameBlocksAdminList')) {
    NBFrame::using('ObjectList');

    class NBFrameBlocksAdminList extends NBFrameObjectList
    {
        function prepare() {
            $this->addElement('mygmap_area_id', '#', 20, array('sort'=>true));
            $this->addElement('mygmap_area_name',  _AD_MYGMAP_LANG_TITLE, 300);
            $this->addElement('mygmap_area_order', _AD_MYGMAP_LANG_ORDER, 50, array('sort'=>true));
            $this->addElement('mygmap_area_maptype', _AD_MYGMAP_LANG_MAPTYPE, 80, array('sort'=>true));
            $this->addElement('__SimpleEditLink__','',50, array('caption'=>$this->__l('Edit')));
            $this->addElement('__SimpleDeleteLink__','',50, array('caption'=>$this->__l('Delete')));
        }
        function formatItem_mygmap_area_maptype($value) {
            $optionArray = array('', _AD_MYGMAP_LANG_MAPTYPE_MAP,_AD_MYGMAP_LANG_MAPTYPE_SATELITE,_AD_MYGMAP_LANG_MAPTYPE_HYBRID);
            return $optionArray[$value];
        }
    }
}
?>
