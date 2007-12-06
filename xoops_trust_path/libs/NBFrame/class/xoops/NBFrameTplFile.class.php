<?php
if(!class_exists('NBFrameTplFileHandler')) {
    class NBFrameTplFile extends NBFrameObject {
        function prepare() {
            $this->setVarRequired('tpl_desc', false);
            $this->setAttribute('tpl_source', '', XOBJ_DTYPE_TXTAREA);
        }
    }

    class NBFrameTplFileHandler extends NBFrameObjectHandler {
        var $mTableName = 'tplfile';
        var $mUseModuleTablePrefix = false;
        
        function insert(&$record, $force=false, $updateOnlyChanged=false) {
            if ($result = parent::insert($record, $force, $updateOnlyChanged)) {
                $id = $record->getVar('tpl_id');
                $tplSourceHandler =& NBFrame::getHandler('NBFrame.xoops.TplSource', $this->mEnvironment);
                if (!$tplSourceObject =& $tplSourceHandler->get($id)) {
                    $tplSourceObject =& $tplSourceHandler->create();
                    $tplSourceObject->setVar('tpl_id', $id);
                }
                $tplSourceObject->setVar('tpl_source', $record->getVar('tpl_source','n'));
                $result = $tplSourceHandler->insert($tplSourceObject);
            }
            return $result;
        }
    }
}
?>
