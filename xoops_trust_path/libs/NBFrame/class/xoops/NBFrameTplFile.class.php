<?php
/**
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
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
                $id = $record->get('tpl_id');
                $tplSourceHandler =& NBFrame::getHandler('NBFrame.xoops.TplSource', $this->mEnvironment);
                if (!$tplSourceObject =& $tplSourceHandler->get($id)) {
                    $tplSourceObject =& $tplSourceHandler->create();
                    $tplSourceObject->set('tpl_id', $id);
                }
                $tplSourceObject->set('tpl_source', $record->get('tpl_source'));
                $result = $tplSourceHandler->insert($tplSourceObject, $force);
            }
            return $result;
        }
    }
}
?>
