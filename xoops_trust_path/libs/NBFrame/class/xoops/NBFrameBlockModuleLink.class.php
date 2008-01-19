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
if(!class_exists('NBFrameBlockModuleLink')) {
    class NBFrameBlockModuleLinkHandler extends NBFrameObjectHandler {
        var $mTableName = 'block_module_link';
        var $mUseModuleTablePrefix = false;
        
        function insert($bid, $modules, $force=false) {
            $this->deleteBlock($bid);
            foreach($modules as $mid) {
                $object =& $this->create();
                $object->set('block_id', $bid);
                $object->set('module_id', $mid);
                $result = parent::insert($object, $force);
                unset($object);
                if (!$result) break;
            }
        }

        function deleteBlock($bid, $force=false) {
            $criteria =& new Criteria('block_id', $bid);
            return $this->deleteAll($criteria, $force);
        }

        function deleteModule($mid, $force=false) {
            $criteria =& new Criteria('Module_id', $mid);
            return $this->deleteAll($criteria, $force);
        }
    }
}
?>
