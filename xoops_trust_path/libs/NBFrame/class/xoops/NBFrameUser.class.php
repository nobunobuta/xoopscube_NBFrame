<?php
/**
 *
 * @package NBFrame
 * @version $Id: NBFrameBlock.class.php 1275 2008-01-22 14:52:17Z nobunobu $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if(!class_exists('NBFrameUser.')) {
    class NBFrameUser extends NBFrameObject
    {
        function prepare() {
            $this->setNameField('uname');
        }
    }

    class NBFrameUserHandler extends NBFrameObjectHandler {
        var $mTableName = 'users';
        var $mUseModuleTablePrefix = false;

    }
}
?>
