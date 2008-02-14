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
if(!class_exists('NBFrameModuleHandler')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $mTableName = 'modules';
        var $mUseModuleTablePrefix = false;

        function &getByDirname($dirName) {
            $criteria = new Criteria('dirname', $dirName);
            $objects = $this->getObjects($criteria);
            if (count($objects) > 0) {
                $object =& $objects[0];
            } else {
                $object = null;
            }
            return $object;
        }

        function &getByEnvironment(&$environment) {
            if (is_object($environment) && ($dirName = $environment->getDirName())) {
                $object =& $this->getByDirname($dirName);
            } else {
                $object = null;
            }
            return $object;
        }
    }
}
?>
