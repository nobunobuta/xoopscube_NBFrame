<?php
NBFrame::using('Action');

class SimpleDefaultAction extends NBFrameAction {
    function viewDefaultOp() {
        echo '<a href="./index.php?action=SimpleNext">Hello World</a>';
    }
}
