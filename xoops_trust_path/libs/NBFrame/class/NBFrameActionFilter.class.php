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
if (!class_exists('XCube_ActionFilter')) exit();
if (!class_exists('NBFrameActionFilter')) {
    class NBFrameActionFilter extends XCube_ActionFilter
    {
        var $mEnvironment;
        function NBFrameActionFilter(&$controller, &$environment)
        {
            parent::XCube_ActionFilter($controller);
            $this->mEnvironment = $environment;
        }

    }
}
?>
