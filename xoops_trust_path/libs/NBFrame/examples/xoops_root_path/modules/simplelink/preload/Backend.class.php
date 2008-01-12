<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('Simplelink_Base_Backend')) {
    NBFrame::using('ActionFilter');
    class Simplelink_Base_Backend extends NBFrameActionFilter
    {
        function preBlockFilter()
        {
            $root=&XCube_Root::getSingleton();
            $root->mDelegateManager->add("Legacy_BackendAction.GetRSSItems", array($this, 'getRSS'));
        }

        function getRSS(&$items)
        {
            $linkHandler =& NBFrame::getHandler('SimpleLinkLink', $this->mEnvironment);
            $linkObjects =& $linkHandler->getObjects();
            foreach ($linkObjects as $linkObject) {
                $item = array (
                    'title'       => $linkObject->getVar('link_name'),
                    'link'        => $this->mEnvironment->mUrlBase,
                    'guid'        => $this->mEnvironment->mUrlBase,
                );
                $items[] = $item;
            }
        }
    }
}
?>