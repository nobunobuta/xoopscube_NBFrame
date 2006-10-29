<?php
NBFrame::using('Action');

class SimpleDefaultAction extends NBFrameAction {
    function viewDefaultOp() {
        echo "Test Print for Main Action";
    }
}
