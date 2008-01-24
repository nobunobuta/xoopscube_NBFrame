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
if(!class_exists('NBFrameTplSourceHandler')) {
    class NBFrameTplSource extends NBFrameObject {
        function prepare() {
        }
    }
    class NBFrameTplSourceHandler extends NBFrameObjectHandler {
        var $mTableName = 'tplsource';
        var $mUseModuleTablePrefix = false;
        var $mKeys = array('tpl_id');
    }
}
?>
