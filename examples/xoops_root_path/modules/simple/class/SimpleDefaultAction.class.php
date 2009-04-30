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
NBFrame::using('Action');

class SimpleDefaultAction extends NBFrameAction {
    function viewDefaultOp() {
        echo '<a href="./index.php?action=SimpleNext">Hello World</a>';
    }
}
