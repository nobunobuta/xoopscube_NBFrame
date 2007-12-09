<?php
NBFrame::using('Action');

class SimpleLinkDefaultAction extends NBFrameAction {
    function viewDefaultOp() {
        echo "Test Print for Main Action";
    }
}
