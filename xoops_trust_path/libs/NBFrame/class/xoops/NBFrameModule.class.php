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
if(!class_exists('NBFrameModuleHandler')) {
    class NBFrameModuleHandler extends NBFrameObjectHandler {
        var $mTableName = 'modules';
        var $mUseModuleTablePrefix = false;

        function &getByEnvironment(&$environment)
        {
            $dirName = $environment->mDirName;
            $criteria = new Criteria('dirname', $dirName);
            $objects = $this->getObjects($criteria);
            if (count($objects) > 0) {
                $object =& $objects[0];
            } else {
                $object = null;
            }
            return $object;
        }
    }
}
?>
