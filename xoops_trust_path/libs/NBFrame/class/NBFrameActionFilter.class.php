<?php
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
